<style>
/* ====== RESPONSIVE MULTIPLATAFORMA SIN TOCAR HTML ====== */

/* Ajustes generales */
.table td, .table th {
    vertical-align: middle;
    white-space: nowrap;
}

/* Botones de acciones alineados */
.table td:last-child {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

/* Tablets (≤ 992px) */
@media (max-width: 992px) {
    h2 {
        font-size: 1.3rem;
    }

    .btn {
        font-size: 0.85rem;
    }
}

/* Celulares (≤ 768px) */
@media (max-width: 768px) {

    /* El contenedor se adapta mejor */
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }

    /* Tabla en modo tarjetas */
    table,
    thead,
    tbody,
    th,
    td,
    tr {
        display: block;
        width: 100%;
    }

    thead {
        display: none;
    }

    tr {
        background: #fff;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        padding: 10px;
    }

    td {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        border: none;
        border-bottom: 1px solid #eee;
        font-size: 0.9rem;
    }

    td:last-child {
        border-bottom: none;
        justify-content: flex-start;
    }

    /* Etiquetas automáticas */
    td:nth-child(1)::before { content: "Nombre"; font-weight: bold; }
    td:nth-child(2)::before { content: "DNI"; font-weight: bold; }
    td:nth-child(3)::before { content: "Teléfono"; font-weight: bold; }
    td:nth-child(4)::before { content: "Correo"; font-weight: bold; }
    td:nth-child(5)::before { content: "Estado"; font-weight: bold; }
    td:nth-child(6)::before { content: "Salones"; font-weight: bold; }
    td:nth-child(7)::before { content: "Acciones"; font-weight: bold; }

    /* Botones en columna */
    td:last-child {
        flex-direction: column;
        gap: 5px;
    }

    .btn {
        width: 100%;
    }
}
</style>

<div class="container mt-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="mb-0">Listado de Docentes</h2>
		<a href="index.php?controller=Docente&action=crear" class="btn btn-primary">Nuevo Docente</a>
	</div>

	<?php if (!empty($docentes)): ?>
		<div class="table-responsive">
			<table class="table table-striped table-hover align-middle">
				<thead>
					<tr>
						<th>Nombre</th>
						<th>DNI</th>
						<th>Teléfono</th>
						<th>Correo</th>
						<th>Estado</th>
						<th>Salones Asignados</th>
						<th style="width: 220px;">Acciones</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($docentes as $d): ?>
					<tr>
						<td>
							<?= htmlspecialchars(($d['apellidos'] ?? '') . ', ' . ($d['nombres'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
						</td>
						<td><?= htmlspecialchars($d['dni'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
						<td><?= htmlspecialchars($d['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
						<td><?= htmlspecialchars($d['correo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
						<td>
							<?php $estado = $d['estado'] ?? '—'; ?>
							<span class="badge bg-<?= $estado === 'activo' ? 'success' : 'secondary' ?>">
								<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
							</span>
						</td>
						<td>
							<?= htmlspecialchars($d['salones_asignados'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
						</td>
						<td>
							<a class="btn btn-sm btn-info" href="index.php?controller=Docente&action=ver&id=<?= urlencode($d['id_docente']) ?>">Ver</a>
							<a class="btn btn-sm btn-warning" href="index.php?controller=Docente&action=editar&id=<?= urlencode($d['id_docente']) ?>">Editar</a>
							<a class="btn btn-sm btn-outline-danger" href="index.php?controller=Docente&action=eliminar&id=<?= urlencode($d['id_docente']) ?>"
							   onclick="return confirm('¿Desea marcar como inactivo este docente?');">Eliminar</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else: ?>
		<div class="alert alert-info">No hay docentes registrados aún.</div>
	<?php endif; ?>
</div>
