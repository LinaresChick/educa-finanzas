<?php
/**
 * Vista de formulario para registrar un nuevo pago
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
                    <h1 class="m-0">Registrar Nuevo Pago</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/pagos">Pagos</a></li>
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

                <form action="/pagos/guardar" method="POST" id="formPago">
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

                            <!-- Concepto y monto -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="concepto">Concepto <span class="text-danger">*</span></label>
                                    <input type="text" name="concepto" id="concepto" class="form-control" required>
                                </div>
                            </div>

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

                            <!-- Método de pago -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago <span class="text-danger">*</span></label>
                                    <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                                        <option value="">-- Seleccione un método --</option>
                                        <?php foreach ($metodos_pago as $valor => $nombre): ?>
                                            <option value="<?= $valor ?>"><?= $nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
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
                        <a href="/pagos" class="btn btn-secondary">
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
    
    // Cambio de estudiante: recargar página con el nuevo ID
    $('#id_estudiante').change(function() {
        var idEstudiante = $(this).val();
        if(idEstudiante) {
            window.location.href = '/pagos/registrar?id_estudiante=' + idEstudiante;
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

<?php require_once 'views/templates/footer.php'; ?>
