<?php require_once VIEWS_PATH . '/templates/header.php'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Importar Salón (CSV / Excel)</h6>
        </div>
        <div class="card-body">
            <p>Sube un archivo <strong>CSV</strong> o <strong>Excel (.xlsx/.xls)</strong> con encabezados: <strong>nombres, apellidos, dni, fecha_nacimiento, direccion, telefono, mencion</strong>. Solo son requeridos <em>nombres</em> y <em>apellidos</em>.</p>
            <form action="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=procesarImportacion" method="post" enctype="multipart/form-data">
                <input type="hidden" name="confirm" value="1">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="docente_id">Docente</label>
                        <select name="docente_id" id="docente_id" class="form-select" required>
                            <option value="">Seleccione un docente</option>
                            <?php if (!empty($docentes)): foreach($docentes as $d): ?>
                                <option value="<?php echo $d['id_docente']; ?>"><?php echo htmlspecialchars($d['apellidos'] . ', ' . $d['nombres']); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_seccion">Sección</label>
                        <select name="id_seccion" id="id_seccion" class="form-select" required>
                            <option value="">Seleccione sección</option>
                            <?php if (!empty($secciones)): foreach($secciones as $s): ?>
                                <option value="<?php echo $s['id_seccion']; ?>"><?php echo htmlspecialchars($s['nombre'] . (isset($s['descripcion']) ? ' - '.$s['descripcion'] : '')); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="monto">Monto por estudiante (opcional)</label>
                        <input type="number" step="0.01" class="form-control" name="monto" id="monto" placeholder="0.00">
                    </div>
                    <div class="col-md-8">
                        <label for="archivo">Archivo (CSV o Excel)</label>
                        <input type="file" class="form-control" name="archivo" id="archivo" accept=".csv,.xlsx,.xls" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Guardar Datos</button>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=Estudiante&action=index" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
