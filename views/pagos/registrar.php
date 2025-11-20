<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
?>

<div class="main-container">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Registrar Pago
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php?controller=Panel" class="text-success">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=Pago" class="text-success">Pagos</a></li>
                        <li class="breadcrumb-item active">Registrar Pago</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="content-card">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="index.php?controller=Pago&action=registrar" method="POST" enctype="multipart/form-data" id="formPago" class="needs-validation" novalidate>
            <div class="row">
                <!-- Información del Estudiante -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-graduate me-2"></i>
                                Información del Estudiante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="id_estudiante">Estudiante <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_estudiante" name="id_estudiante" required>
                                    <option value="">Seleccione un estudiante</option>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                        <option value="<?= $estudiante['id_estudiante'] ?>" 
                                                data-monto="<?= $estudiante['monto'] ?>"
                                                data-fecha-vencimiento="<?= $estudiante['fecha_vencimiento'] ?>"
                                                data-estado-pago="<?= $estudiante['estado_pago'] ?>">
                                            <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un estudiante.</div>
                            </div>

                            <!-- Campos de información del estudiante -->
                            <div id="info-estudiante" class="d-none">
                                <div class="alert alert-info">
                                    <div class="mb-2">
                                        <strong>Monto mensual:</strong> S/ <span id="monto-estudiante">0.00</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Fecha vencimiento:</strong> <span id="fecha-vencimiento">--/--/----</span>
                                    </div>
                                    <div>
                                        <strong>Estado de pago:</strong> <span id="estado-pago-badge"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles del Pago -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Detalles del Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="concepto">Concepto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="concepto" name="concepto" required>
                                <div class="invalid-feedback">Por favor ingrese el concepto del pago.</div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="banco">Banco <span class="text-danger">*</span></label>
                                <select class="form-select" id="banco" name="banco" required>
                                    <option value="">Seleccione un banco</option>
                                    <option value="BCP">BCP</option>
                                    <option value="BBVA">BBVA</option>
                                    <option value="Interbank">Interbank</option>
                                    <option value="Scotiabank">Scotiabank</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un banco.</div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="monto">Monto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0" required>
                                    <div class="invalid-feedback">Por favor ingrese un monto válido.</div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccione un método</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia bancaria</option>
                                    <option value="tarjeta">Tarjeta crédito/débito</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un método de pago.</div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
                                <div class="invalid-feedback">Por favor seleccione la fecha de pago.</div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="descuento">Descuento</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control" id="descuento" name="descuento" step="0.01" min="0" value="0">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="aumento">Aumento</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control" id="aumento" name="aumento" step="0.01" min="0" value="0">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="foto_baucher">Voucher o Comprobante</label>
                                <input type="file" class="form-control" id="foto_baucher" name="foto_baucher" accept="image/jpeg,image/png">
                                <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Máximo 2MB.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label>Pagador</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="pagador_tipo" id="pagador_padre" value="padre" checked>
                                        <label class="form-check-label" for="pagador_padre">Padre/Tutor del estudiante</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="pagador_tipo" id="pagador_otro" value="otro">
                                        <label class="form-check-label" for="pagador_otro">Otra persona</label>
                                    </div>
                                </div>

                                <div id="pagador-seleccion" class="mb-2">
                                    <select class="form-select" id="select_padres" name="id_padre">
                                        <option value="">Seleccione un padre/tutor</option>
                                    </select>
                                </div>

                                <div id="pagador-otro" class="d-none">
                                    <div class="mb-2">
                                        <input type="text" class="form-control" id="pagador_nombre" name="pagador_nombre" placeholder="Nombre del pagador">
                                    </div>
                                    <div>
                                        <input type="text" class="form-control" id="pagador_dni" name="pagador_dni" placeholder="DNI del pagador">
                                    </div>
                                </div>

                                <small class="form-text text-muted">Si el pagador es un padre/tutor, seleccione de la lista; de lo contrario elija "Otra persona" e ingrese nombre y DNI.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-end">
                    <a href="index.php?controller=Pago" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Registrar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formPago');
    const selectEstudiante = document.getElementById('id_estudiante');
    const infoEstudiante = document.getElementById('info-estudiante');
    const montoEstudiante = document.getElementById('monto-estudiante');
    const fechaVencimiento = document.getElementById('fecha-vencimiento');
    const estadoPagoBadge = document.getElementById('estado-pago-badge');

    // Función para formatear fecha
    function formatearFecha(fecha) {
        if (!fecha) return '--/--/----';
        const f = new Date(fecha);
        return f.toLocaleDateString('es-PE');
    }

    // Actualizar información del estudiante cuando se seleccione uno
    selectEstudiante.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (this.value) {
            const monto = option.dataset.monto;
            const fechaVenc = option.dataset.fechaVencimiento;
            const estadoPago = option.dataset.estadoPago;

            montoEstudiante.textContent = parseFloat(monto).toFixed(2);
            fechaVencimiento.textContent = formatearFecha(fechaVenc);

            // Configurar el badge de estado
            let badgeClass = '';
            switch(estadoPago) {
                case 'pagado':
                    badgeClass = 'success';
                    break;
                case 'vencido':
                    badgeClass = 'danger';
                    break;
                default:
                    badgeClass = 'warning';
            }
            estadoPagoBadge.innerHTML = `<span class="badge badge-${badgeClass}">${estadoPago.charAt(0).toUpperCase() + estadoPago.slice(1)}</span>`;
            
            infoEstudiante.classList.remove('d-none');
            // Cargar padres asociados al estudiante
            fetch('index.php?controller=Estudiante&action=obtenerPadresJSON&id=' + this.value)
                .then(res => res.json())
                .then(data => {
                    const selectPadres = document.getElementById('select_padres');
                    selectPadres.innerHTML = '<option value="">Seleccione un padre/tutor</option>';
                    data.forEach(function(p) {
                        const opt = document.createElement('option');
                        opt.value = p.id_padre;
                        opt.textContent = p.nombre_completo + (p.dni ? (' - DNI: ' + p.dni) : '');
                        opt.dataset.dni = p.dni || '';
                        opt.dataset.nombre = p.nombre_completo || '';
                        selectPadres.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error cargando padres:', err));
        } else {
            infoEstudiante.classList.add('d-none');
        }
    });

    // Pagador: alternar tipo
    const radioPadre = document.getElementById('pagador_padre');
    const radioOtro = document.getElementById('pagador_otro');
    const seccionSeleccion = document.getElementById('pagador-seleccion');
    const seccionOtro = document.getElementById('pagador-otro');

    function actualizarPagadorUI() {
        if (radioPadre.checked) {
            seccionSeleccion.classList.remove('d-none');
            seccionOtro.classList.add('d-none');
        } else {
            seccionSeleccion.classList.add('d-none');
            seccionOtro.classList.remove('d-none');
        }
    }

    radioPadre.addEventListener('change', actualizarPagadorUI);
    radioOtro.addEventListener('change', actualizarPagadorUI);
    actualizarPagadorUI();

    // Cuando se seleccione un padre, autopoblar nombre y dni en inputs ocultos si hace falta
    document.getElementById('select_padres').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            // Si se quiere autopoblar pagador_nombre/dni cuando se envíe, pueden leerse en el servidor por id_padre
        }
    });

    // Antes de enviar el formulario, si el tipo es "otro" validar nombre y dni
    form.addEventListener('submit', function(event) {
        if (document.getElementById('pagador_otro').checked) {
            const nombre = document.getElementById('pagador_nombre').value.trim();
            const dni = document.getElementById('pagador_dni').value.trim();
            if (!nombre || !dni) {
                event.preventDefault();
                event.stopPropagation();
                alert('Por favor ingrese nombre y DNI del pagador.');
                return;
            }
        }

    // Validación del formulario
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Inicializar la fecha de pago con la fecha actual
    document.getElementById('fecha_pago').valueAsDate = new Date();
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
