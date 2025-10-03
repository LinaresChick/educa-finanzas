<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Sección</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Modificar Datos de la Sección
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($seccion)): ?>
                <form action="/secciones/actualizar" method="POST">
                    <input type="hidden" name="id_seccion" value="<?php echo htmlspecialchars($seccion['id_seccion']); ?>">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Sección *</label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               required 
                               maxlength="50"
                               value="<?php echo htmlspecialchars($seccion['nombre']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="3" 
                                  maxlength="100"><?php echo htmlspecialchars($seccion['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                        <a href="/secciones" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    No se encontró la sección especificada.
                </div>
                <a href="/secciones" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver al listado
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>