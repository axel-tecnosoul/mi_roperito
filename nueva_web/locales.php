<!DOCTYPE html>
<html lang="en">
<head>
	<?php include("head.php"); ?>
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

	<div class="tt-pageContent">
		<div class="container-fluid-custom contenedor-locales">
			<div class="row">
				<div class="col-1"></div>
				<div class="col-sm-10 contenedor-banner">
					<img src="images/locales/Banner_locales.png" alt="Imagen Banner Locales">
				</div>
				<div class="col-1"></div>
			</div>
			<div class="contenedor-main">
				<main class="contenedor-main-locales">

					<div class="contenido-locales custom-styles">
						<div class="imagen-locales iln-1"></div>
						<div class="contenido-descripcion-btn">
							<div class="contenido-locales-descripcion">
								<h3>Villa Ballester</h3>
								<p><span>DIAS:</span> de lunes a sábados</p>
								<p><span>HORARIOS:</span> de 10 a 20 hs.</p>
								<p><span>DIRECCIÓN:</span> Independencia 4701</p>
								<p>(Planta alta)</p>
							</div>
							<a href="#" class="btn">Como llegar</a>
						</div>
					</div>
						
					<div class="contenido-locales custom-styles">
						<div class="imagen-locales iln-2"></div>
						<div class="contenido-descripcion-btn">
							<div class="contenido-locales-descripcion">
								<h3>San Isidro</h3>
								<p><span>DIAS:</span> de lunes a sábados</p>
								<p><span>HORARIOS:</span> de 11 a 19 hs.</p>
								<p><span>DIRECCIÓN:</span> AV. Fondo de la Legua 425</p>
								<p>(Boulogne Sur)</p>
							</div>
							<a href="#" class="btn">Como llegar</a>
						</div>
					</div>

					<div class="contenido-locales custom-styles">
						<div class="imagen-locales iln-3"></div>
						<div class="contenido-descripcion-btn">
							<div class="contenido-locales-descripcion">
								<h3>Nuñez</h3>
								<p><span>DIAS:</span> de lunes a sábados</p>
								<p><span>HORARIOS:</span> de 10 a 20 hs.</p>
								<p><span>DIRECCIÓN:</span> Crisólogo Larralde 1970</p>
								<br>
							</div>
							<a href="#" class="btn">Como llegar</a>
						</div>
					</div>

					<div class="contenido-locales">
						<div class="contenido-locales-img">
							<img src="images/locales/conoce_nuestros_locales.png" alt="Imagen conoce nuestros lcoales">
						</div>
					</div>
				</main>
			</div>
		</div><!-- /.container-fluid -->

		<?php include("suscribite.php"); ?>

		<footer id="">
			<?php include("footer.php"); ?>
		</footer>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="external/jquery/jquery.min.js"><\/script>')</script>
	<script defer src="js/bundle.js"></script>

	<script defer src="separate-include/single-product/single-product.js"></script>
	<script src="separate-include/portfolio/portfolio.js"></script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>

	<a href="#" class="tt-back-to-top">Volver al inicio</a>
</body>
</html>