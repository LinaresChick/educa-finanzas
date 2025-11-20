<!-- C:\xampp\htdocs\educa-finanzas\views\panel\dashboard_admin.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador - Educa Finanzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f9fafb;
            font-family: "Poppins", sans-serif;
            color: #333;
        }

        .dashboard-title {
            font-weight: 700;
            color: #5f1987ff;
        }

        /* ======= TARJETAS DE MÓDULOS ======= */
        .module-link {
            text-decoration: none;
            color: inherit;
        }

        .module-card {
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            background: linear-gradient(180deg, #ffffff, #fdfdfd);
            height: 100%;
        }

        .module-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 6px 15px rgba(0, 128, 0, 0.15);
            cursor: pointer;
        }

        .module-icon i {
            font-size: 45px;
            color: #ffc107;
            background-color: rgba(255, 193, 7, 0.15);
            border-radius: 50%;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .module-card:hover .module-icon i {
            background-color: #198754;
            color: #fff;
        }

        .btn-module {
            background-color: #198754;
            border: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .btn-module:hover {
            background-color: #ffc107;
            color: #000;
        }

        .stat-card {
            background: linear-gradient(180deg, #fff8e1, #ffffff);
            border: 2px solid #ffc107;
            border-radius: 15px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-card h5 {
            color: #666;
            font-size: 16px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
        }

        .navbar {
            background-color: #fff;
            border-bottom: 2px solid #d9fdd3;
        }

        canvas {
            margin-top: 20px;
        }
    </style>
</head>

<body class="bg-light">

    <div class="dashboard-container container py-4">
        <h2 class="dashboard-title text-center mb-5">
            <i class="fas fa-tachometer-alt me-2"></i>
            Panel de Administración
        </h2>

        <div class="row g-4">

            <!-- Gestión de Usuarios -->
            <div class="col-md-4">
                <a href="index.php?controller=Usuario&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administra los usuarios y sus roles en el sistema.</p>
                    </div>
                </a>
            </div>

            <!-- Estudiantes -->
            <div class="col-md-4">
                <a href="index.php?controller=Estudiante&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="card-title">Estudiantes</h5>
                        <p class="card-text">Gestiona el registro y seguimiento de estudiantes.</p>
                    </div>
                </a>
            </div>

            <!-- Padres -->
            <div class="col-md-4">
                <a href="index.php?controller=Padre&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h5 class="card-title">Padres de Familia</h5>
                        <p class="card-text">Administra la información de padres y tutores.</p>
                    </div>
                </a>
            </div>

            <!-- Pagos -->
            <div class="col-md-4">
                <a href="index.php?controller=Pago&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h5 class="card-title">Pagos</h5>
                        <p class="card-text">Control de pagos y gestión financiera.</p>
                    </div>
                </a>
            </div>

            <!-- Reportes -->
            <div class="col-md-4">
                <a href="index.php?controller=Reporte&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="card-title">Reportes</h5>
                        <p class="card-text">Análisis y estadísticas del sistema.</p>
                    </div>
                </a>
            </div>

            <!-- Configuración -->
            <div class="col-md-4">
                <a href="index.php?controller=Config&action=index" class="module-link">
                    <div class="card module-card text-center shadow-sm border-0 p-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h5 class="card-title">Configuración</h5>
                        <p class="card-text">Personaliza los parámetros del sistema.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
