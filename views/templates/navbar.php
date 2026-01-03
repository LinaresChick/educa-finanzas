<!-- Barra de Navegación Superior -->
<nav class="navbar navbar-expand-lg main-navbar fixed-top">
    <div class="container">
        <!-- Botón del menú lateral -->
        <button id="sidebarToggle" class="btn btn-link">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="/educa-finanzas/public/img/image.png" alt="Logo">
            INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto"></ul>

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
      <a class="dropdown-item" href="index.php?controller=Usuario&action=perfil">
        <i class="fas fa-user text-success"></i> Mi Perfil
      </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <a class="dropdown-item text-danger" href="index.php?controller=Auth&action=logout">
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

<?php if (isset($_SESSION['usuario']) && in_array(strtolower($_SESSION['usuario']['rol']), array_map('strtolower', ['Superadmin','Administrador','Colaborador','Secretario','Contador']))): ?>
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

<?php if (isset($_SESSION['usuario']) && in_array(strtolower($_SESSION['usuario']['rol']), array_map('strtolower', ['Superadmin','Administrador','Secretario','Contador']))): ?>
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

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:"Poppins",sans-serif;background:#bced72ff;overflow-x:hidden}

/* NAVBAR */
.main-navbar{
  background:linear-gradient(90deg,#2e7d32,#cddc39);
  box-shadow:0 3px 10px rgba(0,0,0,.2)
}

.navbar-brand img{
  width:42px;height:42px;margin-right:10px;border-radius:50%;border:2px solid #fff
}

.user-avatar{
  width:35px;height:35px;background:#fff;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-weight:bold;color:#2e7d32
}

/* SIDEBAR */
.sidebar{
  position:fixed;left:-280px;top:0;width:280px;height:100vh;
  background:#84e714ff;transition:left .3s;z-index:1040;
  padding-top:60px;border-right:4px solid #cddc39
}
.sidebar.active{left:0}

.sidebar-header{
  padding:15px 20px;border-bottom:2px solid #e6ee9c;
  display:flex;justify-content:space-between;align-items:center
}

.sidebar-item a{
  display:flex;align-items:center;padding:14px 20px;
  color:#333;text-decoration:none;font-weight:500
}
.sidebar-item a:hover{background:#f1f8e9;color:#2e7d32}

.submenu{display:none;background:#f9fbe7}
.has-submenu.open .submenu{display:block}
.submenu li a{padding-left:50px}

.main-content.shifted{margin-left:280px}

/* ===== RESPONSIVE (AÑADIDO, NO MODIFICA NADA) ===== */
@media(max-width:768px){
  .sidebar{width:260px;left:-260px}
  .sidebar.active{left:0}
  .main-content.shifted{margin-left:0}
  .navbar-brand{font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  #sidebarToggle{font-size:1.5rem;padding:8px}
}
</style>

<script>
document.addEventListener('DOMContentLoaded',()=>{
  const t=document.getElementById('sidebarToggle'),
        c=document.getElementById('sidebarClose'),
        s=document.getElementById('sidebar'),
        m=document.querySelector('.main-content'),
        subs=document.querySelectorAll('.submenu-toggle');

  t.onclick=()=>{s.classList.toggle('active');m?.classList.toggle('shifted')}
  c.onclick=()=>{s.classList.remove('active');m?.classList.remove('shifted')}

  document.onclick=e=>{
    if(!s.contains(e.target)&&!t.contains(e.target)&&s.classList.contains('active')){
      s.classList.remove('active');m?.classList.remove('shifted')
    }
  }

  subs.forEach(x=>x.onclick=e=>{
    e.preventDefault();x.closest('.has-submenu').classList.toggle('open')
  })
})
</script>
