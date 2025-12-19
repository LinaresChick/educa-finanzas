<style>
	.card {
		border: none;
		border-radius: 1rem;
		box-shadow: 0 4px 20px rgba(0,0,0,0.08);
	}
	.card-header {
		background: linear-gradient(90deg, #00ff5e, #00ff04);
		color: white !important;
		border-radius: 1rem 1rem 0 0 !important;
	}
	.card-header h6 { font-weight: 600; letter-spacing: 0.5px; }
	.form-label { font-weight: 500; color: #333; }
	.form-control, .form-select {
		border-radius: 0.5rem;
		border: 1px solid #ccc;
		transition: all 0.2s ease-in-out;
	}
	.form-control:focus, .form-select:focus {
		border-color: #66ff00;
		box-shadow: 0 0 0 0.15rem rgba(0,200,0,0.25);
	}
	.btn-warning {
		border: none;
		border-radius: 0.5rem;
		font-weight: 600;
	}
	.btn-secondary { border-radius: 0.5rem; font-weight: 500; }
</style>

<div class="container-fluid py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1 class="h3 mb-0 text-gray-800">Editar Docente</h1>
		<a href="index.php?controller=Docente&action=index" class="btn btn-secondary">
			<i class="fas fa-arrow-left"></i> Volver al listado
		</a>
	</div>

	<div class="card shadow mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-white">Formulario de Edición</h6>
		</div>

		<div class="card-body">
			<form action="index.php?controller=Docente&action=actualizar" method="POST" class="row g-3">

				<input type="hidden" name="id_docente" value="<?= $docente['id_docente'] ?? '' ?>">

				<?php include __DIR__ . '/form.php'; ?>

				<div class="col-12 mt-2">
					<button class="btn btn-warning">
						<i class="fas fa-save"></i> Actualizar
					</button>
					<a href="index.php?controller=Docente&action=index" class="btn btn-secondary">Cancelar</a>
				</div>

			</form>
		</div>
	</div>
</div>
