$(function() {
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.text();
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            $('#contact-message-text').text('Por favor, verifica el reCAPTCHA.');
            $('#contact-message').modal('show');
            return;
        }
        $submitBtn.prop('disabled', true).text('Enviando...');
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(function(resp) {
            $('#contact-message-text').text(resp.message);
            $('#contact-message').modal('show');
            $submitBtn.prop('disabled', false).text(originalText);
            if (resp.success) {
                $form[0].reset();
                grecaptcha.reset();
            }
        }).fail(function() {
            $('#contact-message-text').text('No se pudo enviar el mensaje.');
            $('#contact-message').modal('show');
            $submitBtn.prop('disabled', false).text(originalText);
        });
    });
});
