<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
?>

<style>
    /* COLORES PASTEL */
    .pastel-blue   { background: #cfe9ff !important; }
    .pastel-green  { background: #ddf7dd !important; }
    .pastel-yellow { background: #fff7cc !important; }
    .pastel-orange { background: #ffe4c4 !important; }

    /* Tarjetas suaves */
    .card {
        border-radius: 12px;
        border: none;
    }
    .card-header {
        border-radius: 12px 12px 0 0;
        font-weight: 600;
    }
    .section-title {
        font-weight: 700;
    }
</style>

<div class="main-container">
    <!-- ENCABEZADO -->
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title mb-2">
                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
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

    <!-- ALERTA DE ERROR -->
    <div class="content-card">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <form action="index.php?controller=Pago&action=registrar" method="POST" enctype="multipart/form-data" id="formPago" class="needs-validation" novalidate>

            <div class="row g-4">

                <!-- SECCIÓN 1: INFORMACIÓN DEL ESTUDIANTE -->
                <div class="col-md-6">
                    <div class="card shadow-sm pastel-blue">
                        <div class="card-header">
                            <i class="fas fa-user-graduate me-2"></i> Información del Estudiante
                        </div>

                        <div class="card-body">

                            <!-- Selector de estudiante -->
                            <div class="form-group mb-3">
                                <label for="id_estudiante">Estudiante <span class="text-danger">*</span></label>
                                <select class="form-select shadow-sm" id="id_estudiante" name="id_estudiante" required>
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

                            <!-- Información dinámica -->
                            <div id="info-estudiante" class="d-none">
                                <div class="alert alert-info shadow-sm p-3 rounded">
                                    <div class="d-flex justify-content-between">
                                        <strong>Monto mensual:</strong>
                                        <span>S/ <span id="monto-estudiante">0.00</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <strong>Fecha vencimiento:</strong>
                                        <span id="fecha-vencimiento">--/--/----</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <strong>Estado:</strong>
                                        <span id="estado-pago-badge"></span>
                                    </div>
                                </div>
                            </div>
                                            <!-- SECCIÓN 3: PAGADOR -->
                
                        <div class="card-header">
                            <i class="fas fa-user-check me-2"></i> Información del Pagador
                        </div>

                        <div class="card-body">

                            <div class="mb-3">
                                <input type="hidden" id="pagador_tipo" name="pagador_tipo" value="padre">
                                <div class="btn-group" role="group" aria-label="Tipo de pagador">
                                    <button type="button" class="btn btn-outline-primary active" id="btn_pagador_padre" data-value="padre">Padre/Tutor</button>
                                    <button type="button" class="btn btn-outline-primary" id="btn_pagador_otro" data-value="otro">Otra persona</button>
                                </div>
                            </div>

                            <div id="pagador-seleccion" class="mb-3">
                                <label>Seleccione padre/tutor</label>
                                <select class="form-select" id="select_padres" name="id_padre"></select>
                            </div>

                            <div id="pagador-otro" class="d-none">
                                <label>Nombre</label>
                                <input type="text" class="form-control mb-2" id="pagador_nombre" name="pagador_nombre">

                                <label>DNI</label>
                                <input type="text" class="form-control" id="pagador_dni" name="pagador_dni">
                            </div>

                        </div>
                    

                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DETALLES DEL PAGO -->
                <div class="col-md-6">
                    <div class="card shadow-sm pastel-green">
                        <div class="card-header">
                            <i class="fas fa-file-invoice-dollar me-2"></i> Detalles del Pago
                        </div>

                        <div class="card-body">

                            <div class="form-group mb-3">
                                <label>Concepto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control shadow-sm" name="concepto" required>
                            </div>

                            <div class="form-group mb-3">
                                <label>Banco <span class="text-danger">*</span></label>
                                <select class="form-select shadow-sm" name="banco" required>
                                    <option value="">Seleccione un banco</option>
                                    <option value="BCP">BCP</option>
                                    <option value="BBVA">BBVA</option>
                                    <option value="Interbank">Interbank</option>
                                    <option value="Scotiabank">Scotiabank</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label>Monto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control shadow-sm" name="monto" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-select shadow-sm" name="metodo_pago" required>
                                    <option value="">Seleccione un método</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label>Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" class="form-control shadow-sm" id="fecha_pago" name="fecha_pago" required>
                            </div>

                            <div class="form-group mb-3">
                                <label>Descuento</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control shadow-sm" name="descuento" step="0.01" value="0">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Aumento</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" class="form-control shadow-sm" name="aumento" step="0.01" value="0">
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Voucher o Comprobante</label>
                                <input type="file" class="form-control shadow-sm" id="foto_baucher" name="foto_baucher" accept="image/jpeg,image/png">
                            </div>

                            <div class="form-group mb-3">
                                <label>Observaciones</label>
                                <textarea class="form-control shadow-sm" name="observaciones" rows="3"></textarea>
                            </div>

                        </div>
                    </div>
                </div>



            </div>

            <!-- BOTONES -->
            <div class="row mt-4">
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-secondary me-2" id="btn_cancel">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Registrar Pago
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- JS ORIGINAL (solo ordenado) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formPago');
    const selectEstudiante = document.getElementById('id_estudiante');
    const infoEstudiante = document.getElementById('info-estudiante');
    const montoEstudiante = document.getElementById('monto-estudiante');
    const fechaVencimiento = document.getElementById('fecha-vencimiento');
    const estadoPagoBadge = document.getElementById('estado-pago-badge');
    const selectPadres = document.getElementById('select_padres');

    function formatearFecha(fecha) {
        if (!fecha) return '--/--/----';
        const f = new Date(fecha);
        return f.toLocaleDateString('es-PE');
    }

    selectEstudiante.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (this.value) {
            montoEstudiante.textContent = parseFloat(option.dataset.monto).toFixed(2);
            fechaVencimiento.textContent = formatearFecha(option.dataset.fechaVencimiento);

            let badgeClass = 'warning';
            if (option.dataset.estadoPago === 'pagado') badgeClass = 'success';
            if (option.dataset.estadoPago === 'vencido') badgeClass = 'danger';

            estadoPagoBadge.innerHTML =
                `<span class="badge bg-${badgeClass}">${option.dataset.estadoPago}</span>`;

            infoEstudiante.classList.remove('d-none');

            fetch('index.php?controller=Estudiante&action=obtenerPadresJSON&id=' + this.value)
                .then(res => res.json())
                .then(data => {
                    selectPadres.innerHTML = '<option value="">Seleccione un padre/tutor</option>';
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id_padre;
                        opt.textContent = p.nombre_completo + (p.dni ? ' - DNI: ' + p.dni : '');
                        selectPadres.appendChild(opt);
                    });
                });
        } else {
            infoEstudiante.classList.add('d-none');
        }
    });

    const btnPadre = document.getElementById('btn_pagador_padre');
    const btnOtro = document.getElementById('btn_pagador_otro');
    const pagadorTipo = document.getElementById('pagador_tipo');
    const seccionSeleccion = document.getElementById('pagador-seleccion');
    const seccionOtro = document.getElementById('pagador-otro');
    const inputPagadorNombre = document.getElementById('pagador_nombre');
    const inputPagadorDni = document.getElementById('pagador_dni');
    const btnCancel = document.getElementById('btn_cancel');

    function actualizarPagadorUI() {
        if (pagadorTipo.value === 'padre') {
            seccionSeleccion.classList.remove('d-none');
            seccionOtro.classList.add('d-none');
            // Requerir el select de padres cuando corresponda
            selectPadres.required = true;
            inputPagadorNombre.required = false;
            inputPagadorDni.required = false;
        } else {
            seccionSeleccion.classList.add('d-none');
            seccionOtro.classList.remove('d-none');
            selectPadres.required = false;
            inputPagadorNombre.required = true;
            inputPagadorDni.required = true;
        }
    }

    btnPadre.addEventListener('click', function () {
        pagadorTipo.value = 'padre';
        btnPadre.classList.add('active');
        btnOtro.classList.remove('active');
        actualizarPagadorUI();
    });

    btnOtro.addEventListener('click', function () {
        pagadorTipo.value = 'otro';
        btnOtro.classList.add('active');
        btnPadre.classList.remove('active');
        actualizarPagadorUI();
    });

    actualizarPagadorUI();

    form.addEventListener('submit', function (event) {
        if (pagadorTipo.value === 'otro') {
            const nombre = inputPagadorNombre.value.trim();
            const dni = inputPagadorDni.value.trim();
            if (!nombre || !dni) {
                event.preventDefault();
                alert('Ingrese nombre y DNI del pagador.');
                return;
            }
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Cancelar: navegar de forma segura usando JS
    btnCancel.addEventListener('click', function () {
        window.location.href = 'index.php?controller=Pago';
    });

    document.getElementById('fecha_pago').valueAsDate = new Date();
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
