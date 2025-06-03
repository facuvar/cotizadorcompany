<?php
// Script para generar PDF del presupuesto
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Configurar cabecera para respuesta JSON
header('Content-Type: application/json');

// Función para registrar información de depuración
function logDebug($message) {
    $logFile = __DIR__ . '/pdf_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }
    
    // Obtener datos del formulario
    $jsonData = file_get_contents('php://input');
    logDebug("Datos JSON recibidos: " . substr($jsonData, 0, 500) . "...");
    
    $data = json_decode($jsonData, true);
    
    if (!$data) {
        throw new Exception("Datos no válidos: " . json_last_error_msg());
    }
    
    logDebug("Datos decodificados correctamente");
    
    // Crear directorio de presupuestos si no existe
    if (!file_exists(__DIR__ . '/presupuestos')) {
        if (!mkdir(__DIR__ . '/presupuestos', 0777, true)) {
            throw new Exception("No se pudo crear el directorio de presupuestos");
        }
    }
    
    // Generar PDF simple (sin TCPDF)
    $htmlFileName = 'presupuesto_' . time() . '.html';
    $htmlFilePath = __DIR__ . '/presupuestos/' . $htmlFileName;
    
    // Generar contenido HTML
    $html = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Presupuesto - ' . htmlspecialchars($data['producto']['nombre']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { max-width: 200px; }
            h1 { color: #333; }
            .cliente { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; text-align: right; }
            .footer { margin-top: 50px; font-size: 12px; color: #666; text-align: center; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Presupuesto</h1>
            <p>' . date('d/m/Y') . '</p>
            <div style="text-align:center; margin-bottom: 20px;">
                <a href="javascript:window.print()" style="display:inline-block; background-color:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; margin-right:10px;">Imprimir</a>
                <a href="pdf_basico.php" style="display:inline-block; background-color:#2196F3; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; margin-right:10px;">PDF Simple</a>
                <a href="pdf_detallado.php?id=' . urlencode('ID_PRESUPUESTO_PLACEHOLDER') . '" id="pdf-detallado-link" style="display:inline-block; background-color:#FF5722; color:white; padding:10px 20px; text-decoration:none; border-radius:4px;">PDF Detallado</a>
                <script>
                    // Este script será reemplazado con el ID real cuando se cargue la página
                    window.addEventListener("message", function(event) {
                        if (event.data && event.data.presupuestoId) {
                            document.getElementById("pdf-detallado-link").href = 
                                document.getElementById("pdf-detallado-link").href.replace("ID_PRESUPUESTO_PLACEHOLDER", event.data.presupuestoId);
                        }
                    }, false);
                </script>
            </div>
        </div>
        
        <div class="cliente">
            <h2>Datos del Cliente</h2>
            <p><strong>Nombre:</strong> ' . htmlspecialchars($data['nombre']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
            <p><strong>Teléfono:</strong> ' . htmlspecialchars($data['telefono']) . '</p>
        </div>
        
        <h2>Detalle del Presupuesto</h2>
        <table>
            <tr>
                <th>Descripción</th>
                <th>Precio</th>
            </tr>
            <tr>
                <td>Producto: ' . htmlspecialchars($data['producto']['nombre']) . '</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Opción: ' . htmlspecialchars($data['opcion']['nombre']) . '</td>
                <td>$' . number_format($data['opcion']['precio'], 2, ',', '.') . '</td>
            </tr>';
    
    // Agregar adicionales si existen
    if (!empty($data['adicionales'])) {
        foreach ($data['adicionales'] as $adicional) {
            $html .= '<tr>
                <td>Adicional: ' . htmlspecialchars($adicional['nombre']) . '</td>
                <td>$' . number_format($adicional['precio'], 2, ',', '.') . '</td>
            </tr>';
        }
    }
    
    $html .= '<tr>
                <td>Subtotal</td>
                <td>$' . number_format($data['subtotal'], 2, ',', '.') . '</td>
            </tr>
            <tr>
                <td>Forma de pago: ' . htmlspecialchars($data['formaPago']['nombre']) . ' (' . $data['formaPago']['descuento'] . '% descuento)</td>
                <td>-$' . number_format($data['formaPago']['descuentoMonto'], 2, ',', '.') . '</td>
            </tr>
            <tr class="total">
                <td>TOTAL</td>
                <td>$' . number_format($data['total'], 2, ',', '.') . '</td>
            </tr>
        </table>
        
        <div>
            <h3>Plazo de entrega</h3>
            <p>' . htmlspecialchars($data['plazo']['nombre']) . '</p>
        </div>
        
        <div class="footer">
            <p>Este presupuesto tiene una validez de 15 días.</p>
            <p>Para cualquier consulta, no dude en contactarnos.</p>
        </div>
    </body>
    </html>';
    
    // Guardar el archivo HTML
    if (!file_put_contents($htmlFilePath, $html)) {
        throw new Exception("No se pudo guardar el archivo HTML");
    }
    
    logDebug("Archivo HTML generado en: $htmlFilePath");
    
    // Obtener el ID del presupuesto recién guardado
    $presupuestoId = 0;
    try {
        // Conectar a la base de datos
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Buscar el presupuesto por los datos del cliente
        $sql = "SELECT id FROM presupuestos WHERE nombre_cliente = ? AND email_cliente = ? ORDER BY fecha_creacion DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $data['nombre'], $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $presupuestoId = $row['id'];
            logDebug("ID del presupuesto encontrado: " . $presupuestoId);
        }
    } catch (Exception $e) {
        logDebug("Error al buscar ID del presupuesto: " . $e->getMessage());
    }
    
    // Devolver respuesta exitosa con enlace para convertir a PDF
    echo json_encode([
        'success' => true,
        'file' => 'presupuestos/' . $htmlFileName,
        'type' => 'html',
        'presupuesto_id' => $presupuestoId
    ]);
    
} catch (Exception $e) {
    logDebug("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
