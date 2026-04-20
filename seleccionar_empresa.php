<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario = $_SESSION['usuario'];
$empresas = $usuario['empresas'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FichajeApp — Seleccionar Empresa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* Ajustes específicos para el selector de empresas */
        .company-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 10px;
        }

        .btn-company {
            width: 100%;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .btn-company:hover {
            background: rgba(102, 126, 234, 0.12);
            border-color: var(--primary);
            transform: translateX(4px);
        }

        .company-info {
            display: flex;
            flex-direction: column;
        }

        .company-name {
            font-weight: 600;
            font-size: 15px;
        }

        .admin-badge {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            text-transform: uppercase;
            color: var(--accent);
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .arrow-icon {
            color: var(--muted);
            transition: transform 0.2s;
        }

        .btn-company:hover .arrow-icon {
            color: var(--primary);
            transform: translateX(3px);
        }

        .user-welcome {
            font-size: 13px;
            color: var(--primary-h);
            margin-bottom: 4px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<canvas id="hexCanvas"></canvas>
<div class="vignette"></div>

<div class="stage">
    <div class="card">
        <div class="logo-wrap">
            <div class="logo-icon">
                <img class="imagen_logo" src="img/fesol.png" alt="logo">
            </div>
            <div>
                <div class="logo-name">FesolCheck</div>
                <div class="logo-sub">Acceso Multicompany</div>
            </div>
        </div>

        <div class="user-welcome">Hola, <?= htmlspecialchars($usuario['nombre']) ?></div>
        <div class="card-title">Selecciona empresa</div>
        <div class="card-sub">Tu cuenta está vinculada a múltiples centros de trabajo.</div>

        <div class="company-list">
            <?php foreach ($empresas as $empresa): ?>
                <form action="usar_empresa.php" method="POST">
                    <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($empresa['id_empresa']) ?>">
                    <button type="submit" class="btn-company">
                        <div class="company-info">
                            <span class="company-name"><?= htmlspecialchars($empresa['nombre']) ?></span>
                            <?php if($empresa['admin']): ?>
                                <span class="admin-badge">Panel Administrador</span>
                            <?php else: ?>
                                <span class="admin-badge" style="color: var(--muted)">Empleado</span>
                            <?php endif; ?>
                        </div>
                        <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>

        <div class="forgot-link">
            <a href="login.php">← Volver al inicio de sesión</a>
        </div>

        <div class="card-footer">
            <div class="status-dot"></div>
            Selección de entorno segura
        </div>
    </div>
</div>

<script>
(function() {
    const canvas = document.getElementById('hexCanvas');
    const ctx    = canvas.getContext('2d');
    const HEX_SIZE = 36; const GAP = 5; const WAVE_RADIUS = 180; const WAVE_SPEED = 0.006;
    let W, H, cols, rows, hexes = [];
    let mouseX = -999, mouseY = -999;
    let time = 0;
    const COLOR_STROKE = 'rgba(102, 126, 234, 0.22)';
    const COLOR_GLOW   = 'rgba(102, 126, 234, ';
    const COLOR_ACCENT = 'rgba(0, 212, 170, ';

    function hexPoints(cx, cy, r) {
        const pts = [];
        for (let i = 0; i < 6; i++) {
            const a = Math.PI / 180 * (60 * i - 30);
            pts.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]);
        }
        return pts;
    }
    function buildGrid() {
        hexes = [];
        const col_w = HEX_SIZE * Math.sqrt(3) + GAP;
        const row_h = HEX_SIZE * 1.5 + GAP * 0.5;
        cols = Math.ceil(W / col_w) + 2; rows = Math.ceil(H / row_h) + 2;
        for (let r = -1; r < rows; r++) {
            for (let c = -1; c < cols; c++) {
                const cx = c * col_w + (r % 2 === 0 ? 0 : col_w * 0.5);
                const cy = r * row_h;
                hexes.push({ cx, cy, phase: Math.random() * Math.PI * 2 });
            }
        }
    }
    function resize() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
        buildGrid();
    }
    function draw() {
        ctx.clearRect(0, 0, W, H);
        time += WAVE_SPEED;
        for (const h of hexes) {
            const dx = h.cx - mouseX; const dy = h.cy - mouseY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            const wave = Math.sin(time + h.phase) * 0.5 + 0.5;
            const cursor = dist < WAVE_RADIUS ? Math.pow(1 - dist / WAVE_RADIUS, 2.5) : 0;
            const intensity = wave * 0.25 + cursor * 0.75;
            const scale = 1 + cursor * 0.12 + wave * 0.02;
            const r = HEX_SIZE * scale;
            const pts = hexPoints(h.cx, h.cy, r);
            ctx.beginPath(); ctx.moveTo(pts[0][0], pts[0][1]);
            for (let i = 1; i < 6; i++) ctx.lineTo(pts[i][0], pts[i][1]);
            ctx.closePath();
            if (intensity > 0.05) {
                const useAccent = cursor > 0.3;
                const alpha = intensity * (useAccent ? 0.18 : 0.09);
                ctx.fillStyle = useAccent ? COLOR_ACCENT + alpha + ')' : COLOR_GLOW + alpha + ')';
                ctx.fill();
            }
            const strokeAlpha = 0.12 + intensity * 0.25;
            ctx.strokeStyle = `rgba(102, 126, 234, ${strokeAlpha})`;
            ctx.lineWidth = 0.8 + cursor * 0.6;
            ctx.stroke();
        }
        requestAnimationFrame(draw);
    }
    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; });
    resize(); draw();
})();
</script>
</body>
</html>