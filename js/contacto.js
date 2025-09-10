$(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            $('#contact-message-text').text('Por favor, verifica el reCAPTCHA.');
            $('#contact-message').modal('show');
            return;
        }
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function(resp) {
            $('#contact-message-text').text(resp.message);
            $('#contact-message').modal('show');
            if (resp.success) {
                $form[0].reset();
                grecaptcha.reset();
            }
        }).fail(function() {
            $('#contact-message-text').text('No se pudo enviar el mensaje.');
            $('#contact-message').modal('show');
        });
    });
});
