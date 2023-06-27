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
			<div class="container vender">
				
				<div class="col-sm-12">
					<img src="images/Banner_vende tus prendas.png" alt="Imagen Banner Vender">
				</div>
				
				<main class="vender-main">
					<div class="col-md-12 mb-2">
						<div class="p-vender">
						<p class="p-main">¡Sé parte de nuestra comunidad de moda sustentable! Como comunidad perseguimos los mismos objetivos y compartimos las mismas elecciones, para que esta rueda no deje de girar. Por eso aquellas prendas de máxima calidad que esperás encontrar en nuestros locales son la base de nuestro criterio para la selección cuando venís a vender. Si vos lo comprarías, nosotros también.</p>
						</div>
				
						<div class="col-sm-12">
							<img src="images/Imagenes_boton_vender.png" alt="Imagen Boton vender">
						</div>
					</div>
				</main>
			</div>

			<?php include("btn-convenio.php"); ?>

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