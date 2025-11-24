<?php
require_once __DIR__ . '/../templates/header.php';
require_once __DIR__ . '/../templates/navbar.php';

// $pago, $estudiantes, $padres deben venir desde el controlador
?>
<div class="main-container">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="section-title mb-2"><i class="fas fa-edit me-2 text-primary"></i>Editar Pago</h1>
            </div>
        </div>
    </div>

    <div class="content-card">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="index.php?controller=Pago&action=editar&id=<?= $pago['id_pago'] ?>" method="POST" enctype="multipart/form-data" id="formPagoEdit">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">Información del Estudiante</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Estudiante</label>
                                <select name="id_estudiante" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($estudiantes as $est): ?>
                                        <option value="<?= $est['id_estudiante'] ?>" <?= $est['id_estudiante'] == $pago['id_estudiante'] ? 'selected' : '' ?> ><?= htmlspecialchars($est['nombres'].' '.$est['apellidos']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Pagador (si aplica)</label>
                                <select name="id_padre" class="form-select">
                                    <option value="">Seleccione padre/tutor</option>
                                    <?php if (!empty($padres) && is_array($padres)): foreach($padres as $pad): ?>
                                        <option value="<?= $pad['id_padre'] ?>" <?= (!empty($pago['id_padre']) && $pago['id_padre'] == $pad['id_padre']) ? 'selected' : '' ?>><?= htmlspecialchars($pad['nombres'].' '.$pad['apellidos']) ?><?= !empty($pad['dni']) ? ' - DNI:'.$pad['dni'] : '' ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Nombre del pagador (si no es padre)</label>
                                <input type="text" name="pagador_nombre" class="form-control" value="<?= htmlspecialchars($pago['pagador_nombre'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>DNI del pagador</label>
                                <input type="text" name="pagador_dni" class="form-control" value="<?= htmlspecialchars($pago['pagador_dni'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">Detalles del Pago</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Concepto</label>
                                <input type="text" name="concepto" class="form-control" required value="<?= htmlspecialchars($pago['concepto'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>Banco</label>
                                <input type="text" name="banco" class="form-control" required value="<?= htmlspecialchars($pago['banco'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>Monto</label>
                                <input type="number" step="0.01" name="monto" class="form-control" required value="<?= htmlspecialchars($pago['monto'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>Método de pago</label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="efectivo" <?= (isset($pago['metodo_pago']) && $pago['metodo_pago']=='efectivo')?'selected':'' ?>>Efectivo</option>
                                    <option value="transferencia" <?= (isset($pago['metodo_pago']) && $pago['metodo_pago']=='transferencia')?'selected':'' ?>>Transferencia</option>
                                    <option value="tarjeta" <?= (isset($pago['metodo_pago']) && $pago['metodo_pago']=='tarjeta')?'selected':'' ?>>Tarjeta</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Fecha de pago</label>
                                <input type="date" name="fecha_pago" class="form-control" value="<?= htmlspecialchars($pago['fecha_pago'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Descuento</label>
                                <input type="number" step="0.01" name="descuento" class="form-control" value="<?= htmlspecialchars($pago['descuento'] ?? 0) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Aumento</label>
                                <input type="number" step="0.01" name="aumento" class="form-control" value="<?= htmlspecialchars($pago['aumento'] ?? 0) ?>">
                            </div>

                            <div class="mb-3">
                                <label>Voucher (subir nuevo para reemplazar)</label>
                                <input type="file" name="foto_baucher" class="form-control">
                                <?php if (!empty($pago['foto_baucher'])): ?>
                                    <small>Archivo actual: <a href="<?= BASE_URL ?>/uploads/vouchers/<?= $pago['foto_baucher'] ?>" target="_blank"><?= htmlspecialchars($pago['foto_baucher']) ?></a></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($pago['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-end">
                    <a href="index.php?controller=Pago&action=index" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
