<?php
require_once __DIR__ . '/../templates/header.php';

$const = $constancia ?? [];
$estNombre = htmlspecialchars($const['estudiante_nombre'] ?? '—');
$solicitante = htmlspecialchars($const['nombre_solicitante'] ?? '—');
$dni = htmlspecialchars($const['dni_solicitante'] ?? '—');
$monto = isset($const['monto']) ? number_format((float)$const['monto'], 2, '.', ',') : '0.00';
$estado = htmlspecialchars($const['estado'] ?? '—');
?>

<div class="main-container">
    <div class="content-header">
        <h1 class="section-title">Editar monto de constancia</h1>
    </div>

    <div class="content-card">
        <form action="index.php?controller=Constancia&action=actualizar" method="POST">
            <input type="hidden" name="id_constancia" value="<?= intval($const['id_constancia'] ?? 0) ?>">

            <div class="mb-3">
                <label>Estudiante</label>
                <input type="text" class="form-control" value="<?= $estNombre ?>" readonly>
            </div>

            <div class="mb-3">
                <label>Solicitante</label>
                <input type="text" class="form-control" value="<?= $solicitante ?>" readonly>
            </div>

            <div class="mb-3">
                <label>DNI</label>
                <input type="text" class="form-control" value="<?= $dni ?>" readonly>
            </div>

            <div class="mb-3">
                <label>Estado actual</label>
                <input type="text" class="form-control" value="<?= $estado ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="monto">Monto (S/)</label>
                <input type="number" step="0.01" min="0" id="monto" name="monto" class="form-control" value="<?= $monto ?>" required>
                <div class="form-text">Ingrese el monto. Para habilitar impresión el monto debe ser al menos S/ 40.00.</div>
            </div>

            <div class="text-end">
                <a href="index.php?controller=Constancia" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary" type="submit">Guardar monto</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>