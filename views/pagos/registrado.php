<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle"></i> Pago Registrado Exitosamente</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-center mb-3">¡El pago ha sido registrado correctamente!</h5>
                    <p class="text-center">El pago ha sido procesado y guardado en nuestro sistema.</p>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=Pago&action=index" class="btn btn-primary">
                            <i class="fas fa-list"></i> Ver Lista de Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>