<?php
// Script para generar un PDF básico a partir de HTML
require_once 'sistema/config.php';

// Verificar que se recibió un parámetro de archivo HTML
if (!isset($_GET['html'])) {
    die("Error: No se especificó el archivo HTML");
}

$htmlFile = $_GET['html'];
$htmlPath = __DIR__ . '/' . $htmlFile;

// Verificar que el archivo existe
if (!file_exists($htmlPath)) {
    die("Error: El archivo HTML no existe");
}

// Leer el contenido HTML
$html = file_get_contents($htmlPath);
if (!$html) {
    die("Error: No se pudo leer el archivo HTML");
}

// Crear nombre para el archivo PDF
$pdfFileName = 'presupuesto_' . time() . '.pdf';
$pdfPath = __DIR__ . '/presupuestos/' . $pdfFileName;

// Crear directorio de presupuestos si no existe
if (!file_exists(__DIR__ . '/presupuestos')) {
    mkdir(__DIR__ . '/presupuestos', 0777, true);
}

// Método simple para generar PDF usando PHP
// Configurar cabeceras para PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="presupuesto.pdf"');

// Usar la biblioteca básica de PHP para generar PDF
require_once('fpdf/fpdf.php');

// Si no existe FPDF, crear un PDF muy básico
if (!class_exists('FPDF')) {
    // Crear un PDF simple con cabeceras HTTP
    $pdf = "
%PDF-1.4
1 0 obj
<</Type /Catalog /Pages 2 0 R>>
endobj
2 0 obj
<</Type /Pages /Kids [3 0 R] /Count 1>>
endobj
3 0 obj
<</Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R>>
endobj
4 0 obj
<</Font <</F1 5 0 R>>>>
endobj
5 0 obj
<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>
endobj
6 0 obj
<</Length 90>>
stream
BT
/F1 12 Tf
50 700 Td
(Presupuesto generado) Tj
50 680 Td
(Por favor, vea el archivo HTML para detalles completos.) Tj
ET
endstream
endobj
xref
0 7
0000000000 65535 f
0000000009 00000 n
0000000056 00000 n
0000000111 00000 n
0000000212 00000 n
0000000250 00000 n
0000000317 00000 n
trailer
<</Size 7 /Root 1 0 R>>
startxref
456
%%EOF
";
    echo $pdf;
    exit;
}

// Si existe FPDF, crear un PDF mejor formateado
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Presupuesto');
$pdf->Ln(15);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, 'Por favor, vea el archivo HTML para detalles completos.');
$pdf->Output('D', 'presupuesto.pdf');
?>
