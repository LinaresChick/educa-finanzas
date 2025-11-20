<!-- Barra de Navegación Superior -->
<nav class="navbar navbar-expand-lg main-navbar fixed-top">
    <div class="container">
        <!-- Botón del menú lateral -->
        <button id="sidebarToggle" class="btn btn-link">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQPR6X-Y9clrkN6XY9AtX6lRof4SAJkL1JZNg&s" alt="Logo">
            INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <!-- Los elementos del menú superior se mantienen aquí -->
            </ul>

            <!-- Perfil Usuario -->
            <!-- Perfil Usuario -->
<?php if (isset($_SESSION['usuario'])): ?>
<div class="dropdown ms-3">
  <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <div class="user-avatar me-2">
      <?php echo substr($_SESSION['usuario']['nombre'], 0, 1); ?>
    </div>
    <span class="fw-semibold d-none d-md-inline"><?php echo $_SESSION['usuario']['nombre']; ?></span>
  </a>

  <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
    <li>
      <a class="dropdown-item" href="<?php echo BASE_URL; ?>usuarios/perfil">
        <i class="fas fa-user text-success"></i> Mi Perfil
      </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
      </a>
    </li>
  </ul>
</div>
<?php endif; ?>

        </div>
    </div>
</nav>

<!-- Menú Lateral -->
<div id="sidebar" class="sidebar">
  <div class="sidebar-header">
    <h3><i class="fas fa-leaf text-success"></i> Menú Principal</h3>
    <button id="sidebarClose" class="btn btn-link">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <ul class="sidebar-menu">
    <li class="sidebar-item">
      <a href="index.php?controller=Panel&action=dashboard">
        <i class="fas fa-home"></i>
        <span>Inicio</span>
      </a>
    </li>

    <?php if (isset($_SESSION['usuario']) && in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador', 'Secretario', 'Contador'])): ?>
    <li class="sidebar-item">
      <a href="index.php?controller=Estudiante&action=index">
        <i class="fas fa-user-graduate text-success"></i>
        <span>Estudiantes</span>
      </a>
    </li>

    <li class="sidebar-item">
      <a href="index.php?controller=Pago&action=index">
        <i class="fas fa-receipt text-warning"></i>
        <span>Pagos</span>
      </a>
    </li>
    <?php endif; ?>

    <?php if (isset($_SESSION['usuario']) && in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Secretario', 'Contador'])): ?>
    <li class="sidebar-item has-submenu">
      <a href="#" class="submenu-toggle">
        <i class="fas fa-cog text-success"></i>
        <span>Administración</span>
        <i class="fas fa-chevron-down submenu-icon"></i>
      </a>
      <ul class="submenu">
        <li>
          <a href="index.php?controller=Usuario&action=index">
            <i class="fas fa-users text-success"></i> Usuarios
          </a>
        </li>
        <li>
          <a href="index.php?controller=Reporte&action=index">
            <i class="fas fa-chart-bar text-warning"></i> Reportes
          </a>
        </li>
      </ul>
    </li>
    <?php endif; ?>
  </ul>
</div>

<!-- 🌿 Estilos Modernos -->
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: "Poppins", sans-serif; background: #bced72ff; }

/* NAVBAR */
.main-navbar {
  background: linear-gradient(90deg, #2e7d32, #cddc39);
  box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.navbar-brand img {
  width: 42px;
  height: 42px;
  margin-right: 10px;
  border-radius: 50%;
  border: 2px solid #fff;
}

.user-avatar {
  width: 35px;
  height: 35px;
  background-color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 8px;
  font-weight: bold;
  color: #2e7d32;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* SIDEBAR */
.sidebar {
  position: fixed;
  left: -280px;
  top: 0;
  width: 280px;
  height: 100vh;
  background: #84e714ff;
  box-shadow: 3px 0 8px rgba(0,0,0,0.1);
  transition: left 0.3s ease;
  z-index: 1040;
  padding-top: 60px;
  border-right: 4px solid #cddc39;
}

.sidebar.active { left: 0; }

.sidebar-header {
  padding: 15px 20px;
  border-bottom: 2px solid #e6ee9c;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #2e7d32;
  font-weight: 600;
}

.sidebar-menu { list-style: none; margin: 0; padding: 0; }
.sidebar-item { border-bottom: 1px solid #f1f8e9; }

.sidebar-item a {
  display: flex;
  align-items: center;
  padding: 14px 20px;
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.sidebar-item a:hover {
  background: #f1f8e9;
  color: #2e7d32;
  border-left: 4px solid #cddc39;
}

.sidebar-item a.active {
  background: #e6ee9c;
  color: #1b5e20;
  border-left: 4px solid #2e7d32;
  font-weight: 600;
}

.sidebar-item i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.submenu { list-style: none; padding: 0; margin: 0; background: #f9fbe7; display: none; }
.submenu li a { padding-left: 50px; }

.has-submenu > a { position: relative; }
.submenu-icon {
  position: absolute;
  right: 20px;
  transition: transform 0.3s ease;
}

.has-submenu.open .submenu-icon { transform: rotate(180deg); }
.has-submenu.open .submenu { display: block; }

#sidebarToggle { color: white; border: none; padding: 5px; font-size: 1.2rem; }
#sidebarClose { color: #2e7d32; font-size: 1.2rem; }

.main-content { transition: margin-left 0.3s ease; }
.main-content.shifted { margin-left: 280px; }
</style>

<!-- Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarClose = document.getElementById('sidebarClose');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.querySelector('.main-content');
  const submenuToggles = document.querySelectorAll('.submenu-toggle');

  // Abrir/cerrar menú lateral
  sidebarToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
    if (mainContent) mainContent.classList.toggle('shifted');
  });

  sidebarClose.addEventListener('click', function() {
    sidebar.classList.remove('active');
    if (mainContent) mainContent.classList.remove('shifted');
  });

  // Cerrar al hacer clic fuera
  document.addEventListener('click', function(e) {
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('active')) {
      sidebar.classList.remove('active');
      if (mainContent) mainContent.classList.remove('shifted');
    }
  });

  // Submenús
  submenuToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      const parent = this.closest('.has-submenu');
      parent.classList.toggle('open');
    });
  });
});
</script>
