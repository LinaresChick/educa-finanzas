<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>estudiantes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
    
    <?php if (isset($_SESSION['flash_mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']); ?>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>estudiantes/guardar" method="post">
                <!-- Datos Personales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Personales</h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni" maxlength="20">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                    </div>
                </div>
                
                <!-- Datos Académicos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Académicos</h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="id_salon" class="form-label">Grado y Sección</label>
                        <select class="form-select" id="id_salon" name="id_salon">
                            <option value="">Seleccione una opción</option>
                            <?php foreach($salones as $salon): ?>
                                <option value="<?php echo $salon['id_salon']; ?>"><?php echo $salon['descripcion']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mencion" class="form-label">Mención o Especialidad</label>
                        <input type="text" class="form-control" id="mencion" name="mencion">
                        <div class="form-text">Solo aplica para niveles superiores</div>
                    </div>
                </div>
                
                <!-- Datos de Acceso -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos de Acceso</h5>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="crear_cuenta" name="crear_cuenta" value="1">
                            <label class="form-check-label" for="crear_cuenta">
                                Crear cuenta de acceso para este estudiante
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3 datos-cuenta" style="display: none;">
                        <label for="correo" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="correo" name="correo">
                    </div>
                    
                    <div class="col-md-6 mb-3 datos-cuenta" style="display: none;">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Estudiante
                        </button>
                        <a href="<?php echo BASE_URL; ?>estudiantes" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campos de cuenta
    const crearCuentaCheck = document.getElementById('crear_cuenta');
    const datosCuentaElements = document.querySelectorAll('.datos-cuenta');
    const correoInput = document.getElementById('correo');
    const passwordInput = document.getElementById('password');
    
    function toggleCuentaFields() {
        const isChecked = crearCuentaCheck.checked;
        
        datosCuentaElements.forEach(el => {
            el.style.display = isChecked ? 'block' : 'none';
        });
        
        // Ajustar atributos required
        correoInput.required = isChecked;
        passwordInput.required = isChecked;
    }
    
    crearCuentaCheck.addEventListener('change', toggleCuentaFields);
    toggleCuentaFields(); // Inicializar estado
});
</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
