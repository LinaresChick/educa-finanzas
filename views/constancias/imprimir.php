<?php
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Constancia de Estudios</h3>
            <p><strong>Estudiante:</strong> <?= htmlspecialchars($constancia['estudiante_nombre'] ?? '—') ?></p>
            <p><strong>Solicitante:</strong> <?= htmlspecialchars($constancia['nombre_solicitante']) ?></p>
            <p><strong>DNI:</strong> <?= htmlspecialchars($constancia['dni_solicitante']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($constancia['estado']) ?></p>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($constancia['fecha_creacion'] ?? date('Y-m-d')) ?></p>

            <div class="mt-4">
                <button class="btn btn-primary" onclick="window.print();">Imprimir</button>
                <a href="index.php?controller=Constancia" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
