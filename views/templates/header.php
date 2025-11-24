<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innova School Independencia - Sistema de Gestión</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 🎨 ESTILO PERSONALIZADO -->
    <style>
        /* ======= ESTILOS GENERALES ======= */
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #a3f527 0%, #baf97d 100%);
            background-image: url('b485d61b-99c2-4022-b4dd-886d345dbed7.png');
            background-repeat: repeat;
            background-size: 250px;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* ======= NAVBAR DEGRADADO VERDE ======= */
        .navbar {
            background: linear-gradient(135deg, #A3F527 0%,  #baec74ff 20%, #6DAF1B 100%) !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            color: #1b2603 !important;
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: white;
            margin-right: 10px;
            padding: 3px;
        }

        .navbar .nav-link {
            color: #1b2603 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #ffffff !important;
            transform: scale(1.05);
        }

        /* ======= DROPDOWN MENU ======= */
        .navbar .dropdown-menu {
            background: linear-gradient(135deg, #A3F527 0%, #87dd1dff 100%);
            border: none;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar .dropdown-item {
            color: #1b2603;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .navbar .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        /* ======= CONTENEDOR PRINCIPAL ======= */
        .container-fluid {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        /* ======= TÍTULOS Y TARJETAS ======= */
        .dashboard-title {
            text-align: center;
            color: #1b5e20;
            font-weight: 700;
            margin-bottom: 2rem;
            border-bottom: 3px solid rgba(46, 125, 50, 0.2);
            padding-bottom: 1rem;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(118, 200, 15, 0.3);
        }

        .card-body {
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .card-title {
            color: #1a2f4e;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .module-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #76c80f;
        }

        .btn-module {
            background-color: #76c80f;
            border: none;
            padding: 0.6rem 2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
            color: white;
            font-weight: 500;
        }

        .btn-module:hover {
            background-color: #8fe629;
            box-shadow: 0 4px 10px rgba(118, 200, 15, 0.3);
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.4);
        }

        .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQPR6X-Y9clrkN6XY9AtX6lRof4SAJkL1JZNg&s" alt="Innova Schools Logo">
            <span>Innova School Independencia</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-home me-1"></i> Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-users me-1"></i> Docentes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-book me-1"></i> Cursos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-envelope me-1"></i> Contacto</a>
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
</body>
</html>
