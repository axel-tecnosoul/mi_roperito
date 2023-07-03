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
		<div class="container-fluid-custom contenedor-nosotros">
			<div class="row">
				<div class="col-1"></div>
				<div class="col-sm-10 contenedor-banner">
					<img src="images/nosotros/Banner_nosotros.png" alt="Imagen Banner Nosotros">
				</div>
				<div class="col-1"></div>
			</div>
			<div class="contenedor-main">
			<main class="contenedor-main-nosotros">
				<div class="contenido-nosotros">
					<div class="contenido-nosotros-img">
						<a href="#" class="tt-collection-item ">
							<img src="images/nosotros/Imagen_Recircula.png" alt="Imagen Recircula">	
						</a>
					</div>
					<div class="contenido-nosotros-descripcion">
						<h3>NUESTRA MISION</h3>
						<p>Extender el ciclo de vida de las prendas de marca. Las prendas elaboradas por marcas reconocidas están diseñadas para durar toda la vida; pueden cambiar de manos innumerables veces y aún conservan su calidad y valor.<br>
						Aportamos experiencia y entusiasmo a nuestra misión de ampliar la vida de las prendas de marca y permitir que más personas las posean y aprecien mientras dan a sus sueños originales la oportunidad de maximizar el valor de sus inversiones.</p>
					</div>
				</div>
					
				<div class="contenido-nosotros">
					<div class="contenido-nosotros-img">
						<a href="#" class="tt-collection-item ">
							<img src="images/nosotros/Imagen_Agentes de cambio.png" alt="Imagen Somos Agentes de Cambio">
						</a>
					</div>
					<div class="contenido-nosotros-descripcion">
								
					<h3>NUESTRA VISION</h3>
					<p>Mi Roperito está empoderando a nuestras decenas de miles de miembros para constribuir a un futuro más osstenible y el crecimiento de una comundid vital en torno a la economía circular.<br>
					Si bien el lujo es tanto nuestra plataforma como nuestra pasión, creemos que nuestro trabajo tiene el poder de afectar un cambio social más amplio para movernos hacia un mundo donde todo consumo es consumo consciente. El futuro de la moda es circular.</p>
					</div>
				</div>
			</main>
			</div>
		</div><!-- /.container-fluid -->
		<section>
			<div class="col-sm-12 mt-6 p-0 contenedor-locales-nosotros">
				<div>
					<div class="contenedor-locales-titulo">
						<h3>Nuestros Locales</h3>
					</div>
					
					<div class="locales-nosotros">
						<div class="locales">
							<div class="imagen-local-nosotros iln-1"></div>
							<div class="contenido-locales">
								<h4>Villa Ballester:</h4>
								<p>de lunes a sábados de 10 a 20 hs.</p>
								<p>Independencia 4701 (Planta alta)</p>
							</div>
						</div>
						<div class="locales">
							<div class="imagen-local-nosotros iln-2"></div>
							<div class="contenido-locales">
								<h4>San Isidro:</h4>
								<p>de lunes a sábados de 11 a 19 hs.</p>
								<p>Av. Fondo de la Legua 425</p>
								<p>(Boulogne Sur)</p>
							</div>
						</div>
						<div class="locales">
							<div class="imagen-local-nosotros iln-3"></div>
							<div class="contenido-locales">
								<h4>Núñez:</h4>
								<p>de lunes a sábados de 10 a 20 hs.</p>
								<p>Crisólogo Larralde 1970</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section>
			<div class="row">
				<div class="col-sm-1"></div>
				<div class="col-sm-10 mt-6 contenedor-tienda-online">
					<img src="images/nosotros/tenemos_tienda_online.png" alt="" class="centrar">
					<div class="btn-tienda-online">
						<a href="http://miroperito.prestotienda.com/" class="button" >ENTRAR</a>
					</div>
				</div>
				<div class="col-sm-1"></div>
			</div>
		</section>

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