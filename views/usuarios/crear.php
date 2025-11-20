<?php
/**
 * Vista de formulario para crear un nuevo usuario
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
                    <h1 class="m-0">Crear Nuevo Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/usuarios">Usuarios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
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

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos del Usuario</h3>
                </div>

                <form action="/usuarios/guardar" method="POST" id="formUsuario">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="correo" name="correo" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rol">Rol <span class="text-danger">*</span></label>
                                    <select class="form-control" id="rol" name="rol" required>
                                        <option value="">-- Seleccione un rol --</option>
                                        <?php foreach ($roles as $valor => $nombre): ?>
                                            <option value="<?= $valor ?>"><?= htmlspecialchars($nombre) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info-circle"></i> Información sobre roles</h5>
                                    <ul class="mb-0">
                                        <li><strong>Super Administrador:</strong> Acceso total al sistema sin restricciones.</li>
                                        <li><strong>Administrador:</strong> Gestión de usuarios, estudiantes, padres y reportes.</li>
                                        <li><strong>Tesorería:</strong> Gestión de pagos, deudas y reportes financieros.</li>
                                        <li><strong>Colaborador:</strong> Solo consulta de información y reportes básicos.</li>
                                        <li><strong>Estudiante:</strong> Para asociar a un estudiante registrado. Vea solo su información.</li>
                                        <li><strong>Padre/Tutor:</strong> Para asociar a un padre registrado. Vea información de sus hijos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <a href="/usuarios" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Validar formulario antes de enviar
    $('#formUsuario').submit(function(e) {
        var password = $('#password').val();
        var passwordConfirm = $('#password_confirm').val();
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 8 caracteres');
            return false;
        }
        
        return true;
    });
    
    // Al cambiar el rol, mostrar/ocultar campos adicionales
    $('#rol').change(function() {
        var rol = $(this).val();
        
        if (rol === 'estudiante') {
            alert('Para crear un usuario de tipo estudiante, vaya a la sección de estudiantes y cree un usuario desde allí.');
            $(this).val('');
        } else if (rol === 'padre') {
            alert('Para crear un usuario de tipo padre/tutor, vaya a la sección de padres y cree un usuario desde allí.');
            $(this).val('');
        }
    });
});
</script>

<?php require_once 'views/templates/footer.php'; ?>
