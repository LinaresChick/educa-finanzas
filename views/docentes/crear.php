<style>
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .card-header {
        background: linear-gradient(90deg, #00ff00ff, #00aaff);
        color: white !important;
        border-radius: 1rem 1rem 0 0 !important;
    }

    .card-header h6 {
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .form-label {
        font-weight: 500;
        color: #333;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ccc;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #66ff00ff;
        box-shadow: 0 0 0 0.15rem rgba(0,200,0,0.25);
    }

    .btn-primary {
        background: linear-gradient(90deg, #00ff5eff, #00ff04ff);
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: transform 0.2s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        background: linear-gradient(90deg, #00cc66ff, #16dd00ff);
    }

    .btn-secondary {
        border-radius: 0.5rem;
        font-weight: 500;
    }

    h5.border-bottom {
        color: #00cc00;
        font-weight: 600;
        border-color: #00ff11ff !important;
    }

    .alert {
        border-radius: 0.5rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Registrar Docente</h1>
        <a href="index.php?controller=Docente&action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-white">Formulario de Registro de Docente</h6>
        </div>

        <div class="card-body">
            <form action="index.php?controller=Docente&action=guardar" method="POST">

                <!-- DATOS PERSONALES -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Personales</h5>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombres *</label>
                        <input type="text" name="nombres" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">DNI *</label>
                        <input type="text" name="dni" class="form-control" maxlength="8" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Especialidad</label>
                        <input type="text" name="especialidad" class="form-control">
                    </div>
                </div>

                <!-- DATOS ACADÉMICOS -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Datos Académicos</h5>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Grado *</label>
                        <select name="id_grado" class="form-select" required>
                            <option value="">Seleccione el grado...</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= $g['id_grado'] ?>">
                                    <?= $g['nivel'] ?> - <?= $g['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sección *</label>
                        <select name="id_seccion" class="form-select" required>
                            <option value="">Seleccione la sección...</option>
                            <?php foreach ($secciones as $s): ?>
                                <option value="<?= $s['id_seccion'] ?>">
                                    <?= $s['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Docente
                        </button>
                        <a href="index.php?controller=Docente&action=index" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
// Convierte cada palabra a Inicial Mayúscula
function capitalizarPalabras(texto) {
    return texto
        .toLowerCase()
        .replace(/\b\w/g, letra => letra.toUpperCase());
}

document.addEventListener("DOMContentLoaded", function () {
    const inputNombres = document.querySelector("input[name='nombres']");
    const inputApellidos = document.querySelector("input[name='apellidos']");

    function aplicarCapitalizacion(e) {
        const cursor = e.target.selectionStart;
        e.target.value = capitalizarPalabras(e.target.value);
        e.target.setSelectionRange(cursor, cursor);
    }

    inputNombres.addEventListener("input", aplicarCapitalizacion);
    inputApellidos.addEventListener("input", aplicarCapitalizacion);
});
</script>


<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
