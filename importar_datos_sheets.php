<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Importación de Datos desde Google Sheets</h1>";
    
    // Obtener la URL de la fuente de datos
    $result = $conn->query("SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC LIMIT 1");
    
    if (!$result || $result->num_rows === 0) {
        mostrarMensaje("No se encontró ninguna fuente de datos en la base de datos.", "error");
        exit;
    }
    
    $fuenteDatos = $result->fetch_assoc();
    $urlActual = $fuenteDatos['url'];
    
    echo "<p><strong>URL actual:</strong> " . htmlspecialchars($urlActual) . "</p>";
    
    // Extraer ID del documento
    $pattern = '/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
    if (!preg_match($pattern, $urlActual, $matches)) {
        mostrarMensaje("No se pudo extraer el ID del documento de Google Sheets desde la URL proporcionada.", "error");
        exit;
    }
    
    $documentId = $matches[1];
    echo "<p><strong>ID del documento:</strong> " . htmlspecialchars($documentId) . "</p>";
    
    // Construir URL para exportar
    $exportUrl = "https://docs.google.com/spreadsheets/d/{$documentId}/export?format=xlsx";
    
    // Descargar el archivo
    echo "<h2>Descargando archivo de Google Sheets</h2>";
    
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
    
    curl_close($ch);
    
    if ($response === false || $httpCode != 200) {
        mostrarMensaje("Error al descargar el archivo: " . $error . " (Código HTTP: " . $httpCode . ")", "error");
        exit;
    }
    
    // Guardar temporalmente para procesar
    $tempFile = 'sistema/temp/import_google_sheets_' . time() . '.xlsx';
    if (!file_exists('sistema/temp')) {
        mkdir('sistema/temp', 0777, true);
    }
    
    file_put_contents($tempFile, $response);
    mostrarMensaje("Archivo descargado correctamente: " . $tempFile . " (" . filesize($tempFile) . " bytes)", "success");
    
    // Procesar el archivo con PhpSpreadsheet
    echo "<h2>Procesando datos del archivo</h2>";
    
    try {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($tempFile);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        mostrarMensaje("Archivo cargado correctamente. Dimensiones: " . $highestColumn . $highestRow . " (filas: " . $highestRow . ")", "success");
        
        // Comenzar la importación
        echo "<h3>Importando categorías y opciones</h3>";
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        // Variables para el seguimiento
        $categoriaActual = null;
        $categoriaId = null;
        $totalCategorias = 0;
        $totalOpciones = 0;
        
        // Recorrer filas
        for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
            $nombre = trim($sheet->getCell('A' . $rowIndex)->getValue());
            
            // Saltar filas vacías
            if (empty($nombre)) {
                continue;
            }
            
            $descripcion = trim($sheet->getCell('B' . $rowIndex)->getFormattedValue());
            $precio = $sheet->getCell('C' . $rowIndex)->getValue();
            
            // Verificar si es una categoría (mayúsculas y sin precio)
            if (strtoupper($nombre) === $nombre && (empty($precio) || !is_numeric($precio))) {
                // Es una categoría
                $categoriaActual = $nombre;
                
                // Verificar si la categoría ya existe
                $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ?");
                $stmt->bind_param("s", $categoriaActual);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // La categoría ya existe
                    $categoria = $result->fetch_assoc();
                    $categoriaId = $categoria['id'];
                    mostrarMensaje("Categoría existente: " . $categoriaActual . " (ID: " . $categoriaId . ")", "info");
                } else {
                    // Crear nueva categoría
                    $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
                    $orden = $totalCategorias + 1;
                    $stmt->bind_param("ssi", $categoriaActual, $descripcion, $orden);
                    $stmt->execute();
                    $categoriaId = $conn->insert_id;
                    $totalCategorias++;
                    mostrarMensaje("Nueva categoría creada: " . $categoriaActual . " (ID: " . $categoriaId . ")", "success");
                }
            } elseif ($categoriaId !== null && is_numeric($precio)) {
                // Es una opción de la categoría actual
                $esObligatorio = 1; // Por defecto todas son obligatorias
                
                // Verificar si la opción ya existe
                $stmt = $conn->prepare("SELECT id FROM opciones WHERE categoria_id = ? AND nombre = ?");
                $stmt->bind_param("is", $categoriaId, $nombre);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // La opción ya existe, actualizarla
                    $opcion = $result->fetch_assoc();
                    $opcionId = $opcion['id'];
                    $stmt = $conn->prepare("UPDATE opciones SET descripcion = ?, precio = ?, es_obligatorio = ? WHERE id = ?");
                    $stmt->bind_param("sdii", $descripcion, $precio, $esObligatorio, $opcionId);
                    $stmt->execute();
                    mostrarMensaje("Opción actualizada: " . $nombre . " - Precio: " . $precio, "info");
                } else {
                    // Crear nueva opción
                    $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, es_obligatorio, orden) VALUES (?, ?, ?, ?, ?, ?)");
                    $orden = $totalOpciones + 1;
                    $stmt->bind_param("issdii", $categoriaId, $nombre, $descripcion, $precio, $esObligatorio, $orden);
                    $stmt->execute();
                    $totalOpciones++;
                    mostrarMensaje("Nueva opción creada: " . $nombre . " - Precio: " . $precio, "success");
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        echo "<h3>Resumen de la importación</h3>";
        echo "<ul>";
        echo "<li>Total de categorías procesadas: " . $totalCategorias . "</li>";
        echo "<li>Total de opciones procesadas: " . $totalOpciones . "</li>";
        echo "</ul>";
        
        mostrarMensaje("Importación completada correctamente.", "success");
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        // Para mysqli no existe inTransaction(), así que simplemente intentamos rollback
        if (isset($conn)) {
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Si falla el rollback, lo registramos pero continuamos
                error_log("Error en rollback: " . $rollbackException->getMessage());
            }
        }
        mostrarMensaje("Error al procesar el archivo: " . $e->getMessage(), "error");
    }
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
}

// Enlaces de navegación
echo "<div style='margin-top: 20px;'>";
echo "<a href='verificar_estado.php' style='padding: 8px 16px; background-color: #2196F3; color: white; text-decoration: none; margin-right: 10px;'>Verificar Estado de la Base de Datos</a>";
echo "<a href='sistema/index.php' style='padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none;'>Ir al Sistema</a>";
echo "</div>";
?>
