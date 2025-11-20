<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])) { header("Location: index.php?controller=Auth&action=acceso_denegado"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Deudas - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">
    
    <?php require_once '../views/templates/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../views/templates/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">📊 Reporte de Deudas</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php?controller=Reporte&action=exportar&tipo=deudas" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-excel"></i> Exportar
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resumen de deudas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-danger mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Deuda</h5>
                                <p class="card-text display-6">
                                    S/ <?= number_format(array_sum(array_column($deudas, 'total_deuda')), 2) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-dark bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pagos Pendientes</h5>
                                <p class="card-text display-6">
                                    <?= array_sum(array_column($deudas, 'pagos_pendientes')) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Estudiantes con Deuda</h5>
                                <p class="card-text display-6">
                                    <?= count($deudas) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de deudas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Detalle de Deudas por Estudiante</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaDeudas" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>DNI</th>
                                        <th>Grado</th>
                                        <th>Nivel</th>
                                        <th>Total Deuda</th>
                                        <th>Pagos Pendientes</th>
                                        <th>Próximo Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deudas as $deuda): ?>
                                    <tr>
                                        <td><?= $deuda['nombres'] ?> <?= $deuda['apellidos'] ?></td>
                                        <td><?= $deuda['dni'] ?></td>
                                        <td><?= $deuda['grado'] ?></td>
                                        <td><?= $deuda['nivel'] ?></td>
                                        <td>
                                            <span class="fw-bold text-danger">
                                                S/ <?= number_format($deuda['total_deuda'], 2) ?>
                                            </span>
                                        </td>
                                        <td><?= $deuda['pagos_pendientes'] ?></td>
                                        <td>
                                            <?php if ($deuda['proxima_fecha_vencimiento']): ?>
                                                <?= date('d/m/Y', strtotime($deuda['proxima_fecha_vencimiento'])) ?>
                                                <?php $diasVencimiento = floor((strtotime($deuda['proxima_fecha_vencimiento']) - time()) / 86400); ?>
                                                <?php if ($diasVencimiento < 0): ?>
                                                    <span class="badge bg-danger">Vencido</span>
                                                <?php elseif ($diasVencimiento <= 5): ?>
                                                    <span class="badge bg-warning text-dark">Próximo</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?controller=Pago&action=registrar&id_estudiante=<?= $deuda['id_estudiante'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-cash"></i> Registrar Pago
                                            </a>
                                            <a href="index.php?controller=Estudiante&action=detalle&id=<?= $deuda['id_estudiante'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> Ver Detalle
                                            </a>
                                        </td>
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
            $('#tablaDeudas').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                order: [[4, 'desc']]
            });
        });
    </script>
</body>
</html>
