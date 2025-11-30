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

                                            <td><?= date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) ?></td>

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


<!-- Modal eliminar -->
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
                <button type="button" id="btnConfirmarEliminar" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    var table = $('.dataTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[0, 'asc']]
    });
    // MODAL ELIMINAR – SOLUCIÓN DEFINITIVA PARA DATATABLES
$(document).on("click", ".btn-eliminar", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("➡️ CLICK EN BOTÓN ELIMINAR DETECTADO");

    var id = $(this).data("id");
    var nombre = $(this).data("nombre");

    if (!id) {
        console.error("❌ ERROR: ID NO DETECTADO");
        return;
    }

    // Mostrar el nombre en el modal
    $("#nombreUsuario").text(nombre);

    // Guardar id en atributo del botón para usar en la petición
    $("#btnConfirmarEliminar").data('id', id);

    // Abrir el modal manualmente
    $("#modalEliminar").modal("show");
});



    $(document).on('change', '.toggle-estado', function(e) {

        var checkbox = $(this);
        var id = checkbox.data('id');
        var nuevoEstado = checkbox.prop('checked') ? 'activo' : 'inactivo';
        var label = checkbox.closest('.form-check').find('.estado-texto');

        if (!confirm('¿Estás seguro que deseas ' + (nuevoEstado === 'activo' ? 'activar' : 'desactivar') + ' este usuario?')) {
            checkbox.prop('checked', !checkbox.prop('checked'));
            return;
        }

        label.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: 'index.php?controller=Usuario&action=toggleEstado&id=' + id,
            method: 'POST',
            dataType: 'json',
            data: { estado: nuevoEstado },
            success: function(response) {

                if (response.success) {
                    label.removeClass('text-success text-danger')
                         .addClass(nuevoEstado === 'activo' ? 'text-success' : 'text-danger')
                         .html(nuevoEstado === 'activo' ? 'Activo' : 'Inactivo');
                } else {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    label.html('Error');
                }
            },
            error: function() {
                checkbox.prop('checked', !checkbox.prop('checked'));
                label.html('Error');
            }
        });
    });

        // Manejar confirmación de eliminar con AJAX y autorefresh
        $(document).on('click', '#btnConfirmarEliminar', function(e){
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            if (!id) {
                alert('ID de usuario no especificado');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');

            $.ajax({
                url: '/educa-finanzas/public/index.php?controller=Usuario&action=eliminar',
                method: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(res) {
                    if (res && res.success) {
                        // Cerrar modal y refrescar automáticamente la página
                        $('#modalEliminar').modal('hide');
                        setTimeout(function(){ location.reload(); }, 300);
                    } else {
                        alert((res && res.message) ? res.message : 'Error al eliminar');
                        btn.prop('disabled', false).html('Eliminar');
                    }
                },
                error: function() {
                    alert('Error en la petición. Inténtalo de nuevo.');
                    btn.prop('disabled', false).html('Eliminar');
                }
            });
        });

    });
</script>

<?php
/**
 * Segunda función de badge (NO se elimina, se mantiene para compatibilidad)
 * evitando conflicto con la primera.
 */
if (!function_exists('getBadgeClassForRole')) {
    function getBadgeClassForRole($rol) {
        switch ($rol) {
            case 'superadmin':   return 'badge-danger';
            case 'admin':        return 'badge-warning';
            case 'tesoreria':    return 'badge-primary';
            case 'colaborador':  return 'badge-info';
            case 'estudiante':   return 'badge-success';
            case 'padre':        return 'badge-secondary';
            default:             return 'badge-light';
        }
    }
}
?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
