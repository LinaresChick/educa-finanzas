<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Secretario - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-info shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Educa-Finanzas</a>
            <div class="d-flex">
                <span class="text-white me-3">👨‍👩‍👧 <?= $_SESSION['usuario']['nombre'] ?> (<?= $_SESSION['usuario']['rol'] ?>)</span>
                <a href="index.php?controller=Auth&action=logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">👨‍👩‍👧 Panel Padre</h2>
        <div class="row g-4">
            <!-- Info hijos -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">📚 Información Académica</h5>
                        <p class="card-text">Consultar datos académicos de sus hijos.</p>
                        <a href="index.php?controller=Estudiante&action=verHijos" class="btn btn-info text-white">Ver información</a>
                    </div>
                </div>
            </div>
            <!-- Estado de pagos -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">💳 Estado de Pagos</h5>
                        <p class="card-text">Consultar deudas, pagos realizados y recibos.</p>
                        <a href="index.php?controller=Pago&action=verPadre" class="btn btn-info text-white">Ver pagos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
