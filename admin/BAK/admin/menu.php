<div class="page-sidebar">
  <div class="main-header-left d-none d-lg-block">
	<div class="logo-wrapper"><a href="dashboard.php"><img src="assets/images/logoBackend.png" width="200px" alt=""></a></div>
  </div>
  <div class="sidebar custom-scrollbar">
	<ul class="sidebar-menu">
		
		<?php 
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarCategorias.php"><i data-feather="shopping-bag"></i><span>Categorías</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarProductos.php"><i data-feather="clipboard"></i><span>Productos</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] == 1) {?>
		  <li><a class="sidebar-header" href="listarAlmacenes.php"><i data-feather="file-text"></i><span>Almacenes</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarStock.php"><i data-feather="layout"></i><span>Stock</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarMovimientoStock.php"><i data-feather="repeat"></i><span>Movimientos de Stock</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}?>
		<li><a class="sidebar-header" href="listarVentas.php"><i data-feather="settings"></i><span>Ventas</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 

		if ($_SESSION['user']['id_perfil'] == 1) {?>
		  <li><a class="sidebar-header" href="listarDescuentos.php"><i data-feather="flag"></i><span>Descuentos</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarPagosPendientes.php"><i data-feather="maximize"></i><span>Pagos Pendientes</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarCanjes.php"><i data-feather="alert-circle"></i><span>Canjes Crédito</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarProveedores.php"><i data-feather="file"></i><span>Proveedores</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] == 1) {?>
		  <li><a class="sidebar-header" href="listarSuscripciones.php"><i data-feather="tag"></i><span>Suscripciones</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] == 1) {?>
		  <li><a class="sidebar-header" href="listarContactos.php"><i data-feather="home"></i><span>Contactos</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] != 3) {?>
		  <li><a class="sidebar-header" href="listarTurnos.php"><i data-feather="calendar"></i><span>Turnos</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] == 1) {?>
		  <li><a class="sidebar-header" href="listarUsuarios.php"><i data-feather="user"></i><span>Usuarios</span><i class="fa fa-angle-right pull-right"></i></a></li><?php 
		}
		if ($_SESSION['user']['id_perfil'] == 1) {?>
      <li><a class="sidebar-header" href="#"><i data-feather="bar-chart"></i><span>Reportes</span><i class="fa fa-angle-right pull-right"></i></a>
        <ul class="sidebar-submenu">
          <li><a href="rankingProductos.php"><i class="fa fa-circle"></i>Ranking Productos</a></li>
          <li><a href="rankingCategorias.php"><i class="fa fa-circle"></i>Ranking Categorías</a></li>
          <li><a href="evolutivoVentas.php"><i class="fa fa-circle"></i>Evolutivo Ventas</a></li>
          <li><a href="reporteAsistencias.php"><i class="fa fa-circle"></i>Asistencia Empleados</a></li>
        </ul>
      </li><?php 
		}?>

	</ul>
  </div>
</div>