<?php
require_once __DIR__ . '/../templates/header.php';

// Datos
$est = $estudiante ?? [];
$nombreEst = htmlspecialchars($est['nombre_completo'] ?? $constancia['estudiante_nombre'] ?? '—');
$dniEst = htmlspecialchars($est['dni'] ?? $constancia['dni_solicitante'] ?? '—');
$grado = '';
if (!empty($est['grado_nombre']) || !empty($est['nivel_educativo'])) {
    $grado = htmlspecialchars(trim(($est['grado_nombre'] ?? '') . ' ' . ($est['nivel_educativo'] ?? '')));
} elseif (!empty($est['seccion_nombre'])) {
    $grado = htmlspecialchars($est['seccion_nombre']);
}

$director = htmlspecialchars($director ?? 'Nombre del Director(a)');
$ciudad = htmlspecialchars($ciudad ?? 'Independencia');
$fecha_src = $constancia['fecha_creacion'] ?? date('Y-m-d');
$ts = strtotime($fecha_src) ?: time();
$day = date('j', $ts);
$months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$month = $months[intval(date('n', $ts)) - 1];
$year = date('Y', $ts);

?>

<div class="container mt-4" id="constancia-print">
    <div class="text-center mb-3">
        <img src="img/image.png" alt="Logo" style="max-height:120px;">
    </div>

    <div class="text-center">
        <h5 style="font-weight:700;">INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA</h5>
        <h4 style="font-weight:700; margin-top:6px;">CONSTANCIA DE ESTUDIOS</h4>
    </div>

    <div class="mt-4" style="font-size:1.05rem; line-height:1.6;">
        <p>La Dirección de la <strong>Institución Educativa Particular Independencia</strong> certifica que:</p>

        <p><strong><?= $nombreEst ?></strong>, identificado(a) con DNI Nº <strong><?= $dniEst ?></strong>, es estudiante regular del <strong><?= $grado ?: '—' ?></strong> en el periodo académico <strong><?= htmlspecialchars($year) ?></strong>.</p>

        <p>El(La) estudiante mencionado(a) viene cursando con normalidad las asignaturas correspondientes a su grado y mantiene la condición de alumno(a) activo(a) en nuestra institución.</p>

        <p>Se expide la presente constancia a solicitud del(la) interesado(a), para los fines que estime por conveniente.</p>

        <p><strong>Lugar y fecha:</strong> <?= $ciudad ?>, <?= $day ?> de <?= $month ?> de <?= $year ?>.</p>
    </div>

    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <p style="margin-bottom:60px;"></p>
            <p style="font-weight:700; margin-bottom:0;"><?= $director ?></p>
            <p>Director(a)</p>
            <p>Institución Educativa Particular Independencia</p>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" onclick="window.print();">Imprimir</button>
        <a href="index.php?controller=Constancia" class="btn btn-secondary">Volver</a>
    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
