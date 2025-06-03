<?php
// Script para convertir HTML a PDF usando la librería dompdf
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
    // Verificar que se recibió un parámetro de archivo HTML
    if (!isset($_GET['html_file'])) {
        throw new Exception("No se especificó el archivo HTML");
    }
    
    $htmlFile = $_GET['html_file'];
    $htmlPath = __DIR__ . '/' . $htmlFile;
    
    // Verificar que el archivo existe
    if (!file_exists($htmlPath)) {
        throw new Exception("El archivo HTML no existe: $htmlPath");
    }
    
    // Leer el contenido HTML
    $html = file_get_contents($htmlPath);
    if (!$html) {
        throw new Exception("No se pudo leer el archivo HTML");
    }
    
    // Crear nombre para el archivo PDF
    $pdfFileName = 'presupuesto_' . time() . '.pdf';
    $pdfPath = __DIR__ . '/presupuestos/' . $pdfFileName;
    
    // Crear directorio de presupuestos si no existe
    if (!file_exists(__DIR__ . '/presupuestos')) {
        if (!mkdir(__DIR__ . '/presupuestos', 0777, true)) {
            throw new Exception("No se pudo crear el directorio de presupuestos");
        }
    }
    
    // Convertir HTML a PDF usando una función básica
    $command = '';
    
    // Intentar usar wkhtmltopdf si está disponible
    if (file_exists('C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe')) {
        $command = '"C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe" "' . $htmlPath . '" "' . $pdfPath . '"';
    } else {
        // Intentar usar Chrome en modo headless
        $chrome = 'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe';
        if (file_exists($chrome)) {
            $command = '"' . $chrome . '" --headless --disable-gpu --print-to-pdf="' . $pdfPath . '" "' . $htmlPath . '"';
        }
    }
    
    if (!empty($command)) {
        // Ejecutar el comando
        logDebug("Ejecutando comando: $command");
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception("Error al ejecutar el comando: " . implode("\n", $output));
        }
        
        if (file_exists($pdfPath)) {
            // Devolver la URL del archivo PDF
            echo json_encode([
                'success' => true,
                'file' => 'presupuestos/' . $pdfFileName,
                'type' => 'pdf'
            ]);
        } else {
            throw new Exception("No se pudo generar el archivo PDF");
        }
    } else {
        // Si no hay herramientas disponibles, usar el HTML directamente
        echo json_encode([
            'success' => true,
            'file' => $htmlFile,
            'type' => 'html',
            'message' => 'No se encontraron herramientas para convertir a PDF. Se está utilizando el HTML directamente.'
        ]);
    }
    
} catch (Exception $e) {
    logDebug("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
