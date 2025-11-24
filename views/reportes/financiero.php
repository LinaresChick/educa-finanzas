<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) { header("Location: index.php?controller=Auth&action=acceso_denegado"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    
    <?php require_once '../views/templates/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../views/templates/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-graph-up-arrow me-2"></i>💰 Reporte Financiero</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportarExcel()">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportarPDF()">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="card shadow-sm mb-4">
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
                                    <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                                </div>

                                <!-- Fecha Fin -->
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
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
                </div>

                <!-- Tarjetas de Resumen -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-cash-stack"></i> Total Ingresos</h6>
                                <p class="card-text display-6">
                                    S/ <?= number_format(!empty($estadisticas) ? ($estadisticas['total_periodo'] ?? 0) : 0, 2) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-receipt"></i> Total Transacciones</h6>
                                <p class="card-text display-6">
                                    <?= !empty($estadisticas) ? ($estadisticas['total_transacciones'] ?? 0) : 0 ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-people"></i> Estudiantes</h6>
                                <p class="card-text display-6">
                                    <?= !empty($estadisticas) ? ($estadisticas['total_estudiantes'] ?? 0) : 0 ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-calculator"></i> Promedio</h6>
                                <p class="card-text display-6">
                                    S/ <?= number_format(!empty($estadisticas) ? ($estadisticas['promedio_transaccion'] ?? 0) : 0, 2) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
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
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0"><i class="bi bi-pie-chart-fill"></i> Conceptos de Pago</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="graficoConceptos" height="100"></canvas>
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
            
            // Datos para los gráficos
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
            const ctxIngresos = document.getElementById('graficoIngresos').getContext('2d');
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
                        legend: {
                            display: true,
                            position: 'top'
                        },
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
            
            // Gráfico de métodos de pago (Pastel)
            const ctxMetodos = document.getElementById('graficoMetodosPago').getContext('2d');
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
                        legend: {
                            position: 'bottom'
                        },
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

            // Gráfico de conceptos (Pastel)
            const ctxConceptos = document.getElementById('graficoConceptos').getContext('2d');
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
                        legend: {
                            position: 'bottom'
                        },
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

            // Gráfico de tendencia (Línea)
            const ctxTendencia = document.getElementById('graficoTendencia').getContext('2d');
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
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        function exportarExcel() {
            const params = new URLSearchParams(window.location.search);
            params.set('controller', 'Reporte');
            params.set('action', 'exportar');
            params.set('tipo', 'financiero');
            window.location.href = 'index.php?' + params.toString();
        }

        function exportarPDF() {
            window.print();
        }

        // Actualizar fechas según periodo seleccionado
        document.getElementById('selectPeriodo').addEventListener('change', function() {
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

            document.querySelector('input[name="fecha_inicio"]').value = fechaInicio;
            document.querySelector('input[name="fecha_fin"]').value = fechaFin;
        });
    </script>

    <style>
        @media print {
            .sidebar, .navbar, .btn-toolbar, .card-header .btn, form { display: none !important; }
            .col-md-9 { flex: 0 0 100%; max-width: 100%; }
            .card { break-inside: avoid; }
        }
    </style>
</body>
</html>
