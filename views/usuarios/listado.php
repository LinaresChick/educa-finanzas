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
                        <li class="breadcrumb-item"><a href="index.php?controller=Panel&action=dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?= $_SESSION['exito']; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?= $_SESSION['error']; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Card mejorada con diseño responsive -->
            <div class="card card-outline card-primary">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-2 mb-md-0">
                        <h3 class="card-title">
                            <i class="fas fa-users me-2"></i>Listado de usuarios del sistema
                        </h3>
                        <p class="text-muted mb-0 mt-1 d-none d-md-block">
                            Total de usuarios: <span class="badge bg-info"><?= count($usuarios ?? []) ?></span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <a href="index.php?controller=Usuario&action=crear" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Nuevo Usuario
                        </a>
                    </div>
                </div>

                <div class="card-body p-0 p-md-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped dataTable" id="tablaUsuarios">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="60">ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th class="text-center" width="120">Estado</th>
                                    <th class="text-center" width="150">Fecha Creación</th>
                                    <th class="text-center" width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay usuarios registrados</h5>
                                                <a href="index.php?controller=Usuario&action=crear" class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus me-1"></i> Crear primer usuario
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr class="<?= $usuario['estado'] === 'inactivo' ? 'table-light' : '' ?>">
                                            <td class="text-center fw-bold">#<?= $usuario['id_usuario'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium"><?= htmlspecialchars($usuario['nombre']) ?></div>
                                                        <?php if ($usuario['id_usuario'] == $_SESSION['usuario']['id_usuario']): ?>
                                                            <small class="text-primary"><i class="fas fa-user me-1"></i>Tú</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($usuario['correo']) ?>" class="text-decoration-none">
                                                    <i class="fas fa-envelope me-1 text-muted"></i>
                                                    <?= htmlspecialchars($usuario['correo']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getRoleBadgeClass($usuario['rol']) ?> px-3 py-2">
                                                    <i class="fas fa-user-tag me-1"></i>
                                                    <?= htmlspecialchars($usuario['rol']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input type="checkbox" 
                                                           class="form-check-input toggle-estado" 
                                                           id="estado_<?= $usuario['id_usuario'] ?>"
                                                           data-id="<?= $usuario['id_usuario'] ?>"
                                                           <?= $usuario['estado'] === 'activo' ? 'checked' : '' ?>
                                                           <?= ($usuario['id_usuario'] == $_SESSION['usuario']['id_usuario'] || 
                                                               (strtolower($usuario['rol']) === 'superadmin' && strtolower($_SESSION['usuario']['rol']) !== 'superadmin')) ? 'disabled' : '' ?>>
                                                    <label class="form-check-label" for="estado_<?= $usuario['id_usuario'] ?>">
                                                        <span class="estado-texto <?= $usuario['estado'] === 'activo' ? 'text-success' : 'text-danger' ?> fw-medium">
                                                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>
                                                            <?= $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('d/m/Y', strtotime($usuario['fecha_creacion'])) ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('H:i', strtotime($usuario['fecha_creacion'])) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="/educa-finanzas/public/index.php?controller=Usuario&action=editar&id=<?= $usuario['id_usuario'] ?>" 
                                                       class="btn btn-outline-info btn-sm action-btn"
                                                       title="Editar usuario"
                                                       <?= (strtolower($usuario['rol']) === 'superadmin' && strtolower($_SESSION['usuario']['rol']) !== 'superadmin') ? 'disabled' : '' ?>>
                                                        <i class="fas fa-edit"></i>
                                                        <span class="d-none d-md-inline"> Editar</span>
                                                    </a>
                                                    
                                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario']['id_usuario']): ?>
                                                        <?php if (strtolower($usuario['rol']) !== 'superadmin' || strtolower($_SESSION['usuario']['rol']) === 'superadmin'): ?>
                                                            <a href="#" 
                                                               class="btn btn-outline-danger btn-sm action-btn btn-eliminar"
                                                               data-bs-toggle="modal" 
                                                               data-bs-target="#modalEliminar"
                                                               data-id="<?= $usuario['id_usuario'] ?>" 
                                                               data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                                               title="Eliminar usuario">
                                                                <i class="fas fa-trash-alt"></i>
                                                                <span class="d-none d-md-inline"> Eliminar</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <a href="#" 
                                                       class="btn btn-outline-secondary btn-sm action-btn btn-detalle"
                                                       data-id="<?= $usuario['id_usuario'] ?>"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                        <span class="d-none d-md-inline"> Ver</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="mb-2 mb-md-0">
                            <small class="text-muted">Mostrando <?= count($usuarios ?? []) ?> usuarios</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Imprimir
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="btnExportar">
                                <i class="fas fa-file-excel me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Eliminar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-user-slash fa-4x text-danger"></i>
                </div>
                <h5 class="mb-3">¿Está seguro que desea eliminar al usuario?</h5>
                <p class="mb-2">Usuario: <strong id="nombreUsuario" class="text-danger"></strong></p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta acción desactivará el usuario del sistema y no se podrá deshacer.
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" id="btnConfirmarEliminar" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Eliminar definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Filtros -->
<div class="modal fade" id="modalFiltros" tabindex="-1" aria-labelledby="modalFiltrosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFiltrosLabel">
                    <i class="fas fa-filter me-2"></i>Filtrar Usuarios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltros">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="filtroRol">
                                <option value="">Todos los roles</option>
                                <option value="superadmin">Super Admin</option>
                                <option value="administrador">Administrador</option>
                                <option value="docente">Docente</option>
                                <option value="estudiante">Estudiante</option>
                                <option value="padre">Padre</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buscar por nombre o correo</label>
                        <input type="text" class="form-control" id="filtroBusqueda" placeholder="Escribe para buscar...">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser me-1"></i> Limpiar
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> Aplicar Filtros
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para mejorar la ergonomía y responsive */
    .avatar-circle {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    
    .action-btn {
        transition: all 0.2s ease;
        border-radius: 4px !important;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
    }
    
    .table td {
        vertical-align: middle;
        padding: 12px 8px;
    }
    
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    /* Responsive optimizations */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .btn-group {
            flex-direction: column;
            gap: 5px;
        }
        
        .btn-group .btn {
            border-radius: 4px !important;
            width: 100%;
            justify-content: center;
        }
        
        .table-responsive {
            border: none;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 4px 8px;
            margin: 2px;
        }
    }
    
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 10px;
        }
        
        .avatar-circle {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    /* Scroll suave */
    * {
        scroll-behavior: smooth;
    }
    
    /* Estado de carga */
    .loading-state {
        opacity: 0.6;
        pointer-events: none;
    }
</style>

<script>
$(document).ready(function() {
    // Inicializar DataTable con configuración avanzada
    var table = $('#tablaUsuarios').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>><"row"<"col-md-12"tr>><"row"<"col-md-6"i><"col-md-6"p>>',
        initComplete: function() {
            // Añadir buscador avanzado
            $('#filtroBusqueda').on('keyup', function() {
                table.search(this.value).draw();
            });
            
            $('#filtroRol').on('change', function() {
                table.column(3).search(this.value).draw();
            });
            
            $('#filtroEstado').on('change', function() {
                table.column(4).search(this.value).draw();
            });
        }
    });
    
    // Limpiar filtros
    $('#btnLimpiarFiltros').on('click', function() {
        $('#formFiltros')[0].reset();
        table.search('').columns().search('').draw();
    });
    
    // Modal Eliminar - Solución mejorada
    $(document).on("click", ".btn-eliminar", function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var id = $(this).data("id");
        var nombre = $(this).data("nombre");
        
        if (!id) {
            showToast('error', 'Error', 'No se pudo identificar el usuario');
            return;
        }
        
        $("#nombreUsuario").text(nombre);
        $("#btnConfirmarEliminar").data('id', id);
        
        // Mostrar modal con animación
        var modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        modal.show();
    });
    
    // Confirmar eliminación
    $(document).on('click', '#btnConfirmarEliminar', function(e){
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        
        if (!id) {
            showToast('error', 'Error', 'ID de usuario no especificado');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...');
        
        $.ajax({
            url: '/educa-finanzas/public/index.php?controller=Usuario&action=eliminar',
            method: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(res) {
                if (res && res.success) {
                    showToast('success', 'Éxito', 'Usuario eliminado correctamente');
                    
                    // Cerrar modal
                    $('#modalEliminar').modal('hide');
                    
                    // Recargar después de 1 segundo
                    setTimeout(function(){ 
                        location.reload(); 
                    }, 1000);
                } else {
                    showToast('error', 'Error', res?.message || 'Error al eliminar usuario');
                    btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Eliminar definitivamente');
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Error', 'Error en la conexión: ' + error);
                btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Eliminar definitivamente');
            }
        });
    });
    
    // Toggle estado con confirmación mejorada
    $(document).on('change', '.toggle-estado', function(e) {
        var checkbox = $(this);
        var id = checkbox.data('id');
        var nuevoEstado = checkbox.prop('checked') ? 'activo' : 'inactivo';
        var label = checkbox.closest('.form-check').find('.estado-texto');
        var icon = label.find('i');
        var currentIcon = icon.attr('class');
        
        // Cambiar icono temporalmente para feedback
        icon.removeClass('fa-circle').addClass('fa-spinner fa-spin');
        
        $.ajax({
            url: 'index.php?controller=Usuario&action=toggleEstado&id=' + id,
            method: 'POST',
            dataType: 'json',
            data: { estado: nuevoEstado },
            success: function(response) {
                if (response.success) {
                    var estadoFinal = response.estado || nuevoEstado;
                    label.removeClass('text-success text-danger')
                         .addClass(estadoFinal === 'activo' ? 'text-success' : 'text-danger')
                         .html('<i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>' + 
                               (estadoFinal === 'activo' ? 'Activo' : 'Inactivo'));
                    
                    // Actualizar fila visualmente
                    var row = checkbox.closest('tr');
                    if (estadoFinal === 'activo') {
                        row.removeClass('table-light');
                    } else {
                        row.addClass('table-light');
                    }
                    
                    showToast('success', 'Estado actualizado', 'El usuario ha sido ' + estadoFinal);
                } else {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    label.html('<i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>' + 
                              (!checkbox.prop('checked') ? 'Activo' : 'Inactivo'));
                    showToast('error', 'Error', response?.message || 'Error al cambiar estado');
                }
            },
            error: function() {
                checkbox.prop('checked', !checkbox.prop('checked'));
                label.html('<i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>' + 
                          (!checkbox.prop('checked') ? 'Activo' : 'Inactivo'));
                showToast('error', 'Error', 'Error en la conexión');
            }
        });
    });
    
    // Exportar a Excel
    $('#btnExportar').on('click', function() {
        window.location.href = 'index.php?controller=Usuario&action=exportar&tipo=excel';
    });
    
    // Función para mostrar notificaciones toast
    function showToast(type, title, message) {
        // Crear toast dinámicamente
        var toastId = 'toast-' + Date.now();
        var toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        // Añadir al contenedor
        $('.toast-container').append(toastHtml);
        
        // Mostrar toast
        var toastElement = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        toast.show();
        
        // Eliminar después de ocultar
        toastElement.addEventListener('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    
    // Añadir contenedor para toasts si no existe
    if ($('.toast-container').length === 0) {
        $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
    }
});
</script>

<?php
// Función de compatibilidad (mantener)
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