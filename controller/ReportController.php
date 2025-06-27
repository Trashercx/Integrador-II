<?php
require("../fpdf/fpdf.php");
include('../bd/conexion.php');



class PDF extends FPDF {
    function Header() {
        // Ajustar la imagen (icono) y su posición
        $this->Image('../view/img/ZEUS2.png', 10, 3, 40); // Tamaño reducido y posición ajustada
        
        // Título con margen izquierdo
        $this->SetFont('Arial', 'B', 18);
        //$this->SetTextColor(0, 102, 204); // Color azul
     
        $this->Cell(0, 10, 'Reporte de clientes', 0, 1, 'C');
        
        // Fecha de impresión con margen izquierdo
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 102, 204); // Color negro
       
        $this->Cell(0, 35, 'Fecha de impresion: ' . date('d/m/Y'), 0, 1, 'L');
        
        // Línea separadora
     
        $this->Cell(0, 0, '', 'T', 1, 'C');
        $this->Ln(5); // Espacio después de la línea
    }
    
    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, 'Bacilio Vilca Cristian Samuel ' , 0, 0, 'C');
    }
    
    function TablaClientes($conn) {
        // Configurar ancho de columnas para que se ajuste a la página
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(200, 200, 200); // Color de fondo de la cabecera
        $this->Cell(20, 10, 'Id', 1, 0, 'C', true);
        $this->Cell(35, 10, 'Nombre', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Email', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Rol', 1, 0, 'C', true);
        $this->Cell(30, 10, 'DNI', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Telefono', 1, 1, 'C', true);
        
        // Contenido de la tabla
        $this->SetFont('Arial', '', 9);
        
   
        $sql = "
    SELECT u.*, r.permiso
    FROM usuarios u
    INNER JOIN rol r ON u.id_rol = r.id_rol
";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $this->Cell(20, 10, $row['id_usuario'], 1,0,"C");
            $this->Cell(35, 10, $row['nombre'], 1,0,"C");
            $this->Cell(50, 10, $row['email'], 1,0,"C");
            $this->Cell(25, 10, $row['permiso'], 1,0,"C");
            $this->Cell(30, 10, $row['dni'], 1,0,"C");
            $this->Cell(30, 10, $row['telefono'], 1, 1 ,"C");
        }
        
        // Total de clientes
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, 'Total de usuarios registrados: ' . $result->num_rows, 0, 1, 'L');
    }
}

// Crear el PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->TablaClientes($conn);
$pdf->Output();
//$pdf->Output("Lista.pdf","d");
?>