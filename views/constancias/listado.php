<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';
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

                            <?php if ($c['estado'] === 'pagado'): ?>
                                <a class="btn btn-sm btn-outline-primary" href="index.php?controller=Constancia&action=imprimir&id=<?= $c['id_constancia'] ?>" target="_blank">Ver / Imprimir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
