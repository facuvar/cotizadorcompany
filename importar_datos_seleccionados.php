<?php
// Script para importar datos de MONTAPLATOS y ESTRUCTURA desde el XLS de referencia
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Importando datos de MONTAPLATOS y ESTRUCTURA</h1>";
    
    // Ruta al archivo XLS
    $inputFileName = 'xls/xls-referencia.xlsx';
    
    // Cargar el archivo con valores calculados
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true); // Esto hace que se lean los valores calculados, no las fórmulas
    $spreadsheet = $reader->load($inputFileName);
    
    // Obtener la hoja activa
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    
    // Mapeo de nombres de plazos a IDs
    $plazosMap = [
        '160/180 dias' => 2,
        '90 dias' => 1,
        '270 dias' => 3
    ];
    
    // Encontrar columnas de plazos
    $plazoColumns = [];
    $highestColumn = $worksheet->getHighestColumn();
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $value = $worksheet->getCell($col . '1')->getValue();
        if ($value && isset($plazosMap[$value])) {
            $plazoColumns[$col] = $plazosMap[$value];
        }
    }
    
    // Función para importar opciones de un producto
    function importarOpcionesProducto($conn, $worksheet, $productoNombre, $startRow, $endRow, $plazoColumns) {
        // Obtener ID del producto
        $query = "SELECT id FROM xls_productos WHERE nombre LIKE ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $search_term = "%$productoNombre%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "<p style='color:red;'>No se encontró el producto: $productoNombre</p>";
            return false;
        }
        
        $producto = $result->fetch_assoc();
        $producto_id = $producto['id'];
        
        echo "<h2>Importando opciones para $productoNombre (ID: $producto_id)</h2>";
        
        // Eliminar opciones existentes y sus precios
        $conn->begin_transaction();
        
        try {
            // Obtener IDs de opciones existentes
            $query = "SELECT id FROM xls_opciones WHERE producto_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $producto_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $opcion_ids = [];
            while ($row = $result->fetch_assoc()) {
                $opcion_ids[] = $row['id'];
            }
            
            // Eliminar precios de estas opciones
            if (!empty($opcion_ids)) {
                $placeholders = implode(',', array_fill(0, count($opcion_ids), '?'));
                $query = "DELETE FROM xls_precios WHERE opcion_id IN ($placeholders)";
                $stmt = $conn->prepare($query);
                
                $types = str_repeat('i', count($opcion_ids));
                $stmt->bind_param($types, ...$opcion_ids);
                $stmt->execute();
                
                // Eliminar opciones
                $query = "DELETE FROM xls_opciones WHERE producto_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $producto_id);
                $stmt->execute();
                
                echo "<p>Eliminadas " . count($opcion_ids) . " opciones existentes y sus precios.</p>";
            }
            
            // Importar opciones desde el XLS
            for ($row = $startRow; $row <= $endRow; $row++) {
                $opcionNombre = $worksheet->getCell('A' . $row)->getValue();
                
                // Si la celda A está vacía o contiene otro producto principal, saltar
                if (!$opcionNombre || (
                    stripos($opcionNombre, 'MONTAPLATO') !== false || 
                    stripos($opcionNombre, 'ESTRUCTURA') !== false ||
                    stripos($opcionNombre, 'GIRACOCHE') !== false ||
                    stripos($opcionNombre, 'EQUIPO') !== false
                )) {
                    continue;
                }
                
                // Insertar opción
                $stmt = $conn->prepare("INSERT INTO xls_opciones (producto_id, nombre, descripcion) VALUES (?, ?, ?)");
                $descripcion = "Opción importada desde XLS de referencia";
                $stmt->bind_param("iss", $producto_id, $opcionNombre, $descripcion);
                $stmt->execute();
                
                $opcion_id = $conn->insert_id;
                
                // Insertar precios para cada plazo
                foreach ($plazoColumns as $col => $plazo_id) {
                    $precio = $worksheet->getCell($col . $row)->getValue();
                    
                    // Si no hay precio, usar 0
                    if (!is_numeric($precio)) {
                        $precio = 0;
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
                    $stmt->bind_param("iid", $opcion_id, $plazo_id, $precio);
                    $stmt->execute();
                }
                
                echo "<p>Importada opción: $opcionNombre con ID: $opcion_id</p>";
                echo "<ul>";
                foreach ($plazoColumns as $col => $plazo_id) {
                    $precio = $worksheet->getCell($col . $row)->getValue();
                    if (!is_numeric($precio)) {
                        $precio = 0;
                    }
                    
                    $plazo_nombre = "";
                    switch ($plazo_id) {
                        case 1: $plazo_nombre = "90 días"; break;
                        case 2: $plazo_nombre = "160/180 días"; break;
                        case 3: $plazo_nombre = "270 días"; break;
                    }
                    echo "<li>Precio para $plazo_nombre: $" . number_format($precio, 2, ',', '.') . "</li>";
                }
                echo "</ul>";
            }
            
            $conn->commit();
            echo "<p style='color:green;font-weight:bold;'>¡Opciones importadas correctamente para $productoNombre!</p>";
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
            return false;
        }
    }
    
    // Buscar filas de MONTAPLATOS y ESTRUCTURA
    $montaplatosRow = null;
    $estructuraRow = null;
    $montaplatosEndRow = null;
    $estructuraEndRow = null;
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $value = $worksheet->getCell('A' . $row)->getValue();
        
        if ($value && stripos($value, 'MONTAPLATO') !== false) {
            $montaplatosRow = $row;
        } else if ($value && stripos($value, 'ESTRUCTURA') !== false) {
            $estructuraRow = $row;
        } else if ($montaplatosRow && !$montaplatosEndRow && $estructuraRow) {
            $montaplatosEndRow = $row - 1;
        }
    }
    
    // Si no se encontró un final para ESTRUCTURA, usar la última fila
    if ($estructuraRow && !$estructuraEndRow) {
        $estructuraEndRow = $highestRow;
    }
    
    // Si no se encontró un final para MONTAPLATOS, usar la fila antes de ESTRUCTURA
    if ($montaplatosRow && !$montaplatosEndRow && $estructuraRow) {
        $montaplatosEndRow = $estructuraRow - 1;
    } else if ($montaplatosRow && !$montaplatosEndRow) {
        $montaplatosEndRow = $highestRow;
    }
    
    // Importar opciones de MONTAPLATOS
    if ($montaplatosRow && $montaplatosEndRow) {
        echo "<p>Encontrado MONTAPLATOS en la fila $montaplatosRow hasta $montaplatosEndRow</p>";
        importarOpcionesProducto($conn, $worksheet, "MONTAPLATO", $montaplatosRow + 1, $montaplatosEndRow, $plazoColumns);
    } else {
        echo "<p style='color:red;'>No se encontró MONTAPLATOS en el archivo XLS</p>";
    }
    
    // Importar opciones de ESTRUCTURA
    if ($estructuraRow && $estructuraEndRow) {
        echo "<p>Encontrado ESTRUCTURA en la fila $estructuraRow hasta $estructuraEndRow</p>";
        importarOpcionesProducto($conn, $worksheet, "ESTRUCTURA", $estructuraRow + 1, $estructuraEndRow, $plazoColumns);
    } else {
        echo "<p style='color:red;'>No se encontró ESTRUCTURA en el archivo XLS</p>";
    }
    
    echo "<p><a href='cotizador_xls_fixed.php' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Ir al Cotizador</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
