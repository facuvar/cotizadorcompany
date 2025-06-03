<?php
// Script para generar un PDF a partir de HTML
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="presupuesto.pdf"');

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

// Extraer los datos del presupuesto del HTML
preg_match('/<div class="cliente">(.*?)<\/div>/s', $html, $clienteMatch);
preg_match('/<table>(.*?)<\/table>/s', $html, $tablaMatch);

$cliente = isset($clienteMatch[1]) ? $clienteMatch[1] : '';
$tabla = isset($tablaMatch[1]) ? $tablaMatch[1] : '';

// Limpiar etiquetas HTML
$cliente = strip_tags($cliente, '<p><strong>');
$tabla = strip_tags($tabla, '<tr><td><th>');

// Generar un PDF básico
echo "%PDF-1.4\n";
echo "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n";
echo "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n";
echo "3 0 obj\n<</Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R>>\nendobj\n";
echo "4 0 obj\n<</Font <</F1 5 0 R>>>>\nendobj\n";
echo "5 0 obj\n<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>\nendobj\n";

// Contenido del PDF
$content = "BT\n/F1 16 Tf\n50 750 Td\n(PRESUPUESTO) Tj\n/F1 12 Tf\n0 -30 Td\n";

// Agregar información del cliente
$content .= "(Datos del Cliente) Tj\n0 -20 Td\n";
$cliente = str_replace(['<p>', '</p>', '<strong>', '</strong>'], '', $cliente);
$lineasCliente = explode("\n", $cliente);
foreach ($lineasCliente as $linea) {
    $linea = trim($linea);
    if (!empty($linea)) {
        $content .= "(" . $linea . ") Tj\n0 -15 Td\n";
    }
}

// Agregar información de la tabla
$content .= "0 -20 Td\n(Detalle del Presupuesto) Tj\n0 -20 Td\n";
$tabla = str_replace(['<tr>', '</tr>', '<td>', '</td>', '<th>', '</th>'], '', $tabla);
$lineasTabla = explode("\n", $tabla);
foreach ($lineasTabla as $linea) {
    $linea = trim($linea);
    if (!empty($linea)) {
        $content .= "(" . $linea . ") Tj\n0 -15 Td\n";
    }
}

$content .= "0 -30 Td\n(Este presupuesto tiene una validez de 15 días.) Tj\n0 -15 Td\n";
$content .= "(Para cualquier consulta, no dude en contactarnos.) Tj\nET\n";

echo "6 0 obj\n<</Length " . strlen($content) . ">>\nstream\n" . $content . "endstream\nendobj\n";

// Finalizar PDF
echo "xref\n0 7\n0000000000 65535 f\n";
echo "0000000009 00000 n\n";
echo "0000000056 00000 n\n";
echo "0000000111 00000 n\n";
echo "0000000212 00000 n\n";
echo "0000000250 00000 n\n";
echo "0000000317 00000 n\n";
echo "trailer\n<</Size 7 /Root 1 0 R>>\nstartxref\n" . (strlen($content) + 317) . "\n%%EOF\n";
?>
