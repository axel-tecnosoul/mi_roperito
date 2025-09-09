<section>	
	<div class="row">
		<div class="col-1"></div>
		<div class="col-sm-10">
			<div class="contenedor-uso">
				<div class="col-sm-12 usar-usado">
					<div class="tt-description">
						<div class="tt-layout-newsletter02">
							<!--<h1 class="tt-title"><b>TESTIMONIOS</b></h1>-->
							<img src="images/usado/usar_usado.png" alt="Titulo Sabes que se usa?">
						</div>
					</div>
				</div>
				
				<div class="col-sm-12 contenedor-carrusel">
					<div id="testimonial-slider" class="owl-carousel carrusel"><?php
						include '../admin/database.php';
						$pdo = Database::connect();
						$sql = "SELECT id, seccion,`url-jpg`, activo FROM banners WHERE activo = 1 AND seccion = 1"; 

						foreach ($pdo->query($sql) as $row) {
							$nombreImagenJPG = $row['url-jpg'];

							echo '<div class="testimonial custom-styles">';	
							echo '<picture>';
							echo '<img loading="lazy" decoding="async" src="images/Banners/Home/' . $nombreImagenJPG . '" alt="imagen" width="400" height="500">';
							echo '</picture>';
							echo '</div>';
						}

						Database::disconnect();?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-1"></div>
	</div>		
</section>