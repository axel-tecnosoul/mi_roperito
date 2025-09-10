<?php require('admin/config.php'); ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<section>
                                <div class="container-indent">
                                        <div class="container contenedor-subscribite">
                                                <div class="row justify-content-center">
                                                        <div class="col-md-10 col-lg-8 col-xl-8">
                                                                <div class="tt-layout-newsletter02">
                                                                        <h3 class="tt-title subscribite">ENTERATE DE LAS ÚLTIMAS NOVEDADES</h3>

                                                                        <form id="subscriptionForm" class="form-inline form-default form-subscribite" method="post" action="suscribir.php">
                                                                                <div class="form-group">
                                                                                        <input type="email" name="email" class="form-control form-subscribite-input" placeholder="Ingresá tu email" required="required" style="">
                                                                                        <div class="g-recaptcha" data-sitekey="<?php echo $recaptchaSite; ?>"></div>
                                                                                        <button type="submit" class="btn btn-lg">Suscribite!</button>
                                                                                </div>
                                                                        </form>
                                                                        <div id="subscriptionLoader" class="spinner-border text-primary" role="status" style="display:none;">
                                                                                <span class="sr-only">Procesando...</span>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>

                                <div class="modal fade" id="subscriptionModal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                        <div class="modal-header">
                                                                <h5 class="modal-title">Suscripción</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                        <span aria-hidden="true">&times;</span>
                                                                </button>
                                                        </div>
                                                        <div class="modal-body" id="subscriptionModalBody"></div>
                                                        <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </section>
