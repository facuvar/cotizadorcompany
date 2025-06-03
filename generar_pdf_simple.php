<?php
// Script simple para generar un PDF a partir de HTML
// Configurar cabeceras para forzar la descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="presupuesto.pdf"');

// Verificar que se recibió un parámetro de archivo HTML
if (!isset($_GET['html'])) {
    echo "Error: No se especificó el archivo HTML";
    exit;
}

$htmlFile = $_GET['html'];
$htmlPath = __DIR__ . '/' . $htmlFile;

// Verificar que el archivo existe
if (!file_exists($htmlPath)) {
    echo "Error: El archivo HTML no existe: $htmlPath";
    exit;
}

// Leer el contenido HTML
$html = file_get_contents($htmlPath);
if (!$html) {
    echo "Error: No se pudo leer el archivo HTML";
    exit;
}

// Crear un PDF muy básico
$pdf = '%PDF-1.4
1 0 obj
<</Type/Catalog/Pages 2 0 R>>
endobj
2 0 obj
<</Type/Pages/Kids[3 0 R]/Count 1>>
endobj
3 0 obj
<</Type/Page/MediaBox[0 0 612 792]/Resources<<>>/Contents 4 0 R/Parent 2 0 R>>
endobj
4 0 obj
<</Length 150>>
stream
BT
/F1 24 Tf
100 700 Td
(Presupuesto) Tj
/F1 12 Tf
0 -50 Td
(Este documento PDF es una versión simplificada del presupuesto.) Tj
0 -20 Td
(Por favor, imprima la versión HTML para obtener todos los detalles.) Tj
ET
endstream
endobj
5 0 obj
<</Type/Font/Subtype/Type1/BaseFont/Helvetica/Encoding/WinAnsiEncoding>>
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000056 00000 n
0000000111 00000 n
0000000212 00000 n
0000000413 00000 n
trailer
<</Size 6/Root 1 0 R>>
startxref
498
%%EOF';

// Enviar el PDF al navegador
echo $pdf;
?>
