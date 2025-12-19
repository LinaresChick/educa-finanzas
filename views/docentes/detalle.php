<div class="container mt-4">
    <h2>Detalles del Docente</h2>

    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Nombre:</strong> <?= $docente['nombres'] ?? '—' ?></li>
        <li class="list-group-item"><strong>Apellidos:</strong> <?= $docente['apellidos'] ?? '—' ?></li>
        <li class="list-group-item"><strong>DNI:</strong> <?= $docente['dni'] ?? '—' ?></li>
        <li class="list-group-item"><strong>Correo:</strong> <?= $docente['correo'] ?? '—' ?></li>
        <li class="list-group-item"><strong>Teléfono:</strong> <?= $docente['telefono'] ?? '—' ?></li>
        <li class="list-group-item"><strong>Dirección:</strong> <?= $docente['direccion'] ?? '—' ?></li>
        <li class="list-group-item"><strong>Estado:</strong> <?= $docente['estado'] ?? '—' ?></li>
        <li class="list-group-item">
    <strong>Salón Asignado:</strong>
    <?= $docente['id_salon'] ?? '—' ?>
</li>

<li class="list-group-item">
    <strong>Grado:</strong>
    <?= $docente['grado'] ?? '—' ?>
</li>

<li class="list-group-item">
    <strong>Sección:</strong>
    <?= $docente['seccion'] ?? '—' ?>
</li>

<li class="list-group-item">
    <strong>Nivel:</strong>
    <?= $docente['nivel'] ?? '—' ?>
</li>

    </ul>

    <a href="index.php?controller=Docente&action=index" class="btn btn-primary">Volver</a>
</div>
