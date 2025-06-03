<?php
// Script para generar PDF directamente sin dependencias externas
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="presupuesto.pdf"');

// Obtener datos del presupuesto
$presupuestoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($presupuestoId <= 0) {
    die("ID de presupuesto no válido");
}

// Conectar a la base de datos
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Obtener datos del presupuesto
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT * FROM presupuestos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $presupuestoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Presupuesto no encontrado");
    }
    
    $presupuesto = $result->fetch_assoc();
    
    // Generar un PDF básico
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n";
    $pdf .= "2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n";
    $pdf .= "3 0 obj\n<</Type /Page /Parent 2 0 R /Resources 4 0 R /MediaBox [0 0 612 792] /Contents 6 0 R>>\nendobj\n";
    $pdf .= "4 0 obj\n<</Font <</F1 5 0 R>>>>\nendobj\n";
    $pdf .= "5 0 obj\n<</Type /Font /Subtype /Type1 /BaseFont /Helvetica>>\nendobj\n";
    
    // Contenido del PDF
    $content = "BT\n/F1 16 Tf\n50 750 Td\n(PRESUPUESTO) Tj\n/F1 12 Tf\n0 -30 Td\n";
    $content .= "(Cliente: " . $presupuesto['nombre_cliente'] . ") Tj\n0 -20 Td\n";
    $content .= "(Email: " . $presupuesto['email_cliente'] . ") Tj\n0 -20 Td\n";
    $content .= "(Teléfono: " . $presupuesto['telefono_cliente'] . ") Tj\n0 -30 Td\n";
    $content .= "(Producto: " . $presupuesto['producto_nombre'] . ") Tj\n0 -20 Td\n";
    $content .= "(Opción: " . $presupuesto['opcion_nombre'] . ") Tj\n0 -20 Td\n";
    $content .= "(Plazo: " . $presupuesto['plazo_nombre'] . ") Tj\n0 -20 Td\n";
    $content .= "(Forma de pago: " . $presupuesto['forma_pago'] . ") Tj\n0 -20 Td\n";
    $content .= "(Subtotal: $" . number_format($presupuesto['subtotal'], 2, ',', '.') . ") Tj\n0 -20 Td\n";
    $content .= "(Descuento: $" . number_format($presupuesto['descuento'], 2, ',', '.') . ") Tj\n0 -20 Td\n";
    $content .= "(TOTAL: $" . number_format($presupuesto['total'], 2, ',', '.') . ") Tj\n0 -40 Td\n";
    $content .= "(Fecha: " . date('d/m/Y', strtotime($presupuesto['fecha_creacion'])) . ") Tj\n0 -20 Td\n";
    $content .= "(Código: " . $presupuesto['codigo'] . ") Tj\nET\n";
    
    $pdf .= "6 0 obj\n<</Length " . strlen($content) . ">>\nstream\n" . $content . "endstream\nendobj\n";
    
    // Finalizar PDF
    $pdf .= "xref\n0 7\n0000000000 65535 f\n";
    $pdf .= "0000000009 00000 n\n";
    $pdf .= "0000000056 00000 n\n";
    $pdf .= "0000000111 00000 n\n";
    $pdf .= "0000000212 00000 n\n";
    $pdf .= "0000000250 00000 n\n";
    $pdf .= "0000000317 00000 n\n";
    $pdf .= "trailer\n<</Size 7 /Root 1 0 R>>\nstartxref\n" . (strlen($content) + 317) . "\n%%EOF\n";
    
    echo $pdf;
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
