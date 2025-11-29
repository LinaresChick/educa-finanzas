<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) { header("Location: index.php?controller=Auth&action=acceso_denegado"); exit; } ?>

<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero - Educa Finanzas</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom styles removed -->
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../views/templates/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom report-header">
                <div class="report-title w-100 w-md-auto mb-2 mb-md-0 d-flex align-items-center">
                    <!-- TABS encajadas arriba -->
                    <ul class="nav nav-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-financiero" data-bs-toggle="tab" data-bs-target="#pane-financiero" type="button" role="tab" aria-controls="pane-financiero" aria-selected="true">Reporte Financiero</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-constancias" data-bs-toggle="tab" data-bs-target="#pane-constancias" type="button" role="tab" aria-controls="pane-constancias" aria-selected="false">Reporte Constancias</button>
                        </li>
                    </ul>
                </div>

                
            </div>

            
            <!-- Tab panes -->
            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="pane-financiero" role="tabpanel" aria-labelledby="tab-financiero">
                <!-- FINANCIERO PANEL START -->

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="card shadow-sm mb-4 filters-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php" id="formFiltros">
                        <input type="hidden" name="controller" value="Reporte">
                        <input type="hidden" name="action" value="financiero">
                        
                        <div class="row g-3">
                            <!-- Periodo -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Periodo</label>
                                <select name="periodo" class="form-select" id="selectPeriodo">
                                    <option value="semanal" <?= ($periodo ?? 'mensual') == 'semanal' ? 'selected' : '' ?>>Semanal</option>
                                    <option value="mensual" <?= ($periodo ?? 'mensual') == 'mensual' ? 'selected' : '' ?>>Mensual</option>
                                    <option value="anual" <?= ($periodo ?? 'mensual') == 'anual' ? 'selected' : '' ?>>Anual</option>
                                </select>
                            </div>

                            <!-- Fecha Inicio -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                            </div>

                            <!-- Fecha Fin -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Fin</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
                            </div>

                            <!-- Sección -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Sección/Aula</label>
                                <select name="id_seccion" class="form-select">
                                    <option value="">Todas las secciones</option>
                                    <?php if (!empty($secciones)): foreach ($secciones as $sec): ?>
                                        <option value="<?= $sec['id_seccion'] ?>" <?= (!empty($filtros['id_seccion']) && $filtros['id_seccion'] == $sec['id_seccion']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sec['nombre'] ?? '') ?> - <?= htmlspecialchars($sec['grado'] ?? '') ?> <?= htmlspecialchars($sec['nivel'] ?? '') ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <!-- Grado -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Grado</label>
                                <select name="grado" class="form-select">
                                    <option value="">Todos los grados</option>
                                    <?php if (!empty($grados)): foreach ($grados as $grado): ?>
                                        <option value="<?= htmlspecialchars($grado) ?>" <?= (!empty($filtros['grado']) && $filtros['grado'] == $grado) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($grado) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <!-- Nivel -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Nivel</label>
                                <select name="nivel" class="form-select">
                                    <option value="">Todos los niveles</option>
                                    <?php if (!empty($niveles)): foreach ($niveles as $nivel): ?>
                                        <option value="<?= htmlspecialchars($nivel) ?>" <?= (!empty($filtros['nivel']) && $filtros['nivel'] == $nivel) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nivel) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <a href="index.php?controller=Reporte&action=financiero" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </a>
                             
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Download controls for Financiero (inside financiero tab) -->
                <div class="mb-4">
                    <div style="background:#ffffff;padding:16px;border-radius:8px;border:1px solid #e6e6e6;">
                        <h6 style="margin:0 0 10px 0;font-weight:700;color:#155724;">📊 Descarga de Reportes Financieros</h6>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                           <div style="display:flex;gap:8px;align-items:center;">
    <label style="margin:0;font-weight:600;">Fecha:</label>

    <input type="date" id="fecha_financiero" class="form-control" style="width:160px;">

    <button class="btn btn-success" onclick="(function(){
        const fecha = document.getElementById('fecha_financiero').value;
        if(!fecha){
            alert('Seleccione una fecha');
            return;
        }

        const params = new URLSearchParams(window.location.search);
        params.set('controller','Reporte');
        params.set('action','exportar');
        params.set('tipo','financiero');
        params.set('granularidad','diario');
        params.set('fecha_inicio', fecha);
        params.set('fecha_fin', fecha);

        const idSec = document.querySelector('select[name=&quot;id_seccion&quot;]')?.value || '';
        const grado = document.querySelector('select[name=&quot;grado&quot;]')?.value || '';
        const nivel = document.querySelector('select[name=&quot;nivel&quot;]')?.value || '';

        if(idSec) params.set('id_seccion', idSec);
        if(grado) params.set('grado', grado);
        if(nivel) params.set('nivel', nivel);

        window.location.href = 'index.php?' + params.toString();
    })(); return false;">📅 Descargar</button>
