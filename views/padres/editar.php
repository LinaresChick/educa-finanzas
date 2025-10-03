<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <a href="<?php echo BASE_URL; ?>padres/detalle/<?php echo $padre['id_padre']; ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
            <a href="<?php echo BASE_URL; ?>padres" class="btn btn-secondary">
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
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulario de Edición</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>padres/actualizar/<?php echo $padre['id_padre']; ?>" method="post">
                <!-- Datos Personales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Personales</h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $padre['nombres']; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $padre['apellidos']; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni" value="<?php echo $padre['dni']; ?>" maxlength="20">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="relacion" class="form-label">Relación *</label>
                        <select class="form-select" id="relacion" name="relacion" required>
                            <option value="Padre" <?php echo $padre['relacion'] == 'Padre' ? 'selected' : ''; ?>>Padre</option>
                            <option value="Madre" <?php echo $padre['relacion'] == 'Madre' ? 'selected' : ''; ?>>Madre</option>
                            <option value="Tutor" <?php echo $padre['relacion'] == 'Tutor' ? 'selected' : ''; ?>>Tutor Legal</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo $padre['telefono']; ?>" maxlength="20">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $padre['correo']; ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo" <?php echo $padre['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo $padre['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo $padre['direccion']; ?></textarea>
                    </div>
                </div>
                
                <!-- Datos de Acceso -->
                <?php if (isset($padre['id_usuario']) && $padre['id_usuario']): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Datos de Acceso</h5>
                            <p class="mb-3">Este padre/tutor tiene una cuenta de usuario asociada. Puede actualizar los datos de acceso.</p>
                        </div>
                        
                        <input type="hidden" name="id_usuario" value="<?php echo $padre['id_usuario']; ?>">
                        
                        <div class="col-md-6 mb-3">
                            <label for="correo_usuario" class="form-label">Correo Electrónico (usuario) *</label>
                            <input type="email" class="form-control" id="correo_usuario" name="correo_usuario" value="<?php echo $padre['correo']; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Dejar en blanco para mantener la contraseña actual</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="<?php echo BASE_URL; ?>padres/detalle/<?php echo $padre['id_padre']; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
