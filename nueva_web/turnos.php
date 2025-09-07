<?php
require("../admin/config.php");
include('../admin/database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include("head.php"); ?>
  <style>
    #modalNoRecibimosPrendas .btn:hover {
      background: #191919;
      text-decoration: underline;
      color: #ffffff;
    }
  </style>
</head>
<body>
<div id="loader-wrapper">
	<div id="loader">
		<div class="dot"></div>
		<div class="dot"></div>
		<div class="dot"></div>
		<div class="dot"></div>
		<div class="dot"></div>
		<div class="dot"></div>
		<div class="dot"></div>
	</div>
</div>
<header id="tt-header">
	<?php include("header.php"); ?>
</header>
<br>

<!-- Section: Contact v.1 -->
<section class="my-5">

  <!-- Section heading -->
  <h2 class="h1-responsive font-weight-bold text-center my-5">Sacá tu turno para vender</h2>
  <!-- Section description -->
  

  <!-- Grid row -->
  <div class="row">

    <!-- Grid column -->
    <div class="col-lg-6 mb-lg-4 mb-4 centrar-div2">

      <!-- Form with header -->
      <div class="card">
        <div class="card-body">
          <!-- Header -->
          
          <form id="" class="" method="post" action="emitirTurno.php">
									
          <!-- Body -->
            <div class="md-form">
              <label for="form-suc">Sucursal</label>
              <select name="id_almacen" id="id_almacen" class="form-control" required="required">
                <option value="">Seleccione...</option><?php
                $pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sqlZon = "SELECT id, almacen FROM almacenes WHERE id_tipo=1 AND activo = 1 and id!=5";
                $q = $pdo->prepare($sqlZon);
                $q->execute();
                while ($fila = $q->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value='".$fila['id']."'";
                  echo ">".$fila['almacen']."</option>";
                }
                Database::disconnect();?>
              </select>
            </div>
		  
		      <div class="md-form">
            <label for="form-cant">Cantidad</label>
            <select name="cantidad" id="form-cant" class="form-control" required="required">
              <option value="">Seleccione...</option>
              <option value="1 a 15 prendas">1 a 15 prendas</option>
              <option value="16 a 35 prendas">16 a 35 prendas</option>
              <option value="36 a 50 prendas">36 a 50 prendas</option>
              <option value="51 a 70 prendas">51 a 70 prendas</option>
              <option value="71 a 85 prendas">71 a 85 prendas</option>
              <option value="Más de 86 prendas">Más de 86 prendas</option>
			      </select>
            
           </div><?php
          $hoy=date("Y-m-d");?>
                      <div class="md-form">
            <label for="form-fec">Fecha</label>
            <!-- Línea original:
            <input type="date" name="fecha" id="form-fec" class="form-control" required="required" min="2023-08-15">
            -->
            <input type="date" name="fecha" id="form-fec" class="form-control" required="required" min="<?=$hoy?>" value="<?=$hoy?>">
          </div>
                      <?php
          // Código original del campo hora:
          /*
          <div class="md-form">
            <label for="form-hora">Hora</label>
            <input type="time" name="hora" id="form-hora" class="form-control" min="11:00" max="18:30" required="required">
          </div>
          */
          ?>
                      <div class="md-form">
            <label for="form-hora">Hora</label>
            <select name="hora" id="form-hora" class="form-control" required="required" disabled>
              <option value="">Seleccione un horario</option>
            </select>
          </div>
		      <div class="md-form">
            <label for="form-dni">DNI</label>
            <input type="text" name="dni" id="form-dni" class="form-control" required="required">
          </div>
          <div class="md-form">
            <label for="form-name">Nombre y Apellido</label>
            <input type="text" name="nombre" id="form-name" class="form-control" required="required">
          </div>
          <div class="md-form">
            <label for="form-email">E-mail</label>
            <input type="email" name="email" id="form-email" class="form-control" required="required">
          </div>
          <div class="md-form">
            <label for="form-tel">Teléfono</label>
            <input type="text" name="telefono" id="form-tel" class="form-control" required="required">
          </div>
		      <br>
          <div class="text-center">
            <!-- Botón original:
            <button type="submit" class="btn btn-lg" style="height:70px; font-size:35px;">Solicitar Turno</button>
            -->
            <button type="submit" class="btn btn-lg" style="height:70px; font-size:35px;" id="btn-submit" disabled>Solicitar Turno</button>
          </div>
		    </form>
        </div>
      </div>
      <!-- Form with header -->

    </div>
    <!-- Grid column -->  
  </div>

  <div class="modal" id="modalNoRecibimosPrendas" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <!-- <div class="modal-header">
          <h5 class="modal-title">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div> -->
        <div class="modal-body">
          <!-- <p>A partir del día lunes 31 de Julio ya no se reciben prendas de invierno. Gracias</p> -->
          <!-- <p>No estamos tomando mas prendas en esta sucural. Por favor sepa disculparnos. Gracias</p> -->
          <p>Solo estamos tomando prendas Primavera/Verano. Muchas gracias</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal aviso sabados o domingos -->
  <div class="modal" id="modalFinesDeSemana" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <!-- <div class="modal-header">
          <h5 class="modal-title">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div> -->
        <div class="modal-body">
          <!-- <p>A partir del día lunes 31 de Julio ya no se reciben prendas de invierno. Gracias</p> -->
          <!-- <p>No estamos tomando mas prendas en esta sucural. Por favor sepa disculparnos. Gracias</p> -->
          <p>Por favor seleccione un día entre lunes y viernes. Para turnos los días Sabado por favor comunicarse vía email para verificar disponibilidad. Muchas gracias</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  <!-- FIN Modal aviso sabados o domingos -->
