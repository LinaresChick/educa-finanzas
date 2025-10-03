<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Crear Nueva Sección</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Datos de la Sección
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

            <form action="/secciones/guardar" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Sección *</label>
                    <input type="text" 
                           class="form-control" 
                           id="nombre" 
                           name="nombre" 
                           required 
                           maxlength="50"
                           value="<?php echo isset($_SESSION['datos']) ? htmlspecialchars($_SESSION['datos']['nombre']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" 
                              id="descripcion" 
                              name="descripcion" 
                              rows="3" 
                              maxlength="100"><?php echo isset($_SESSION['datos']) ? htmlspecialchars($_SESSION['datos']['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <a href="/secciones" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </form>
            
            <?php
            // Limpiar datos del formulario almacenados en sesión
            if (isset($_SESSION['datos'])) {
                unset($_SESSION['datos']);
            }
            ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>