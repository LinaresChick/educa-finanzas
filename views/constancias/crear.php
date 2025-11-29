<?php
require_once __DIR__ . '/../templates/header.php';

?>

<div class="main-container">
    <div class="content-header">
        <h1 class="section-title">Solicitar Constancia de Estudios</h1>
    </div>

    <div class="content-card">
        <form action="index.php?controller=Constancia&action=registrar" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="id_estudiante">Estudiante</label>
                <select name="id_estudiante" id="id_estudiante" class="form-select">
                    <option value="">-- Sin seleccionar --</option>
                    <?php foreach ($estudiantes as $e): ?>
                        <option value="<?= $e['id_estudiante'] ?>"><?= htmlspecialchars($e['nombres'] . ' ' . $e['apellidos']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="nombre_solicitante">Nombre del solicitante</label>
                <input type="text" id="nombre_solicitante" name="nombre_solicitante" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="dni_solicitante">DNI del solicitante</label>
                <input type="text" id="dni_solicitante" name="dni_solicitante" class="form-control">
            </div>

            <div class="text-end">
                <a href="index.php?controller=Constancia" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary" type="submit">Registrar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
