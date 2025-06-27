<?php
require("../fpdf/fpdf.php");
include('../bd/conexion.php');

class PDF extends FPDF
{
    // Encabezado de la página
    function Header()
    {
        // Logo
        $this->Image('../view/img/ZEUS2.png', 10, 6, 30);
        
        // Fuente para el título
        $this->SetFont('Arial', 'B', 20);
        //$this->SetTextColor(6,48,249);
        // Movernos a la derecha
        $this->Cell(80);
        
        // Título
    
        $this->Cell(100, 10, ('Lista de Productos'), 0, 1, 'C');
        

        
        // Fecha de impresión
        $this->SetTextColor(0,0,0);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Fecha de impresion: ' . date('d-m-Y'), 0, 1, 'R');
        
        // Salto de línea
        $this->Ln(10);
        
        // Encabezado de la tabla
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 10, 'ID', 1, 0, 'C');
        $this->Cell(30, 10, 'Nombre', 1, 0, 'C');
        $this->Cell(75, 10, 'Descripcion', 1, 0, 'C');
        $this->Cell(30, 10, 'Categoria', 1, 0, 'C');
        $this->Cell(30, 10, 'Foto1', 1, 0, 'C');
        $this->Cell(30, 10, 'Foto2', 1, 0, 'C');
        $this->Cell(30, 10, 'Foto3', 1, 0, 'C');
        $this->Cell(30, 10, 'Foto4', 1, 1, 'C');
     
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1.5 cm del final
        $this->SetY(-15);
        // Fuente para el pie de página
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Crear instancia del PDF
$pdf = new PDF('L', 'mm', 'A4'); // 'L' para orientación horizontal (landscape)
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta a la base de datos para obtener los registros de clientes
 $sql = "SELECT p.*, c.nombre AS nombre_categoria 
                FROM productos p 
                INNER JOIN categoria c ON p.id_categoria = c.id_categoria";
$result = $conn->query($sql);

// Contador de registros
$totalRegistros = 0;

// Agregar registros al PDF
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 25, $row["id_producto"], 1, 0, 'C');
    $pdf->Cell(30, 25, ($row["nombre"]), 1, 0, 'C');
    $pdf->Cell(75, 25, ($row["descripcion"]), 1, 0, 'L');
    $pdf->Cell(30, 25, ($row["nombre_categoria"]), 1, 0, 'C');
    //$pdf->Cell(40, 25, utf8_decode($row["direccion_cli"]), 1, 0, 'C');
    //$pdf->Cell(20, 25, $row["telefono_cli"], 1, 0, 'C');
    //$pdf->Cell(30, 25, utf8_decode($row["nombre_distrito"]), 1, 0, 'C');
    //$pdf->Cell(50, 25, utf8_decode($row["correo_cli"]), 1, 0, 'C');
    
    // Verificar si hay foto y agregarla al PDF
    if (!empty($row["imagen"]) && file_exists('../view/images/' . $row["imagen"])) {
        // Tamaño de la imagen
        $imgWidth = 20;
        $imgHeight = 20;
        
        // Definir una celda para la imagen
        $pdf->Cell(30, 25, '', 1, 0, 'C');
        
        // Centrar la imagen dentro de la celda
        $xPos = $pdf->GetX() - 30 + (30 - $imgWidth) / 2; // 30 es el ancho de la celda
        $yPos = $pdf->GetY() + (25 - $imgHeight) / 2;     // 10 es la altura de la celda
        $pdf->Image('../view/images/' . $row["imagen"] ?? '', $xPos, $yPos, $imgWidth, $imgHeight);
        
        //$pdf->Ln(); // Salto de línea para pasar a la siguiente fila
    } else {
        $pdf->Cell(30, 25, 'Sin foto', 1, 0, 'C');
    }
    if (!empty($row["imagen2"]) && file_exists('../view/images/' . $row["imagen2"])) {
        // Tamaño de la imagen
        $imgWidth = 20;
        $imgHeight = 20;
        
        // Definir una celda para la imagen
        $pdf->Cell(30, 25, '', 1, 0, 'C');
        
        // Centrar la imagen dentro de la celda
        $xPos = $pdf->GetX() - 30 + (30 - $imgWidth) / 2; // 30 es el ancho de la celda
        $yPos = $pdf->GetY() + (25 - $imgHeight) / 2;     // 10 es la altura de la celda
        $pdf->Image('../view/images/' . $row["imagen2"] ?? '', $xPos, $yPos, $imgWidth, $imgHeight);
        
        //$pdf->Ln(); // Salto de línea para pasar a la siguiente fila
    } else {
        $pdf->Cell(30, 25, 'Sin foto', 1, 0, 'C');
        
    }
    if (!empty($row["imagen3"]) && file_exists('../view/images/' . $row["imagen3"])) {
        // Tamaño de la imagen
        $imgWidth = 20;
        $imgHeight = 20;
        
        // Definir una celda para la imagen
        $pdf->Cell(30, 25, '', 1, 0, 'C');
        
        // Centrar la imagen dentro de la celda
        $xPos = $pdf->GetX() - 30 + (30 - $imgWidth) / 2; // 30 es el ancho de la celda
        $yPos = $pdf->GetY() + (25 - $imgHeight) / 2;     // 10 es la altura de la celda
        $pdf->Image('../view/images/' . $row["imagen3"] ?? '', $xPos, $yPos, $imgWidth, $imgHeight);
        
        //$pdf->Ln(); // Salto de línea para pasar a la siguiente fila
    } else {
        $pdf->Cell(30, 25, 'Sin foto', 1, 0, 'C');
        
    }
    if (!empty($row["imagen4"]) && file_exists('../view/images/' . $row["imagen4"])) {
        // Tamaño de la imagen
        $imgWidth = 20;
        $imgHeight = 20;
        
        // Definir una celda para la imagen
        $pdf->Cell(30, 25, '', 1, 0, 'C');
        
        // Centrar la imagen dentro de la celda
        $xPos = $pdf->GetX() - 30 + (30 - $imgWidth) / 2; // 30 es el ancho de la celda
        $yPos = $pdf->GetY() + (25 - $imgHeight) / 2;     // 10 es la altura de la celda
        $pdf->Image('../view/images/' . $row["imagen4"] ?? '', $xPos, $yPos, $imgWidth, $imgHeight);
        
        //$pdf->Ln(); // Salto de línea para pasar a la siguiente fila
    } else {
        $pdf->Cell(30, 25, 'Sin foto', 1, 0, 'C');
        $pdf->Ln();
        
    }
    
    
    
    
    

    // Incrementar contador de registros
    $totalRegistros++;
}

// Agregar total de registros al final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Total de registros: ' . $totalRegistros, 0, 1, 'C');

// Salida del archivo
$pdf->Output();
//$pdf->Output("Lista.pdf","d");
?>