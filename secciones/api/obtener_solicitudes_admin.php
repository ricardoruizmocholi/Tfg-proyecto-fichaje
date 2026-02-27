<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idEmpresa = $_SESSION['empresa_activa'];

// Filtros opcionales
$filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : 'TODOS';
$filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'TODOS';
$filtroEmpleado = isset($_GET['empleado']) ? (int)$_GET['empleado'] : 0;

try {
    // ============================================
    // OBTENER SOLICITUDES CON FILTROS
    // ============================================
    $sql = "
        SELECT 
            S.id_solicitud,
            S.tipo_solicitud,
            S.fecha_inicio,
            S.fecha_fin,
            S.estado,
            S.motivo_rechazo,
            S.observaciones,
            S.creado_en,
            S.validado_en,
            U.nombre,
            U.apellidos,
            U.email,
            U.id_usuario,
            VALIDADOR.nombre as validador_nombre,
            VALIDADOR.apellidos as validador_apellidos,
            COUNT(D.id_detalle) as total_dias
        FROM SOLICITUDES_HORARIO S
        INNER JOIN USUARIO U ON S.id_usuario = U.id_usuario
        LEFT JOIN USUARIO VALIDADOR ON S.validado_por = VALIDADOR.id_usuario
        LEFT JOIN DETALLE_SOLICITUD_HORARIO D ON S.id_solicitud = D.id_solicitud
        WHERE S.id_empresa = :id_empresa
    ";

    $params = ['id_empresa' => $idEmpresa];

    // Aplicar filtro de estado
    if ($filtroEstado !== 'TODOS') {
        $sql .= " AND S.estado = :estado";
        $params['estado'] = $filtroEstado;
    }

    // Aplicar filtro de tipo
    if ($filtroTipo !== 'TODOS') {
        $sql .= " AND S.tipo_solicitud = :tipo";
        $params['tipo'] = $filtroTipo;
    }

    // Aplicar filtro de empleado
    if ($filtroEmpleado > 0) {
        $sql .= " AND S.id_usuario = :id_usuario";
        $params['id_usuario'] = $filtroEmpleado;
    }

    $sql .= " GROUP BY S.id_solicitud ORDER BY 
        CASE S.estado 
            WHEN 'PENDIENTE' THEN 1 
            WHEN 'RECHAZADO' THEN 2 
            WHEN 'APROBADO' THEN 3 
        END,
        S.creado_en DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================
    // OBTENER ESTADÍSTICAS
    // ============================================
    $sqlStats = "
        SELECT 
            estado,
            COUNT(*) as total
        FROM SOLICITUDES_HORARIO
        WHERE id_empresa = :id_empresa
        GROUP BY estado
    ";

    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute(['id_empresa' => $idEmpresa]);
    $stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

    $estadisticas = [
        'PENDIENTE' => 0,
        'APROBADO' => 0,
        'RECHAZADO' => 0
    ];

    foreach ($stats as $stat) {
        $estadisticas[$stat['estado']] = (int)$stat['total'];
    }

    // ============================================
    // OBTENER LISTA DE EMPLEADOS (para filtro)
    // ============================================
    $sqlEmpleados = "
        SELECT DISTINCT
            U.id_usuario,
            U.nombre,
            U.apellidos
        FROM USUARIO U
        INNER JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
        WHERE EU.id_empresa = :id_empresa 
        AND EU.activo = 1
        AND EU.admin = 0
        ORDER BY U.nombre, U.apellidos
    ";

    $stmtEmpleados = $pdo->prepare($sqlEmpleados);
    $stmtEmpleados->execute(['id_empresa' => $idEmpresa]);
    $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'solicitudes' => $solicitudes,
        'estadisticas' => $estadisticas,
        'empleados' => $empleados
    ]);

} catch (PDOException $e) {
    error_log("Error en obtener_solicitudes_admin: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener solicitudes'
    ]);
}
?>