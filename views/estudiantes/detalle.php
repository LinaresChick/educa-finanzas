<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>
        <div>
            <?php if (empty($estudiante['id_usuario'])): ?>
                <a href="<?php echo BASE_URL; ?>usuarios/crear_usuario_estudiante/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Crear Cuenta de Acceso
                </a>
            <?php else: ?>
                <span class="badge badge-success p-2">
                    <i class="fas fa-check-circle"></i> Ya tiene cuenta de acceso
                </span>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>estudiantes/editar/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="<?php echo BASE_URL; ?>estudiantes" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']); ?>
    <?php endif; ?>
    
    <div class="row">
        <!-- Información Personal -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo BASE_URL; ?>public/img/student-avatar.png" class="img-profile rounded-circle" style="width: 120px; height: 120px;">
                        <h5 class="mt-3 mb-0"><?php echo $estudiante['nombre_completo']; ?></h5>
                        <p class="text-muted mb-3">
                            <span class="badge badge-<?php 
                                echo $estudiante['estado'] === 'activo' ? 'success' : 
                                    ($estudiante['estado'] === 'inactivo' ? 'danger' : 'warning');
                            ?>">
                                <?php echo ucfirst($estudiante['estado']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%"><i class="fas fa-id-card"></i> DNI</th>
                                    <td><?php echo $estudiante['dni'] ?? 'No registrado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-birthday-cake"></i> Fecha Nac.</th>
                                    <td><?php echo $estudiante['fecha_nacimiento'] ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'No registrada'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-phone"></i> Teléfono</th>
                                    <td><?php echo $estudiante['telefono'] ?? 'No registrado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-home"></i> Dirección</th>
                                    <td><?php echo $estudiante['direccion'] ?? 'No registrada'; ?></td>
                                </tr>
                                <?php if (isset($estudiante['correo'])): ?>
                                <tr>
                                    <th><i class="fas fa-envelope"></i> Correo</th>
                                    <td><?php echo $estudiante['correo']; ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información Académica -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Académica</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%"><i class="fas fa-graduation-cap"></i> Grado</th>
                                    <td>
                                        <?php echo isset($estudiante['grado_nombre']) && isset($estudiante['nivel_educativo']) 
                                            ? $estudiante['grado_nombre'] . ' ' . $estudiante['nivel_educativo'] 
                                            : 'No asignado'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-users"></i> Sección</th>
                                    <td><?php echo $estudiante['seccion_nombre'] ?? 'No asignada'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Año Escolar</th>
                                    <td><?php echo $estudiante['anio_escolar'] ?? 'No asignado'; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-book"></i> Mención</th>
                                    <td><?php echo $estudiante['mencion'] ?? 'No registrada'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Padres Asociados -->
            <div class="card shadow mt-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Padres o Tutores</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#asociarPadreModal">
                        <i class="fas fa-plus"></i> Asociar Padre
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($padres)): ?>
                        <div class="alert alert-info mb-0">
                            No hay padres asociados a este estudiante.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($padres as $padre): ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $padre['nombre_completo']; ?></h6>
                                        <div>
                                            <a href="<?php echo BASE_URL; ?>padres/detalle/<?php echo $padre['id_padre']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#desasociarPadreModal" data-id="<?php echo $padre['id_padre']; ?>">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-user-tag"></i> <?php echo $padre['parentesco'] ?? $padre['relacion']; ?>
                                            | <i class="fas fa-phone"></i> <?php echo $padre['telefono'] ?? 'No registrado'; ?>
                                        </small>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Panel Financiero -->
        <div class="col-lg-4">
            <!-- Deudas Pendientes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Deudas Pendientes</h6>
                    <a href="<?php echo BASE_URL; ?>pagos/registrar/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-dollar-sign"></i> Registrar Pago
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($deudas)): ?>
                        <div class="alert alert-success mb-0">
                            No hay deudas pendientes.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($deudas as $deuda): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $deuda['concepto']; ?></h6>
                                        <span class="badge badge-danger">
                                            S/ <?php echo number_format($deuda['monto'], 2); ?>
                                        </span>
                                    </div>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt"></i> Vence: <?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?>
                                            | <i class="fas fa-tag"></i> <?php echo ucfirst($deuda['tipo_costo']); ?>
                                        </small>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 text-center">
                            <span class="font-weight-bold">Total pendiente: </span>
                            <span class="text-danger font-weight-bold">
                                S/ <?php echo number_format(array_sum(array_column($deudas, 'monto')), 2); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?php echo BASE_URL; ?>pagos/historial/<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-history"></i> Ver Historial de Pagos
                    </a>
                </div>
            </div>
            
            <!-- Últimos Pagos -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimos Pagos</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($pagos)): ?>
                        <div class="alert alert-info mb-0">
                            No hay pagos registrados.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($pagos, 0, 5) as $pago): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                            <td><?php echo $pago['concepto']; ?></td>
                                            <td>S/ <?php echo number_format($pago['monto'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asociar Padre -->
<div class="modal fade" id="asociarPadreModal" tabindex="-1" aria-labelledby="asociarPadreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asociarPadreModalLabel">Asociar Padre/Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo BASE_URL; ?>estudiantes/asociarPadre" method="post" id="formAsociarPadre">
                    <input type="hidden" name="id_estudiante" value="<?php echo $estudiante['id_estudiante']; ?>">
                    
                    <div class="mb-3">
                        <label for="id_padre" class="form-label">Seleccionar Padre/Tutor</label>
                        <select class="form-select" id="id_padre" name="id_padre" required>
                            <option value="">Seleccione un padre/tutor</option>
                            <!-- Esta opción se cargará mediante AJAX -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="parentesco" class="form-label">Parentesco</label>
                        <select class="form-select" id="parentesco" name="parentesco" required>
                            <option value="Padre">Padre</option>
                            <option value="Madre">Madre</option>
                            <option value="Tutor Legal">Tutor Legal</option>
                            <option value="Abuelo/a">Abuelo/a</option>
                            <option value="Tío/a">Tío/a</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formAsociarPadre" class="btn btn-primary">Asociar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Desasociar Padre -->
<div class="modal fade" id="desasociarPadreModal" tabindex="-1" aria-labelledby="desasociarPadreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="desasociarPadreModalLabel">Confirmar Desasociación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea desasociar a este padre/tutor del estudiante? Esta acción no eliminará al padre del sistema.
            </div>
            <div class="modal-footer">
                <form action="<?php echo BASE_URL; ?>estudiantes/desasociarPadre" method="post">
                    <input type="hidden" name="id_estudiante" value="<?php echo $estudiante['id_estudiante']; ?>">
                    <input type="hidden" name="id_padre" id="desasociar_id_padre" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Desasociar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el modal de desasociación
    const desasociarModal = document.getElementById('desasociarPadreModal');
    if (desasociarModal) {
        desasociarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            document.getElementById('desasociar_id_padre').value = id;
        });
    }
    
    // Cargar padres disponibles para asociar
    const selectPadre = document.getElementById('id_padre');
    if (selectPadre) {
        // En una aplicación real, aquí se cargarían los padres mediante AJAX
        // Por ahora, simulamos algunos datos
        fetch('<?php echo BASE_URL; ?>padres/obtenerPadresJSON')
            .then(response => response.json())
            .then(data => {
                data.forEach(padre => {
                    const option = document.createElement('option');
                    option.value = padre.id_padre;
                    option.textContent = `${padre.nombres} ${padre.apellidos} (${padre.relacion})`;
                    selectPadre.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error al cargar los padres:', error);
                // Para demo, agregamos algunos padres estáticos si falla la carga
                const padresDemo = [
                    {id_padre: 1, nombre: "Juan Pérez (Padre)"},
                    {id_padre: 2, nombre: "María Gómez (Madre)"},
                    {id_padre: 3, nombre: "Carlos Rodríguez (Tutor)"}
                ];
                padresDemo.forEach(padre => {
                    const option = document.createElement('option');
                    option.value = padre.id_padre;
                    option.textContent = padre.nombre;
                    selectPadre.appendChild(option);
                });
            });
    }
});
</script>

<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>