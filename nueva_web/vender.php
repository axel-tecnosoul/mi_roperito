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
			<div class="container container-banner">
				<div class="col-sm-12">
					<div class="banner">
						<div class="banner-contenido">
							<div class="banner-img">
								<img src="images/banner-vende-btn.png" alt="Imagen Logo vende">
							</div>
							<div class="banner-descripcion">
								<div class="banner-descripcion-titulo">
									<h1>Vendé tus prendas</h1>
								</div>
								<div class="banner-descripcion-parrafo">
									<p>
										Te recomendamos leer los criterios de selección antes de sacar 
									</p>
									<p>tu turno, ya que pueden haber cambiado.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container vender">	
				<main class="vender-main">
					<div class="col-md-12 mb-2">
						<div class="p-vender">
						<p class="p-main">¡Sé parte de nuestra comunidad de moda sustentable!</p>
						<p class="p-main">Como comunidad perseguimos los mismos objetivos y compartimos las mismas elecciones, para que esta rueda no deje de girar. Por eso aquellas prendas de máxima calidad que esperás encontrar en nuestros locales son la base de nuestro criterio para la selección cuando venís a vender.</p>
						<p class="p-main">Si vos lo comprarías, nosotros también.</p>
						</div>
				
						<div class="col-sm-12">
							<div class="vender-prenda">
								<div class="prenda">

								</div>
								<div class="prenda">

								</div>
								<a href="turnos.php" class="green-button">QUIERO VENDER MIS PRENDAS</a>
							</div>
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