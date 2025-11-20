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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    
    <?php require_once '../views/templates/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../views/templates/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">💰 Reporte Financiero</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php?controller=Reporte&action=exportar&tipo=financiero&fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-excel"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros de fecha -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form action="index.php" method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="controller" value="Reporte">
                            <input type="hidden" name="action" value="financiero">
                            
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resumen financiero -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Ingresos Totales</h5>
                                <p class="card-text display-6">
                                    S/ <?= number_format(!empty($reporte) ? array_sum(array_column($reporte, 'total_ingresos')) : 0, 2) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-dark bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Pagos</h5>
                                <p class="card-text display-6">
                                    <?= !empty($reporte) ? array_sum(array_column($reporte, 'total_pagos')) : 0 ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pagos Mensualidad</h5>
                                <p class="card-text display-6">
                                    <?= array_sum(array_column($reporte, 'pagos_mensualidad')) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-secondary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pagos Matrícula</h5>
                                <p class="card-text display-6">
                                    <?= array_sum(array_column($reporte, 'pagos_matricula')) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Ingresos por Período</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="graficoIngresos" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Distribución por Método de Pago</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="graficoMetodosPago" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de datos por período -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Detalle Financiero por Período</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaFinanciero" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Total Pagos</th>
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
                                    <?php if (!empty($reporte)) foreach ($reporte as $periodo): ?>
                                    <tr>
                                        <td><?= $periodo['periodo'] ?></td>
                                        <td><?= $periodo['total_pagos'] ?></td>
                                        <td>S/ <?= number_format($periodo['total_ingresos'], 2) ?></td>
                                        <td>S/ <?= number_format($periodo['monto_efectivo'] ?? 0, 2) ?></td>
                                        <td>S/ <?= number_format($periodo['monto_transferencia'] ?? 0, 2) ?></td>
                                        <td>S/ <?= number_format($periodo['monto_tarjeta'] ?? 0, 2) ?></td>
                                        <td>S/ <?= number_format($periodo['monto_mensualidad'] ?? 0, 2) ?></td>
                                        <td>S/ <?= number_format($periodo['monto_matricula'] ?? 0, 2) ?></td>
                                        <td>S/ <?= number_format(
                                            ($periodo['monto_otro'] ?? 0) + 
                                            ($periodo['monto_material'] ?? 0) + 
                                            ($periodo['monto_uniforme'] ?? 0) + 
                                            ($periodo['monto_actividad'] ?? 0), 
                                            2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
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
                order: [[0, 'desc']]
            });
            
            // Datos para los gráficos
            const periodos = <?= json_encode(array_column($reporte, 'periodo')) ?>;
            const ingresos = <?= json_encode(array_column($reporte, 'total_ingresos')) ?>;
            const montoEfectivo = <?= json_encode(array_sum(array_column($reporte, 'monto_efectivo'))) ?>;
            const montoTransferencia = <?= json_encode(array_sum(array_column($reporte, 'monto_transferencia'))) ?>;
            const montoTarjeta = <?= json_encode(array_sum(array_column($reporte, 'monto_tarjeta'))) ?>;
            
            // Gráfico de ingresos
            const ctxIngresos = document.getElementById('graficoIngresos').getContext('2d');
            new Chart(ctxIngresos, {
                type: 'bar',
                data: {
                    labels: periodos,
                    datasets: [{
                        label: 'Ingresos (S/)',
                        data: ingresos,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Gráfico de métodos de pago
            const ctxMetodos = document.getElementById('graficoMetodosPago').getContext('2d');
            new Chart(ctxMetodos, {
                type: 'pie',
                data: {
                    labels: ['Efectivo', 'Transferencia', 'Tarjeta'],
                    datasets: [{
                        data: [montoEfectivo, montoTransferencia, montoTarjeta],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                            'rgba(255, 159, 64, 0.5)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
        });
    </script>
</body>
</html>
