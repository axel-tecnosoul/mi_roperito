<?php require('admin/config.php'); ?>
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
		<div class="container-fluid-custom contenido-principal">
			<main>
				<div class="row">
					<div class="col-1"></div>
					<div class="col-sm-10">
						<div class="imagen-banner-descuento">
							<picture>
								<source
									sizes="1200w" 
									srcset="images/Home/WEBP/banner-descuento1200px.webp 1200w" 
									type="image/webp">
								<img loading="lazy" decoding="async" src="images/Home/JPG/banner-descuento1200px.jpg" alt="Banner descuento 1200px" width="1200" height="300">
							</picture>
						</div>
					</div>
					<div class="col-1"></div>
					<div class="col-1"></div>
					<div class="col-sm-5 main-index imagen-1">
						<a href="http://miroperito.prestotienda.com/" class="tt-promo-box tt-one-child">
							
							<div class="tt-description">
								<div class="tt-description-wrapper">
									<div class="tt-background"></div>
									<div class="tt-title-small"><b>COMPRÁ</b></div>
								</div>
							</div>
						</a>
					</div>
					<div class="col-sm-5 main-index imagen-2">
						<a href="vender.php" class="tt-promo-box tt-one-child">
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
	<?php include("scripts.php"); ?>
</body>
</html>