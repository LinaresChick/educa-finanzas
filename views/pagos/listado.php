<?php
/**
 * Vista de listado de pagos
 */
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
?>

<div class="main-container">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="header-left">
                <h1 class="section-title mb-0">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Gestión de Pagos
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 mt-2">
                        <li class="breadcrumb-item"><a href="/panel" class="text-success">Inicio</a></li>
                        <li class="breadcrumb-item active">Pagos</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=registrar" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Registrar Nuevo Pago
                </a>
            </div>
        </div>
    </div>

    <div class="content-card">
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filtros de búsqueda</h5>
            </div>
            <div class="card-body">
                <form action="" method="GET" class="row">
                    <input type="hidden" name="controller" value="Pago">
                    <input type="hidden" name="action" value="index">
                    
                    <div class="col-md-3 mb-3">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?= isset($filtros['fecha_inicio']) ? $filtros['fecha_inicio'] : '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                               value="<?= isset($filtros['fecha_fin']) ? $filtros['fecha_fin'] : '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="estudiante">Estudiante:</label>
                        <input type="text" class="form-control" id="estudiante" name="estudiante" 
                               placeholder="Nombre o apellido"
                               value="<?= isset($filtros['estudiante']) ? $filtros['estudiante'] : '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="concepto">Concepto:</label>
                        <input type="text" class="form-control" id="concepto" name="concepto" 
                               placeholder="Concepto de pago"
                               value="<?= isset($filtros['concepto']) ? $filtros['concepto'] : '' ?>">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="metodo_pago">Método de Pago:</label>
                        <select class="form-control" id="metodo_pago" name="metodo_pago">
                            <option value="">Todos</option>
                            <option value="efectivo" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'efectivo') ? 'selected' : '' ?>>Efectivo</option>
                            <option value="transferencia" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'transferencia') ? 'selected' : '' ?>>Transferencia</option>
                            <option value="deposito" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'deposito') ? 'selected' : '' ?>>Depósito</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="?controller=Pago&action=index" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>

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

        <!-- Sección de filtros -->
        <div class="filter-section mb-4">
            <form action="/pagos" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="busqueda" 
                               placeholder="Buscar por estudiante o concepto" 
                               value="<?= $busqueda ?? '' ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="metodo_pago" id="metodo_pago" class="form-select">
                        <option value="">Todos los métodos</option>
                        <option value="efectivo" <?= ($metodo_pago ?? '') == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                        <option value="transferencia" <?= ($metodo_pago ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                        <option value="tarjeta" <?= ($metodo_pago ?? '') == 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="banco" id="banco" class="form-select">
                        <option value="">Todos los bancos</option>
                        <option value="BCP" <?= ($banco ?? '') == 'BCP' ? 'selected' : '' ?>>BCP</option>
                        <option value="BBVA" <?= ($banco ?? '') == 'BBVA' ? 'selected' : '' ?>>BBVA</option>
                        <option value="Interbank" <?= ($banco ?? '') == 'Interbank' ? 'selected' : '' ?>>Interbank</option>
                        <option value="Scotiabank" <?= ($banco ?? '') == 'Scotiabank' ? 'selected' : '' ?>>Scotiabank</option>
                        <option value="Otro" <?= ($banco ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" name="fecha_inicio" 
                           id="fecha_inicio" placeholder="Fecha inicio" 
                           value="<?= $fecha_inicio ?? '' ?>">
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" name="fecha_fin" 
                           id="fecha_fin" placeholder="Fecha fin" 
                           value="<?= $fecha_fin ?? '' ?>">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table table-hover dataTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th style="width: 15%"><i class="fas fa-user-graduate me-2"></i>Estudiante</th>
                            <th style="width: 15%"><i class="fas fa-file-invoice me-2"></i>Concepto</th>
                            <th style="width: 10%"><i class="fas fa-coins me-2"></i>Monto</th>
                            <th style="width: 10%"><i class="fas fa-credit-card me-2"></i>Método</th>
                            <th style="width: 10%"><i class="fas fa-calendar me-2"></i>Fecha</th>
                            <th style="width: 10%"><i class="fas fa-receipt me-2"></i>Comprobante</th>
                            <th style="width: 10%"><i class="fas fa-dollar-sign me-2"></i>Monto Final</th>
                            <th style="width: 10%"><i class="fas fa-calendar-day me-2"></i>Vencimiento</th>
                            <th style="width: 10%"><i class="fas fa-hourglass-half me-2"></i>Estado</th>
                            <th class="text-center" style="width: 10%"><i class="fas fa-cogs me-2"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                            <tr>
                                <td colspan="11" class="text-center">No hay pagos registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td class="text-center"><?= $pago['id_pago'] ?></td>
                                    <td><?= htmlspecialchars($pago['estudiante_nombre_completo'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($pago['concepto']) ?></td>
                                    <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                                    <td>
                                        <?php
                                        $metodo = '';
                                        switch ($pago['metodo_pago']) {
                                            case 'efectivo': $metodo = 'Efectivo'; break;
                                            case 'transferencia': $metodo = 'Transferencia bancaria'; break;
                                            case 'deposito': $metodo = 'Depósito bancario'; break;
                                            default: $metodo = htmlspecialchars($pago['metodo_pago']);
                                        }
                                        echo $metodo;
                                        ?></td>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($pago['foto_baucher'])): ?>
                                            <a href="/public/uploads/vouchers/<?= $pago['foto_baucher'] ?>" 
                                               target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Sin comprobante</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>S/ <?= number_format($pago['monto'] - ($pago['descuento'] ?? 0) + ($pago['aumento'] ?? 0), 2) ?></td>
                                    <td><?= !empty($pago['fecha_vencimiento']) ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : 'N/A' ?></td>
                                    <td>
                                        <?php
                                        $estado = 'Pendiente';
                                        $badgeClass = 'badge-warning';
                                        if (isset($pago['estado'])) {
                                            switch ($pago['estado']) {
                                                case 'pagado':
                                                    $estado = 'Pagado';
                                                    $badgeClass = 'badge-success';
                                                    break;
                                                case 'vencido':
                                                    $estado = 'Vencido';
                                                    $badgeClass = 'badge-danger';
                                                    break;
                                                case 'anulado':
                                                    $estado = 'Anulado';
                                                    $badgeClass = 'badge-secondary';
                                                    break;
                                            }
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $estado ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="?controller=Pago&action=comprobante&id=<?= $pago['id_pago'] ?>" 
                                               class="btn btn-sm btn-info" title="Ver comprobante">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-toggle="modal" 
                                                    data-target="#modalAnular" 
                                                    data-id="<?= $pago['id_pago'] ?>"
                                                    data-info="Pago #<?= $pago['id_pago'] ?> - S/ <?= number_format($pago['monto'], 2) ?>"
                                                    title="Anular pago">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar pagos -->
<div class="modal fade" id="modalAnular" tabindex="-1" role="dialog" aria-labelledby="modalAnularLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="modalAnularLabel">Eliminar Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAnular" action="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=eliminar" method="POST">
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el siguiente pago?</p>
                    <p id="infoPago" class="font-weight-bold"></p>
                    <p>Esta acción no se puede deshacer.</p>
                    <input type="hidden" name="id_pago" id="id_pago" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('.dataTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[5, 'desc']],
        pageLength: 25,
        columnDefs: [
            {targets: [0], width: "5%"},
            {targets: [1], width: "15%"},
            {targets: [2], width: "15%"},
            {targets: [3,4,5,6,7,8,9], width: "10%"},
            {targets: [10], width: "10%", orderable: false}
        ],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Manejar el envío del formulario de eliminación
    $('#formAnular').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#modalAnular').modal('hide');
                // Recargar la tabla actual
                table.ajax.reload(null, false);
                // Mostrar mensaje de éxito
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'El pago ha sido eliminado correctamente',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el pago',
                    icon: 'error'
                });
            }
        });
    });

    $('#modalAnular').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var info = button.data('info');
        var modal = $(this);
        modal.find('#infoPago').text(info);
        modal.find('#id_pago').val(id);
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- Custom JavaScript -->
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>/public/js/pagos.js"></script>
