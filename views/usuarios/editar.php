<?php
/**
 * Vista de formulario para editar un usuario existente
 */
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/educa-finanzas/public/index.php?controller=Panel&action=index">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/educa-finanzas/public/index.php?controller=Usuario&action=index">Usuarios</a></li>
                        <li class="breadcrumb-item active">Editar</li>
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

                <form action="/educa-finanzas/public/index.php?controller=Usuario&action=actualizar" method="POST" id="formUsuario">
                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña (dejar en blanco para no cambiarla)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirm">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm">
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
                                            <option value="<?= $valor ?>" <?= $usuario['rol'] === $valor ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="activo" <?= $usuario['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($usuario['id_estudiante']) && $usuario['id_estudiante'] !== null || 
                                  isset($usuario['id_padre']) && $usuario['id_padre'] !== null): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-warning">
                                        <h5><i class="icon fas fa-exclamation-triangle"></i> Información importante</h5>
                                        <p>
                                            <?php if (isset($usuario['id_estudiante']) && $usuario['id_estudiante'] !== null): ?>
                                                Este usuario está asociado a un estudiante. Algunas opciones están limitadas.
                                            <?php elseif (isset($usuario['id_padre']) && $usuario['id_padre'] !== null): ?>
                                                Este usuario está asociado a un padre/tutor. Algunas opciones están limitadas.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info-circle"></i> Información adicional</h5>
                                    <p><strong>Fecha de creación:</strong> <?= isset($usuario['creado']) ? date('d/m/Y H:i', strtotime($usuario['creado'])) : 'N/A' ?></p>
                                    <p><strong>Última actualización:</strong> <?= isset($usuario['actualizado']) ? date('d/m/Y H:i', strtotime($usuario['actualizado'])) : 'N/A' ?></p>
                                    <p><strong>Último acceso:</strong> <?= isset($usuario['ultimo_acceso']) && $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="/educa-finanzas/public/index.php?controller=Usuario&action=index" class="btn btn-secondary">
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
        
        if (password || passwordConfirm) {
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
        }
        
        return true;
    });
    
    <?php if ($usuario['id_estudiante'] || $usuario['id_padre']): ?>
    // Bloquear cambio de rol para usuarios asociados a estudiantes o padres
    $('#rol').attr('readonly', true).css('pointer-events', 'none');
    <?php endif; ?>
});
