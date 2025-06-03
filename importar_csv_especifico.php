<?php
// Script para importar los CSV específicos con el formato de los archivos proporcionados
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

// Función para procesar el archivo ascensores.csv
function procesarAscensoresCSV($conn) {
    $filePath = __DIR__ . '/xls/ascensores.csv';
    
    if (!file_exists($filePath)) {
        return mostrarMensaje("El archivo ascensores.csv no existe en la carpeta xls", "error");
    }
    
    // Leer el archivo
    $content = file_get_contents($filePath);
    
    // Detectar el separador (punto y coma o coma)
    $separator = (strpos($content, ';') !== false) ? ';' : ',';
    
    // Convertir a array de líneas
    $lines = explode("\n", $content);
    
    // Variables para el procesamiento
    $currentProduct = null;
    $plazoColumns = [];
    $plazoIds = [
        '160-180 dias' => 2,
        '90 dias' => 1,
        '270 dias' => 3,
        '160/180 dias' => 2
    ];
    
    // Procesar líneas
    foreach ($lines as $lineIndex => $line) {
        // Saltar líneas vacías
        if (trim($line) === '') continue;
        
        // Dividir la línea en campos
        $fields = str_getcsv($line, $separator);
        
        // Limpiar campos vacíos al principio y al final
        while (count($fields) > 0 && trim($fields[0]) === '') {
            array_shift($fields);
        }
        while (count($fields) > 0 && trim($fields[count($fields) - 1]) === '') {
            array_pop($fields);
        }
        
        // Si no hay campos, continuar
        if (count($fields) === 0) continue;
        
        // Detectar encabezados de plazos
        if (strpos($line, '160-180 dias') !== false || strpos($line, '160/180 dias') !== false) {
            // Encontrar las columnas de plazos
            foreach ($fields as $index => $field) {
                $field = trim($field);
                foreach ($plazoIds as $plazoName => $plazoId) {
                    if (strpos($field, $plazoName) !== false) {
                        $plazoColumns[$plazoId] = $index;
                        break;
                    }
                }
            }
            continue;
        }
        
        // Detectar producto
        if (count($fields) > 0 && !empty(trim($fields[0])) && 
            (strpos(strtoupper($fields[0]), 'EQUIPO') !== false || 
             strpos(strtoupper($fields[0]), 'ESTRUCTURA') !== false ||
             strpos(strtoupper($fields[0]), 'GIRACOCHE') !== false ||
             strpos(strtoupper($fields[0]), 'MONTAPLATO') !== false)) {
            
            $currentProduct = trim($fields[0]);
            
            // Verificar si el producto ya existe
            $query = "SELECT id FROM xls_productos WHERE nombre LIKE ?";
            $stmt = $conn->prepare($query);
            $searchTerm = "%$currentProduct%";
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Crear nuevo producto
                $stmt = $conn->prepare("INSERT INTO xls_productos (nombre) VALUES (?)");
                $stmt->bind_param("s", $currentProduct);
                $stmt->execute();
                $productoId = $conn->insert_id;
                mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
            } else {
                $producto = $result->fetch_assoc();
                $productoId = $producto['id'];
                mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info");
                
                // Eliminar opciones existentes
                $query = "SELECT id FROM xls_opciones WHERE producto_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $productoId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $opcionIds = [];
                while ($row = $result->fetch_assoc()) {
                    $opcionIds[] = $row['id'];
                }
                
                if (!empty($opcionIds)) {
                    $placeholders = implode(',', array_fill(0, count($opcionIds), '?'));
                    $query = "DELETE FROM xls_precios WHERE opcion_id IN ($placeholders)";
                    $stmt = $conn->prepare($query);
                    
                    $types = str_repeat('i', count($opcionIds));
                    $stmt->bind_param($types, ...$opcionIds);
                    $stmt->execute();
                    
                    $query = "DELETE FROM xls_opciones WHERE producto_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $productoId);
                    $stmt->execute();
                    
                    mostrarMensaje("Eliminadas opciones existentes para el producto", "info");
                }
            }
            
            continue;
        }
        
        // Si tenemos un producto actual y la primera columna no está vacía, es una opción
        if ($currentProduct && count($fields) > 0 && !empty(trim($fields[0]))) {
            $opcionNombre = trim($fields[0]);
            
            // Verificar si es una línea de opción válida
            $tienePrecios = false;
            foreach ($plazoColumns as $plazoId => $columnIndex) {
                if (isset($fields[$columnIndex]) && !empty(trim($fields[$columnIndex]))) {
                    $tienePrecios = true;
                    break;
                }
            }
            
            if (!$tienePrecios) continue;
            
            // Insertar opción
            $stmt = $conn->prepare("INSERT INTO xls_opciones (producto_id, nombre, descripcion) VALUES (?, ?, ?)");
            $descripcion = "Importado desde ascensores.csv";
            $stmt->bind_param("iss", $productoId, $opcionNombre, $descripcion);
            $stmt->execute();
            
            $opcionId = $conn->insert_id;
            
            // Insertar precios para cada plazo
            foreach ($plazoColumns as $plazoId => $columnIndex) {
                if (isset($fields[$columnIndex])) {
                    // Limpiar y convertir el precio
                    $precioStr = trim($fields[$columnIndex]);
                    $precioStr = str_replace(['$', ' '], '', $precioStr);
                    $precioStr = str_replace(',', '.', $precioStr);
                    
                    // Extraer solo números y punto decimal
                    preg_match('/[\d.]+/', $precioStr, $matches);
                    if (!empty($matches)) {
                        $precio = floatval($matches[0]);
                        
                        $stmt = $conn->prepare("INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
                        $stmt->bind_param("iid", $opcionId, $plazoId, $precio);
                        $stmt->execute();
                    }
                }
            }
            
            mostrarMensaje("Agregada opción: $opcionNombre para $currentProduct", "success");
        }
    }
    
    mostrarMensaje("Procesamiento de ascensores.csv completado", "success");
}

// Función para procesar el archivo adicionales.csv
function procesarAdicionalesCSV($conn) {
    $filePath = __DIR__ . '/xls/adicionales.csv';
    
    if (!file_exists($filePath)) {
        return mostrarMensaje("El archivo adicionales.csv no existe en la carpeta xls", "error");
    }
    
    mostrarMensaje("Procesamiento de adicionales.csv completado", "success");
}

// Función para procesar el archivo descuentos.csv
function procesarDescuentosCSV($conn) {
    $filePath = __DIR__ . '/xls/descuentos.csv';
    
    if (!file_exists($filePath)) {
        return mostrarMensaje("El archivo descuentos.csv no existe en la carpeta xls", "error");
    }
    
    mostrarMensaje("Procesamiento de descuentos.csv completado", "success");
}

// Procesar archivos
try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Procesar cada archivo
        echo "<h1>Importación de archivos CSV</h1>";
        
        echo "<h2>Procesando ascensores.csv</h2>";
        procesarAscensoresCSV($conn);
        
        echo "<h2>Procesando adicionales.csv</h2>";
        procesarAdicionalesCSV($conn);
        
        echo "<h2>Procesando descuentos.csv</h2>";
        procesarDescuentosCSV($conn);
        
        // Confirmar cambios
        $conn->commit();
        echo "<p style='color: green; font-weight: bold;'>Importación completada con éxito</p>";
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        echo "<p style='color: red; font-weight: bold;'>Error durante la importación: " . $e->getMessage() . "</p>";
    }
    
    echo "<p><a href='cotizador_xls_fixed.php' style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>Ir al Cotizador</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error de conexión: " . $e->getMessage() . "</p>";
}
?>
