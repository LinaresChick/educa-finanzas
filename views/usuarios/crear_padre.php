<?php
/**
 * Vista para crear un usuario para un padre/tutor
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
                    <h1 class="m-0">Crear Acceso para Padre/Tutor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/padres">Padres</a></li>
                        <li class="breadcrumb-item"><a href="/padres/detalle/<?= $padre['id_padre'] ?>">Detalle</a></li>
                        <li class="breadcrumb-item active">Crear Acceso</li>
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

            <!-- Información del padre/tutor -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Información del Padre/Tutor</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($padre['nombres'] . ' ' . $padre['apellidos']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>DNI:</strong> <?= htmlspecialchars($padre['dni']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($padre['telefono']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos de Acceso</h3>
                </div>

                <form action="/usuarios/crear_padre/<?= $padre['id_padre'] ?>" method="POST" id="formUsuario">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Está a punto de crear una cuenta de acceso al sistema para este padre/tutor. Se creará un usuario con rol de "Padre" y acceso limitado a información de sus hijos asociados.</span>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="correo" name="correo" required>
                                    <small class="form-text text-muted">Este será el nombre de usuario para acceder al sistema.</small>
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

                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="confirmar" name="confirmar" required>
                                <label class="form-check-label" for="confirmar">He verificado que la información es correcta</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Crear Cuenta
                        </button>
                        <a href="/padres/detalle/<?= $padre['id_padre'] ?>" class="btn btn-secondary">
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
        
        if (!$('#confirmar').is(':checked')) {
            e.preventDefault();
            alert('Debe confirmar que la información es correcta');
            return false;
        }
        
        return true;
    });
});
</script>

<?php require_once 'views/templates/footer.php'; ?>