</section>

<footer id="">
	<?php include("footer.php"); ?>
</footer>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="external/jquery/jquery.min.js"><\/script>')</script>
<script defer src="js/bundle.js"></script>

<script defer src="separate-include/single-product/single-product.js"></script>
<script src="separate-include/portfolio/portfolio.js"></script>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
  <script>
    /* Código anterior:
    $(document).ready(function () {
      $("#id_almacen").on("change",function(){
        if(this.value==4 || this.value==6 || this.value==7){
          $("#modalNoRecibimosPrendas").modal("show");
          if(this.value==6){
            $("#form-hora").attr("max","17:30");
          }else{
            $("#form-hora").attr("max","18:30");
          }
          //$("#id_almacen").val("")
        }
      })

      document.getElementById("form-fec").addEventListener("change", function() {
        var selectedDate = new Date(this.value);
        var dayOfWeek = selectedDate.getDay();
        console.log(dayOfWeek);
        if (dayOfWeek === 5 || dayOfWeek === 6) {
          console.log("La fecha seleccionada es un sábado o domingo.");
          $("#modalFinesDeSemana").modal("show")
          this.value="";
        } else {
          console.log("La fecha seleccionada NO es un sábado ni domingo.");
        }
      });

    });
    */
    $(document).ready(function () {
      function fetchHorarios() {
        var idAlmacen = $("#id_almacen").val();
        var fecha = $("#form-fec").val();
        var $hora = $("#form-hora");
        var $submit = $("#btn-submit");
        if (!idAlmacen || !fecha) {
          $hora.empty().append('<option value="">Seleccione un horario</option>');
          $hora.prop('disabled', true);
          $submit.prop('disabled', true);
          return;
        }
        $.getJSON('../obtenerHorarios.php', { id_almacen: idAlmacen, fecha: fecha }, function (data) {
          var todayStr = new Date().toISOString().split('T')[0];
          if (fecha === todayStr) {
            var now = new Date();
            var currentTime = ("0" + now.getHours()).slice(-2) + ":" + ("0" + now.getMinutes()).slice(-2);
            data = data.filter(function (h) { return h >= currentTime; });
          }
          $hora.empty();
          if (data.length) {
            $hora.append('<option value="">Seleccione...</option>');
            data.forEach(function (h) {
              $hora.append('<option value="' + h + '">' + h + '</option>');
            });
            $hora.prop('disabled', false);
            $submit.prop('disabled', false);
          } else {
            $hora.append('<option value="">Sin horarios disponibles</option>');
            $hora.prop('disabled', true);
            $submit.prop('disabled', true);
          }
        });
      }

      $("#id_almacen").on("change", function () {
        if (this.value == 4 || this.value == 6 || this.value == 7) {
          $("#modalNoRecibimosPrendas").modal("show");
        }
        fetchHorarios();
      });

      $("#form-fec").on("change", function () {
        var selectedDate = new Date(this.value);
        var dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 5 || dayOfWeek === 6) {
          $("#modalFinesDeSemana").modal("show");
          this.value = "";
          fetchHorarios();
          return;
        }
        fetchHorarios();
      });

      fetchHorarios();
    });
  </script>
<a href="#" class="tt-back-to-top">Volver al inicio</a>
</body>
</html>