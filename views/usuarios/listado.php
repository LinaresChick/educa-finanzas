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
                        <div class="col-md-4 text-right">
                            <a href="/usuarios/crear" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                            </a>
                            <a href="/usuarios/roles" class="btn btn-info">
                                <i class="fas fa-user-tag"></i> Roles
                            </a>
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
                                                               ($usuario['rol'] === 'superadmin' && $_SESSION['usuario']['rol'] !== 'superadmin')) ? 'disabled' : '' ?>>
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
                                                       <?= ($usuario['rol'] === 'superadmin' && $_SESSION['usuario']['rol'] !== 'superadmin') ? 'disabled' : '' ?>>
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                    
                                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id_usuario']): ?>
                                                        <?php if ($usuario['rol'] !== 'superadmin' || $_SESSION['usuario']['rol'] === 'superadmin'): ?>
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
    // Inicializar DataTable
    $('.dataTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "order": [[0, 'asc']]
    });
    
    // Configurar modal de eliminación
    // Modal de eliminación
    $('#modalEliminar').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nombre = button.data('nombre');
        
        $('#nombreUsuario').text(nombre);
        $('#btnConfirmarEliminar').attr('href', '/educa-finanzas/public/index.php?controller=Usuario&action=eliminar&id=' + id);
    });
        $('#btnConfirmarEliminar').attr('href', '/educa-finanzas/public/index.php?controller=Usuario&action=eliminar&id=' + id);
        $('#btnConfirmarEliminar').attr('href', '/educa-finanzas/public/index.php?controller=Usuario&action=eliminar&id=' + id);
    });
    
    // Manejar el clic en el botón de confirmar eliminación
    $('#btnConfirmarEliminar').click(function(e) {
        e.preventDefault();
        var eliminarUrl = $(this).attr('href');
        
        // Realizar la petición de eliminación
        window.location.href = eliminarUrl;
    });
    });

    // Manejar cambio de estado
    $('.toggle-estado').on('click', function(e) {
        e.preventDefault(); // Prevenir el comportamiento predeterminado
        
        var checkbox = $(this);
        var id = checkbox.data('id');
        var estadoActual = checkbox.prop('checked');
        var nuevoEstado = 'inactivo'; // Siempre desactivamos al hacer clic
        var label = checkbox.siblings('label').find('.estado-texto');

        if (confirm('¿Estás seguro que deseas desactivar este usuario?')) {
            // Mostrar indicador de carga
            label.html('<i class="fas fa-spinner fa-spin"></i>');

            // Enviar petición AJAX para cambiar el estado
            $.ajax({
                url: '/educa-finanzas/public/index.php?controller=Usuario&action=toggleEstado&id=' + id,
                method: 'POST',
                data: { estado: nuevoEstado },
                success: function(response) {
                    response = typeof response === 'string' ? JSON.parse(response) : response;
                    if (response.success) {
                        // Actualizar el texto y color
                        checkbox.prop('checked', false);
                        label.removeClass('text-success text-danger')
                            .addClass('text-danger')
                            .html('Inactivo');
                        
                        alert('Usuario desactivado correctamente');
                        // Recargar la página para actualizar la lista
                        window.location.reload();
                    } else {
                        // Revertir el cambio en caso de error
                        checkbox.prop('checked', estadoActual);
                        label.removeClass('text-success text-danger')
                            .addClass(estadoActual ? 'text-success' : 'text-danger')
                            .html(estadoActual ? 'Activo' : 'Inactivo');
                        alert(response.message || 'Error al desactivar el usuario');
                    }
                },
                error: function(xhr) {
                    // Revertir el cambio en caso de error
                    checkbox.prop('checked', estadoActual);
                    label.removeClass('text-success text-danger')
                        .addClass(estadoActual ? 'text-success' : 'text-danger')
                        .html(estadoActual ? 'Activo' : 'Inactivo');
                    var response = xhr.responseJSON || {};
                    alert(response.message || 'Error al desactivar el usuario');
                }
            });
        } else {
            // Si el usuario cancela, revertir el cambio visual
            checkbox.prop('checked', estadoActual);
        }
    });
    });
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
