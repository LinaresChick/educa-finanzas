<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <a href="<?php echo BASE_URL; ?>estudiantes/detalle/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
            <a href="<?php echo BASE_URL; ?>estudiantes" class="btn btn-secondary">
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
            <form action="<?php echo BASE_URL; ?>estudiantes/actualizar/<?php echo $estudiante['id_estudiante']; ?>" method="post">
                <!-- Datos Personales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Personales</h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $estudiante['nombres']; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $estudiante['apellidos']; ?>" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="dni" name="dni" value="<?php echo $estudiante['dni']; ?>" maxlength="20">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $estudiante['fecha_nacimiento']; ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo $estudiante['telefono']; ?>" maxlength="20">
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo $estudiante['direccion']; ?></textarea>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo" <?php echo $estudiante['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo $estudiante['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                            <option value="graduado" <?php echo $estudiante['estado'] == 'graduado' ? 'selected' : ''; ?>>Graduado</option>
                        </select>
                    </div>
                </div>
                
                <!-- Datos Académicos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Académicos</h5>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="id_salon" class="form-label">Grado y Sección</label>
                        <select class="form-select" id="id_salon" name="id_salon">
                            <option value="">Seleccione una opción</option>
                            <?php foreach($salones as $salon): ?>
                                <option value="<?php echo $salon['id_salon']; ?>" <?php echo $estudiante['id_salon'] == $salon['id_salon'] ? 'selected' : ''; ?>>
                                    <?php echo $salon['descripcion']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mencion" class="form-label">Mención o Especialidad</label>
                        <input type="text" class="form-control" id="mencion" name="mencion" value="<?php echo $estudiante['mencion']; ?>">
                        <div class="form-text">Solo aplica para niveles superiores</div>
                    </div>
                </div>
                
                <!-- Datos de Acceso -->
                <?php if (isset($estudiante['id_usuario']) && $estudiante['id_usuario']): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Datos de Acceso</h5>
                            <p class="mb-3">Este estudiante tiene una cuenta de usuario asociada. Puede actualizar los datos de acceso.</p>
                        </div>
                        
                        <input type="hidden" name="id_usuario" value="<?php echo $estudiante['id_usuario']; ?>">
                        
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $estudiante['correo']; ?>" required>
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
                        <a href="<?php echo BASE_URL; ?>estudiantes/detalle/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
