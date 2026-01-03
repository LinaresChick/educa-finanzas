<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innova School Independencia - Sistema de Gestión</title>
    
    <!-- Bootstrap: use local copy if available, otherwise fall back to CDN -->
    <?php
    $localBootstrap = __DIR__ . '/../../public/vendor/bootstrap/css/bootstrap.min.css';
    if (file_exists($localBootstrap)) {
        // compute web path relative to project public folder
        $webPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($localBootstrap));
        if ($webPath === '') {
            // fallback: attempt relative path
            $webPath = '/public/vendor/bootstrap/css/bootstrap.min.css';
        }
        echo '<link href="' . htmlspecialchars($webPath) . '" rel="stylesheet">';
    } else {
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    }
    ?>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/theme.css" rel="stylesheet">
</head>

<body>
    <!-- jQuery (moved to header so inline page scripts can use $) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php?controller=Panel&action=index">
            <img class="brand-logo" src="/educa-finanzas/public/img/image.png" alt="Innova Schools Logo">
            <span class="ms-2 brand-text"> INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="index.php?controller=Panel&action=dashboard"><i class="fas fa-home me-1"></i> Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php?controller=Estudiante&action=index"><i class="fas fa-chalkboard-teacher me-1"></i> Estudiantes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php?controller=Pago&action=index"><i class="fas fa-book-open me-1"></i> Pagos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="index.php?controller=Reporte&action=index"><i class="fas fa-chart-line me-1"></i> Reportes</a>
                </li>

                <?php if (isset($_SESSION['usuario'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="index.php?controller=Usuario&action=perfil"><i class="fas fa-cog me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="index.php?controller=Usuario&action=perfil"><i class="fas fa-key me-2"></i> Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?controller=Auth&action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>