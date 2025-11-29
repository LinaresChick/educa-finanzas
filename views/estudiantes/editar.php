<?php require_once VIEWS_PATH . '/templates/header.php'; ?>
<style>
    /* ===== ESTILOS PERSONALIZADOS ===== */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .card-header {
        background: linear-gradient(90deg, #00ff5e, #00aaff);
        color: white !important;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1rem 1.25rem;
    }

    .card-header h6 {
        font-weight: 600;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .form-label {
        font-weight: 500;
        color: #333;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ccc;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #00ff5e;
        box-shadow: 0 0 0 0.2rem rgba(0,255,94,0.25);
    }

    .btn-primary {
        background: linear-gradient(90deg, #00ff5e, #00d084);
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: transform 0.2s ease-in-out, background 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        background: linear-gradient(90deg, #00cc66, #16dd00);
    }

    .btn-secondary {
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .section-title {
        color: #00aa88;
        font-weight: 700;
    }

    h5.border-bottom {
        color: #00ff26;
        font-weight: 600;
        border-color: #00ff11 !important;
    }

    textarea {
        resize: none;
    }

    .alert {
        border-radius: 0.5rem;
    }

    .card-body {
        padding: 2rem;
    }

    .datos-cuenta {
        transition: all 0.4s ease;
    }

    .form-text {
        color: #777;
    }

    .btn i {
        margin-right: 6px;
    }
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <a href="<?php echo BASE_URL; ?>/estudiantes/detalle/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
            <a href="<?php echo BASE_URL; ?>/estudiantes" class="btn btn-secondary">
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
            <form action="<?php echo BASE_URL; ?>/estudiantes/actualizar/<?php echo $estudiante['id_estudiante']; ?>" method="post">
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
                        <label for="id_seccion" class="form-label">Grado y Sección</label>
                        <select class="form-select" id="id_seccion" name="id_seccion">
                            <option value="">Seleccione una sección</option>
                            <?php if (!empty($secciones) && is_array($secciones)):
                                foreach ($secciones as $sec):
                            ?>
                                <option value="<?php echo $sec['id_seccion']; ?>" <?php echo (!empty($estudiante['id_seccion']) && $estudiante['id_seccion'] == $sec['id_seccion']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sec['nombre'] . (!empty($sec['descripcion']) ? ' - ' . $sec['descripcion'] : '')); ?>
                                </option>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <option value="">No hay secciones disponibles</option>
                            <?php endif; ?>
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
                        <a href="<?php echo BASE_URL; ?>/estudiantes/detalle/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
