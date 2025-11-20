<?php
require_once __DIR__ . '/../templates/header.php';

// Función helper para el color del badge según el rol
function getRoleBadgeClass($rol) {
    switch (strtolower($rol)) {
        case 'superadmin': return 'danger';
        case 'administrador': return 'warning';
        case 'docente': return 'info';
        case 'estudiante': return 'success';
        case 'padre': return 'primary';
        default: return 'secondary';
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gestión de Usuarios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/educa-finanzas/public/index.php?controller=Panel&action=index">Inicio</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Mensajes de alerta -->
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['exito']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="card-title">Listado de usuarios del sistema</h3>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay usuarios registrados</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id_usuario'] ?></td>
                                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getRoleBadgeClass($usuario['rol']) ?>">
                                                    <?= htmlspecialchars($usuario['rol']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" 
                                                           class="form-check-input toggle-estado" 
                                                           id="estado_<?= $usuario['id_usuario'] ?>"
                                                           data-id="<?= $usuario['id_usuario'] ?>"
                                                           <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>
                                                           <?= ($usuario['id_usuario'] == $_SESSION['usuario']['id_usuario'] || 
                                                               (strtolower($usuario['rol']) === 'superadmin' && strtolower($_SESSION['usuario']['rol']) !== 'superadmin')) ? 'disabled' : '' ?>>
                                                    <label class="form-check-label" for="estado_<?= $usuario['id_usuario'] ?>">
                                                        <span class="estado-texto <?= $usuario['estado'] === 'activo' ? 'text-success' : 'text-danger' ?>">
                                                            <?= $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/educa-finanzas/public/index.php?controller=Usuario&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Editar"
                                                       <?= (strtolower($usuario['rol']) === 'superadmin' && strtolower($_SESSION['usuario']['rol']) !== 'superadmin') ? 'disabled' : '' ?>>
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                    
                                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id_usuario']): ?>
                                                        <?php if (strtolower($usuario['rol']) !== 'superadmin' || strtolower($_SESSION['usuario']['rol']) === 'superadmin'): ?>
                                                            <a href="#" 
                                                               class="btn btn-danger btn-sm btn-eliminar" 
                                                               data-toggle="modal" 
                                                               data-target="#modalEliminar" 
                                                               data-id="<?= $usuario['id_usuario'] ?>" 
                                                               data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                                               title="Eliminar">
                                                                <i class="fas fa-trash-alt"></i> Eliminar
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para eliminar usuario -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="modalEliminarLabel">Eliminar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el usuario <strong id="nombreUsuario"></strong>?</p>
                <p>Esta acción no se puede deshacer. El usuario será desactivado del sistema.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    console.log('Listado usuarios: script cargado');

    // Inicializar DataTable
    var table = $('.dataTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "order": [[0, 'asc']],
        "drawCallback": function(settings) {
            // Re-asignar eventos después de cada redibujado de DataTable
            console.log('DataTable redibujado, re-asignando eventos...');
        }
    });

    // Modal eliminar
    $(document).on('show.bs.modal', '#modalEliminar', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nombre = button.data('nombre');
        $('#nombreUsuario').text(nombre);
        $('#btnConfirmarEliminar').attr('href', 'index.php?controller=Usuario&action=eliminar&id=' + id);
    });

    // MANEJO DEL CAMBIO DE ESTADO - VERSIÓN MEJORADA
    $(document).on('change', '.toggle-estado', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var checkbox = $(this);
        var id = checkbox.data('id');
        
        if (!id) {
            console.error('❌ ID no encontrado en checkbox');
            return;
        }

        // Obtener el estado actual antes del cambio
        var estadoActual = checkbox.prop('checked') ? 'inactivo' : 'activo';
        var nuevoEstado = checkbox.prop('checked') ? 'activo' : 'inactivo';
        var label = checkbox.closest('.form-check').find('.estado-texto');

        console.log('🔔 Cambio de estado detectado:');
        console.log('   ID:', id);
        console.log('   Estado actual:', estadoActual);
        console.log('   Nuevo estado:', nuevoEstado);
        console.log('   Checkbox checked:', checkbox.prop('checked'));

        // Confirmación
        if (!confirm('¿Estás seguro que deseas ' + (nuevoEstado === 'activo' ? 'activar' : 'desactivar') + ' este usuario?')) {
            console.log('❌ Usuario canceló la acción');
            // Revertir el cambio visual
            checkbox.prop('checked', !checkbox.prop('checked'));
            return;
        }

        // Mostrar loading
        label.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        // Construir URL correctamente
        var url = 'index.php?controller=Usuario&action=toggleEstado&id=' + id;
        console.log('📤 Enviando AJAX a:', url);
        console.log('📤 Método: POST');
        console.log('📤 Datos:', { estado: nuevoEstado });

        // Realizar petición AJAX
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: { 
                estado: nuevoEstado
            },
            success: function(response) {
                console.log('✅ Respuesta del servidor recibida:', response);
                
                if (response && response.success) {
                    // Éxito - mantener el estado actual
                    label.removeClass('text-success text-danger')
                         .addClass(nuevoEstado === 'activo' ? 'text-success' : 'text-danger')
                         .html(nuevoEstado === 'activo' ? 'Activo' : 'Inactivo');
                    console.log('🎉 Estado actualizado correctamente a:', nuevoEstado);
                    
                    // Mostrar notificación de éxito
                    showNotification('Estado actualizado correctamente', 'success');
                } else {
                    // Error - revertir visualmente
                    console.error('❌ Error del servidor:', response);
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    label.removeClass('text-success text-danger')
                         .addClass(checkbox.prop('checked') ? 'text-success' : 'text-danger')
                         .html(checkbox.prop('checked') ? 'Activo' : 'Inactivo');
                    
                    var mensajeError = (response && response.message) ? response.message : 'Error desconocido del servidor';
                    console.error('❌ Mensaje de error:', mensajeError);
                    showNotification('Error: ' + mensajeError, 'error');
                }
            },
            error: function(xhr, status, err) {
                console.error('❌ Error en la petición AJAX:');
                console.error('   Status:', status);
                console.error('   Error:', err);
                console.error('   Response Text:', xhr.responseText);
                
                // Revertir visualmente
                checkbox.prop('checked', !checkbox.prop('checked'));
                label.removeClass('text-success text-danger')
                     .addClass(checkbox.prop('checked') ? 'text-success' : 'text-danger')
                     .html(checkbox.prop('checked') ? 'Activo' : 'Inactivo');
                
                showNotification('Error de conexión con el servidor', 'error');
            }
        });
    });

    // Función para mostrar notificaciones
    function showNotification(mensaje, tipo) {
        // Puedes usar toastr o alertas simples
        if (tipo === 'success') {
            alert('✅ ' + mensaje);
        } else {
            alert('❌ ' + mensaje);
        }
    }

    // Debug: Verificar que los eventos estén asignados
    console.log('✅ Eventos asignados correctamente');
    console.log('✅ Número de checkboxes encontrados:', $('.toggle-estado').length);
});
</script>

<?php
/**
 * Obtiene la clase CSS para la badge según el rol
 * 
 * @param string $rol El rol del usuario
 * @return string La clase CSS para la badge
 */
function getBadgeClassForRole($rol) {
    switch ($rol) {
        case 'superadmin':
            return 'badge-danger';
        case 'admin':
            return 'badge-warning';
        case 'tesoreria':
            return 'badge-primary';
        case 'colaborador':
            return 'badge-info';
        case 'estudiante':
            return 'badge-success';
        case 'padre':
            return 'badge-secondary';
        default:
            return 'badge-light';
    }
}
?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>