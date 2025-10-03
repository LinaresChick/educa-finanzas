<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php 
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'Docente') {
    header("Location: index.php?controller=Auth&action=login");
    exit;
} 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Docente - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Educa-Finanzas</a>
            <div class="d-flex">
                <span class="text-dark fw-bold me-3">👩‍🏫 <?= $_SESSION['usuario']['nombre'] ?> (<?= $_SESSION['usuario']['rol'] ?>)</span>
                <a href="index.php?controller=Auth&action=logout" class="btn btn-outline-dark btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">👩‍🏫 Panel Docente</h2>
        <div class="row g-4">
            <!-- Mis salones -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">🏫 Mis Salones</h5>
                        <p class="card-text">Ver los salones que tengo asignados y sus estudiantes.</p>
                        <a href="index.php?controller=Salon&action=verSalones" class="btn btn-warning">Ver salones</a>
                    </div>
                </div>
            </div>
            <!-- Mis estudiantes -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">👨‍🎓 Mis Estudiantes</h5>
                        <p class="card-text">Consultar la lista de estudiantes de mis salones.</p>
                        <a href="index.php?controller=Estudiante&action=verEstudiantesDocente" class="btn btn-warning">Ver estudiantes</a>
                    </div>
                </div>
            </div>
            <!-- Pagos de mis estudiantes -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">💰 Pagos de Estudiantes</h5>
                        <p class="card-text">Consultar pagos, deudas y recibos emitidos de mis estudiantes.</p>
                        <a href="index.php?controller=Pago&action=verPagosDocente" class="btn btn-warning">Ver pagos</a>
                    </div>
                </div>
            </div>
            <!-- Reportes -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">📊 Reportes</h5>
                        <p class="card-text">Generar reportes de asistencia, notas o pagos de mis estudiantes.</p>
                        <a href="index.php?controller=Reporte&action=verReportes" class="btn btn-warning">Ver reportes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
