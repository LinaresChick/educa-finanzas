<?php require_once VIEWS_PATH . '/templates/header.php'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=importarSalon" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al formulario
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Previsualización de datos (<?php echo count($rows); ?> filas)</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=procesarImportacion" method="post">
                <input type="hidden" name="confirm" value="1">
                <input type="hidden" name="docente_id" value="<?php echo htmlspecialchars($docente_id); ?>">
                <input type="hidden" name="id_seccion" value="<?php echo htmlspecialchars($id_seccion); ?>">
                <input type="hidden" name="monto" value="<?php echo htmlspecialchars($monto); ?>">
                <input type="hidden" name="tmp_path" value="<?php echo htmlspecialchars($tmp_path); ?>">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Fecha Nac.</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Mención</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($r['nombres'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['apellidos'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['dni'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['fecha_nacimiento'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['direccion'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['telefono'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($r['mencion'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Subir datos</button>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=importarSalon" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
