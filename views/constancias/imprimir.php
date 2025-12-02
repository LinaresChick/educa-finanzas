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
$ciudad   = htmlspecialchars($ciudad ?? 'Independencia');

$fecha_src = $constancia['fecha_creacion'] ?? date('Y-m-d');
$ts = strtotime($fecha_src) ?: time();
$day = date('j', $ts);
$months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$month = $months[intval(date('n', $ts)) - 1];
$year = date('Y', $ts);

$anioImprimir = isset($anio_solicitado) && !empty($anio_solicitado)
    ? htmlspecialchars($anio_solicitado)
    : $year;

$observacion = isset($observacion) && !empty($observacion)
    ? htmlspecialchars($observacion)
    : null;

$estadoEst = htmlspecialchars($est['estado'] ?? ($constancia['estado'] ?? '—'));
$fechaRetiro = htmlspecialchars($est['fecha_vencimiento'] ?? '');
?>

<style>
    /* --- ESTILO GENERAL --- */
    #constancia-print {
        font-size: 15px !important;
        max-width: 750px; /* CENTRADO EN PANTALLA */
        margin: auto;
        padding: 20px 10mm; /* 10 mm LATERALES */
    }

    #constancia-print p {
        text-align: justify;
    }

    .titulo-constancia {
        font-weight: 800;
        font-size: 17px !important;
        margin: 18px 0 0 0;
        text-align: center;
    }

    /* --- IMPRESIÓN --- */
    @media print {

        @page {
            margin: 25mm; /* MARGEN REAL DE 10 MM EN TODA LA HOJA */
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* QUITAR EFECTO DE BOOTSTRAP */
        .container {
            max-width: none !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* CONTENIDO PERFECTAMENTE CENTRADO */
        #constancia-print {
            max-width: 750px;
            margin: auto !important;
            padding: 0 !important;
        }

     
    }
</style>

<div class="container mt-4" id="constancia-print">

    <?php
    $fotoEstudiante = !empty($est['foto']) ? 'uploads/' . ltrim($est['foto'], '/') : null;
    ?>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div style="width:140px; height:160px; display:flex; align-items:center; justify-content:center; border:1px solid #ccc; background:#f8f9fa;">
            <?php if ($fotoEstudiante): ?>
                <img src="<?= $fotoEstudiante ?>" alt="Foto del estudiante"
                     style="max-width:100%; max-height:100%; object-fit:cover;">
            <?php else: ?>
                <div style="font-size:0.85rem; color:#666; text-align:center;">Foto del estudiante</div>
            <?php endif; ?>
        </div>

        <div class="text-center flex-grow-1 px-3">
            <h5 style="font-weight:600; font-size:0.85rem; margin:0;">
                INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA
            </h5>
        </div>

        <div style="width:220px; text-align:right;">
            <img src="img/image.png" alt="Logo" style="max-height:90px;">
        </div>
    </div>

    <h4 class="titulo-constancia">CONSTANCIA DE ESTUDIOS</h4>
    <p></p>

    <p>La Dirección de la <strong>Institución Educativa Particular Independencia</strong> certifica que:</p>

    <p>
        <strong><?= $nombreEst ?></strong>, identificado(a) con DNI Nº
        <strong><?= $dniEst ?></strong>, es estudiante del
        <strong><?= $grado ?: '—' ?></strong> en el periodo académico
        <strong><?= $anioImprimir ?></strong>.
    </p>

    <?php if ($estadoEst !== 'activo'): ?>
        <p>
            Estado en registros: <strong><?= $estadoEst ?></strong>
            <?php if (!empty($fechaRetiro)): ?>
                (<?= $fechaRetiro ?>)
            <?php endif; ?>.
        </p>
    <?php else: ?>
        <p>
            El(La) estudiante mencionado(a) viene cursando con normalidad las asignaturas
            correspondientes a su grado y mantiene la condición de alumno(a) activo(a).
        </p>
    <?php endif; ?>

    <?php if ($observacion): ?>
        <p><strong>Observación:</strong> <?= $observacion ?></p>
    <?php endif; ?>

    <p>
        Se expide la presente constancia a solicitud del(la) interesado(a), para los fines que estime por conveniente.
    </p>

    <p><strong>Lugar y fecha:</strong> <?= $ciudad ?>, <?= $day ?> de <?= $month ?> de <?= $year ?>.</p>

    <div class="row mt-5">
        <div class="col-6"></div>
        <div class="col-6 text-center">
            <p style="margin-bottom:60px;"></p>
            <p style="margin-bottom:0;">
                <img src="img/firma.png" alt="Firma Director" style="max-height:90px;">
            </p>
            <div style="text-align:center; margin-top:5px;">
    <p style="font-weight:700; font-size:1rem; margin:0;">
        Director(a)
    </p>

    <p style="margin:0; font-size:0.9rem;">
        Institución Educativa Particular Independencia
    </p>
</div>

        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" onclick="window.print();">Imprimir</button>
        <a href="index.php?controller=Constancia&action=index" class="btn btn-secondary">Volver</a>
    </div>

</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
