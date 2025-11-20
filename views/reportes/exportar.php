<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])) { header("Location: index.php?controller=Auth&action=acceso_denegado"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exportar Reportes - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">
    
    <?php require_once '../views/templates/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require_once '../views/templates/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">📊 Exportar Reportes</h1>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Seleccionar Reporte a Exportar</h5>
                    </div>
                    <div class="card-body">
                        <form action="index.php" method="get" class="row g-3">
                            <input type="hidden" name="controller" value="Reporte">
                            <input type="hidden" name="action" value="exportar">
                            
                            <div class="col-md-12">
                                <label for="tipo" class="form-label">Tipo de Reporte</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione un tipo de reporte...</option>
                                    <option value="deudas">Reporte de Deudas</option>
                                    <option value="financiero">Reporte Financiero</option>
                                    <option value="pagos">Reporte de Pagos</option>
                                    <option value="estudiantes">Reporte de Estudiantes</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 fecha-group">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= date('Y-m-01') ?>">
                            </div>
                            
                            <div class="col-md-6 fecha-group">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= date('Y-m-t') ?>">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-arrow-down"></i> Exportar a CSV
                                </button>
                                <a href="index.php?controller=Reporte&action=index" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Reportes Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-cash-coin" style="font-size: 2.5rem; color: #dc3545;"></i>
                                        <h5 class="card-title mt-3">Reporte de Deudas</h5>
                                        <p class="card-text">Listado de estudiantes con deudas pendientes.</p>
                                        <a href="index.php?controller=Reporte&action=exportar&tipo=deudas" class="btn btn-sm btn-outline-primary">Exportar</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-graph-up" style="font-size: 2.5rem; color: #198754;"></i>
                                        <h5 class="card-title mt-3">Reporte Financiero</h5>
                                        <p class="card-text">Análisis financiero por períodos de tiempo.</p>
                                        <a href="index.php?controller=Reporte&action=exportar&tipo=financiero" class="btn btn-sm btn-outline-primary">Exportar</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-receipt" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                        <h5 class="card-title mt-3">Reporte de Pagos</h5>
                                        <p class="card-text">Listado detallado de pagos realizados.</p>
                                        <a href="index.php?controller=Reporte&action=exportar&tipo=pagos" class="btn btn-sm btn-outline-primary">Exportar</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-mortarboard" style="font-size: 2.5rem; color: #6c757d;"></i>
                                        <h5 class="card-title mt-3">Reporte de Estudiantes</h5>
                                        <p class="card-text">Listado completo de estudiantes registrados.</p>
                                        <a href="index.php?controller=Reporte&action=exportar&tipo=estudiantes" class="btn btn-sm btn-outline-primary">Exportar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar campos de fecha según el tipo de reporte
            $('.fecha-group').hide();
            
            $('#tipo').change(function() {
                const tipo = $(this).val();
                if (tipo === 'financiero' || tipo === 'pagos') {
                    $('.fecha-group').show();
                } else {
                    $('.fecha-group').hide();
                }
            });
        });
    </script>
</body>
</html>
