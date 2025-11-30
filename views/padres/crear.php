<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>/padres" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
    
    <?php
        // Compatibilidad con el sistema de flash: puede venir como 'flash' (array) o como variables sueltas
        if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            $tipoRaw = $f['tipo'] ?? 'info';
            // Mapear tipos a clases bootstrap
            $tipoMap = [ 'exito' => 'success', 'error' => 'danger', 'info' => 'info' ];
            $bootTipo = $tipoMap[$tipoRaw] ?? $tipoRaw;
            $mensajeFlash = $f['mensaje'] ?? '';
            echo "<div class=\"alert alert-{$bootTipo} alert-dismissible fade show\" role=\"alert\">" . htmlspecialchars($mensajeFlash) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>";
            unset($_SESSION['flash']);
        } elseif (isset($_SESSION['flash_mensaje'])) {
            // antiguo formato
            echo "<div class=\"alert alert-" . htmlspecialchars($_SESSION['flash_tipo'] ?? 'info') . " alert-dismissible fade show\" role=\"alert\">" . htmlspecialchars($_SESSION['flash_mensaje']) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>";
            unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);
        }
    ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Registro</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=guardar" method="post">
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
                    
                    <div class="col-md-6 mb-3">
                        <label for="dni" class="form-label">DNI *</label>
                        <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required>
                        <small id="dniHelp" class="text-danger" style="display:none;">
                            El DNI debe tener exactamente 8 números.
                        </small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="relacion" class="form-label">Relación *</label>
                        <select class="form-select" id="relacion" name="relacion" required>
                            <option value="Padre">Padre</option>
                            <option value="Madre">Madre</option>
                            <option value="Tutor">Tutor Legal</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="20">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
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
                                Crear cuenta de acceso para este padre/tutor
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3 datos-cuenta" style="display: none;">
                        <label for="correo_usuario" class="form-label">Correo Electrónico (usuario) *</label>
                        <input type="email" class="form-control" id="correo_usuario" name="correo_usuario">
                        <div class="form-text">Este correo será usado para el acceso al sistema</div>
                    </div>
                    
                    <div class="col-md-6 mb-3 datos-cuenta" style="display: none;">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Padre/Tutor
                        </button>
                        <a href="<?php echo BASE_URL; ?>/padres" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ======== MAYÚSCULAS AUTOMÁTICAS ========
    function capitalizar(texto) {
        return texto.replace(/\b\w/g, c => c.toUpperCase());
    }

    document.getElementById('nombres').addEventListener('input', function() {
        this.value = capitalizar(this.value);
    });

    document.getElementById('apellidos').addEventListener('input', function() {
        this.value = capitalizar(this.value);
    });

    // ======== VALIDACIÓN DNI ========
    const dni = document.getElementById('dni');
    const dniHelp = document.getElementById('dniHelp');

    dni.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length !== 8) {
            dniHelp.style.display = 'block';
            dni.classList.add('is-invalid');
        } else {
            dniHelp.style.display = 'none';
            dni.classList.remove('is-invalid');
        }
    });

    // ======== BOTÓN DE CONSULTA DNI ========
    const dniField = document.getElementById('dni');

    const btnBuscar = document.createElement("button");
    btnBuscar.type = "button";
    btnBuscar.className = "btn btn-sm btn-primary mt-2";
    btnBuscar.innerHTML = "Buscar DNI";
    dniField.parentNode.appendChild(btnBuscar);

    btnBuscar.addEventListener("click", function () {
        const numero = dniField.value;

        if (numero.length !== 8) {
            alert("El DNI debe tener 8 dígitos.");
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.innerHTML = "Consultando...";

        fetch("https://apiperu.dev/api/dni", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Authorization": "Bearer e3cd144f611320be744de2b653d16ba0e2c48a8a9a106e92e80450dd804173d7"
            },
            body: JSON.stringify({ dni: numero })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success === true) {
                document.getElementById("nombres").value = capitalizar(data.data.nombres);
                document.getElementById("apellidos").value = capitalizar(
                    data.data.apellido_paterno + " " + data.data.apellido_materno
                );
            } else {
                alert("No se encontró información del DNI.");
            }
        })
        .catch(() => {
            alert("Error consultando la API.");
        })
        .finally(() => {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = "Buscar DNI";
        });
    });

    // ======== CAMPOS DE ACCESO ========
    const crearCuentaCheck = document.getElementById('crear_cuenta');
    const datosCuentaElements = document.querySelectorAll('.datos-cuenta');
    const correoUsuarioInput = document.getElementById('correo_usuario');
    const passwordInput = document.getElementById('password');
    const correoInput = document.getElementById('correo');
    
    function toggleCuentaFields() {
        const isChecked = crearCuentaCheck.checked;
        datosCuentaElements.forEach(el => el.style.display = isChecked ? 'block' : 'none');
        correoUsuarioInput.required = isChecked;
        passwordInput.required = isChecked;
        if (isChecked && correoInput.value && !correoUsuarioInput.value) {
            correoUsuarioInput.value = correoInput.value;
        }
    }
    
    crearCuentaCheck.addEventListener('change', toggleCuentaFields);
    correoInput.addEventListener('change', function() {
        if (crearCuentaCheck.checked && this.value && !correoUsuarioInput.value) {
            correoUsuarioInput.value = this.value;
        }
    });
    
    toggleCuentaFields();
});
</script>


<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
