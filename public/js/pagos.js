document.addEventListener('DOMContentLoaded', function() {
    // Configuración MÍNIMA de DataTables
    $('.dataTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[0, 'desc']],
        pageLength: 25
    });

    // Configurar el modal de eliminación
    $('#modalAnular').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const info = button.data('info');
        const modal = $(this);
        modal.find('#infoPago').text(info);
        modal.find('#id_pago').val(id);
    });
});