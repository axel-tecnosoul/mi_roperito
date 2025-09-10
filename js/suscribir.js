$(function() {
    $('#subscriptionForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        $('#subscriptionLoader').show();
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function(resp) {
            $('#subscriptionModalBody').text(resp.message);
            $('#subscriptionModal').modal('show');
            if (resp.status === 'success') {
                $form[0].reset();
            }
        }).fail(function() {
            $('#subscriptionModalBody').text('No se pudo procesar la solicitud.');
            $('#subscriptionModal').modal('show');
        }).always(function() {
            $('#subscriptionLoader').hide();
        });
    });
});
