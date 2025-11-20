<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h3>Confirmar Pago</h3>
        </div>
        <div class="card-body">
            <h5>¿Está seguro que desea registrar este pago?</h5>
            
            <div class="mt-3">
                <p><strong>Estudiante:</strong> <?php echo $datos['nombre_estudiante']; ?></p>
                <p><strong>Concepto:</strong> <?php echo $datos['concepto']; ?></p>
                <p><strong>Monto:</strong> $<?php echo number_format($datos['monto'], 2); ?></p>
                <p><strong>Método de Pago:</strong> <?php echo $datos['metodo_pago']; ?></p>
                <p><strong>Banco:</strong> <?php echo $datos['banco']; ?></p>
                <p><strong>Fecha de Pago:</strong> <?php echo $datos['fecha_pago']; ?></p>
            </div>

            <div class="mt-4">
                <form action="index.php?controller=Pago&action=guardar" method="POST">
                    <input type="hidden" name="confirmar" value="si">
                    <button type="submit" class="btn btn-success">Sí, Registrar Pago</button>
                    <a href="index.php?controller=Pago&action=registrar" class="btn btn-secondary ml-2">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>