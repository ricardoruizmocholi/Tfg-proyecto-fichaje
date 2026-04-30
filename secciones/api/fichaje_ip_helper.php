<?php
/*
 * fichaje_ip_helper.php — Utilidad de verificación de IP para fichaje
 * Define la función fichaje_ip_permitida($pdo, $idEmpresa).
 * Comprueba si la IP del cliente está en la lista blanca de EMPRESA_IPS_AUTORIZADAS.
 * Admite IPs exactas y prefijos de subred (ej. "192.168.1."). Localhost siempre permitido.
 * Uso: require_once __DIR__ . '/../api/fichaje_ip_helper.php';
 *      $ok = fichaje_ip_permitida($pdo, $idEmpresa);
 */

/**
 * Comprueba si la IP actual tiene permiso para fichar en la empresa.
 *
 * Lógica:
 *  1. Localhost → siempre permitido (desarrollo/pruebas).
 *  2. Si empresa.restringir_ip = 0 → acceso libre.
 *  3. Si = 1 → la IP debe estar en empresa_ips_autorizadas.
 *     Admite IP exacta ("192.168.1.50") o prefijo de subred ("192.168.1.").
 *
 * @param PDO $pdo
 * @param int $idEmpresa
 * @return bool
 */
function fichaje_ip_permitida(PDO $pdo, int $idEmpresa): bool
{
    $ipUsuario = $_SERVER['REMOTE_ADDR'] ?? '';

    // 1. Localhost siempre pasa (dev / pruebas)
    if ($ipUsuario === '127.0.0.1' || $ipUsuario === '::1') {
        return true;
    }

    // 2. Leer estado del toggle
    $stmtToggle = $pdo->prepare(
        "SELECT restringir_ip FROM empresa WHERE id_empresa = ? LIMIT 1"
    );
    $stmtToggle->execute([$idEmpresa]);
    $empresa = $stmtToggle->fetch(PDO::FETCH_ASSOC);

    // Empresa no encontrada o restricción desactivada → libre
    if (!$empresa || !(bool)$empresa['restringir_ip']) {
        return true;
    }

    // 3. Restricción activa: comprobar lista blanca
    $stmtIPs = $pdo->prepare(
        "SELECT ip_address FROM empresa_ips_autorizadas WHERE id_empresa = ?"
    );
    $stmtIPs->execute([$idEmpresa]);
    $ips = $stmtIPs->fetchAll(PDO::FETCH_COLUMN);

    if (empty($ips)) {
        // Lista vacía + restricción activa = nadie puede fichar
        return false;
    }

    foreach ($ips as $ip) {
        $ip = trim($ip);
        // IP exacta o coincidencia por prefijo de subred
        if ($ipUsuario === $ip || str_starts_with($ipUsuario, $ip)) {
            return true;
        }
    }

    return false;
}