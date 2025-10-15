document.addEventListener('DOMContentLoaded', function() {
    // Configuración de DataTables
    var table = $('.dataTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        order: [[5, 'desc']],
        pageLength: 25,
        columnDefs: [
            {targets: [0], width: "5%"},
            {targets: [1], width: "15%"},
            {targets: [2], width: "15%"},
            {targets: [3,4,5,6,7,8,9], width: "10%"},
            {targets: [10], width: "10%", orderable: false}
        ],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Función para eliminar un pago usando AJAX
    function eliminarPago(idPago) {
        const formData = new FormData();
        formData.append('id_pago', idPago);

        fetch(`${BASE_URL}/index.php?controller=Pago&action=eliminar`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#modalAnular').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'No se pudo eliminar el pago: ' + error.message,
                icon: 'error'
            });
        });
    }

    // Manejar el envío del formulario de eliminación
    $('#formAnular').on('submit', function(e) {
        e.preventDefault();
        const idPago = $('#id_pago').val();
        eliminarPago(idPago);
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