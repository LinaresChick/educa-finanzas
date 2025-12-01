
<?php
require_once __DIR__ . '/../templates/header.php';

?>


<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Lista de Docentes</h2>
        <a href="index.php?controller=Docente&action=crear" class="btn btn-primary">
            Nuevo Docente
        </a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Docente</th>
                <th>DNI</th>
                <th>Grado / Sección</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($docentes)): ?>
                <?php foreach ($docentes as $docente): ?>
                    <tr>
                        <!-- ID -->
                        <td><?= $docente['id_docente'] ?></td>

                        <!-- Nombre Completo -->
                        <td><?= $docente['nombres'] . ' ' . $docente['apellidos'] ?></td>

                        <!-- DNI -->
                        <td><?= $docente['dni'] ?: '-' ?></td>
                        <td>
    <?php if ($docente['grado']): ?>
        <?= $docente['grado'] ?> - <?= $docente['seccion'] ?>
        <br><small class="text-muted"><?= $docente['nivel'] ?></small>
    <?php else: ?>
        <span class="text-muted">Sin asignación</span>
    <?php endif; ?>
</td>


                        <!-- Correo -->
                        <td><?= $docente['correo'] ?: '-' ?></td>



                        <!-- Acciones -->
                        <td>
                            <a href="index.php?controller=Docente&action=ver&id=<?= $docente['id_docente'] ?>" 
                               class="btn btn-info btn-sm">Ver</a>

                            <a href="index.php?controller=Docente&action=editar&id=<?= $docente['id_docente'] ?>" 
                               class="btn btn-warning btn-sm">Editar</a>

                            <a href="index.php?controller=Docente&action=eliminar&id=<?= $docente['id_docente'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Seguro de eliminar este docente?')">
                                Eliminar
                            </a>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">
                        No hay docentes registrados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

    </table>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
