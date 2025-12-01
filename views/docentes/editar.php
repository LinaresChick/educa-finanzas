<div class="container mt-4">
    <h2>Editar Docente</h2>

    <form action="index.php?controller=Docente&action=actualizar" method="POST" class="row g-3">

        <input type="hidden" name="id" value="<?= $docente['id'] ?>">

        <?php include __DIR__ . '/form.php'; ?>

        <div class="col-12">
            <button class="btn btn-warning">Actualizar</button>
            <a href="index.php?controller=Docente&action=index" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>
