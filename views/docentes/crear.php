<div class="container mt-4">
    <h2>Registrar Docente</h2>

    <form action="index.php?controller=Docente&action=guardar" method="POST" class="row g-3">

        <?php include __DIR__ . '/form.php'; ?>

        <div class="col-12">
            <button class="btn btn-success">Guardar</button>
            <a href="index.php?controller=Docente&action=index" class="btn btn-secondary">Cancelar</a>
        </div>

    </form>
</div>
