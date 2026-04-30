<?php
/*
 * config_ip_api.php — Endpoint POST del módulo Config
 * Gestiona la configuración de restricción de fichaje por IP.
 * Acceso: solo admin. Acepta POST JSON con campo `accion`:
 *   "toggle"    → activa/desactiva la restricción global de IP para la empresa.
 *   "add_ip"    → añade una IP o prefijo de subred a EMPRESA_IPS_AUTORIZADAS.
 *   "delete_ip" → elimina una IP de la lista blanca.
 * Devuelve JSON {success, message}.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once '../../config.php';

// Solo admins
if (!isset($_SESSION['usuario']) || !$_SESSION['es_admin']) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$idEmpresa = (int)$_SESSION['empresa_activa'];
$body      = json_decode(file_get_contents('php://input'), true);

if (!isset($body['accion'])) {
    echo json_encode(['ok' => false, 'error' => 'Acción no especificada']);
    exit;
}

try {
    switch ($body['accion']) {

        // ── Activar / desactivar restricción ──────────────────
        case 'toggle':
            $valor = isset($body['activo']) ? (bool)$body['activo'] : false;
            $pdo->prepare("UPDATE empresa SET restringir_ip = ? WHERE id_empresa = ?")
                ->execute([$valor ? 1 : 0, $idEmpresa]);
            echo json_encode(['ok' => true]);
            break;

        // ── Añadir IP ──────────────────────────────────────────
        case 'add_ip':
            $ip       = trim($body['ip_address'] ?? '');
            $etiqueta = trim($body['etiqueta']   ?? '');

            // Validar: solo dígitos, puntos y dos puntos (IPv4 + IPv6 básico)
            if (!preg_match('/^[\d\.:a-fA-F]{3,45}$/', $ip)) {
                echo json_encode(['ok' => false, 'error' => 'Formato de IP no válido']);
                exit;
            }

            // Límite por seguridad: máx 50 IPs por empresa
            $count = $pdo->prepare(
                "SELECT COUNT(*) FROM empresa_ips_autorizadas WHERE id_empresa = ?"
            );
            $count->execute([$idEmpresa]);
            if ((int)$count->fetchColumn() >= 50) {
                echo json_encode(['ok' => false, 'error' => 'Límite de 50 IPs alcanzado']);
                exit;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO empresa_ips_autorizadas (id_empresa, ip_address, etiqueta)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE etiqueta = VALUES(etiqueta)"
            );
            $stmt->execute([
                $idEmpresa,
                $ip,
                $etiqueta !== '' ? substr($etiqueta, 0, 100) : null
            ]);

            $nuevoId = $pdo->lastInsertId();
            echo json_encode([
                'ok' => true,
                'ip' => [
                    'id'         => $nuevoId ?: null,
                    'ip_address' => $ip,
                    'etiqueta'   => $etiqueta ?: null
                ]
            ]);
            break;

        // ── Eliminar IP ────────────────────────────────────────
        case 'delete_ip':
            $id = (int)($body['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['ok' => false, 'error' => 'ID inválido']);
                exit;
            }
            // Verificar que la IP pertenece a esta empresa antes de borrar
            $pdo->prepare(
                "DELETE FROM empresa_ips_autorizadas
                 WHERE id = ? AND id_empresa = ?"
            )->execute([$id, $idEmpresa]);

            echo json_encode(['ok' => true]);
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Acción desconocida']);
    }

} catch (PDOException $e) {
    error_log('config_ip_api: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error de base de datos']);
}