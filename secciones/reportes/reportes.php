<!--
  reportes.php — Vista principal del módulo Reportes (admin)
  Incluida por panel.php (seccion=reportes&vista=generar). Solo admin.
  Permite seleccionar empleado, mes, año y tipo (mensual/anual) para generar un reporte.
  Previsualiza con reporte_preview.php, genera PDF con reporte_generar_pdf.php o
  reporte_anual_generar.php, exporta Excel con reporte_excel.php.
  Muestra historial de reportes generados con acciones de descarga y eliminación.
-->
<link rel="stylesheet" href="css/reportes.css">
<?php
// Sección de reportes - Generar y ver historial

$vista = $_GET['vista'] ?? 'generar';
$empresa   = $_SESSION['empresa_activa'];

// Obtener empleados de la empresa
$stmtEmpleados = $pdo->prepare("
    SELECT U.id_usuario, U.nombre, U.apellidos 
    FROM USUARIO U
    JOIN EMPRESA_USUARIO EU ON U.id_usuario = EU.id_usuario
    WHERE EU.id_empresa = ? AND EU.activo = 1
    ORDER BY U.nombre, U.apellidos
");
$stmtEmpleados->execute([$empresa]);
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

if ($vista === 'generar') {
    // Vista para generar nuevo reporte
    ?>
    <div class="section-header">
        <h2> Generar Reporte</h2>
        <p>Genera el listado resumen mensual del registro de jornada</p>
    </div>

    <div class="reportes-container">
        <div class="form-container">
            <form id="formGenerarReporte">
                <div class="form-section">
                    <h3> Datos del Reporte</h3>
                    
                    <div class="form-group">
                        <label>Empleado: *</label>
                        <select id="reporte_empleado" required>
                            <option value="">Seleccionar empleado</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id_usuario'] ?>">
                                    <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Mes: *</label>
                            <select id="reporte_mes" required>
                                <option value="">Seleccionar mes</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Año: *</label>
                            <select id="reporte_anio" required>
                                <option value="">Seleccionar año</option>
                                <?php 
                                $anioActual = date('Y');
                                for ($i = $anioActual - 2; $i <= $anioActual + 1; $i++): 
                                ?>
                                    <option value="<?= $i ?>" <?= $i == $anioActual ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tipo de Reporte: *</label>
                        <select id="reporte_tipo" required>
                            <option value="mensual">Listado Resumen Mensual de Registro de Jornada</option>
                            <option value="anual">Resumen anual de registros de Jornada</option>
                            
                        </select>
                    </div>
                </div>
                
                <div class="preview-section" id="previewSection" style="display:none;">
                    <h3> Vista Previa</h3>
                    <div id="previewContent"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="previsualizarReporte()">
                         Previsualizar
                    </button>
                    <button type="button" id="btnExportExcel" class="btn-pro btn-excel">Excel Export</button>
                    <button type="submit" class="btn-primary">
                         Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('formGenerarReporte').addEventListener('submit', function(e) {
        e.preventDefault();
        generarReportePDF();
    });

    function previsualizarReporte() {
        const empleadoId = document.getElementById('reporte_empleado').value;
        const mes = document.getElementById('reporte_mes').value;
        const anio = document.getElementById('reporte_anio').value;
        const tipo = document.getElementById('reporte_tipo').value;
        
        if (!empleadoId || !mes || !anio) {
            alert('⚠️ Completa todos los campos');
            return;
        }
        
        fetch('secciones/reportes/reporte_preview.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id_usuario: empleadoId,
                mes: mes,
                anio: anio,
                tipo: tipo
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                document.getElementById('previewSection').style.display = 'block';
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('❌ Error de conexión');
        });
    }

    document.getElementById('btnExportExcel').addEventListener('click', function() {
        const idUsuario = document.getElementById('reporte_empleado').value;
        const mes = document.getElementById('reporte_mes').value;
        const anio = document.getElementById('reporte_anio').value;

        if (!idUsuario || !mes || !anio) {
            alert('Por favor, selecciona todos los campos antes de exportar.');
            return;
        }

        // Redirigimos al archivo PHP que genera el Excel pasando los parámetros por GET
        window.location.href = `secciones/reportes/reporte_excel.php?id_usuario=${idUsuario}&mes=${mes}&anio=${anio}`;
    });

    function generarReportePDF() {
    // 1. Capturamos los valores
    const empleadoId = document.getElementById('reporte_empleado').value;
    const mes        = document.getElementById('reporte_mes').value;
    const anio       = document.getElementById('reporte_anio').value;
    const tipo       = document.getElementById('reporte_tipo').value; // 'mensual' o 'anual'
    
    // 2. Validación dinámica
    if (!empleadoId || !anio || (tipo === 'mensual' && !mes)) {
        alert('⚠️ Completa los campos necesarios (el mes es obligatorio para reportes mensuales)');
        return;
    }
    
    // 3. Configuración según el tipo de reporte
    // Si es anual, usamos el nuevo archivo. Si es mensual, el de siempre.
    let endpoint = (tipo === 'anual') 
        ? 'secciones/reportes/reporte_anual_generar.php' 
        : 'secciones/reportes/reporte_generar_pdf.php';

    // Preparamos los datos a enviar
    let datos = {
        id_usuario: empleadoId,
        anio: anio,
        tipo: tipo
    };

    // Solo añadimos el mes si el reporte es mensual
    if (tipo === 'mensual') {
        datos.mes = mes;
    }

    // 4. Interfaz de usuario (Botón)
    const btn = event.target;
    btn.disabled = true;
    const textoOriginal = btn.textContent;
    btn.textContent = '⏳ Generando...';
    
    // 5. Envío de datos
    fetch(endpoint, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reporte generado correctamente');
            
            // Abrir el PDF (algunos archivos devuelven url_pdf y otros url)
            const urlFinal = data.url_pdf || data.url;
            window.open(urlFinal, '_blank');
            
            // Ir al historial
            setTimeout(() => {
                window.location.href = 'panel.php?seccion=reportes&vista=historial';
            }, 1000);
        } else {
            alert('❌ Error: ' + data.message);
            btn.disabled = false;
            btn.textContent = textoOriginal;
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Error de conexión');
        btn.disabled = false;
        btn.textContent = textoOriginal;
    });
}
    </script>

    <?php
} else {
    // Vista de historial de reportes
    ?>
    <div class="section-header">
        <h2> Historial de Reportes</h2>
        <p>Consulta, visualiza y descarga los reportes generados anteriormente</p>
    </div>

    <div class="filter-container">
        <form method="GET" action="panel.php" class="filter-form">
            <input type="hidden" name="seccion" value="reportes">
            <input type="hidden" name="vista" value="historial">
            
            <div class="filter-group">
                <label>Empleado:</label>
                <select name="empleado" onchange="this.form.submit()">
                    <option value="">Todos los empleados</option>
                    <?php 
                    $filtroEmpleado = $_GET['empleado'] ?? '';
                    foreach ($empleados as $emp): 
                    ?>
                        <option value="<?= $emp['id_usuario'] ?>" <?= $filtroEmpleado == $emp['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Tipo de Reporte:</label>
                <select name="tipo" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <option value="registro_jornada" <?= ($_GET['tipo'] ?? '') == 'registro_jornada' ? 'selected' : '' ?>>
                         Registro de Jornada
                    </option>
                    <option value="horas_trabajadas_anuales" <?= ($_GET['tipo'] ?? '') == 'horas_trabajadas_anuales' ? 'selected' : '' ?>>
                         Resumen anual de registros de Jornada
                    </option>
                   
                </select>
            </div>
            
            <div class="filter-group">
                <label>Mes:</label>
                <select name="mes" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php 
                    $filtroMes = $_GET['mes'] ?? '';
                    $mesesFiltro = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    for ($i = 1; $i <= 12; $i++): 
                    ?>
                        <option value="<?= $i ?>" <?= $filtroMes == $i ? 'selected' : '' ?>><?= $mesesFiltro[$i] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Año:</label>
                <select name="anio" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php 
                    $filtroAnio = $_GET['anio'] ?? '';
                    $anioActual = date('Y');
                    for ($i = $anioActual; $i >= $anioActual - 3; $i--): 
                    ?>
                        <option value="<?= $i ?>" <?= $filtroAnio == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <a href="panel.php?seccion=reportes&vista=generar" class="btn-success"> Generar Nuevo Reporte</a>
        </form>
    </div>

    <?php
    // Consultar reportes con filtros
    $sql = "SELECT 
        R.*,
        U.nombre,
        U.apellidos,
        U.foto_perfil,
        UG.nombre as generado_nombre,
        UG.apellidos as generado_apellidos
    FROM REPORTES R
    JOIN USUARIO U ON R.id_usuario = U.id_usuario
    LEFT JOIN USUARIO UG ON R.generado_por = UG.id_usuario
    WHERE R.id_empresa = ?";
    
    $params = [$empresa];
    
    if (!empty($_GET['empleado'])) {
        $sql .= " AND R.id_usuario = ?";
        $params[] = $_GET['empleado'];
    }
    
    if (!empty($_GET['tipo'])) {
        $sql .= " AND R.tipo_reporte = ?";
        $params[] = $_GET['tipo'];
    }
    
    if (!empty($_GET['mes'])) {
        $sql .= " AND R.mes = ?";
        $params[] = $_GET['mes'];
    }
    
    if (!empty($_GET['anio'])) {
        $sql .= " AND R.anio = ?";
        $params[] = $_GET['anio'];
    }
    
    $sql .= " ORDER BY R.fecha_generacion DESC LIMIT 100";
    
    $stmtReportes = $pdo->prepare($sql);
    $stmtReportes->execute($params);
    $reportes = $stmtReportes->fetchAll(PDO::FETCH_ASSOC);
    
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $tipos = [
        'registro_jornada' => ' Registro de Jornada',
        'horas_trabajadas_anuales' => ' Registro de Jornada anual',
        
    ];
    ?>

    <!-- Estadísticas -->
    <div class="stats-reportes">
        <div class="stat-reporte">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></div>
            <div class="stat-info">
                <div class="stat-label">Total Reportes</div>
                <div class="stat-value"><?= count($reportes) ?></div>
            </div>
        </div>
        <div class="stat-reporte">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></div>
            <div class="stat-info">
                <div class="stat-label">Este Mes</div>
                <div class="stat-value">
                    <?= count(array_filter($reportes, fn($r) => date('Y-m', strtotime($r['fecha_generacion'])) == date('Y-m'))) ?>
                </div>
            </div>
        </div>
        <div class="stat-reporte">
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></div>
            <div class="stat-info">
                <div class="stat-label">Empleados</div>
                <div class="stat-value">
                    <?= count(array_unique(array_column($reportes, 'id_usuario'))) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($reportes) === 0): ?>
        <div class="no-results">
            <div class="no-results-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z"/></svg></div>
            <h3>No hay reportes generados</h3>
            <p>No se encontraron reportes con los filtros aplicados</p>
            <a href="panel.php?seccion=reportes&vista=generar" class="btn-primary" style="margin-top:20px;display:inline-block;text-decoration:none;">
                 Generar Primer Reporte
            </a>
        </div>
    <?php else: ?>
        <div class="reportes-grid">
            <?php foreach ($reportes as $rep): 
                // Verificar si el archivo existe
                $archivoExiste = file_exists(__DIR__ . '/../' . $rep['ruta_archivo']);
            ?>
                <div class="reporte-card <?= $archivoExiste ? '' : 'reporte-missing' ?>">
                    <div class="reporte-header">
                        <div class="reporte-tipo">
                            <?= $tipos[$rep['tipo_reporte']] ?? $rep['tipo_reporte'] ?>
                        </div>
                        <?php if (!$archivoExiste): ?>
                            <span class="badge-error" title="Archivo no encontrado"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em" style="vertical-align:-0.125em"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg> Falta PDF</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="reporte-empleado">
                        <img src="<?= htmlspecialchars($rep['foto_perfil']) ?>" 
                             alt="Foto" 
                             class="reporte-foto"
                             onerror="this.src='secciones/uploads/perfil_default.jpg'">
                        <div class="reporte-empleado-info">
                            <div class="reporte-empleado-nombre">
                                <?= htmlspecialchars($rep['nombre'] . ' ' . $rep['apellidos']) ?>
                            </div>
                            <div class="reporte-periodo">
                                 <?= $meses[$rep['mes']] ?> <?= $rep['anio'] ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="reporte-meta">
                        <div class="reporte-meta-item">
                            <span class="meta-label">Generado por:</span>
                            <span class="meta-value"><?= htmlspecialchars($rep['generado_nombre'] . ' ' . $rep['generado_apellidos']) ?></span>
                        </div>
                        <div class="reporte-meta-item">
                            <span class="meta-label">Fecha:</span>
                            <span class="meta-value"><?= date('d/m/Y H:i', strtotime($rep['fecha_generacion'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="reporte-actions">
                        <?php if ($archivoExiste): ?>
                            <button class="btn-reporte btn-abrir" 
                                    onclick="abrirReporte(<?= $rep['id_reporte'] ?>)" 
                                    title="Abrir PDF en nueva pestaña">
                                <span class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg></span>
                                <span class="btn-text">Abrir</span>
                            </button>
                            <button class="btn-reporte btn-descargar" 
                                    onclick="descargarReporte(<?= $rep['id_reporte'] ?>)" 
                                    title="Descargar PDF">
                                <span class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></span>
                                <span class="btn-text">Descargar</span>
                            </button>
                        <?php else: ?>
                            <button class="btn-reporte btn-disabled" disabled title="Archivo no disponible">
                                <span class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                                <span class="btn-text">No disponible</span>
                            </button>
                        <?php endif; ?>
                        <button class="btn-reporte btn-eliminar" 
                                onclick="eliminarReporte(<?= $rep['id_reporte'] ?>)" 
                                title="Eliminar reporte">
                            <span class="btn-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="1em" height="1em"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    

    <script>
    function abrirReporte(idReporte) {
        console.log('Abriendo reporte ID:', idReporte);
        window.open(`secciones/reportes/reporte_descarga.php?id=${idReporte}&preview=1`, '_blank');
    }

    function descargarReporte(idReporte) {
        console.log('Descargando reporte ID:', idReporte);
        window.open(`secciones/reportes/reporte_descarga.php?id=${idReporte}`, '_blank');
    }

    function eliminarReporte(idReporte) {
        if (!confirm('⚠️ ¿Eliminar este reporte?\n\nEsta acción no se puede deshacer.')) return;
        
        console.log('Eliminando reporte ID:', idReporte);
        
        fetch('secciones/reportes/reporte_eliminar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id_reporte: idReporte})
        })
        .then(res => res.json())
        .then(data => {
            console.log('Respuesta:', data);
            if (data.success) {
                alert('✅ Reporte eliminado correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error completo:', err);
            alert('❌ Error de conexión');
        });
    }
    </script>
    <?php
}
?>

