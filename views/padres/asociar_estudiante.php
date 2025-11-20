<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <a href="<?php echo BASE_URL; ?>/padres/detalle/<?php echo $padre['id_padre']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Detalles
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
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Padre/Tutor</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo BASE_URL; ?>/public/img/parent-avatar.png" class="img-profile rounded-circle" style="width: 80px; height: 80px;">
                        <h5 class="mt-3 mb-0"><?php echo $padre['nombre_completo']; ?></h5>
                        <p class="text-muted"><?php echo $padre['relacion']; ?></p>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Asociar Estudiantes</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($estudiantes)): ?>
                        <div class="alert alert-info mb-3">
                            No hay estudiantes disponibles para asociar a este padre/tutor.
                        </div>
                    <?php else: ?>
                        <form action="<?php echo BASE_URL; ?>/padres/guardarAsociacion" method="post">
                            <input type="hidden" name="id_padre" value="<?php echo $padre['id_padre']; ?>">
                            
                            <div class="mb-3">
                                <label for="id_estudiante" class="form-label">Seleccione el estudiante a asociar *</label>
                                <select class="form-select" id="id_estudiante" name="id_estudiante" required>
                                    <option value="">Seleccione un estudiante</option>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                        <option value="<?php echo $estudiante['id_estudiante']; ?>">
                                            <?php echo $estudiante['nombre_completo']; ?> 
                                            <?php if ($estudiante['dni']): ?>
                                                - DNI: <?php echo $estudiante['dni']; ?>
                                            <?php endif; ?>
                                            <?php if ($estudiante['grado_seccion']): ?>
                                                - <?php echo $estudiante['grado_seccion']; ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="parentesco" class="form-label">Parentesco *</label>
                                <select class="form-select" id="parentesco" name="parentesco" required>
                                    <option value="Padre" <?php echo $padre['relacion'] == 'Padre' ? 'selected' : ''; ?>>Padre</option>
                                    <option value="Madre" <?php echo $padre['relacion'] == 'Madre' ? 'selected' : ''; ?>>Madre</option>
                                    <option value="Tutor Legal" <?php echo $padre['relacion'] == 'Tutor' ? 'selected' : ''; ?>>Tutor Legal</option>
                                    <option value="Abuelo/a">Abuelo/a</option>
                                    <option value="Tío/a">Tío/a</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-link"></i> Asociar Estudiante
                                </button>
                                <a href="<?php echo BASE_URL; ?>/padres/detalle/<?php echo $padre['id_padre']; ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.select2) {
        $('#id_estudiante').select2({
            placeholder: 'Seleccione un estudiante',
            width: '100%'
        });
    }
});
</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
