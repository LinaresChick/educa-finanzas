<!-- C:\xampp\htdocs\educa-finanzas\views\panel\dashboard_admin.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/dashboard_admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Contenido -->
    <div class="dashboard-container">
        <h2 class="dashboard-title">
            <i class="fas fa-tachometer-alt me-2"></i>
            Panel de Administración
        </h2>

        <div class="row g-4">
            <!-- Gestión de Usuarios -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administra los usuarios y sus roles en el sistema.</p>
                        <a href="index.php?controller=Usuario&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estudiantes -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="card-title">Estudiantes</h5>
                        <p class="card-text">Gestiona el registro y seguimiento de estudiantes.</p>
                        <a href="index.php?controller=Estudiante&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Padres -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h5 class="card-title">Padres de Familia</h5>
                        <p class="card-text">Administra la información de padres y tutores.</p>
                        <a href="index.php?controller=Padre&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pagos -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h5 class="card-title">Pagos</h5>
                        <p class="card-text">Control de pagos y gestión financiera.</p>
                        <a href="index.php?controller=Pago&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="card-title">Reportes</h5>
                        <p class="card-text">Análisis y estadísticas del sistema.</p>
                        <a href="index.php?controller=Reporte&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="module-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="card-title">Configuración</h5>
                        <p class="card-text">Personaliza los parámetros del sistema.</p>
                        <a href="index.php?controller=Config&action=index" class="btn btn-primary btn-module">
                            <i class="fas fa-arrow-right me-1"></i> Ir al módulo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
