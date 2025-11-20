<?php
/**
 * Vista de roles del sistema
 */
require_once 'views/templates/header.php';
require_once 'views/templates/navbar.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Roles y Permisos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/usuarios">Usuarios</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Mensajes de alerta -->
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['exito']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Roles y Permisos del Sistema</h3>
                </div>

                <div class="card-body">
                    <p>El sistema utiliza un modelo de roles para administrar los permisos. A continuación se describe cada rol y sus permisos:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Descripción</th>
                                    <th>Permisos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-danger">Super Administrador</span></td>
                                    <td>Administrador con acceso total al sistema</td>
                                    <td>
                                        <ul>
                                            <li>Acceso completo a todas las funciones</li>
                                            <li>Gestión de usuarios y roles</li>
                                            <li>Configuración del sistema</li>
                                            <li>Puede crear otros superadministradores</li>
                                            <li>Acceso a todos los reportes</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning">Administrador</span></td>
                                    <td>Administrador con acceso a la mayoría de funciones</td>
                                    <td>
                                        <ul>
                                            <li>Gestión de usuarios (excepto superadministradores)</li>
                                            <li>Gestión de estudiantes y padres</li>
                                            <li>Gestión de pagos y deudas</li>
                                            <li>Acceso a reportes administrativos y financieros</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary">Tesorería</span></td>
                                    <td>Usuario encargado de la gestión financiera</td>
                                    <td>
                                        <ul>
                                                <li>Gestión de pagos, deudas y reportes financieros.</li>
                                                <li>Gestión completa de pagos</li>
                                            <li>Registrar y anular pagos</li>
                                            <li>Emisión de comprobantes</li>
                                            <li>Gestión de deudas</li>
                                            <li>Acceso a reportes financieros</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info">Colaborador</span></td>
                                    <td>Personal con acceso limitado al sistema</td>
                                    <td>
                                        <ul>
                                            <li>Consulta de información de estudiantes</li>
                                            <li>Consulta de pagos y deudas (sin modificación)</li>
                                            <li>Acceso a reportes básicos</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">Estudiante</span></td>
                                    <td>Acceso para estudiantes registrados</td>
                                    <td>
                                        <ul>
                                            <li>Ver su propia información</li>
                                            <li>Consultar su historial de pagos</li>
                                            <li>Ver sus deudas pendientes</li>
                                            <li>Descargar sus comprobantes</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary">Padre/Tutor</span></td>
                                    <td>Acceso para padres o tutores</td>
                                    <td>
                                        <ul>
                                            <li>Ver información de sus hijos asociados</li>
                                            <li>Consultar el historial de pagos de sus hijos</li>
                                            <li>Ver deudas pendientes de sus hijos</li>
                                            <li>Descargar comprobantes de sus pagos</li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5><i class="icon fas fa-info-circle"></i> Información importante</h5>
                        <p>Los roles y permisos están predefinidos en el sistema y no pueden ser modificados. Para casos especiales, contacte con el administrador del sistema.</p>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="/usuarios" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a usuarios
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'views/templates/footer.php'; ?>
