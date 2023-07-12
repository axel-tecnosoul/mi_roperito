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
			<div class="col-sm-12 contenido-centrado">
				<div class="banner">
					<div class="banner-contenido">
						<div class="banner-img">
							<img src="images/logo/banner-nosotros-btn.png" alt="Imagen Logo vende">
						</div>
						<div class="banner-descripcion">
							<div class="banner-descripcion-titulo">
								<h1>Locales</h1>
							</div>
							<div class="banner-descripcion-parrafo">
								<p>
									Te invitamos a conocer nuestros locales! Te dejamos los días, los 
								</p>
								<p>horarios y la info para que sepas como llegar. ¡Te esperamos!</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid-custom contenedor-locales">
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
							<a href="https://goo.gl/maps/C7RxmcVVe8LGJjKi6" target="_blank" class="btn">Como llegar</a>
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
							<a href="https://goo.gl/maps/o6o3nw287iZWcP74A" target="_blank" class="btn">Como llegar</a>
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
							<a href="https://goo.gl/maps/AirnWpSpJSyQsVUJ6" target="_blank" class="btn">Como llegar</a>
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

	<?php include("scripts.php"); ?>
</body>
</html>