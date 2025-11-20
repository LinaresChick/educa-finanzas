<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Secciones</h1>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Lista de Secciones
            </div>
            <a href="/secciones/crear" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nueva Sección
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['exito'];
                    unset($_SESSION['exito']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Total Salones</th>
                        <th>Total Estudiantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($secciones as $seccion): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($seccion['id_seccion']); ?></td>
                        <td><?php echo htmlspecialchars($seccion['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($seccion['descripcion']); ?></td>
                        <td><?php echo (int)$seccion['total_salones']; ?></td>
                        <td><?php echo (int)$seccion['total_estudiantes']; ?></td>
                        <td>
                            <a href="/secciones/editar/<?php echo $seccion['id_seccion']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($_SESSION['usuario']['rol'] === 'Superadmin' && $seccion['total_salones'] == 0): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-danger"
                                    onclick="confirmarEliminacion(<?php echo $seccion['id_seccion']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta sección?')) {
        window.location.href = '/secciones/eliminar/' + id;
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>