<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <!-- ✅ CORREGIDO -->
            <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=editar&id=<?php echo $padre['id_padre']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <!-- ✅ CORREGIDO -->
            <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']); ?>
    <?php endif; ?>
    
    <div class="row">
        <!-- Información Personal -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo BASE_URL; ?>/public/img/parent-avatar.png" class="img-profile rounded-circle" style="width: 120px; height: 120px;">
                        <h5 class="mt-3 mb-0"><?php echo $padre['nombre_completo']; ?></h5>
                        <p class="text-muted"><?php echo $padre['relacion']; ?></p>
                        <p>
                            <span class="badge badge-<?php echo $padre['estado'] === 'activo' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($padre['estado']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%"><i class="fas fa-id-card"></i> DNI</th>
                                    <td><?php echo $padre['dni'] ?? 'No registrado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone"></i> Teléfono</th>
                                    <td><?php echo $padre['telefono'] ?? 'No registrado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-envelope"></i> Correo</th>
                                    <td><?php echo $padre['correo'] ?? 'No registrado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-home"></i> Dirección</th>
                                    <td><?php echo $padre['direccion'] ?? 'No registrada'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if (isset($padre['id_usuario']) && $padre['id_usuario']): ?>
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Cuenta de Usuario</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="35%"><i class="fas fa-user"></i> Usuario</th>
                                        <td><?php echo $padre['correo']; ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-user-tag"></i> Rol</th>
                                        <td>Padre</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Estudiantes Asociados -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Estudiantes Asociados</h6>
                    <!-- ✅ CORREGIDO -->
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=asociarEstudiante&id=<?php echo $padre['id_padre']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus"></i> Asociar Estudiante
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($estudiantes)): ?>
                        <div class="alert alert-info mb-0">
                            No hay estudiantes asociados a este padre/tutor.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre Completo</th>
                                        <th>DNI</th>
                                        <th>Grado y Sección</th>
                                        <th>Parentesco</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                        <tr>
                                            <td><?php echo $estudiante['nombre_completo']; ?></td>
                                            <td><?php echo $estudiante['dni'] ?? 'No registrado'; ?></td>
                                            <td>
                                                <?php echo $estudiante['grado'] ?? 'No asignado'; ?>
                                                <?php if (!empty($estudiante['seccion'])): ?>
                                                    - Sección <?php echo $estudiante['seccion']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $estudiante['parentesco']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- ✅ CORREGIDO -->
                                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=detalle&id=<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#desasociarModal" 
                                                        data-id="<?php echo $estudiante['id_estudiante']; ?>"
                                                        title="Desasociar">
                                                        <i class="fas fa-unlink"></i>
                                                    </button>
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
    </div>
</div>

<!-- Modal Desasociar Estudiante -->
<div class="modal fade" id="desasociarModal" tabindex="-1" aria-labelledby="desasociarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="desasociarModalLabel">Confirmar Desasociación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea desasociar a este estudiante del padre/tutor? Esta acción no eliminará al estudiante del sistema.
            </div>
            <div class="modal-footer">
                <!-- ✅ CORREGIDO -->
                <form action="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=desasociarEstudiante" method="post">
                    <input type="hidden" name="id_padre" value="<?php echo $padre['id_padre']; ?>">
                    <input type="hidden" name="id_estudiante" id="desasociar_id_estudiante" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Desasociar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const desasociarModal = document.getElementById('desasociarModal');
    if (desasociarModal) {
        desasociarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            document.getElementById('desasociar_id_estudiante').value = id;
        });
    }
});
</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>