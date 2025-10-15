<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>/estudiantes/crear" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Estudiante
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Estudiantes</h6>
            <div class="d-flex">
                <form action="<?php echo BASE_URL; ?>estudiantes/buscar" method="get" class="d-flex">
                    <input type="text" name="termino" class="form-control" placeholder="Buscar estudiante" required>
                    <button type="submit" class="btn btn-outline-primary ml-2">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($estudiantes)): ?>
                <div class="alert alert-info">
                    No hay estudiantes registrados<?php echo isset($termino) ? ' para la búsqueda "' . $termino . '"' : ''; ?>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombres y Apellidos</th>
                                <th>DNI</th>
                                <th>Grado y Sección</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td><?php echo $estudiante['id_estudiante']; ?></td>
                                    <td><?php echo $estudiante['nombre_completo']; ?></td>
                                    <td><?php echo $estudiante['dni'] ?? 'No registrado'; ?></td>
                                    <td><?php echo $estudiante['grado'] ?? 'No asignado'; ?> - <?php echo $estudiante['seccion'] ?? ''; ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $estudiante['estado'] === 'activo' ? 'success' : 
                                                ($estudiante['estado'] === 'inactivo' ? 'danger' : 'warning');
                                        ?>">
                                            <?php echo ucfirst($estudiante['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo BASE_URL; ?>estudiantes/detalle/<?php echo $estudiante['id_estudiante']; ?>" 
                                                class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>estudiantes/editar/<?php echo $estudiante['id_estudiante']; ?>" 
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['usuario']['rol'] === 'Superadmin' || $_SESSION['usuario']['rol'] === 'Administrador'): ?>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#eliminarModal" 
                                                    data-id="<?php echo $estudiante['id_estudiante']; ?>"
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
                ¿Está seguro que desea eliminar este estudiante? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <form action="<?php echo BASE_URL; ?>estudiantes/eliminar" method="post">
                    <input type="hidden" name="id_estudiante" id="eliminar_id_estudiante" value="">
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
            document.getElementById('eliminar_id_estudiante').value = id;
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
