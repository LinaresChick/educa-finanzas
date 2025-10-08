<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educa-Finanzas - Sistema de Gestión</title>
    
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos comunes -->
    <link href="public/css/common.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- 🎨 ESTILO PERSONALIZADO (Navbar verde + fondo suave) -->
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
        }

        /* --- NAVBAR VERDE --- */
        .navbar {
            background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%) !important;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: 700;
            letter-spacing: 0.6px;
            font-size: 1.3rem;
        }

        .navbar-brand i {
            color: #c8e6c9;
        }

        .navbar .nav-link {
            color: #e8f5e9 !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #c8e6c9 !important;
            transform: scale(1.05);
        }

        .navbar .dropdown-menu {
            background-color: #2e7d32;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .navbar .dropdown-item {
            color: #ffffff;
            transition: background 0.3s ease;
        }

        .navbar .dropdown-item:hover {
            background-color: #388e3c;
            color: #ffffff;
        }

        /* Contenedor general */
        .container-fluid {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 20px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/educa-finanzas/public/index.php?controller=Panel&action=index">
            <i class="fas fa-wallet me-2"></i> 
            <span>Educa-Finanzas</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="/educa-finanzas/public/index.php?controller=Panel&action=index">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/educa-finanzas/public/index.php?controller=Usuario&action=index">
                        <i class="fas fa-users me-1"></i> Usuarios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-graduation-cap me-1"></i> Cursos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-book me-1"></i> Blog
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-envelope me-1"></i> Contacto
                    </a>
                </li>

                <?php if (isset($_SESSION['usuario'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                    </a>

                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i> Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/educa-finanzas/public/index.php?controller=Auth&action=logout">
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

<div class="container-fluid mt-4">
    <!-- El contenido de cada página se insertará aquí -->
