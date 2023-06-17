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
						<img src="images/Banner.png" alt="" style="max-width: 100%; display: block; max-height:100%;">
					</div>
					<div class="col-1"></div>
					<div class="col-1"></div>
					<div class="col-sm-5">
						<a href="http://miroperito.prestotienda.com/" class="tt-promo-box tt-one-child">
							<img src="images/loader-08.svg" data-src="images/Imagen_boton_Compra.png" alt="">
							<div class="tt-description">
								<div class="tt-description-wrapper">
									<div class="tt-background"></div>
									<div class="tt-title-small"><b>COMPRÁ</b></div>
								</div>
							</div>
						</a>
					</div>
					<div class="col-sm-5">
						<a href="vender.php" class="tt-promo-box tt-one-child">
							<img src="images/loader-08.svg" data-src="images/Imagen_boton_Vende.png" alt="">
							<div class="tt-description">
								<div class="tt-description-wrapper">
									<div class="tt-background"></div>
									<div class="tt-title-small"><b>VENDÉ</b></div>
								</div>
							</div>
						</a>
					</div>
				</div>
			</main>
			<?php include("testimonios.php"); ?>

			<?php include("suscribite.php"); ?>

			<footer id="">
				<?php include("footer.php"); ?>
			</footer>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="../external/jquery/jquery.min.js"><\/script>')</script>
	<script defer src="js/bundle.js"></script>

	<script defer src="../separate-include/single-product/single-product.js"></script>
	<script src="../separate-include/portfolio/portfolio.js"></script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
	
	<a href="#" class="tt-back-to-top">Volver al inicio</a>
</body>
</html>