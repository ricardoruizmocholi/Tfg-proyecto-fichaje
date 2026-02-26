<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$idSolicitud = isset($_GET['id_solicitud']) ? (int)$_GET['id_solicitud'] : 0;

if ($idSolicitud === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de solicitud inválido']);
    exit;
}

try {
    // ============================================
    // OBTENER INFORMACIÓN DE LA SOLICITUD
    // ============================================
    $sqlSolicitud = "
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
            VALIDADOR.apellidos as validador_apellidos
        FROM SOLICITUDES_HORARIO S
        INNER JOIN USUARIO U ON S.id_usuario = U.id_usuario
        LEFT JOIN USUARIO VALIDADOR ON S.validado_por = VALIDADOR.id_usuario
        WHERE S.id_solicitud = :id_solicitud
        AND S.id_empresa = :id_empresa
    ";

    $stmtSolicitud = $pdo->prepare($sqlSolicitud);
    $stmtSolicitud->execute([
        'id_solicitud' => $idSolicitud,
        'id_empresa' => $_SESSION['empresa_activa']
    ]);

    $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada']);
        exit;
    }

    // ============================================
    // OBTENER DETALLE DÍA A DÍA
    // ============================================
    $sqlDetalle = "
        SELECT 
            fecha,
            tipo_jornada,
            hora_inicio,
            hora_fin,
            horas_totales,
            observaciones
        FROM DETALLE_SOLICITUD_HORARIO
        WHERE id_solicitud = :id_solicitud
        ORDER BY fecha ASC
    ";

    $stmtDetalle = $pdo->prepare($sqlDetalle);
    $stmtDetalle->execute(['id_solicitud' => $idSolicitud]);
    $detalleDias = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $totalHoras = 0;
    $diasPorTipo = [
        'TRABAJO' => 0,
        'VACACIONES' => 0,
        'MEDICO' => 0,
        'LIBRE' => 0
    ];

    foreach ($detalleDias as $dia) {
        $totalHoras += (float)$dia['horas_totales'];
        if (isset($diasPorTipo[$dia['tipo_jornada']])) {
            $diasPorTipo[$dia['tipo_jornada']]++;
        }
    }

    echo json_encode([
        'success' => true,
        'solicitud' => $solicitud,
        'detalle_dias' => $detalleDias,
        'estadisticas' => [
            'total_dias' => count($detalleDias),
            'total_horas' => $totalHoras,
            'dias_por_tipo' => $diasPorTipo
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en obtener_detalle_solicitud: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalle de la solicitud'
    ]);
}
?>