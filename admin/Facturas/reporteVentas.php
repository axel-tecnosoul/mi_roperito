<?php


require('../vendor/fpdf/fpdf.php');

class PDF extends FPDF
{

   // Cabecera de página
   function Header()
   {
      $id = 5;
      include '../database.php';
      $pdo = Database::connect();
	   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $sql = "SELECT v.id, v.nombre_cliente,v.direccion, v.cae, v.fecha_vencimiento_cae, v.numero_comprobante, v.tipo_comprobante, vd.id_producto, p.codigo, p.descripcion, p.precio, vd.cantidad,vd.precio as precio_vd, vd.subtotal FROM ventas_detalle vd LEFT JOIN ventas v ON v.id = vd.id_venta INNER JOIN productos p ON p.id = vd.id_producto WHERE vd.id_venta = ?";
      $q = $pdo->prepare($sql);
      $q->execute(array($id));
		$data = $q->fetch(PDO::FETCH_ASSOC);

      
      /* LINEAS HORIZONTALES*/
      $this->Line(10, 2,201,2);
      $this->Line(10, 14,201,14);
      $this->Line(98, 30,113,30);
      $this->Line(10, 67,201,67);
      $this->Line(10, 76,201,76);
      $this->Line(10, 77,201,77);
      $this->Line(10, 95,201,95);
      $this->Line(10, 254,201,254);
      $this->Line(10, 283,201,283);

      /* LINEAS VERTICALES*/
      $this->Line(10, 2,10,76);
      $this->Line(98, 14,98,30);
      $this->Line(113, 14,113,30);
      //$this->SetDrawColor(0, 0, 255, 0);
      $this->Line(106, 30,106,67);
      $this->Line(201, 2,201,76);
      $this->Line(201, 77,201,95);
      $this->Line(10, 77,10,95);

      /* ORIGINAL */
      $this->Cell(80);  // mover a la derecha
      $this->SetFont('Arial', 'B', 20);
      $this->Cell(0, -2, utf8_decode("ORIGINAL"));
      $this->Ln(5);

      /* Filas */
      
      /* TIPO COMPROBANTE*/
      $this->Cell(91);  // mover a la derecha
      $this->SetFont('Arial', 'B', 25);
      $this->Cell(0, 15, utf8_decode($data['tipo_comprobante']));
      $this->Ln(5);
      /* Tipo de Factura */
      $this->SetFont('Arial', 'B', 19); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(110); // Movernos a la derecha
      //creamos una celda o fila
      $this->Cell(110, 5, utf8_decode('FACTURA')); // AnchoCelda,AltoCelda,titulo,borde(1-0),saltoLinea(1-0),posicion(L-C-R),ColorFondo(1-0)
      $this->Ln(8); // Salto de línea
      /* NOMBRE */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(96, 20, utf8_decode("CINTIA ROMINA GIRBINO"), 0, 0, '', 0);
      $this->Ln(5);

      /* Direccion */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', '',  8);
      $this->Cell(96, 24, utf8_decode("RioBamba 2751 - 1653 - Villa Ballester- BSAS"), 0, 0, '', 0);
      $this->Ln(5);

      /* Tel */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', '', 8);
      $this->Cell(96, 26, utf8_decode("Tel: 1140467012"), 0, 0, '', 0);
      $this->Ln(5);

      /* Email */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', '', 8);
      $this->Cell(96, 28, utf8_decode("miroperitooficial@gmail.com"), 0, 0, '', 0);
      $this->Ln(5);

      /* Comp Nº */
      $this->Cell(160);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(59, -26, utf8_decode("Comp Nº: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(174);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -26, utf8_decode($data['numero_comprobante']), 0, 0, '', 0);
      $this->Ln(2);
      

      /* Punto de Venta */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(59, -28, utf8_decode("Punto de Venta:"), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(132);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -28, utf8_decode("124"), 0, 0, '', 0);
      $this->Ln(0);

      /* Fecha de emisión */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("Fecha de Emisión: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(136);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -20, utf8_decode(date('d/m/Y')), 0, 0, '', 0);
      $this->Ln(5);

      /* CUIT */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("CUIT: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(119);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -20, utf8_decode("27-27032771-6"), 0, 0, '', 0);
      $this->Ln(5);


      /* Ingresos Brutos */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("Ingresos Brutos: 27-27032771-6"), 0, 0, '', 0);
      $this->Ln(5);

      /* Fecha de Inicio de Actividades */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("Fecha de Inicio de Actividades: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(153);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -20, utf8_decode("01/09/2017"), 0, 0, '', 0);
      $this->Ln(5);

      /* Condición IVA */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("Condición IVA: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(131);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -20, utf8_decode("Monotributista"), 0, 0, '', 0);
      $this->Ln(6);

      /* Fecha de Vencimiento */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -10, utf8_decode("Fecha de Vto. para el pago: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(148);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -10, utf8_decode("05/04/2023"), 0, 0, '', 0);
      $this->Ln(10);

      /* Apellido y Nombre / Razon Social */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -5, utf8_decode("Apellido y Nombre/Razon Social: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(47);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -5, utf8_decode($data['nombre_cliente']), 0, 0, '', 0);
      $this->Ln(5);

      /* Condición IVA */
      $this->Cell(1);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, 0, utf8_decode("Condición IVA: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(22);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, 0, utf8_decode("Consumidor Final"), 0, 0, '', 0);
      $this->Ln(5);

      /* CUIT */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -25, utf8_decode("CUIT: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(118);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -25, utf8_decode("-"), 0, 0, '', 0);
      $this->Ln(5);

      /* Domicilio Comercial */
      $this->Cell(110);  // mover a la derecha
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(85, -20, utf8_decode("Domicilio Comercial: "), 0, 0, '', 0);
      $this->Ln(0);
      $this->Cell(139);
      $this->SetFont('Arial', '', 8);
      $this->Cell(0, -20, utf8_decode($data['direccion']), 0, 0, '', 0);
      $this->Ln(-5);

      /* CAMPOS DE LA TABLA */
      $this->SetFillColor(160, 160, 160); //colorFondo
      $this->SetTextColor(0, 0, 0); //colorTexto
      $this->SetDrawColor(0, 0, 0); //colorBorde
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(13, 7, utf8_decode('Código'), 1, 0, 'C', 1);
      $this->Cell(103, 7, utf8_decode('Descripción'), 1, 0, 'C', 1);
      $this->Cell(15, 7, utf8_decode('Cant.'), 1, 0, 'C', 1);
      $this->Cell(24, 7, utf8_decode('Precio Unit.'), 1, 0, 'C', 1);
      $this->Cell(18, 7, utf8_decode('Bonif'), 1, 0, 'C', 1);
      $this->Cell(18, 7, utf8_decode('Subtotal'), 1, 1, 'C', 1);

      $this->SetFillColor(255, 255, 255); //colorFondo
      $this->SetTextColor(0, 0, 0); //colorTexto
      $this->SetDrawColor(0, 0, 0); //colorBorde
      $this->SetFont('Arial', '', 8);
      $sql2 = " SELECT p.codigo, p.descripcion, p.precio, vd.cantidad,vd.precio as precio_vd, vd.subtotal FROM ventas_detalle vd LEFT JOIN ventas v ON v.id = vd.id_venta INNER JOIN productos p ON p.id = vd.id_producto WHERE vd.id_venta = $id ";
      $subtotal = 0;
      foreach ($pdo->query($sql2) as $row){
         $this->Cell(13, 7, utf8_decode($row[0]), 1, 0, 'C', 0);
         $this->Cell(103, 7, utf8_decode($row[1]), 1, 0, 'C', 0);
         $this->Cell(15, 7, utf8_decode($row[3]), 1, 0, 'C', 0);
         $this->Cell(24, 7, utf8_decode("$".$row[2]), 1, 0, 'C', 0);
         $this->Cell(18, 7, utf8_decode("$450,00"), 1, 0, 'C', 0);
         $this->Cell(18, 7, utf8_decode("$".$row[5]), 1, 1, 'C', 0);
         $subtotal= $row[5] + $subtotal;
        
      }

      $this->Ln(90);
      $this->Cell(110);
      $this->SetFillColor(160, 160, 160); //colorFondo
      $this->SetTextColor(0, 0, 0); //colorTexto
      $this->SetDrawColor(0, 0, 0); //colorBorde
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(80, 8, utf8_decode("Subtotal: ". "$". "                                                  ".$subtotal), 0, 5, '', 1);
      $this->Cell(80, 8, utf8_decode("Total Venta: ". "$". "                                             ".$subtotal), 0, 0, '', 1);
      $this->Ln(10);
      $this->Cell(1);
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(190, 12, utf8_decode("Observaciones: "), 1, 0, '', 0);
      $this->Ln(20);
      $this->Cell(1);
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(190, 12, utf8_decode("CAE Nº: ".$data['cae']), 0, 0, '', 0);
      $this->Ln(5);
      $this->Cell(1);
      $this->SetFont('Arial', 'B', 10);
      $this->Cell(190, 12, utf8_decode("Fecha de Vto CAE: ".$data['fecha_vencimiento_cae']), 0, 0, '', 0);
   }

   // Pie de página
   function Footer()
   {
      $this->SetY(-15); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, negrita(B-I-U-BIU), tamañoTexto
      $this->Cell(355, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C'); //pie de pagina(numero de pagina)

      $this->SetY(-15); // Posición: a 1,5 cm del final
      $this->SetFont('Arial', 'I', 8); //tipo fuente, cursiva, tamañoTexto
      $this->Cell(0, 10, utf8_decode('2023 @ Desarrollado por MISS'), 0, 0, 'C'); // pie de pagina(fecha de pagina)
   }
}


$pdf = new PDF();
$pdf->AddPage(); /* aqui entran dos para parametros (horientazion,tamaño)V->portrait H->landscape tamaño (A3.A4.A5.letter.legal) */
$pdf->AliasNbPages(); //muestra la pagina / y total de paginas

$i = 0;
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetDrawColor(0, 0, 0); //colorBorde


$i = $i + 1;
/* TABLA */



$pdf->Output('reporteVentasCliente.pdf', 'I');//nombreDescarga, Visor(I->visualizar - D->descargar)
