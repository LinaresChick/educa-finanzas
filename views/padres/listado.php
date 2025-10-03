<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>padres/crear" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Padre/Tutor
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
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Padres y Tutores</h6>
            <div class="d-flex">
                <form action="<?php echo BASE_URL; ?>padres/buscar" method="get" class="d-flex">
                    <input type="text" name="termino" class="form-control" placeholder="Buscar padre/tutor" required>
                    <button type="submit" class="btn btn-outline-primary ml-2">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($padres)): ?>
                <div class="alert alert-info">
                    No hay padres o tutores registrados<?php echo isset($termino) ? ' para la búsqueda "' . $termino . '"' : ''; ?>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombres y Apellidos</th>
                                <th>DNI</th>
                                <th>Relación</th>
                                <th>Contacto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($padres as $padre): ?>
                                <tr>
                                    <td><?php echo $padre['id_padre']; ?></td>
                                    <td><?php echo $padre['nombre_completo']; ?></td>
                                    <td><?php echo $padre['dni'] ?? 'No registrado'; ?></td>
                                    <td><?php echo $padre['relacion']; ?></td>
                                    <td>
                                        <?php if ($padre['telefono']): ?>
                                            <i class="fas fa-phone"></i> <?php echo $padre['telefono']; ?><br>
                                        <?php endif; ?>
                                        <?php if ($padre['correo']): ?>
                                            <i class="fas fa-envelope"></i> <?php echo $padre['correo']; ?>
                                        <?php endif; ?>
                                        <?php if (!$padre['telefono'] && !$padre['correo']): ?>
                                            No registrado
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $padre['estado'] === 'activo' ? 'success' : 'danger';
                                        ?>">
                                            <?php echo ucfirst($padre['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo BASE_URL; ?>padres/detalle/<?php echo $padre['id_padre']; ?>" 
                                                class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>padres/editar/<?php echo $padre['id_padre']; ?>" 
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['usuario']['rol'] === 'Superadmin' || $_SESSION['usuario']['rol'] === 'Administrador'): ?>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#eliminarModal" 
                                                    data-id="<?php echo $padre['id_padre']; ?>"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar este padre/tutor? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <form action="<?php echo BASE_URL; ?>padres/eliminar" method="post">
                    <input type="hidden" name="id_padre" id="eliminar_id_padre" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el modal de eliminación
    const eliminarModal = document.getElementById('eliminarModal');
    if (eliminarModal) {
        eliminarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            document.getElementById('eliminar_id_padre').value = id;
        });
    }
    
    // Inicializar DataTable si existe
    if ($.fn.DataTable && document.getElementById('dataTable')) {
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            }
        });
    }
});
</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
