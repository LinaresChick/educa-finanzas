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
                            <th class="text-center">#</th>
                            <th><i class="fas fa-user-graduate me-2"></i>Estudiante</th>
                            <th><i class="fas fa-file-invoice me-2"></i>Concepto</th>
                            <th><i class="fas fa-coins me-2"></i>Monto</th>
                            <th><i class="fas fa-credit-card me-2"></i>Método</th>
                            <th><i class="fas fa-calendar me-2"></i>Fecha</th>
                            <th><i class="fas fa-receipt me-2"></i>Comprobante</th>
                            <th><i class="fas fa-dollar-sign me-2"></i>Monto Estudiante</th>
                            <th><i class="fas fa-calendar-day me-2"></i>Fecha Vencimiento</th>
                            <th><i class="fas fa-hourglass-half me-2"></i>Estado Pago</th>
                            <th class="text-center"><i class="fas fa-cogs me-2"></i>Acciones</th>
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
                                    <td><?= $pago['id_pago'] ?></td>
                                    <td><?= htmlspecialchars($pago['estudiante_nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($pago['concepto']) ?></td>
                                    <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                                    <td>
                                        <?php
                                        switch ($pago['metodo_pago']) {
                                            case 'efectivo': echo 'Efectivo'; break;
                                            case 'transferencia': echo 'Transferencia bancaria'; break;
                                            case 'tarjeta': echo 'Tarjeta crédito/débito'; break;
                                            default: echo htmlspecialchars($pago['metodo_pago']);
                                        }
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                    <td>
                                        <?php if (!empty($pago['numero_comprobante'])): ?>
                                            <?= htmlspecialchars($pago['tipo_comprobante'] === 'factura' ? 'Factura' : 'Recibo') ?>
                                            <?= htmlspecialchars($pago['numero_comprobante']) ?>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Sin comprobante</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>S/ <?= number_format($pago['monto_estudiante'], 2) ?></td>
                                    <td><?= $pago['fecha_vencimiento'] ? date('d/m/Y', strtotime($pago['fecha_vencimiento'])) : '—' ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = '';
                                        switch ($pago['estado_pago']) {
                                            case 'pagado':
                                                $badgeClass = 'success';
                                                $estado = 'Pagado';
                                                break;
                                            case 'vencido':
                                                $badgeClass = 'danger';
                                                $estado = 'Vencido';
                                                break;
                                            default:
                                                $badgeClass = 'warning';
                                                $estado = 'Pendiente';
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>"><?= $estado ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=comprobante&id=<?= $pago['id_pago'] ?>" class="btn btn-info btn-sm" title="Ver comprobante">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <?php if ($pago['estado_pago'] !== 'vencido'): ?>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-toggle="modal" 
                                                        data-target="#modalAnular" 
                                                        data-id="<?= $pago['id_pago'] ?>"
                                                        data-info="Pago #<?= $pago['id_pago'] ?> - <?= htmlspecialchars($pago['estudiante_nombre_completo']) ?> - S/ <?= number_format($pago['monto'], 2) ?>"
                                                        title="Anular pago">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- Modal para anular pagos -->
<div class="modal fade" id="modalAnular" tabindex="-1" role="dialog" aria-labelledby="modalAnularLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="modalAnularLabel">Anular Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAnular" action="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=anular" method="POST">
                <div class="modal-body">
                    <p>¿Está seguro que desea anular el siguiente pago?</p>
                    <p id="infoPago" class="font-weight-bold"></p>
                    <p>Esta acción no se puede deshacer.</p>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo de la anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Anular Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.dataTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: { url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
        order: [[5, 'desc']],
        pageLength: 25
    });

    $('#modalAnular').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var info = button.data('info');
        var modal = $(this);
        modal.find('#infoPago').text(info);
        modal.find('form').attr('action', '/pagos/anular/' + id);
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
