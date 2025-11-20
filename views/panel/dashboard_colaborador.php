<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Colaborador - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Educa-Finanzas</a>
            <div class="d-flex">
                <span class="text-white me-3">👤 <?= $_SESSION['usuario']['nombre'] ?> (<?= $_SESSION['usuario']['rol'] ?>)</span>
                <a href="index.php?controller=Auth&action=logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">🤝 Panel Colaborador</h2>
        <div class="row g-4">
            <!-- Estudiantes -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">🎓 Estudiantes</h5>
                        <p class="card-text">Registrar y actualizar estudiantes.</p>
                        <a href="index.php?controller=Estudiante&action=index" class="btn btn-success">Ir al módulo</a>
                    </div>
                </div>
            </div>
            <!-- Pagos -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">💰 Pagos</h5>
                        <p class="card-text">Registrar pagos y controlar deudas.</p>
                        <a href="index.php?controller=Pago&action=index" class="btn btn-success">Ir al módulo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
