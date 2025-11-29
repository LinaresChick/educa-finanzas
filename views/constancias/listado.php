<?php
require_once __DIR__ . '/../templates/header.php';

?>

<div class="main-container">
    <div class="content-header d-flex justify-content-between align-items-center">
        <h1 class="section-title">Constancias de Estudios</h1>
        <a href="index.php?controller=Constancia&action=crear" class="btn btn-primary">Nueva constancia</a>
    </div>

    <div class="content-card mt-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Solicitante</th>
                    <th>DNI</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($constancias as $c): ?>
                        <tr>
                            <td><?= $c['id_constancia'] ?></td>
                            <td><?= htmlspecialchars($c['estudiante_nombre'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($c['nombre_solicitante']) ?></td>
                            <td><?= htmlspecialchars($c['dni_solicitante']) ?></td>
                            <td><?= htmlspecialchars($c['estado']) ?></td>
                            <td>
                                <form method="POST" action="index.php?controller=Constancia&action=toggle" style="display:inline">
                                    <input type="hidden" name="id_constancia" value="<?= $c['id_constancia'] ?>">
                                    <button class="btn btn-sm <?= $c['estado'] === 'pendiente' ? 'btn-warning' : 'btn-secondary' ?>" type="submit">
                                        <?= $c['estado'] === 'pendiente' ? 'Pendiente' : 'Pagado' ?>
                                    </button>
                                </form>

                                <?php
                                // Mostrar botón Imprimir solo si constancia está pagada y monto >= 40
                                $montoC = isset($c['monto']) ? floatval($c['monto']) : 0.0;
                                if ($c['estado'] === 'pagado' && $montoC >= 40.0): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="index.php?controller=Constancia&action=imprimir&id=<?= $c['id_constancia'] ?>" target="_blank">Ver / Imprimir</a>
                                <?php endif; ?>

                                <?php if ($c['estado'] === 'pendiente'): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="index.php?controller=Constancia&action=editar&id=<?= $c['id_constancia'] ?>">Editar monto</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
