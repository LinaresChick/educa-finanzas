<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
?>

<div class="main-container">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title mb-2">
                    <i class="fas fa-user-circle me-2 text-primary"></i>Mi Perfil
                </h1>
                <p class="text-muted">Administra tu información personal y contraseña</p>
            </div>
            <div>
                <a href="index.php?controller=Panel&action=dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Panel
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['exito']); unset($_SESSION['exito']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="index.php?controller=Usuario&action=perfil" method="POST" id="formPerfil">
        <div class="row g-4">
            <!-- Información Personal -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nombre" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                       required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="correo" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"
                                       required>
                                <small class="text-muted">Este correo se usa para iniciar sesión</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rol</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($usuario['rol'] ?? '') ?>"
                                       disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estado</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars(ucfirst($usuario['estado'] ?? 'activo')) ?>"
                                       disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Deja estos campos en blanco si no deseas cambiar tu contraseña
                        </p>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Contraseña Actual</label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password_actual" 
                                           id="passwordActual"
                                           class="form-control"
                                           placeholder="Ingresa tu contraseña actual">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('passwordActual')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password_nueva" 
                                           id="passwordNueva"
                                           class="form-control"
                                           placeholder="Mínimo 6 caracteres"
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('passwordNueva')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirmar Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password_confirm" 
                                           id="passwordConfirm"
                                           class="form-control"
                                           placeholder="Repite la nueva contraseña"
                                           minlength="6">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="togglePassword('passwordConfirm')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="index.php?controller=Panel&action=dashboard" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>

            <!-- Panel Lateral con Información Adicional -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Cuenta</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="user-avatar-large mx-auto bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 100px; height: 100px; border-radius: 50%; font-size: 48px; font-weight: bold;">
                                <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
                            </div>
                            <h5 class="mt-3 mb-0"><?= htmlspecialchars($usuario['nombre'] ?? '') ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($usuario['correo'] ?? '') ?></p>
                            <span class="badge bg-<?= ($usuario['estado'] ?? 'activo') == 'activo' ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars(ucfirst($usuario['estado'] ?? 'activo')) ?>
                            </span>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-user-tag me-1"></i>Rol en el Sistema
                            </small>
                            <strong><?= htmlspecialchars($usuario['rol'] ?? '') ?></strong>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-calendar-plus me-1"></i>Fecha de Registro
                            </small>
                            <strong><?= isset($usuario['fecha_registro']) ? date('d/m/Y', strtotime($usuario['fecha_registro'])) : 'N/A' ?></strong>
                        </div>

                        <?php if (isset($usuario['ultima_sesion'])): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-clock me-1"></i>Última Sesión
                            </small>
                            <strong><?= date('d/m/Y H:i', strtotime($usuario['ultima_sesion'])) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Seguridad</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Contraseña encriptada</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Sesión segura activa</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <small>Cambia tu contraseña regularmente</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.currentTarget.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validar que las contraseñas coincidan
document.getElementById('formPerfil').addEventListener('submit', function(e) {
    const passwordNueva = document.getElementById('passwordNueva').value;
    const passwordConfirm = document.getElementById('passwordConfirm').value;
    const passwordActual = document.getElementById('passwordActual').value;
    
    // Si están intentando cambiar la contraseña
    if (passwordNueva || passwordConfirm) {
        if (!passwordActual) {
            e.preventDefault();
            alert('Debes ingresar tu contraseña actual para cambiarla');
            return false;
        }
        
        if (passwordNueva !== passwordConfirm) {
            e.preventDefault();
            alert('Las contraseñas nuevas no coinciden');
            return false;
        }
        
        if (passwordNueva.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres');
            return false;
        }
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
