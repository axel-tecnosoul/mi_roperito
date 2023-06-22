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
		<div class="container-fluid-custom" style="margin-top: 20px;">
			<main>
				<div class="row">
					<div class="col-1"></div>
					<div class="col-sm-10">
						<img src="images/nosotros/Banner_nosotros.png" alt="" style="max-width: 100%; display: block; max-height:100%;">
					</div>
					<div class="col-1"></div>
					<div class="col-1"></div>
					<div class="col-sm-5">
						<a href="#" class="tt-collection-item ">
							<img src="images/loader-08.svg" data-src="images/nosotros/Imagen_Recircula.png" alt="">
							
						</a>
					</div>
					<div class="col-sm-5">
						<a href="#" class="tt-collection-item ">
							<img src="images/loader-08.svg" data-src="images/nosotros/Imagen_Agentes de cambio.png" alt="">
							
						</a>
					</div>
				</div>

				<div class="row">
					<div class="col-1"></div>
					<div class="col-sm-5">
						<div>
							<h1>NUESTRA MISION</h1>
							<p>Extender el ciclo de vida de las prendas de marca. Las prendas elaboradas por marcas reconocidas están diseñadas para durar toda la vida; pueden cambiar de manos innumerables veces y aún conservan su calidad y valor. Aportamos experiencia y entusiasmo a nuestra misión de ampliar la vida de las prendas de marca y permitir que más personas las posean y aprecien mientras dan a sus sueños originales la oportunidad de maximizar el valor de sus inversiones.</p>
							
						</div>
					</div>
					<div class="col-sm-5">
						<div>
							<h1>NESTRA VISION</h1>
							<p>Mi Roperito está empoderando a nuestras decenas de miles de miembros para constribuir a un futuro más osstenible y el crecimiento de una comundid vital en torno a la economía circular. Si bien el lujo es tanto nuestra plataforma como nuestra pasión, creemos que nuestro trabajo tiene el poder de afectar un cambio social más amplio para movernos hacia un mundo donde todo consumo es consumo consciente. El futuro de la moda es circular.</p>
							
						</div>
					</div>
					<div class="col-1"></div>
				</div>
			</main>

			<?php include("suscribite.php"); ?>

			<footer id="">
				<?php include("footer.php"); ?>
			</footer>
		</div>
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