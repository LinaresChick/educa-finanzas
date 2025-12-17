<?php

require_once VIEWS_PATH . '/templates/header.php'; 


?>
<style>
    /* ===== ESTILOS PERSONALIZADOS ===== */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .card-header {
        background: linear-gradient(90deg, #00ff00ff, #00aaff);
        color: white !important;
        border-radius: 1rem 1rem 0 0 !important;
    }

    .card-header h6 {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .form-label {
        font-weight: 500;
        color: #333;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ccc;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #66ff00ff;
        box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.25);
    }

    .btn-primary {
        background: linear-gradient(90deg, #00ff5eff, #00ff04ff);
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: transform 0.2s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        background: linear-gradient(90deg, #00cc66ff, #16dd00ff);
    }

    .btn-secondary {
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .section-title {
        color: #007bff;
        font-weight: 700;
    }

    h5.border-bottom {
        color: #00ff26ff;
        font-weight: 600;
        border-color: #00ff11ff !important;
    }

    textarea {
        resize: none;
    }

    .alert {
        border-radius: 0.5rem;
    }

    /* Animación sutil al aparecer los campos de cuenta */
    .datos-cuenta {
        transition: all 0.4s ease;
    }
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=index" class="btn btn-secondary">
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
            <form action="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=guardar" method="post">
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
                        <label for="dni" class="form-label">DNI *</label>
                        <input type="text" 
                        class="form-control" 
                        id="dni" 
                        name="dni" 
                        maxlength="8"
                        minlength="8"
                        pattern="\d{8}"
                        required>
                        <div id="dni-alert" class="text-danger mt-1" style="display:none;">
                            El DNI debe tener exactamente 8 números.
                        </div>
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
                        <label for="id_seccion" class="form-label">Grado y Sección</label>
                        <select class="form-select" id="id_seccion" name="id_seccion">
                            <option value="">Seleccione una sección</option>
                            <?php
                            // Mostrar las secciones (tabla `secciones`) pero enviar el id_salon correspondiente
                            if (!empty($secciones) && is_array($secciones)):
                                foreach ($secciones as $sec):
                                    // Buscar un salón activo que pertenezca a esta sección
                                    $valor = '';
                                    if (!empty($salones) && is_array($salones)) {
                                        foreach ($salones as $salon) {
                                            if (isset($salon['id_seccion']) && $salon['id_seccion'] == $sec['id_seccion']) {
                                                $valor = $salon['id_salon'];
                                                break;
                                            }
                                        }
                                    }
                            ?>
                                <option value="<?php echo $sec['id_seccion']; ?>">
                                    <?php echo htmlspecialchars($sec['nombre'] . (!empty($sec['descripcion']) ? ' - ' . $sec['descripcion'] : '')); ?>
                                </option>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <option value="">No hay secciones disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mencion" class="form-label">Mención o Especialidad</label>
                        <input type="text" class="form-control" id="mencion" name="mencion">
                        <div class="form-text">Solo aplica para niveles superiores</div>
                    </div>
                </div>
                
                <!-- Datos de Pago -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos de Pago</h5>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="monto" class="form-label">Monto de Pensión</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" placeholder="0.00">
                        <div class="form-text">Monto mensual de pensión</div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                        <div class="form-text">Fecha límite de pago mensual</div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="estado_pago" class="form-label">Estado de Pago</label>
                        <select class="form-select" id="estado_pago" name="estado_pago">
                            <option value="al_dia">Al Día</option>
                            <option value="pendiente" selected>Pendiente</option>
                            <option value="atrasado">Atrasado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Datos de Acceso -->
                
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Estudiante
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=index" class="btn btn-secondary">Cancelar</a>
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
// VALIDACIÓN DNI EN TIEMPO REAL
const dniInput = document.getElementById('dni');
const dniAlert = document.getElementById('dni-alert');

dniInput.addEventListener('input', function () {
    // Solo números
    this.value = this.value.replace(/\D/g, '');

    // Mostrar advertencia
    if (this.value.length !== 8) {
        dniAlert.style.display = 'block';
    } else {
        dniAlert.style.display = 'none';
    }
});

</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>