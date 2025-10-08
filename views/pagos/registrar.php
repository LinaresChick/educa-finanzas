<?php
/**
 * Vista de formulario para registrar un nuevo pago
 */
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar.php';
?>

<!-- Script de validación de pagos -->
<script src="<?php echo BASE_URL; ?>/public/js/validacion_pagos.js"></script>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Registrar Nuevo Pago</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?controller=Pago">Pagos</a></li>
                        <li class="breadcrumb-item active">Registrar</li>
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
                    <h3 class="card-title">Datos del Pago</h3>
                </div>

                <form action="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=guardar" method="POST" id="formPago" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row">
                            <!-- Selección de estudiante -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_estudiante">Estudiante <span class="text-danger">*</span></label>
                                    <select name="id_estudiante" id="id_estudiante" class="form-control select2" required>
                                        <option value="">-- Seleccione un estudiante --</option>
                                        <?php foreach ($estudiantes as $estudiante): ?>
                                            <option value="<?= $estudiante['id_estudiante'] ?>" 
                                                <?= (isset($estudiante_seleccionado) && $estudiante_seleccionado['id_estudiante'] == $estudiante['id_estudiante']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?> 
                                                (<?= htmlspecialchars($estudiante['dni']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_deuda">Pago por concepto de deuda (opcional)</label>
                                    <select name="id_deuda" id="id_deuda" class="form-control">
                                        <option value="">-- Seleccione una deuda pendiente --</option>
                                        <?php if (!empty($deudas)): ?>
                                            <?php foreach ($deudas as $deuda): ?>
                                                <option value="<?= $deuda['id_deuda'] ?>" data-monto="<?= $deuda['monto'] ?>" data-concepto="<?= htmlspecialchars($deuda['concepto']) ?>">
                                                    <?= htmlspecialchars($deuda['concepto']) ?> - 
                                                    Vence: <?= date('d/m/Y', strtotime($deuda['fecha_vencimiento'])) ?> - 
                                                    S/ <?= number_format($deuda['monto'], 2) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted">Si selecciona una deuda, se actualizará automáticamente el concepto y monto.</small>
                                </div>
                            </div>

                            <!-- Concepto -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="concepto">Concepto <span class="text-danger">*</span></label>
                                    <input type="text" name="concepto" id="concepto" class="form-control" required>
                                </div>
                            </div>

                            <!-- Monto -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monto">Monto (S/) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de Pago -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Método de pago -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                    <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                                        <option value="">-- Seleccione un método --</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia Bancaria</option>
                                        <option value="deposito">Depósito Bancario</option>
                                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                        <option value="yape">Yape</option>
                                        <option value="plin">Plin</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Banco -->
                            <!-- Banco -->
<div class="col-md-6">
    <div class="form-group">
        <label for="banco">Banco <span class="text-danger">*</span></label>
        <select name="banco" id="banco" class="form-control" required>
            <option value="">-- Seleccione un banco --</option>
            <option value="bcp">BCP</option>
            <option value="bbva">BBVA</option>
            <option value="interbank">Interbank</option>
            <option value="scotiabank">Scotiabank</option>
            <option value="nacion">Banco de la Nación</option>
            <option value="otro">Otro...</option>
        </select>
    </div>

    <!-- Campo adicional solo si se elige "Otro" -->
    <div class="form-group mt-2" id="campo_otro_banco" style="display: none;">
        <label for="otro_banco">Especifique el banco</label>
        <input type="text" name="otro_banco" id="otro_banco" class="form-control" placeholder="Ingrese el nombre del banco">
    </div>
</div>

<script>
    // Mostrar el campo para escribir si se elige "Otro"
    document.getElementById('banco').addEventListener('change', function() {
        const campoOtro = document.getElementById('campo_otro_banco');
        campoOtro.style.display = (this.value === 'otro') ? 'block' : 'none';
    });
</script>


                            <!-- Descuento -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="descuento">Descuento (S/)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" name="descuento" id="descuento" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Aumento -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aumento">Aumento/Mora (S/)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">S/</span>
                                        </div>
                                        <input type="number" name="aumento" id="aumento" class="form-control" step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Foto del Baucher -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="foto_baucher">Foto del Voucher/Baucher <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="foto_baucher" name="foto_baucher" accept="image/*" required>
                                        <label class="custom-file-label" for="foto_baucher">Elegir archivo...</label>
                                    </div>
                                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Máximo 2MB.</small>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observaciones">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de comprobante --><script>
    // Mostrar el campo para escribir si se elige "Otro"
    document.getElementById('banco').addEventListener('change', function() {
        const campoOtro = document.getElementById('campo_otro_banco');
        campoOtro.style.display = (this.value === 'otro') ? 'block' : 'none';
    });
</script>



                        <!-- Sección de comprobante -->
                        <div class="card card-info mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Información de Comprobante</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" name="emitir_comprobante" id="emitir_comprobante">
                                    <label class="form-check-label" for="emitir_comprobante">Emitir comprobante de pago</label>
                                </div>

                                <div id="seccion_comprobante" class="d-none">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tipo_comprobante">Tipo de Comprobante</label>
                                                <select name="tipo_comprobante" id="tipo_comprobante" class="form-control">
                                                    <option value="recibo">Recibo</option>
                                                    <option value="factura">Factura</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    



                                    <p class="text-info">
                                        <i class="fas fa-info-circle"></i>
                                        El número de comprobante será generado automáticamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Pago
                        </button>
                        <a href="<?php echo '/educa-finanzas/public/index.php?controller=Pago&action=index'; ?>" class="btn btn-secondary" id="btnCancelar">
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
    // Inicializar Select2
    $('.select2').select2({
        placeholder: "Seleccione un estudiante",
        width: '100%'
    });
    
    // Mostrar/ocultar sección de comprobante
    $('#emitir_comprobante').change(function() {
        if($(this).is(':checked')) {
            $('#seccion_comprobante').removeClass('d-none');
        } else {
            $('#seccion_comprobante').addClass('d-none');
        }
    });
    
    // Manejar la visibilidad del campo banco según el método de pago
    $('#metodo_pago').change(function() {
        var metodoPago = $(this).val();
        var bancoCampo = $('#banco');
        
        // Lista de métodos que requieren banco
        var metodosConBanco = ['transferencia', 'deposito', 'yape', 'plin'];
        
        if (metodosConBanco.includes(metodoPago)) {
            bancoCampo.prop('disabled', false).prop('required', true);
            bancoCampo.closest('.form-group').find('label').append('<span class="text-danger">*</span>');
        } else {
            bancoCampo.prop('disabled', true).prop('required', false).val('');
            bancoCampo.closest('.form-group').find('label .text-danger').remove();
        }
    });

    // Cambio de estudiante: recargar página con el nuevo ID
    $('#id_estudiante').change(function() {
        var idEstudiante = $(this).val();
        if(idEstudiante) {
            window.location.href = '<?php echo BASE_URL; ?>/index.php?controller=Pago&action=registrar&id_estudiante=' + idEstudiante;
        }
    });
    
    // Seleccionar una deuda: actualizar concepto y monto
    $('#id_deuda').change(function() {
        var opcionSeleccionada = $(this).find('option:selected');
        var concepto = opcionSeleccionada.data('concepto');
        var monto = opcionSeleccionada.data('monto');
        
        if(concepto && monto) {
            $('#concepto').val(concepto);
            $('#monto').val(monto);
        }
    });
    
    // Validar formulario antes de enviar
    $('#formPago').submit(function(e) {
        var idEstudiante = $('#id_estudiante').val();
        var concepto = $('#concepto').val();
        var monto = $('#monto').val();
        var metodoPago = $('#metodo_pago').val();
        
        if(!idEstudiante || !concepto || !monto || !metodoPago) {
            e.preventDefault();
            alert('Todos los campos marcados con * son obligatorios');
            return false;
        }
        
        if(parseFloat(monto) <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor que cero');
            return false;
        }
        
        return true;
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
