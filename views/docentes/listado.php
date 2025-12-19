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
