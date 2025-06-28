<?php
// Agregar estas líneas al inicio para capturar cualquier salida previa
ob_start();
error_reporting(0); // Opcional: deshabilitar reportes de error en producción

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
        // Movernos a la derecha
        $this->Cell(80);
        
        // Título
        $this->Cell(100, 10, 'Lista de Productos', 0, 1, 'C');
        
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

// Limpiar cualquier salida previa
ob_clean();

// Crear instancia del PDF
$pdf = new PDF('L', 'mm', 'A4'); // 'L' para orientación horizontal (landscape)
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta a la base de datos para obtener los registros de productos
$sql = "SELECT p.*, c.nombre AS nombre_categoria 
        FROM productos p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria";
$result = $conn->query($sql);

// Contador de registros
$totalRegistros = 0;

// Agregar registros al PDF
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 25, $row["id_producto"], 1, 0, 'C');
        $pdf->Cell(30, 25, utf8_decode($row["nombre"]), 1, 0, 'C');
        $pdf->Cell(75, 25, utf8_decode($row["descripcion"]), 1, 0, 'L');
        $pdf->Cell(30, 25, utf8_decode($row["nombre_categoria"]), 1, 0, 'C');
        
        // Función para procesar imágenes
        $imagenes = ['imagen', 'imagen2', 'imagen3', 'imagen4'];
        foreach ($imagenes as $campo_imagen) {
            if (!empty($row[$campo_imagen]) && file_exists('../view/images/' . $row[$campo_imagen])) {
                // Tamaño de la imagen
                $imgWidth = 20;
                $imgHeight = 20;
                
                // Definir una celda para la imagen
                $pdf->Cell(30, 25, '', 1, 0, 'C');
                
                // Centrar la imagen dentro de la celda
                $xPos = $pdf->GetX() - 30 + (30 - $imgWidth) / 2;
                $yPos = $pdf->GetY() + (25 - $imgHeight) / 2;
                $pdf->Image('../view/images/' . $row[$campo_imagen], $xPos, $yPos, $imgWidth, $imgHeight);
            } else {
                $pdf->Cell(30, 25, 'Sin foto', 1, 0, 'C');
            }
        }
        
        $pdf->Ln(); // Salto de línea para pasar a la siguiente fila
        
        // Incrementar contador de registros
        $totalRegistros++;
    }
}

// Agregar total de registros al final
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Total de registros: ' . $totalRegistros, 0, 1, 'C');

// Limpiar el buffer de salida antes de enviar el PDF
ob_end_clean();

// Enviar headers para el PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="lista_productos.pdf"');

// Salida del archivo
$pdf->Output('I'); // 'I' para mostrar en el navegador, 'D' para descargar