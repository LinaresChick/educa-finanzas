<?php
/**
 * Vista de listado de pagos - VERSIÓN CORREGIDA
 */
require_once __DIR__ . '/../templates/header.php';

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
                <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=crear" class="btn btn-primary">
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
                    
                                    <td class="text-center">
                    <div class="col-md-3 mb-3">
                        <label for="metodo_pago">Método de Pago:</label>
                        <select class="form-control" id="metodo_pago" name="metodo_pago">
                            <option value="">Todos</option>
                            <option value="efectivo" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'efectivo') ? 'selected' : '' ?>>Efectivo</option>
                            <option value="transferencia" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'transferencia') ? 'selected' : '' ?>>Transferencia</option>
                            <option value="tarjeta" <?= (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] == 'tarjeta') ? 'selected' : '' ?>>Tarjeta</option>
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

        <!-- Tabla de pagos - SIN DATATABLES (ELIMINA EL ERROR) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Lista de Pagos Registrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Estudiante</th>
                                <th width="20%">Concepto</th>
                                <th width="10%">Monto</th>
                                <th width="10%">Método</th>
                                <th width="10%">Fecha</th>
                                <th width="10%" class="text-center">Voucher</th>
                                <th width="10%">Total</th>
                                <th width="10%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pagos) && is_array($pagos)): ?>
                                <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= $pago['id_pago'] ?? '' ?></td>
                                    <td><?= htmlspecialchars($pago['estudiante_nombre_completo'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($pago['concepto'] ?? '') ?></td>
                                    <td>S/ <?= number_format($pago['monto'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php
                                        $metodo = $pago['metodo_pago'] ?? '';
                                        switch($metodo) {
                                            case 'efectivo': echo 'Efectivo'; break;
                                            case 'transferencia': echo 'Transferencia'; break;
                                            case 'tarjeta': echo 'Tarjeta'; break;
                                            default: echo ucfirst($metodo);
                                        }
                                        ?>
                                    </td>
                                    <td><?= !empty($pago['fecha_pago']) ? date('d/m/Y', strtotime($pago['fecha_pago'])) : 'N/A' ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($pago['foto_baucher'])): ?>
                                                          <a href="<?= BASE_URL ?>/uploads/vouchers/<?= $pago['foto_baucher'] ?>" 
                                               target="_blank" class="btn btn-sm btn-info" title="Ver voucher">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>S/ <?= number_format(($pago['monto'] ?? 0) - ($pago['descuento'] ?? 0) + ($pago['aumento'] ?? 0), 2) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-secondary btn-sm btn-detalles" 
                                                    title="Ver detalles"
                                                    data-pago-base64="<?= base64_encode(json_encode($pago, JSON_UNESCAPED_UNICODE)) ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                            <a href="?controller=Pago&action=comprobante&id=<?= $pago['id_pago'] ?>" 
                                               class="btn btn-info" title="Ver comprobante">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <a href="?controller=Pago&action=editar&id=<?= $pago['id_pago'] ?>" class="btn btn-primary" title="Editar pago">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (isset($_SESSION['usuario']['rol']) && in_array($_SESSION['usuario']['rol'], ['Superadmin', 'Administrador', 'Colaborador'])): ?>
                                            <button type="button" class="btn btn-danger" 
                                                    data-toggle="modal" 
                                                    data-target="#modalAnular" 
                                                    data-id="<?= $pago['id_pago'] ?>"
                                                    data-info="Pago #<?= $pago['id_pago'] ?> - <?= htmlspecialchars($pago['concepto'] ?? '') ?> - S/ <?= number_format($pago['monto'] ?? 0, 2) ?>"
                                                    title="Eliminar pago">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-3 d-block"></i>
                                        No hay pagos registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Información de resumen -->
                <?php if (!empty($pagos) && is_array($pagos)): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total de pagos:</strong> <?= count($pagos) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Monto total:</strong> S/ 
                            <?php
                            $totalGeneral = 0;
                            foreach ($pagos as $pago) {
                                $totalGeneral += ($pago['monto'] ?? 0) - ($pago['descuento'] ?? 0) + ($pago['aumento'] ?? 0);
                            }
                            echo number_format($totalGeneral, 2);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Última actualización:</strong> <?= date('d/m/Y H:i:s') ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar pagos -->
<div class="modal fade" id="modalAnular" tabindex="-1" role="dialog" aria-labelledby="modalAnularLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalAnularLabel">Eliminar Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAnular" action="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=eliminar" method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                    </div>
                    <p>¿Está seguro que desea eliminar el siguiente pago?</p>
                    <p id="infoPago" class="font-weight-bold text-danger"></p>
                    <input type="hidden" name="id_pago" id="id_pago">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<!-- ELIMINADO TODO EL CÓDIGO DE DATATABLES PARA EVITAR EL ERROR -->

<script>
// Solo el código necesario para el modal
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el modal de eliminación
    const modalAnular = document.getElementById('modalAnular');
    if (modalAnular) {
        modalAnular.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const info = button.getAttribute('data-info');
            
            document.getElementById('infoPago').textContent = info;
            document.getElementById('id_pago').value = id;
        });
    }

    // Confirmación antes de eliminar
    const formAnular = document.getElementById('formAnular');
    if (formAnular) {
        formAnular.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('¿Está completamente seguro que desea eliminar este pago?\n\nEsta acción es permanente y no se puede deshacer.')) {
                this.submit();
            }
        });
    }
    // Mostrar detalles en modal
    const modalEl = document.createElement('div');
        modalEl.innerHTML = `
        <div class="modal fade" id="modalDetallesPago" tabindex="-1" aria-labelledby="modalDetallesPagoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDetallesPagoLabel">Detalles del Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body"><div id="modalDetallesContenido" class="p-2"></div></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modalEl);

    const modalElement = document.getElementById('modalDetallesPago');
    let bsModal = null;
    if (modalElement && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalElement);
    }

    document.querySelectorAll('.btn-detalles').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const b64 = btn.getAttribute('data-pago-base64') || '';
            try {
                const jsonStr = atob(b64);
                const obj = JSON.parse(jsonStr);
                const contenido = document.getElementById('modalDetallesContenido');
                if (contenido) {
                    const esc = (s) => {
                        if (s === null || s === undefined) return '';
                        return String(s)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    };

                    const estudiante = obj.estudiante_nombre_completo || ((obj.nombres || '') + ' ' + (obj.apellidos || ''));
                    const fmtFecha = (f) => {
                        if (!f) return '';
                        const d = new Date(f);
                        if (isNaN(d)) return esc(f);
                        return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
                    };

                    const rows = [];
                    rows.push(['N° Pago', esc(obj.id_pago || '')]);
                    rows.push(['Estudiante', esc(estudiante)]);
                    let pagador = '';
                    if (obj.pagador_nombre) pagador = esc(obj.pagador_nombre);
                    else if (obj.id_padre) pagador = 'Padre ID: ' + esc(obj.id_padre);
                    rows.push(['Pagador', pagador]);
                    // Preferir DNI directo del pago, si no existe usar DNI del padre (tabla 'padres')
                    const dniPagador = obj.pagador_dni || obj.pagador_dni_db || '';
                    rows.push(['DNI del Pagador', esc(dniPagador)]);
                    rows.push(['Concepto', esc(obj.concepto || '')]);
                    rows.push(['Banco', esc(obj.banco || '')]);
                    rows.push(['Método de Pago', esc(obj.metodo_pago || '')]);
                    rows.push(['Monto', obj.monto !== undefined ? 'S/ ' + parseFloat(obj.monto).toFixed(2) : '']);
                    rows.push(['Descuento', obj.descuento !== undefined ? 'S/ ' + parseFloat(obj.descuento).toFixed(2) : 'S/ 0.00']);
                    rows.push(['Aumento', obj.aumento !== undefined ? 'S/ ' + parseFloat(obj.aumento).toFixed(2) : 'S/ 0.00']);
                    rows.push(['Total', (obj.monto !== undefined ? 'S/ ' + (parseFloat(obj.monto) - (parseFloat(obj.descuento||0)) + (parseFloat(obj.aumento||0))).toFixed(2) : '')]);
                    rows.push(['Fecha de Pago', fmtFecha(obj.fecha_pago)]);
                    rows.push(['Observaciones', esc(obj.observaciones || '')]);
                    if (obj.foto_baucher) {
                        const url = '<?= BASE_URL ?>' + '/uploads/vouchers/' + esc(obj.foto_baucher);
                        rows.push(['Voucher', '<a href="' + url + '" target="_blank">Ver voucher</a>']);
                    } else {
                        rows.push(['Voucher', 'No disponible']);
                    }
                    rows.push(['Registrado por (Usuario ID)', esc(obj.usuario_registro || '')]);
                    rows.push(['Fecha de creación', fmtFecha(obj.fecha_creacion || obj.fecha_registro || '')]);

                    let html = '<table class="table table-sm table-borderless">';
                    rows.forEach(r => {
                        html += '<tr><th style="width:35%; text-align:right; vertical-align:top;">' + r[0] + ':</th><td style="padding-left:15px;">' + r[1] + '</td></tr>';
                    });
                    html += '</table>';
                    contenido.innerHTML = html;
                }
                if (bsModal) bsModal.show();
            } catch (err) {
                alert('Error al mostrar detalles: ' + err.message);
            }
        });
    });
});
</script>