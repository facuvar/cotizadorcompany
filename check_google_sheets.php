<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Verificación de Conexión a Google Sheets</h1>";
    
    // Obtener la URL actual
    $result = $conn->query("SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC LIMIT 1");
    
    if (!$result || $result->num_rows === 0) {
        echo "<p>No se encontró ninguna fuente de datos en la base de datos.</p>";
        exit;
    }
    
    $fuenteDatos = $result->fetch_assoc();
    $urlActual = $fuenteDatos['url'];
    
    echo "<p><strong>URL actual:</strong> " . htmlspecialchars($urlActual) . "</p>";
    
    // Extraer ID del documento
    $pattern = '/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
    if (!preg_match($pattern, $urlActual, $matches)) {
        echo "<p>No se pudo extraer el ID del documento de Google Sheets desde la URL proporcionada.</p>";
        exit;
    }
    
    $documentId = $matches[1];
    echo "<p><strong>ID del documento:</strong> " . htmlspecialchars($documentId) . "</p>";
    
    // Construir URL para exportar
    $exportUrl = "https://docs.google.com/spreadsheets/d/{$documentId}/export?format=xlsx";
    echo "<p><strong>URL de exportación:</strong> " . htmlspecialchars($exportUrl) . "</p>";
    
    // Probar la conexión con cURL
    echo "<h2>Prueba de conexión con cURL</h2>";
    
    $ch = curl_init($exportUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    curl_close($ch);
    
    echo "<p><strong>Código HTTP:</strong> " . $httpCode . "</p>";
    
    if ($response === false) {
        echo "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
        echo "<pre>" . print_r($info, true) . "</pre>";
    } else {
        echo "<p><strong>Respuesta recibida:</strong> " . strlen($response) . " bytes</p>";
        
        if ($httpCode == 200) {
            echo "<p style='color: green;'><strong>✓ Conexión exitosa</strong> - El archivo se puede descargar correctamente.</p>";
            
            // Guardar temporalmente para verificar
            $tempFile = 'sistema/temp/test_google_sheets_' . time() . '.xlsx';
            if (!file_exists('sistema/temp')) {
                mkdir('sistema/temp', 0777, true);
            }
            
            file_put_contents($tempFile, $response);
            echo "<p>Archivo guardado temporalmente en: " . $tempFile . " (" . filesize($tempFile) . " bytes)</p>";
            
            // Intentar leer con PhpSpreadsheet
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                
                try {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    $spreadsheet = $reader->load($tempFile);
                    $sheet = $spreadsheet->getSheet(0);
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    
                    echo "<p><strong>Archivo leído correctamente con PhpSpreadsheet</strong></p>";
                    echo "<p>Dimensiones: " . $highestColumn . $highestRow . " (filas: " . $highestRow . ")</p>";
                    
                    // Buscar GIRACOCHES
                    $giracochesFound = false;
                    $giracochesRow = 0;
                    
                    for ($row = 1; $row <= min($highestRow, 100); $row++) {
                        $cellValue = $sheet->getCell('A' . $row)->getValue();
                        if (is_string($cellValue) && strpos(strtoupper($cellValue), 'GIRACOCHES') !== false) {
                            $giracochesFound = true;
                            $giracochesRow = $row;
                            break;
                        }
                    }
                    
                    if ($giracochesFound) {
                        echo "<p style='color: green;'><strong>✓ Sección GIRACOCHES encontrada</strong> en la fila " . $giracochesRow . "</p>";
                    } else {
                        echo "<p style='color: red;'><strong>✗ Sección GIRACOCHES no encontrada</strong> en las primeras 100 filas</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'><strong>Error al leer con PhpSpreadsheet:</strong> " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p><em>No se puede verificar el contenido con PhpSpreadsheet (vendor/autoload.php no encontrado)</em></p>";
            }
        } else {
            echo "<p style='color: red;'><strong>✗ Error en la conexión</strong> - Código HTTP: " . $httpCode . "</p>";
        }
    }
    
    // Formulario para actualizar la URL
    echo "<h2>Actualizar URL de Google Sheets</h2>";
    echo "<form method='post' action=''>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='url' style='display: block; margin-bottom: 5px;'>Nueva URL de Google Sheets:</label>";
    echo "<input type='text' id='url' name='url' value='" . htmlspecialchars($urlActual) . "' style='width: 100%; padding: 8px;'>";
    echo "</div>";
    echo "<button type='submit' name='actualizar' style='padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Actualizar URL</button>";
    echo "</form>";
    
    // Procesar formulario
    if (isset($_POST['actualizar']) && isset($_POST['url'])) {
        $nuevaUrl = trim($_POST['url']);
        
        if (empty($nuevaUrl)) {
            echo "<p style='color: red;'>La URL no puede estar vacía.</p>";
        } else {
            // Actualizar la URL en la base de datos
            $stmt = $conn->prepare("UPDATE fuente_datos SET url = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->bind_param("si", $nuevaUrl, $fuenteDatos['id']);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>URL actualizada correctamente.</p>";
                echo "<p><a href='" . $_SERVER['PHP_SELF'] . "'>Recargar para verificar la nueva URL</a></p>";
            } else {
                echo "<p style='color: red;'>Error al actualizar la URL: " . $stmt->error . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 