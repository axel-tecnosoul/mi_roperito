$(function() {
    $('#subscriptionForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        if (typeof grecaptcha !== 'undefined' && !grecaptcha.getResponse()) {
            $('#subscriptionModalBody').text('Por favor, completá el reCAPTCHA.');
            $('#subscriptionModal').modal('show');
            return;
        }

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
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }
            }
        }).fail(function() {
            $('#subscriptionModalBody').text('No se pudo procesar la solicitud.');
            $('#subscriptionModal').modal('show');
        }).always(function() {
            $('#subscriptionLoader').hide();
        });
    });
});
