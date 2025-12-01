<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php if (!isset($_SESSION['usuario'])) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<?php require_once __DIR__ . '/../templates/header.php'; ?>

<style>

/* ============================================================
   FONDO ANIMADO MÁS INTENSO (DEGRADADO MULTICOLOR)
   ============================================================ */
body {
    background: linear-gradient(135deg,
        #c6e8ff,   /* celeste pastel */
        #c4ffc8,   /* verde pastel   */
        #fff3a1,   /* amarillo pastel */
        #ffcfa3    /* naranja pastel  */
    );
    background-size: 400% 400%;
    animation: bgMove 10s ease-in-out infinite;
    font-family: 'Poppins', sans-serif;
}

@keyframes bgMove {
    0% { background-position: 0% 50%; filter: brightness(1); }
    50% { background-position: 100% 50%; filter: brightness(1.15); }
    100% { background-position: 0% 50%; filter: brightness(1); }
}

/* ============================================================
   ANIMACIONES POTENTES
   ============================================================ */

@keyframes floatStrong {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}

@keyframes pulseGlow {
    0% { box-shadow: 0 0 0px rgba(255,255,255,0.4); }
    50% { box-shadow: 0 0 25px rgba(255,255,255,0.9); }
    100% { box-shadow: 0 0 0px rgba(255,255,255,0.4); }
}

@keyframes bounceIn {
    0% { transform: scale(0.6); opacity: 0; }
    60% { transform: scale(1.10); opacity: 1; }
    100% { transform: scale(1); }
}

@keyframes shine {
    0% { transform: translateX(-150%); }
    100% { transform: translateX(250%); }
}

/* ============================================================
   TARJETAS PROFESIONALES SUPER ANIMADAS
   ============================================================ */

.module-card {
    position: relative;
    border-radius: 22px;
    padding: 30px;
    border: 3px solid rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    transition: 0.4s ease-in-out;
    cursor: pointer;
    animation: bounceIn 0.7s ease forwards;
    overflow: hidden;
}

/* Colores pastel intensificados */
.module-1 { background: rgba(198, 232, 255, 0.75); } /* celeste */
.module-2 { background: rgba(196, 255, 200, 0.75); } /* verde */
.module-3 { background: rgba(255, 243, 161, 0.75); } /* amarillo */
.module-4 { background: rgba(255, 207, 163, 0.75); } /* naranja */

/* Hover con explosión de luz */
.module-card:hover {
    transform: translateY(-15px) scale(1.06);
    border-color: white;
    animation: pulseGlow 1.5s infinite;
}

/* Efecto de destello diagonal */
.module-card::after {
    content: "";
    position: absolute;
    top: 0;
    left: -150%;
    width: 120%;
    height: 100%;
    background: linear-gradient(120deg, rgba(255,255,255,0.0), rgba(255,255,255,0.8), rgba(255,255,255,0));
    transform: skewX(-20deg);
    animation: shine 3s infinite;
}

/* Iconos más grandes + animación fuerte */
.module-icon i {
    font-size: 55px;
    color: #004cbf;
    animation: floatStrong 3.5s ease-in-out infinite;
    transition: 0.3s ease;
}

/* Icono explota al pasar */
.module-card:hover .module-icon i {
    transform: scale(1.35) rotate(5deg);
    filter: brightness(1.3);
}

/* Títulos */
.card-title {
    font-weight: 700;
    letter-spacing: 0.7px;
    text-shadow: 0 0 6px white;
}

/* Delays */
.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }
.delay-5 { animation-delay: 0.5s; }
.delay-6 { animation-delay: 0.6s; }

</style>


<!-- ============================================================
     PANEL DE ADMINISTRACIÓN
     ============================================================ -->

<div class="main-container container py-4">

    <h2 class="section-title text-center mb-4">
        <i class="fas fa-tachometer-alt me-2"></i>PANEL DEL DIRECTOR
    </h2>

    <div class="content-card">
        <div class="row g-4">

            <div class="col-md-4">
                <a href="index.php?controller=Usuario&action=index" class="module-link">
                    <div class="card module-card module-1 delay-1">
                        <div class="module-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p>Administra usuarios y roles del sistema.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?controller=Docente&action=index" class="module-link">
                    <div class="card module-card module-3 delay-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h5 class="card-title">Profesores</h5>
                        <p>Gestión completa de docentes.</p>
                    </div>
                </a>
            </div>


            <div class="col-md-4">
                <a href="index.php?controller=Estudiante&action=index" class="module-link">
                    <div class="card module-card module-2 delay-2">
                        <div class="module-icon mb-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="card-title">Estudiantes</h5>
                        <p>Registro y seguimiento académico.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?controller=Padre&action=index" class="module-link">
                    <div class="card module-card module-3 delay-3">
                        <div class="module-icon mb-3">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h5 class="card-title">Padres de Familia</h5>
                        <p>Administración de tutores.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?controller=Pago&action=index" class="module-link">
                    <div class="card module-card module-4 delay-4">
                        <div class="module-icon mb-3">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h5 class="card-title">Pagos</h5>
                        <p>Control financiero institucional.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?controller=Reporte&action=index" class="module-link">
                    <div class="card module-card module-1 delay-5">
                        <div class="module-icon mb-3">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="card-title">Reportes</h5>
                        <p>Indicadores y estadísticas.</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="index.php?controller=Constancia&action=index" class="module-link">
                    <div class="card module-card module-2 delay-6">
                        <div class="module-icon mb-3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="card-title">Constancias</h5>
                        <p>Generación de documentos oficiales.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
