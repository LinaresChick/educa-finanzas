<div class="container mt-4">
    <h2>Registrar Docente</h2>

    <form action="index.php?controller=Docente&action=guardar" method="POST">

        <div class="row">
            <div class="col-md-6">
                <label>Nombres</label>
                <input type="text" name="nombres" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Apellidos</label>
                <input type="text" name="apellidos" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>DNI</label>
                <input type="text" name="dni" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control">
            </div>

            <div class="col-md-6 mt-3">
                <label>Especialidad</label>
                <input type="text" name="especialidad" class="form-control">
            </div>

            <!-- SELECCION DE GRADO -->
            <div class="col-md-6 mt-3">
                <label>Grado</label>
                <select name="id_grado" id="grado" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?= $grado['id_grado'] ?>">
                            <?= $grado['nombre'] ?> - <?= $grado['nivel'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SELECCION DE SECCIÓN -->
            <div class="col-md-6 mt-3">
                <label>Sección</label>
                <select name="id_seccion" id="seccion" class="form-control" required>
                    <option value="">Seleccione grado primero...</option>
                </select>
            </div>
        </div>

        <button class="btn btn-primary mt-4">Registrar Docente</button>

    </form>
</div>

<script>
// FILTRAR SECCIONES SEGÚN EL GRADO
let secciones = <?= json_encode($secciones) ?>;

document.getElementById("grado").addEventListener("change", function() {
    let idGrado = this.value;
    let selectSeccion = document.getElementById("seccion");

    selectSeccion.innerHTML = "<option value=''>Seleccione...</option>";

    secciones.forEach(s => {
        // IDENTIFICAR A QUÉ GRADO PERTENECE ESTA SECCIÓN
        if (s.descripcion.includes(idGrado + "er grado") || 
            s.descripcion.includes(idGrado + "do grado") ||
            s.descripcion.includes(idGrado + "to grado")) {

            selectSeccion.innerHTML += `
                <option value="${s.id_seccion}">
                    ${s.nombre}
                </option>`;
        }
    });
});
</script>
