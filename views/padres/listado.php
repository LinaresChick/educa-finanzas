<?php require_once VIEWS_PATH . '/templates/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $titulo; ?></h1>

        <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=crear" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Padre/Tutor
        </a>
    </div>

    <?php if (isset($_SESSION['flash_mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Padres y Tutores</h6>

            <form action="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=buscar" method="get" class="d-flex">
                <input type="text" name="termino" class="form-control" placeholder="Buscar padre/tutor" required>
                <button type="submit" class="btn btn-outline-primary ml-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="card-body">
            <?php if (empty($padres)): ?>
                <div class="alert alert-info">
                    No hay padres o tutores registrados<?php echo isset($termino) ? ' para la búsqueda "' . $termino . '"' : ''; ?>.
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombres y Apellidos</th>
                                <th>DNI</th>
                                <th>Relación</th>
                                <th>Contacto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($padres as $padre): ?>
                                <tr>
                                    <td><?php echo $padre['id_padre']; ?></td>
                                    <td><?php echo $padre['nombre_completo']; ?></td>
                                    <td><?php echo $padre['dni'] ?? 'No registrado'; ?></td>
                                    <td><?php echo $padre['relacion']; ?></td>

                                    <td>
                                        <?php if ($padre['telefono']): ?>
                                            <i class="fas fa-phone"></i> <?php echo $padre['telefono']; ?><br>
                                        <?php endif; ?>

                                        <?php if ($padre['correo']): ?>
                                            <i class="fas fa-envelope"></i> <?php echo $padre['correo']; ?>
                                        <?php endif; ?>

                                        <?php if (!$padre['telefono'] && !$padre['correo']): ?>
                                            No registrado
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge badge-<?php echo $padre['estado'] === 'activo' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($padre['estado']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=detalle&id=<?php echo $padre['id_padre']; ?>" 
                                                class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="<?php echo BASE_URL; ?>/index.php?controller=Padre&action=editar&id=<?php echo $padre['id_padre']; ?>" 
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <?php if ($_SESSION['usuario']['rol'] === 'Superadmin' || $_SESSION['usuario']['rol'] === 'Administrador'): ?>
                                                <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#eliminarModal"
                                                    data-id="<?php echo $padre['id_padre']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Modal Eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                ¿Está seguro que desea eliminar este padre/tutor? Esta acción no se puede deshacer.
            </div>

            <div class="modal-footer">

                <!-- ⚠️ AHORA SÍ FUNCIONA: el id va en la URL -->
                <form id="formEliminar" method="post">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>

            </div>

        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    // Configurar ID dinámico en formulario de eliminar
    var eliminarModal = document.getElementById('eliminarModal');

    eliminarModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');

        // Actualizamos la URL del formulario
        document.getElementById('formEliminar').action =
            "<?php echo BASE_URL; ?>/index.php?controller=Padre&action=eliminar&id=" + id;
    });

    // DataTable
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#dataTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' }
        });
    }
});
</script>
<style>
    /* ================================
   MEJORAS ERGONÓMICAS PARA TELÉFONO - PADRES/TUTORES
   (Sin modificar nada del código existente)
================================ */

/* ===== MEJORAS GENERALES PARA MÓVIL ===== */
@media (max-width: 576px) {
  
  /* Contenedor principal con mejor espaciado */
  .container-fluid.py-4 {
    padding-left: 10px !important;
    padding-right: 10px !important;
  }
  
  /* Header ajustado */
  .d-flex.justify-content-between.align-items-center.mb-4 {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 15px;
  }
  
  .d-flex.justify-content-between.align-items-center.mb-4 .btn {
    width: 100%;
    min-height: 48px;
    font-size: 1rem;
  }
  
  /* Card header responsive */
  .card-header.py-3.d-flex {
    flex-direction: column;
    gap: 15px;
    align-items: stretch !important;
  }
  
  /* Formulario de búsqueda mejorado */
  .card-header form.d-flex {
    flex-direction: row;
    width: 100%;
  }
  
  .card-header form input {
    flex: 1;
    min-height: 44px;
    font-size: 16px; /* Evita zoom en iOS */
  }
  
  .card-header form button {
    min-width: 50px;
    min-height: 44px;
    margin-left: 10px !important;
  }
}

/* ===== TABLA RESPONSIVA MEJORADA ===== */
@media (max-width: 768px) {
  
  /* Tabla tipo tarjetas */
  #dataTable thead {
    display: none;
  }
  
  #dataTable tbody tr {
    display: block;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    background: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
  }
  
  #dataTable tbody td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 8px;
    border: none;
    font-size: 0.9rem;
    min-height: 48px;
  }
  
  #dataTable tbody td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #495057;
    min-width: 120px;
    font-size: 0.85rem;
  }
  
  /* Añadir etiquetas a las celdas */
  #dataTable tbody td:nth-child(1)::before { content: "ID"; }
  #dataTable tbody td:nth-child(2)::before { content: "Nombre Completo"; }
  #dataTable tbody td:nth-child(3)::before { content: "DNI"; }
  #dataTable tbody td:nth-child(4)::before { content: "Relación"; }
  #dataTable tbody td:nth-child(5)::before { content: "Contacto"; }
  #dataTable tbody td:nth-child(6)::before { content: "Estado"; }
  #dataTable tbody td:nth-child(7)::before { content: "Acciones"; }
  
  /* Separador visual entre filas en modo tarjeta */
  #dataTable tbody td:not(:last-child) {
    border-bottom: 1px solid #f0f0f0;
  }
}

