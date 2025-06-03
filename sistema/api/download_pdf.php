<?php
/**
 * API para descargar PDFs de presupuestos
 * Usado por el cotizador moderno
 */

// Cargar configuración
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado");
}
require_once $configPath;

// Cargar DB
$dbPath = __DIR__ . '/../includes/db.php';
if (!file_exists($dbPath)) {
    die("Error: Archivo de base de datos no encontrado");
}
require_once $dbPath;

// Obtener ID del presupuesto
$presupuesto_id = $_GET['id'] ?? 0;

if (!$presupuesto_id) {
    die("Error: ID de presupuesto no proporcionado");
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Obtener datos del presupuesto
    $query = "SELECT * FROM presupuestos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $presupuesto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Error: Presupuesto no encontrado");
    }
    
    $presupuesto = $result->fetch_assoc();
    
    // Para esta implementación simple, vamos a generar un HTML que simule un PDF
    // En una implementación real, usarías una librería como TCPDF o DOMPDF
    
    $html = generarHTMLPresupuesto($presupuesto);
    
    // Headers para mostrar como PDF (simulado)
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="presupuesto_' . $presupuesto['numero_presupuesto'] . '.html"');
    
    echo $html;
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

function generarHTMLPresupuesto($presupuesto) {
    $fecha_formateada = date('d/m/Y', strtotime($presupuesto['created_at']));
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Presupuesto {$presupuesto['numero_presupuesto']}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
                color: #333;
                line-height: 1.6;
            }
            
            .header {
                text-align: center;
                border-bottom: 3px solid #3b82f6;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .company-name {
                font-size: 28px;
                font-weight: bold;
                color: #3b82f6;
                margin-bottom: 10px;
            }
            
            .document-title {
                font-size: 24px;
                margin: 20px 0;
                color: #1a1a1a;
            }
            
            .quote-number {
                font-size: 18px;
                color: #666;
            }
            
            .info-section {
                display: flex;
                justify-content: space-between;
                margin: 30px 0;
            }
            
            .client-info, .quote-info {
                width: 45%;
            }
            
            .section-title {
                font-size: 16px;
                font-weight: bold;
                color: #3b82f6;
                border-bottom: 1px solid #e5e5e5;
                padding-bottom: 5px;
                margin-bottom: 15px;
            }
            
            .info-row {
                margin: 8px 0;
            }
            
            .label {
                font-weight: bold;
                display: inline-block;
                width: 100px;
            }
            
            .items-section {
                margin: 40px 0;
            }
            
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            
            .items-table th {
                background: #f8f9fa;
                padding: 12px;
                text-align: left;
                border: 1px solid #dee2e6;
                font-weight: bold;
            }
            
            .items-table td {
                padding: 10px 12px;
                border: 1px solid #dee2e6;
            }
            
            .items-table tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            .totals-section {
                margin: 30px 0;
                text-align: right;
            }
            
            .totals-table {
                margin-left: auto;
                width: 300px;
            }
            
            .totals-table td {
                padding: 8px 15px;
                border-bottom: 1px solid #e5e5e5;
            }
            
            .total-final {
                font-size: 18px;
                font-weight: bold;
                background: #3b82f6;
                color: white;
            }
            
            .footer {
                margin-top: 50px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #e5e5e5;
                padding-top: 20px;
            }
            
            .print-button {
                background: #3b82f6;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin: 20px 0;
                font-size: 14px;
            }
            
            @media print {
                .print-button { display: none; }
                body { margin: 0; }
            }
        </style>
    </head>
    <body>
        <button class='print-button' onclick='window.print()'>🖨️ Imprimir Presupuesto</button>
        
        <div class='header'>
            <div class='company-name'>Sistema de Cotización de Ascensores</div>
            <div class='document-title'>PRESUPUESTO</div>
            <div class='quote-number'>{$presupuesto['numero_presupuesto']}</div>
        </div>
        
        <div class='info-section' style='display: flex; justify-content: space-between;'>
            <div class='client-info'>
                <div class='section-title'>Datos del Cliente</div>
                <div class='info-row'>
                    <span class='label'>Nombre:</span> {$presupuesto['cliente_nombre']}
                </div>
                <div class='info-row'>
                    <span class='label'>Email:</span> {$presupuesto['cliente_email']}
                </div>";
    
    if (!empty($presupuesto['cliente_telefono'])) {
        $html .= "
                <div class='info-row'>
                    <span class='label'>Teléfono:</span> {$presupuesto['cliente_telefono']}
                </div>";
    }
    
    if (!empty($presupuesto['cliente_empresa'])) {
        $html .= "
                <div class='info-row'>
                    <span class='label'>Empresa:</span> {$presupuesto['cliente_empresa']}
                </div>";
    }
    
    $html .= "
            </div>
            
            <div class='quote-info'>
                <div class='section-title'>Información del Presupuesto</div>
                <div class='info-row'>
                    <span class='label'>Fecha:</span> $fecha_formateada
                </div>
                <div class='info-row'>
                    <span class='label'>Estado:</span> Pendiente
                </div>
                <div class='info-row'>
                    <span class='label'>Validez:</span> 30 días
                </div>
            </div>
        </div>
        
        <div class='items-section'>
            <div class='section-title'>Configuración del Ascensor</div>
            
            <table class='items-table'>
                <thead>
                    <tr>
                        <th style='width: 60px;'>#</th>
                        <th>Descripción</th>
                        <th style='width: 120px; text-align: right;'>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Configuración personalizada de ascensor</td>
                        <td style='text-align: right;'>€" . number_format($presupuesto['subtotal'], 2, ',', '.') . "</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class='totals-section'>
            <table class='totals-table'>
                <tr>
                    <td>Subtotal:</td>
                    <td style='text-align: right;'>€" . number_format($presupuesto['subtotal'], 2, ',', '.') . "</td>
                </tr>";
    
    if ($presupuesto['descuento_porcentaje'] > 0) {
        $html .= "
                <tr>
                    <td>Descuento ({$presupuesto['descuento_porcentaje']}%):</td>
                    <td style='text-align: right; color: #dc3545;'>-€" . number_format($presupuesto['descuento_monto'], 2, ',', '.') . "</td>
                </tr>";
    }
    
    $html .= "
                <tr class='total-final'>
                    <td>TOTAL:</td>
                    <td style='text-align: right;'>€" . number_format($presupuesto['total'], 2, ',', '.') . "</td>
                </tr>
            </table>
        </div>
        
        <div class='footer'>
            <p>Este presupuesto es válido por 30 días desde la fecha de emisión.</p>
            <p>Para cualquier consulta, no dude en contactarnos.</p>
            <p>¡Gracias por confiar en nosotros!</p>
        </div>
    </body>
    </html>";
    
    return $html;
}
?> 