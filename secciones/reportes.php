
<link rel="stylesheet" href="css/reportes.css">
<?php
// Sección de reportes - Generar y ver historial

$vista = $_GET['vista'] ?? 'generar';

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
        
        fetch('secciones/reporte_preview.php', {
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
        window.location.href = `secciones/reporte_excel.php?id_usuario=${idUsuario}&mes=${mes}&anio=${anio}`;
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
        ? 'secciones/reporte_anual_generar.php' 
        : 'secciones/reporte_generar_pdf.php';

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
            <div class="stat-icon"></div>
            <div class="stat-info">
                <div class="stat-label">Total Reportes</div>
                <div class="stat-value"><?= count($reportes) ?></div>
            </div>
        </div>
        <div class="stat-reporte">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <div class="stat-label">Este Mes</div>
                <div class="stat-value">
                    <?= count(array_filter($reportes, fn($r) => date('Y-m', strtotime($r['fecha_generacion'])) == date('Y-m'))) ?>
                </div>
            </div>
        </div>
        <div class="stat-reporte">
            <div class="stat-icon"></div>
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
            <div class="no-results-icon">📭</div>
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
                            <span class="badge-error" title="Archivo no encontrado">⚠️ Falta PDF</span>
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
                                <span class="btn-icon"></span>
                                <span class="btn-text">Abrir</span>
                            </button>
                            <button class="btn-reporte btn-descargar" 
                                    onclick="descargarReporte(<?= $rep['id_reporte'] ?>)" 
                                    title="Descargar PDF">
                                <span class="btn-icon"></span>
                                <span class="btn-text">Descargar</span>
                            </button>
                        <?php else: ?>
                            <button class="btn-reporte btn-disabled" disabled title="Archivo no disponible">
                                <span class="btn-icon">❌</span>
                                <span class="btn-text">No disponible</span>
                            </button>
                        <?php endif; ?>
                        <button class="btn-reporte btn-eliminar" 
                                onclick="eliminarReporte(<?= $rep['id_reporte'] ?>)" 
                                title="Eliminar reporte">
                            <span class="btn-icon">🗑️</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    

    <script>
    function abrirReporte(idReporte) {
        console.log('Abriendo reporte ID:', idReporte);
        window.open(`secciones/reporte_descargar.php?id=${idReporte}&preview=1`, '_blank');
    }

    function descargarReporte(idReporte) {
        console.log('Descargando reporte ID:', idReporte);
        window.open(`secciones/reporte_descargar.php?id=${idReporte}`, '_blank');
    }

    function eliminarReporte(idReporte) {
        if (!confirm('⚠️ ¿Eliminar este reporte?\n\nEsta acción no se puede deshacer.')) return;
        
        console.log('Eliminando reporte ID:', idReporte);
        
        fetch('secciones/reporte_eliminar.php', {
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

