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
    // Crear directorio de presupuestos si no existe
    if (!file_exists(__DIR__ . '/presupuestos')) {
        if (!mkdir(__DIR__ . '/presupuestos', 0777, true)) {
            throw new Exception("No se pudo crear el directorio de presupuestos");
        }
    }
    
    // Verificar si existe la carpeta vendor, si no, usar método simple
    if (!file_exists('vendor/autoload.php')) {
    // Función para generar un PDF simple sin dependencias externas
    function generarPDFSimple($data) {
        // Crear un archivo HTML temporal
        $tempFile = 'temp_presupuesto_' . time() . '.html';
        
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
        
        // Crear un nombre de archivo único para el HTML
        $htmlFileName = 'presupuesto_' . time() . '.html';
        $htmlFilePath = __DIR__ . '/presupuestos/' . $htmlFileName;
        
        // Guardar el archivo HTML
        file_put_contents($htmlFilePath, $html);
        
        return [
            'success' => true,
            'file' => 'presupuestos/' . $htmlFileName,
            'type' => 'html'
        ];
    }
    
    // Procesar la solicitud
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener los datos JSON y decodificarlos
        $jsonData = file_get_contents('php://input');
        logDebug("Datos JSON recibidos: " . substr($jsonData, 0, 500) . "...");
        
        $data = json_decode($jsonData, true);
        
        if ($data) {
            logDebug("Datos decodificados correctamente");
            $result = generarPDFSimple($data);
            
            logDebug("PDF generado: " . json_encode($result));
            echo json_encode($result);
        } else {
            logDebug("Error al decodificar JSON: " . json_last_error_msg());
            echo json_encode([
                'success' => false,
                'message' => 'Datos no válidos: ' . json_last_error_msg()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
    }
} else {
    // Si existe la carpeta vendor, usar TCPDF para generar un PDF profesional
    try {
        require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
    } catch (Exception $e) {
        logDebug("Error al cargar TCPDF: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar TCPDF: ' . $e->getMessage()
        ]);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Obtener los datos JSON y decodificarlos
            $jsonData = file_get_contents('php://input');
            logDebug("Datos JSON recibidos (TCPDF): " . substr($jsonData, 0, 500) . "...");
            
            $data = json_decode($jsonData, true);
            
            if (!$data) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
            
            logDebug("Datos decodificados correctamente (TCPDF)");
            
            // Crear directorio de presupuestos si no existe
            if (!file_exists(__DIR__ . '/presupuestos')) {
                if (!mkdir(__DIR__ . '/presupuestos', 0777, true)) {
                    throw new Exception("No se pudo crear el directorio de presupuestos");
                }
            }
        
            // Crear nuevo documento PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Configurar documento
            $pdf->SetCreator('Cotizador');
            $pdf->SetAuthor('Empresa');
            $pdf->SetTitle('Presupuesto - ' . $data['producto']['nombre']);
            $pdf->SetSubject('Presupuesto');
            
            // Establecer márgenes
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            
            // Establecer auto salto de página
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            // Agregar una página
            $pdf->AddPage();
            
            // Establecer fuente
            $pdf->SetFont('helvetica', '', 10);
            
            // Contenido del PDF
            $html = '<h1 style="text-align:center;">Presupuesto</h1>';
            $html .= '<p style="text-align:center;">' . date('d/m/Y') . '</p>';
            
            $html .= '<h2>Datos del Cliente</h2>';
            $html .= '<p><b>Nombre:</b> ' . $data['nombre'] . '</p>';
            $html .= '<p><b>Email:</b> ' . $data['email'] . '</p>';
            $html .= '<p><b>Teléfono:</b> ' . $data['telefono'] . '</p>';
            
            $html .= '<h2>Detalle del Presupuesto</h2>';
            $html .= '<table border="1" cellpadding="5">
                <tr style="background-color:#f2f2f2;">
                    <th width="70%">Descripción</th>
                    <th width="30%">Precio</th>
                </tr>
                <tr>
                    <td>Producto: ' . $data['producto']['nombre'] . '</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Opción: ' . $data['opcion']['nombre'] . '</td>
                    <td>$' . number_format($data['opcion']['precio'], 2, ',', '.') . '</td>
                </tr>';
            
            // Agregar adicionales si existen
            if (!empty($data['adicionales'])) {
                foreach ($data['adicionales'] as $adicional) {
                    $html .= '<tr>
                        <td>Adicional: ' . $adicional['nombre'] . '</td>
                        <td>$' . number_format($adicional['precio'], 2, ',', '.') . '</td>
                    </tr>';
                }
            }
            
            $html .= '<tr>
                    <td>Subtotal</td>
                    <td>$' . number_format($data['subtotal'], 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>Forma de pago: ' . $data['formaPago']['nombre'] . ' (' . $data['formaPago']['descuento'] . '% descuento)</td>
                    <td>-$' . number_format($data['formaPago']['descuentoMonto'], 2, ',', '.') . '</td>
                </tr>
                <tr style="font-weight:bold;">
                    <td>TOTAL</td>
                    <td>$' . number_format($data['total'], 2, ',', '.') . '</td>
                </tr>
            </table>';
            
            $html .= '<h3>Plazo de entrega</h3>';
            $html .= '<p>' . $data['plazo']['nombre'] . '</p>';
            
            $html .= '<div style="text-align:center; margin-top:50px; font-size:12px; color:#666;">
                <p>Este presupuesto tiene una validez de 15 días.</p>
                <p>Para cualquier consulta, no dude en contactarnos.</p>
            </div>';
            
            // Escribir HTML en el PDF
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Cerrar y generar el PDF
            $pdfFileName = 'presupuesto_' . time() . '.pdf';
            $pdfFilePath = __DIR__ . '/presupuestos/' . $pdfFileName;
            
            logDebug("Generando PDF en: " . $pdfFilePath);
            
            $pdf->Output($pdfFilePath, 'F');
            
            if (!file_exists($pdfFilePath)) {
                throw new Exception("No se pudo generar el archivo PDF");
            }
            
            logDebug("PDF generado correctamente");
            
            // Devolver la URL del archivo generado
            echo json_encode([
                'success' => true,
                'file' => 'presupuestos/' . $pdfFileName,
                'type' => 'pdf'
            ]);
        } catch (Exception $e) {
            logDebug("Error en generación de PDF (TCPDF): " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
    }
} catch (Exception $e) {
    logDebug("Error general: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
?>