</div>


                            <div style="display:flex;gap:8px;align-items:center;">
                                <select id="mes_financiero" class="form-select" style="width:150px;">
                                    <option value="">Selecciona Mes</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                                <select id="anio_financiero" class="form-select" style="width:110px;">
                                    <?php $y = date('Y'); for ($i = $y; $i >= $y-5; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button class="btn btn-primary" onclick="(function(){
                                    const mes = document.getElementById('mes_financiero').value;
                                    const anio = document.getElementById('anio_financiero').value;
                                    if (!mes) { alert('Seleccione un mes'); return; }
                                    const params = new URLSearchParams(window.location.search);
                                    params.set('controller','Reporte'); params.set('action','exportar'); params.set('tipo','financiero'); params.set('granularidad','mes');
                                    // set month range: first and last day of selected month
                                    const inicio = anio + '-' + mes + '-01';
                                    const fin = new Date(anio, parseInt(mes,10), 0).toISOString().split('T')[0];
                                    params.set('fecha_inicio', inicio); params.set('fecha_fin', fin);
                                    window.location.href = 'index.php?'+params.toString();
                                })(); return false;">📆 Descargar Mes</button>
                            </div>

                            <div>
                                <select id="anio_financiero_anio" class="form-select" style="width:110px;display:inline-block;">
                                    <?php $y = date('Y'); for ($i = $y; $i >= $y-5; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button class="btn btn-warning" onclick="(function(){ const anio = document.getElementById('anio_financiero_anio').value; const params = new URLSearchParams(window.location.search); params.set('controller','Reporte'); params.set('action','exportar'); params.set('tipo','financiero'); params.set('granularidad','anio'); params.set('fecha_inicio', anio+'-01-01'); params.set('fecha_fin', anio+'-12-31'); window.location.href = 'index.php?'+params.toString(); })(); return false;">🗓️ Descargar Año</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Resumen -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-cash-stack"></i> Total Ingresos</h6>
                            <p class="card-text display-6">
                                S/ <?= number_format(!empty($estadisticas) ? ($estadisticas['total_periodo'] ?? 0) : 0, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-receipt"></i> Total Transacciones</h6>
                            <p class="card-text display-6">
                                <?= !empty($estadisticas) ? ($estadisticas['total_transacciones'] ?? 0) : 0 ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-people"></i> Estudiantes</h6>
                            <p class="card-text display-6">
                                <?= !empty($estadisticas) ? ($estadisticas['total_estudiantes'] ?? 0) : 0 ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3 shadow-sm summary-card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-calculator"></i> Promedio</h6>
                            <p class="card-text display-6">
                                S/ <?= number_format(!empty($estadisticas) ? ($estadisticas['promedio_transaccion'] ?? 0) : 0, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAFICOS -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-bar-chart-line"></i> Ingresos por Período</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoIngresos" height="80"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-pie-chart"></i> Métodos de Pago</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoMetodosPago" height="160"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                            <!-- FINANCIERO PANEL END -->
                            </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0"><i class="bi bi-graph-up"></i> Tendencia de Pagos</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="graficoTendencia" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            

            <!-- Tabla de datos por período -->
            <!-- CONSTANCIAS TAB PANE -->
            <div class="tab-pane fade" id="pane-constancias" role="tabpanel" aria-labelledby="tab-constancias">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 style="margin:0 0 10px 0;font-weight:700;color:#3a0ca3;">📄 Descarga de Reporte Constancias</h6>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                            <div style="display:flex;gap:8px;align-items:center;">
                                <label style="margin:0;font-weight:600;">Rango:</label>
                                <input type="date" id="fecha_inicio_const" class="form-control" style="width:150px;" />
                                <input type="date" id="fecha_fin_const" class="form-control" style="width:150px;" />
                            </div>
                            <button class="btn btn-dark" onclick="exportarTipoGranular('constancias','semanal','#fecha_inicio_const','#fecha_fin_const'); return false;">📅 Descargar Semana</button>

                            <div style="display:flex;gap:8px;align-items:center;">
                                <select id="mes_constancias" class="form-select" style="width:150px;">
                                    <option value="">Selecciona Mes</option>
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                                <select id="anio_constancias" class="form-select" style="width:110px;">
                                    <?php $y = date('Y'); for ($i = $y; $i >= $y-5; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button class="btn btn-primary" onclick="(function(){ const mes=document.getElementById('mes_constancias').value; const anio=document.getElementById('anio_constancias').value; if(!mes){alert('Seleccione un mes');return;} const params=new URLSearchParams(window.location.search); params.set('controller','Reporte'); params.set('action','exportar'); params.set('tipo','constancias'); params.set('granularidad','mes'); const inicio=anio+'-'+mes+'-01'; const fin=new Date(anio,parseInt(mes,10),0).toISOString().split('T')[0]; params.set('fecha_inicio',inicio); params.set('fecha_fin',fin); window.location.href='index.php?'+params.toString(); })(); return false;">📆 Descargar Mes</button>
                            </div>

                            <div>
                                <select id="anio_constancias_anio" class="form-select" style="width:110px;display:inline-block;">
                                    <?php $y = date('Y'); for ($i = $y; $i >= $y-5; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button class="btn btn-secondary" onclick="(function(){ const anio=document.getElementById('anio_constancias_anio').value; const params=new URLSearchParams(window.location.search); params.set('controller','Reporte'); params.set('action','exportar'); params.set('tipo','constancias'); params.set('granularidad','anio'); params.set('fecha_inicio',anio+'-01-01'); params.set('fecha_fin',anio+'-12-31'); window.location.href='index.php?'+params.toString(); })(); return false;">🗓️ Descargar Año</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="bi bi-table"></i> Detalle Financiero por Período</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaFinanciero" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Período</th>
                                    <th>Transacciones</th>
                                    <th>Total Ingresos</th>
                                    <th>Efectivo</th>
                                    <th>Transferencia</th>
                                    <th>Tarjeta</th>
                                    <th>Mensualidad</th>
                                    <th>Matrícula</th>
                                    <th>Otros</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reporte)): ?>
                                    <?php foreach ($reporte as $periodo): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($periodo['periodo'] ?? '') ?></strong></td>
                                            <td><?= htmlspecialchars($periodo['total_pagos'] ?? 0) ?></td>
                                            <td class="text-success fw-bold">S/ <?= number_format($periodo['total_ingresos'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format($periodo['monto_efectivo'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format($periodo['monto_transferencia'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format($periodo['monto_tarjeta'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format($periodo['monto_mensualidad'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format($periodo['monto_matricula'] ?? 0, 2) ?></td>
                                            <td>S/ <?= number_format((
                                                ($periodo['monto_otro'] ?? 0) +
                                                ($periodo['monto_material'] ?? 0) +
                                                ($periodo['monto_uniforme'] ?? 0) +
                                                ($periodo['monto_actividad'] ?? 0)
                                            ), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary fw-bold">
                                        <td>TOTAL</td>
                                        <td><?= !empty($reporte) ? array_sum(array_column($reporte, 'total_pagos')) : 0 ?></td>
                                        <td class="text-success">S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'total_ingresos')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'monto_efectivo')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'monto_transferencia')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'monto_tarjeta')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'monto_mensualidad')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'monto_matricula')) : 0, 2) ?></td>
                                        <td>S/ <?= number_format(!empty($reporte) ? array_sum(array_map(function($p) {
                                            return ($p['monto_otro'] ?? 0) + ($p['monto_material'] ?? 0) + 
                                                   ($p['monto_uniforme'] ?? 0) + ($p['monto_actividad'] ?? 0);
                                        }, $reporte)) : 0, 2) ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No hay datos para mostrar en el período seleccionado</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Scripts (mantengo los tuyos) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="js/reportes.js"></script>

<script>
    $(document).ready(function() {
        $('#tablaFinanciero').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 25
        });
        // Inicializar DataTable en constancias si existe
        if ($('.constancias-table').length) {
            $('.constancias-table').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
                pageLength: 25
            });
        }
        
        // Datos para los gráficos (usando tus variables PHP)
        const periodos = <?= json_encode(array_column(!empty($reporte) ? $reporte : [], 'periodo')) ?>;
        const ingresos = <?= json_encode(array_column(!empty($reporte) ? $reporte : [], 'total_ingresos')) ?>;
        const transacciones = <?= json_encode(array_column(!empty($reporte) ? $reporte : [], 'total_pagos')) ?>;
        const montoEfectivo = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_efectivo')) : 0) ?>;
        const montoTransferencia = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_transferencia')) : 0) ?>;
        const montoTarjeta = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_tarjeta')) : 0) ?>;
        const montoMensualidad = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_mensualidad')) : 0) ?>;
        const montoMatricula = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_matricula')) : 0) ?>;
        const montoMaterial = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_material')) : 0) ?>;
        const montoUniforme = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_uniforme')) : 0) ?>;
        const montoActividad = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_actividad')) : 0) ?>;
        const montoOtro = <?= json_encode(!empty($reporte) ? array_sum(array_column($reporte, 'monto_otro')) : 0) ?>;
        
        // Gráfico de ingresos por período (Barras)
        const ctxIngresos = document.getElementById('graficoIngresos')?.getContext('2d');
        if (ctxIngresos) {
            new Chart(ctxIngresos, {
                type: 'bar',
                data: {
                    labels: periodos,
                    datasets: [{
                        label: 'Ingresos (S/)',
                        data: ingresos,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'S/ ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de métodos de pago (Doughnut)
        const ctxMetodos = document.getElementById('graficoMetodosPago')?.getContext('2d');
        if (ctxMetodos) {
            new Chart(ctxMetodos, {
                type: 'doughnut',
                data: {
                    labels: ['Efectivo', 'Transferencia', 'Tarjeta'],
                    datasets: [{
                        data: [montoEfectivo, montoTransferencia, montoTarjeta],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': S/ ' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de conceptos (Pie)
        const ctxConceptos = document.getElementById('graficoConceptos')?.getContext('2d');
        if (ctxConceptos) {
            new Chart(ctxConceptos, {
                type: 'pie',
                data: {
                    labels: ['Mensualidad', 'Matrícula', 'Material', 'Uniforme', 'Actividad', 'Otros'],
                    datasets: [{
                        data: [montoMensualidad, montoMatricula, montoMaterial, montoUniforme, montoActividad, montoOtro],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(201, 203, 207, 0.8)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': S/ ' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de tendencia (Linea)
        const ctxTendencia = document.getElementById('graficoTendencia')?.getContext('2d');
        if (ctxTendencia) {
            new Chart(ctxTendencia, {
                type: 'line',
                data: {
                    labels: periodos,
                    datasets: [{
                        label: 'Número de Pagos',
                        data: transacciones,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });

    function exportarExcel() {
        const params = new URLSearchParams(window.location.search);
        params.set('controller', 'Reporte');
        params.set('action', 'exportar');
        params.set('tipo', 'financiero');
        window.location.href = 'index.php?' + params.toString();
    }

    function exportarTipoGranular(tipo, granularidad, fechaInicioSelector, fechaFinSelector) {
        const params = new URLSearchParams(window.location.search);
        params.set('controller', 'Reporte');
        params.set('action', 'exportar');
        params.set('tipo', tipo);
        params.set('granularidad', granularidad);

        let fInicio = '';
        let fFin = '';
        if (fechaInicioSelector && document.querySelector(fechaInicioSelector)) {
            fInicio = document.querySelector(fechaInicioSelector).value;
        } else if (document.querySelector('input[name="fecha_inicio"]')) {
            fInicio = document.querySelector('input[name="fecha_inicio"]').value;
        }
        if (fechaFinSelector && document.querySelector(fechaFinSelector)) {
            fFin = document.querySelector(fechaFinSelector).value;
        } else if (document.querySelector('input[name="fecha_fin"]')) {
            fFin = document.querySelector('input[name="fecha_fin"]').value;
        }

        if (granularidad === 'semanal') {
            // semanal: use provided selectors if available, otherwise last 7 days
            if (fInicio && fFin) {
                params.set('fecha_inicio', fInicio);
                params.set('fecha_fin', fFin);
            } else {
                const hoy = new Date();
                const fin = hoy.toISOString().split('T')[0];
                const ini = new Date(); ini.setDate(hoy.getDate() - 6);
                const inicio = ini.toISOString().split('T')[0];
                params.set('fecha_inicio', inicio);
                params.set('fecha_fin', fin);
            }
        } else if (granularidad === 'dia') {
            const dia = fInicio || new Date().toISOString().split('T')[0];
            params.set('fecha_inicio', dia);
            params.set('fecha_fin', dia);
        } else if (granularidad === 'mes') {
            if (fInicio && fFin) {
                params.set('fecha_inicio', fInicio);
                params.set('fecha_fin', fFin);
            } else {
                const hoy = new Date();
                const primer = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                const ultimo = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                params.set('fecha_inicio', primer);
                params.set('fecha_fin', ultimo);
            }
        } else if (granularidad === 'anio') {
            const hoy = new Date();
            const inicio = hoy.getFullYear() + '-01-01';
            const fin = hoy.getFullYear() + '-12-31';
            params.set('fecha_inicio', inicio);
            params.set('fecha_fin', fin);
        }

        const idSec = document.querySelector('select[name="id_seccion"]') ? document.querySelector('select[name="id_seccion"]').value : '';
        const grado = document.querySelector('select[name="grado"]') ? document.querySelector('select[name="grado"]').value : '';
        const nivel = document.querySelector('select[name="nivel"]') ? document.querySelector('select[name="nivel"]').value : '';
        if (idSec) params.set('id_seccion', idSec);
        if (grado) params.set('grado', grado);
        if (nivel) params.set('nivel', nivel);

        window.location.href = 'index.php?' + params.toString();
    }

    /**
     * Devuelve objeto {start, end} con fechas YYYY-MM-DD para la semana ISO indicada
     */
    function getDateRangeOfISOWeek(week, year) {
        // ISO week: Monday as first day
        const simple = new Date(Date.UTC(year, 0, 1 + (week - 1) * 7));
        const dow = simple.getUTCDay();
        const ISOweekStart = new Date(simple);
        // Adjust to Monday
        const diff = (dow <= 4 ? simple.getUTCDate() - (dow === 0 ? 6 : dow - 1) : simple.getUTCDate() + (8 - dow));
        ISOweekStart.setUTCDate(diff);
        const start = new Date(Date.UTC(ISOweekStart.getUTCFullYear(), ISOweekStart.getUTCMonth(), ISOweekStart.getUTCDate()));
        const end = new Date(start);
        end.setUTCDate(start.getUTCDate() + 6);
        const toYMD = d => d.toISOString().split('T')[0];
        return { start: toYMD(start), end: toYMD(end) };
    }

    function exportarPDF() {
        window.print();
    }

    // Actualizar fechas según periodo seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        const selectPeriodo = document.getElementById('selectPeriodo');
        if (selectPeriodo) {
            selectPeriodo.addEventListener('change', function() {
                const periodo = this.value;
                const hoy = new Date();
                let fechaInicio, fechaFin;

                switch(periodo) {
                    case 'semanal':
                        fechaFin = hoy.toISOString().split('T')[0];
                        fechaInicio = new Date(hoy.setDate(hoy.getDate() - 7)).toISOString().split('T')[0];
                        break;
                    case 'anual':
                        fechaInicio = hoy.getFullYear() + '-01-01';
                        fechaFin = hoy.getFullYear() + '-12-31';
                        break;
                    case 'mensual':
                    default:
                        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                        const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                        fechaInicio = primerDia.toISOString().split('T')[0];
                        fechaFin = ultimoDia.toISOString().split('T')[0];
                        break;
                }

                const inInicio = document.querySelector('input[name="fecha_inicio"]');
                const inFin = document.querySelector('input[name="fecha_fin"]');
                if (inInicio) inInicio.value = fechaInicio;
                if (inFin) inFin.value = fechaFin;
            });
        }
    });
</script>

</body>
</html>
