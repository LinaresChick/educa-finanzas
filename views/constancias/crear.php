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
                <label for="id_salon">Grado y Sección</label>
                <select name="id_salon" id="id_salon" class="form-select mb-2">
                    <option value="">-- Todos los salones --</option>
                    <?php if (!empty($salones) && is_array($salones)): ?>
                        <?php foreach ($salones as $salon): ?>
                            <option value="<?= $salon['id_salon'] ?>"><?= htmlspecialchars($salon['descripcion'] ?? ($salon['grado_nombre'].' - '.$salon['seccion_nombre'])) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <label for="id_estudiante">Estudiante</label>
                <select name="id_estudiante" id="id_estudiante" class="form-select">
                    <option value="">-- Sin seleccionar --</option>
                    <?php foreach ($estudiantes as $e): ?>
                        <option value="<?= $e['id_estudiante'] ?>" data-salon="<?= htmlspecialchars($e['id_salon'] ?? '') ?>"><?= htmlspecialchars($e['nombres'] . ' ' . $e['apellidos']) ?></option>
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

            <div class="mb-3">
                <label for="monto">Monto (S/)</label>
                <input type="number" step="0.01" min="0" id="monto" name="monto" class="form-control" value="0.00">
                <div class="form-text">Opcional: monto asociado a la constancia (p. ej. pago, saldo).</div>
            </div>

            <div class="mb-3">
                <label for="anio">Periodo académico (Año)</label>
                <input type="text" id="anio" name="anio" class="form-control" placeholder="Ej. 2025" value="<?= date('Y') ?>"> 
                <div class="form-text">Opcional: indique el año para el que solicita la constancia.</div>
            </div>

            <div class="mb-3">
                <label for="observacion">Observación</label>
                <textarea id="observacion" name="observacion" class="form-control" rows="2" placeholder="Ej. Retirado en 2023, transferido a otra institución..."></textarea>
                <div class="form-text">Opcional: texto breve que aparecerá en la constancia.</div>
            </div>

            <div class="text-end">
                <a href="index.php?controller=Constancia" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary" type="submit">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const salonSelect = document.getElementById('id_salon');
    const estudianteSelect = document.getElementById('id_estudiante');

    function filterEstudiantes() {
        const selectedSalon = salonSelect.value;
        for (const opt of estudianteSelect.options) {
            const optSalon = opt.getAttribute('data-salon') || '';
            if (!selectedSalon || selectedSalon === '') {
                opt.style.display = '';
            } else if (optSalon === selectedSalon) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
        // Si la opción actualmente seleccionada fue ocultada, resetear la selección
        if (estudianteSelect.selectedIndex >= 0) {
            const selOpt = estudianteSelect.options[estudianteSelect.selectedIndex];
            if (selOpt && selOpt.style.display === 'none') {
                estudianteSelect.value = '';
            }
        }
    }

    salonSelect.addEventListener('change', filterEstudiantes);
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
