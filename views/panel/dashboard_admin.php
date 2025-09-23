<!-- C:\xampp\htdocs\educa-finanzas\views\panel\dashboard_admin.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Educa-Finanzas</a>
            <div class="d-flex">
                <span class="text-white me-3">👤 <?= $_SESSION['usuario']['nombre'] ?> (<?= $_SESSION['usuario']['rol'] ?>)</span>
                <a href="index.php?controller=Auth&action=logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    <div class="container mt-4">
        <h2 class="mb-4">📊 Panel de Administración</h2>

        <div class="row g-4">
            <!-- Gestión de Usuarios -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">👥 Gestión de Usuarios</h5>
                        <p class="card-text">Administra los usuarios y sus roles.</p>
                        <a href="index.php?controller=Usuario&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Estudiantes -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">🎓 Estudiantes</h5>
                        <p class="card-text">Registro, consulta y actualización de estudiantes.</p>
                        <a href="index.php?controller=Estudiante&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Padres -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">👨‍👩‍👧 Padres de Familia</h5>
                        <p class="card-text">Gestión de padres y vinculación con estudiantes.</p>
                        <a href="index.php?controller=Padre&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Pagos -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">💰 Pagos</h5>
                        <p class="card-text">Registro de pagos, deudas y emisión de recibos.</p>
                        <a href="index.php?controller=Pago&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">📑 Reportes</h5>
                        <p class="card-text">Visualiza estadísticas y reportes financieros.</p>
                        <a href="index.php?controller=Reporte&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">⚙️ Configuración</h5>
                        <p class="card-text">Ajustes y parámetros generales del sistema.</p>
                        <a href="index.php?controller=Config&action=index" class="btn btn-primary">Ir al módulo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