/* ===== BOTONES MÁS ERGONÓMICOS ===== */
@media (max-width: 576px) {
  
  /* Grupo de botones apilados verticalmente */
  .btn-group[role="group"] {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  
  .btn-group .btn {
    width: 100%;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    font-size: 0.95rem;
    border-radius: 8px;
  }
  
  .btn-group .btn i {
    margin-right: 8px;
    font-size: 1.1rem;
  }
  
  /* Badges más legibles */
  .badge {
    padding: 8px 12px;
    font-size: 0.9rem;
    border-radius: 20px;
    min-width: 80px;
    text-align: center;
    display: inline-block;
  }
}

/* ===== MEJORAS DE CONTACTO ===== */
@media (max-width: 768px) {
  
  /* Contacto en columnas para móvil */
  #dataTable tbody td:nth-child(5) {
    flex-direction: column;
    align-items: flex-start !important;
  }
  
  #dataTable tbody td:nth-child(5)::before {
    margin-bottom: 8px;
  }
  
  #dataTable tbody td:nth-child(5) i {
    margin-right: 10px;
    min-width: 20px;
    color: #6c757d;
  }
  
  #dataTable tbody td:nth-child(5) br {
    display: none;
  }
  
  /* Cada dato de contacto en su propia línea */
  #dataTable tbody td:nth-child(5) > span {
    display: block;
    margin-bottom: 5px;
    width: 100%;
  }
}

/* ===== FEEDBACK TÁCTIL ===== */
@media (hover: none) and (pointer: coarse) {
  
  /* Feedback al tocar filas */
  #dataTable tbody tr {
    transition: transform 0.2s, box-shadow 0.2s;
  }
  
  #dataTable tbody tr:active {
    transform: scale(0.98);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    background-color: #f8f9fa;
  }
  
  /* Feedback en botones */
  .btn:active {
    opacity: 0.8;
    transform: translateY(1px);
  }
  
  /* Evitar zoom en inputs */
  input.form-control {
    font-size: 16px !important;
  }
}

/* ===== MODAL MÁS ERGONÓMICO ===== */
@media (max-width: 576px) {
  
  .modal-dialog {
    margin: 10px;
    max-width: calc(100% - 20px);
  }
  
  .modal-content {
    border-radius: 12px;
  }
  
  .modal-footer {
    flex-direction: column;
    gap: 10px;
  }
  
  .modal-footer .btn {
    width: 100%;
    min-height: 48px;
  }
}

/* ===== MEJORAS DE TEXTO ===== */
@media (max-width: 576px) {
  
  h1.h3 {
    font-size: 1.4rem;
    margin-bottom: 0;
  }
  
  h6.m-0.font-weight-bold {
    font-size: 1.1rem;
    text-align: center;
    width: 100%;
  }
  
  .alert {
    font-size: 0.95rem;
    padding: 12px 15px;
  }
  
  /* Prevenir desbordamiento de texto largo */
  #dataTable tbody td {
    word-break: break-word;
    hyphens: auto;
  }
}

/* ===== SCROLL SUAVE PARA TABLAS ===== */
.table-responsive {
  -webkit-overflow-scrolling: touch;
  touch-action: pan-x;
  scrollbar-width: thin;
  scrollbar-color: #adb5bd #f8f9fa;
}

.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: #f8f9fa;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #adb5bd;
  border-radius: 4px;
}

/* ===== PANTALLAS MUY PEQUEÑAS ===== */
@media (max-width: 360px) {
  
  #dataTable tbody td {
    font-size: 0.85rem;
    padding: 10px 6px;
  }
  
  #dataTable tbody td::before {
    font-size: 0.8rem;
    min-width: 100px;
  }
  
  .btn-group .btn {
    font-size: 0.9rem;
    padding: 8px 12px;
  }
  
  .badge {
    min-width: 70px;
    padding: 6px 10px;
    font-size: 0.85rem;
  }
}

/* ===== COMPATIBILIDAD CON DARK MODE ===== */
@media (prefers-color-scheme: dark) and (max-width: 576px) {
  
  #dataTable tbody tr {
    background-color: #2d3748;
    border-color: #4a5568;
  }
  
  #dataTable tbody td {
    color: #e2e8f0;
  }
  
  #dataTable tbody td::before {
    color: #a0aec0;
  }
  
  #dataTable tbody td:not(:last-child) {
    border-bottom-color: #4a5568;
  }
}
</style>
<?php require_once VIEWS_PATH . '/templates/footer.php'; ?>
