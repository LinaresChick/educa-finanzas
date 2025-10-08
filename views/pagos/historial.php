<?php
/**
 * Vista de historial de pagos de un estudiante
 */
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
require_once __DIR__ . '/../templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Historial de Pagos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item active">Historial de Pagos</li>
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

            <!-- Información del estudiante -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Información del Estudiante</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Estudiante:</strong> <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>DNI:</strong> <?= htmlspecialchars($estudiante['dni']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Grado y Sección:</strong> <?= htmlspecialchars($estudiante['grado'] . ' ' . $estudiante['seccion']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen de estado financiero -->
            <div class="row">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <?php
                            $totalPagos = 0;
                            foreach ($pagos as $pago) {
                                if ($pago['estado'] === 'completado') {
                                    $totalPagos += $pago['monto'];
                                }
                            }
                            ?>
                            <h3>S/ <?= number_format($totalPagos, 2) ?></h3>
                            <p>Total Pagado</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <?php
                            $totalDeudas = 0;
                            $cantidadDeudas = 0;
                            foreach ($deudas as $deuda) {
                                $totalDeudas += $deuda['monto'];
                                $cantidadDeudas++;
                            }
                            ?>
                            <h3>S/ <?= number_format($totalDeudas, 2) ?></h3>
                            <p>Deuda Pendiente</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $cantidadDeudas ?></h3>
                            <p>Deudas Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= count($pagos) ?></h3>
                            <p>Pagos Registrados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestañas: Historial de Pagos y Deudas Pendientes -->
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#pagos" role="tab">Historial de Pagos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#deudas" role="tab">Deudas Pendientes</a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab: Historial de Pagos -->
                        <div class="tab-pane fade show active" id="pagos" role="tabpanel">
                            <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'tesoreria' || $_SESSION['usuario']['rol'] === 'superadmin'): ?>
                                <div class="mb-3">
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=registrar&id_estudiante=<?= $estudiante['id_estudiante'] ?>" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i> Registrar Nuevo Pago
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Concepto</th>
                                            <th>Monto</th>
                                            <th>Método de Pago</th>
                                            <th>Fecha y Hora</th>
                                            <th>Comprobante</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pagos)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No hay pagos registrados</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pagos as $pago): ?>
                                                <tr>
                                                    <td><?= $pago['id_pago'] ?></td>
                                                    <td><?= htmlspecialchars($pago['concepto']) ?></td>
                                                    <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                                                    <td>
                                                        <?php
                                                        switch ($pago['metodo_pago']) {
                                                            case 'efectivo':
                                                                echo 'Efectivo';
                                                                break;
                                                            case 'transferencia':
                                                                echo 'Transferencia bancaria';
                                                                break;
                                                            case 'tarjeta':
                                                                echo 'Tarjeta crédito/débito';
                                                                break;
                                                            default:
                                                                echo htmlspecialchars($pago['metodo_pago']);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                                    <td>
                                                        <?php if (isset($pago['numero_comprobante']) && $pago['numero_comprobante']): ?>
                                                            <?= htmlspecialchars($pago['tipo_comprobante'] === 'factura' ? 'Factura' : 'Recibo') ?>
                                                            <?= htmlspecialchars($pago['numero_comprobante']) ?>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Sin comprobante</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pago['estado'] === 'completado'): ?>
                                                            <span class="badge badge-success">Completado</span>
                                                        <?php elseif ($pago['estado'] === 'anulado'): ?>
                                                            <span class="badge badge-danger">Anulado</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-info"><?= htmlspecialchars(ucfirst($pago['estado'])) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=comprobante&id=<?= $pago['id_pago'] ?>" class="btn btn-info btn-sm" title="Ver comprobante">
                                                                <i class="fas fa-receipt"></i>
                                                            </a>
                                                            
                                                            <?php if (($pago['estado'] !== 'anulado') && ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'tesoreria' || $_SESSION['usuario']['rol'] === 'superadmin')): ?>
                                                                <button type="button" class="btn btn-danger btn-sm" 
                                                                        data-toggle="modal" 
                                                                        data-target="#modalAnular" 
                                                                        data-id="<?= $pago['id_pago'] ?>"
                                                                        data-info="Pago #<?= $pago['id_pago'] ?> - <?= htmlspecialchars($pago['concepto']) ?> - S/ <?= number_format($pago['monto'], 2) ?>"
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
                        
                        <!-- Tab: Deudas Pendientes -->
                        <div class="tab-pane fade" id="deudas" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Concepto</th>
                                            <th>Monto</th>
                                            <th>Fecha Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($deudas)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No hay deudas pendientes</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($deudas as $deuda): ?>
                                                <tr>
                                                    <td><?= $deuda['id_deuda'] ?></td>
                                                    <td><?= htmlspecialchars($deuda['concepto']) ?></td>
                                                    <td>S/ <?= number_format($deuda['monto'], 2) ?></td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($deuda['fecha_vencimiento'])) ?>
                                                        <?php 
                                                        $fechaVencimiento = new DateTime($deuda['fecha_vencimiento']);
                                                        $fechaActual = new DateTime();
                                                        if ($fechaVencimiento < $fechaActual) {
                                                            echo '<span class="badge badge-danger">Vencida</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-warning">Pendiente</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'tesoreria' || $_SESSION['usuario']['rol'] === 'superadmin'): ?>
                                                            <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=registrar&id_estudiante=<?= $estudiante['id_estudiante'] ?>&id_deuda=<?= $deuda['id_deuda'] ?>" class="btn btn-primary btn-sm" title="Registrar pago">
                                                                <i class="fas fa-money-bill"></i> Pagar
                                                            </a>
                                                        <?php endif; ?>
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
            </div>
        </div>
    </section>
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
    // Inicializar DataTable
    $('.dataTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "order": [[4, 'desc']], // Ordenar por fecha de pago descendente
        "pageLength": 10
    });
    
    // Configurar el modal de anulación
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
