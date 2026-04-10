<?php
// secciones/api/obtener_resumen_empleado.php
// Devuelve vacaciones restantes + horas extra mes/año para el empleado (o cualquiera si es admin)

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Admin puede consultar cualquier empleado; empleado solo se ve a sí mismo
$idUsuario = (isset($_GET['id_usuario']) && $_SESSION['es_admin'])
    ? (int)$_GET['id_usuario']
    : (int)$_SESSION['usuario']['id'];

$idEmpresa = (int)$_SESSION['empresa_activa'];
$anio      = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$mes       = isset($_GET['mes'])  ? (int)$_GET['mes']  : (int)date('n');

try {
    // ── Datos del empleado ────────────────────────────────────
    $stmtUser = $pdo->prepare("
        SELECT u.nombre, u.apellidos,
               u.tipo_contrato, u.horas_contrato_mes,
               u.vacaciones_totales_anuales, u.horas_extra_anuales_limite
        FROM usuario u
        WHERE u.id_usuario = ? AND u.activo = 1
    ");
    $stmtUser->execute([$idUsuario]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $horasContratoMes  = (int)$user['horas_contrato_mes'];        // ej: 160
    $vacTotales        = (float)$user['vacaciones_totales_anuales']; // ej: 30
    $limiteHorasExtra  = (int)$user['horas_extra_anuales_limite'];  // ej: 80

    // ── 1. VACACIONES ─────────────────────────────────────────
    // Días de vacaciones ya aprobados en HORARIOS (tipo_jornada = VACACIONES)
    $stmtVacUsadas = $pdo->prepare("
        SELECT COUNT(*) 
        FROM horarios
        WHERE id_usuario = ? AND id_empresa = ?
          AND tipo_jornada = 'VACACIONES'
          AND YEAR(fecha) = ?
    ");
    $stmtVacUsadas->execute([$idUsuario, $idEmpresa, $anio]);
    $diasUsados = (int)$stmtVacUsadas->fetchColumn();

    // Días de vacaciones en solicitudes PENDIENTES (aún no aprobadas)
    $stmtVacPendientes = $pdo->prepare("
        SELECT COUNT(dsh.id_detalle)
        FROM solicitudes_horario sh
        JOIN detalle_solicitud_horario dsh ON sh.id_solicitud = dsh.id_solicitud
        WHERE sh.id_usuario = ? AND sh.id_empresa = ?
          AND sh.estado = 'PENDIENTE'
          AND dsh.tipo_jornada = 'VACACIONES'
          AND YEAR(dsh.fecha) = ?
    ");
    $stmtVacPendientes->execute([$idUsuario, $idEmpresa, $anio]);
    $diasPendientes = (int)$stmtVacPendientes->fetchColumn();

    $diasRestantes   = max(0, $vacTotales - $diasUsados);
    $diasDisponibles = max(0, $diasRestantes - $diasPendientes);

    // ── 2. HORAS EXTRA DEL MES ────────────────────────────────
    // Suma todas las horas fichadas (ambos tramos si hay jornada partida)
    $stmtHorasMes = $pdo->prepare("
        SELECT COALESCE(SUM(
            CASE 
                WHEN hora_entrada IS NOT NULL AND hora_salida IS NOT NULL
                THEN TIME_TO_SEC(TIMEDIFF(hora_salida, hora_entrada)) / 3600.0
                ELSE 0
            END
        ), 0)
        FROM fichaje
        WHERE id_usuario = ?
          AND MONTH(fecha) = ? AND YEAR(fecha) = ?
    ");
    $stmtHorasMes->execute([$idUsuario, $mes, $anio]);
    $horasFichadasMes = round((float)$stmtHorasMes->fetchColumn(), 2);

    $diferenciaMes    = $horasFichadasMes - $horasContratoMes;
    $horasExtraMes    = max(0, $diferenciaMes);
    $excedeMes        = $diferenciaMes > 0;

    // ── 3. HORAS EXTRA ACUMULADAS EN EL AÑO ──────────────────
    // Leer de la tabla horas_extra (se actualiza al aprobar fichajes)
    $stmtExtraAnio = $pdo->prepare("
        SELECT COALESCE(horas_acumuladas, 0)
        FROM horas_extra
        WHERE id_usuario = ? AND anio = ?
    ");
    $stmtExtraAnio->execute([$idUsuario, $anio]);
    $horasExtraAnio = round((float)$stmtExtraAnio->fetchColumn(), 2);

    // Si no hay registro en horas_extra, calcularlo en tiempo real sumando por mes
    if ($horasExtraAnio == 0) {
        $stmtCalcExtra = $pdo->prepare("
            SELECT COALESCE(SUM(extra_mes), 0)
            FROM (
                SELECT 
                    GREATEST(
                        SUM(CASE 
                            WHEN hora_entrada IS NOT NULL AND hora_salida IS NOT NULL
                            THEN TIME_TO_SEC(TIMEDIFF(hora_salida, hora_entrada)) / 3600.0
                            ELSE 0
                        END) - ?,
                        0
                    ) AS extra_mes
                FROM fichaje
                WHERE id_usuario = ? AND YEAR(fecha) = ?
                GROUP BY MONTH(fecha)
            ) meses
        ");
        $stmtCalcExtra->execute([$horasContratoMes, $idUsuario, $anio]);
        $horasExtraAnio = round((float)$stmtCalcExtra->fetchColumn(), 2);
    }

    $excedeAnioLimite = $horasExtraAnio > $limiteHorasExtra;
    $margenExtra      = max(0, $limiteHorasExtra - $horasExtraAnio);

    echo json_encode([
        'success' => true,
        'usuario' => [
            'nombre'           => $user['nombre'] . ' ' . $user['apellidos'],
            'tipo_contrato'    => $user['tipo_contrato'],
            'horas_contrato'   => $horasContratoMes,
            'vac_anuales'      => $vacTotales,
            'limite_extra'     => $limiteHorasExtra,
        ],
        'vacaciones' => [
            'anio'           => $anio,
            'total_anuales'  => $vacTotales,
            'dias_usados'    => $diasUsados,
            'dias_pendientes'=> $diasPendientes,
            'dias_restantes' => $diasRestantes,
            'dias_disponibles'=> $diasDisponibles,
            'porcentaje_usado'=> $vacTotales > 0 ? round(($diasUsados / $vacTotales) * 100, 1) : 0,
        ],
        'horas_mes' => [
            'mes'              => $mes,
            'anio'             => $anio,
            'horas_contrato'   => $horasContratoMes,
            'horas_fichadas'   => $horasFichadasMes,
            'horas_extra'      => $horasExtraMes,
            'excede'           => $excedeMes,
            'diferencia'       => round($diferenciaMes, 2),
        ],
        'horas_extra_anio' => [
            'anio'             => $anio,
            'acumuladas'       => $horasExtraAnio,
            'limite'           => $limiteHorasExtra,
            'margen'           => $margenExtra,
            'excede_limite'    => $excedeAnioLimite,
            'porcentaje'       => round(($horasExtraAnio / $limiteHorasExtra) * 100, 1),
        ],
    ]);

} catch (Exception $e) {
    error_log('Error obtener_resumen_empleado: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>