<div class="col-md-6">
    <label>Nombres</label>
    <input type="text" name="nombres" class="form-control"
        value="<?= $docente['nombres'] ?? '' ?>" required>
</div>

<div class="col-md-6">
    <label>Apellidos</label>
    <input type="text" name="apellidos" class="form-control"
        value="<?= $docente['apellidos'] ?? '' ?>" required>
</div>

<div class="col-md-4">
    <label>DNI</label>
    <input type="text" name="dni" class="form-control"
        value="<?= $docente['dni'] ?? '' ?>" required>
</div>

<div class="col-md-4">
    <label>Teléfono</label>
    <input type="text" name="telefono" class="form-control"
        value="<?= $docente['telefono'] ?? '' ?>">
</div>

<div class="col-md-4">
    <label>Estado</label>
    <select name="estado" class="form-control">
        <option value="activo"   <?= isset($docente) && $docente['estado']=='activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= isset($docente) && $docente['estado']=='inactivo' ? 'selected' : '' ?>>Inactivo</option>
    </select>
</div>

<div class="col-md-6">
    <label>Correo</label>
    <input type="email" name="correo" class="form-control"
        value="<?= $docente['correo'] ?? '' ?>">
</div>

<div class="col-md-6">
    <label>Dirección</label>
    <input type="text" name="direccion" class="form-control"
        value="<?= $docente['direccion'] ?? '' ?>">
</div>

<!-- ============================================================ -->
<!--  SALÓN DISPONIBLE (MOSTRAR SOLO LOS QUE NO TIENEN DOCENTE)   -->
<!-- ============================================================ -->

<div class="col-md-6">
    <label>Salón Asignado</label>
    <select name="id_salon" class="form-control" required>
        <option value="">Seleccione un salón...</option>

        <?php foreach ($salonesDisponibles as $sal): ?>
            <option value="<?= $sal['id_salon'] ?>">
                <?= $sal['grado_nivel'] ?> - <?= $sal['grado_nombre'] ?> / <?= $sal['seccion_nombre'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (empty($salonesDisponibles)): ?>
        <small class="text-danger">No hay salones disponibles sin docente.</small>
    <?php endif; ?>
</div>
