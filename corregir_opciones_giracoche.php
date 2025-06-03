<?php
// Corregir opciones para GIRACOCHES según el archivo XLS de referencia
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener ID del producto GIRACOCHES
    $query = "SELECT id FROM xls_productos WHERE nombre LIKE '%GIRACOCHE%' LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows == 0) {
        die("No se encontró el producto GIRACOCHES");
    }
    
    $producto = $result->fetch_assoc();
    $producto_id = $producto['id'];
    
    echo "<h1>Corrigiendo opciones para GIRACOCHES (ID: $producto_id)</h1>";
    
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
        
        // Opciones correctas para GIRACOCHES según el archivo XLS
        $opciones = [
            [
                'nombre' => 'ESTRUCTURA 1,80X1,80 2 PARADAS',
                'descripcion' => 'Estructura de giracoche 1,80x1,80 con 2 paradas',
                'precios' => [
                    2 => 13431378.00, // 160/180 días
                    1 => 17460791.40, // 90 días
                    3 => 12088240.20  // 270 días
                ]
            ],
            [
                'nombre' => 'ESTRUCTURA 1,80X1,80 3 PARADAS',
                'descripcion' => 'Estructura de giracoche 1,80x1,80 con 3 paradas',
                'precios' => [
                    2 => 15246428.00, // 160/180 días
                    1 => 19820356.40, // 90 días
                    3 => 13721785.20  // 270 días
                ]
            ]
        ];
        
        // Insertar nuevas opciones con sus precios exactos
        foreach ($opciones as $opcion) {
            // Insertar opción
            $stmt = $conn->prepare("INSERT INTO xls_opciones (producto_id, nombre, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $producto_id, $opcion['nombre'], $opcion['descripcion']);
            $stmt->execute();
            
            $opcion_id = $conn->insert_id;
            
            // Insertar precios específicos para cada plazo
            foreach ($opcion['precios'] as $plazo_id => $precio) {
                $stmt = $conn->prepare("INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $opcion_id, $plazo_id, $precio);
                $stmt->execute();
            }
            
            echo "<p>Agregada opción: {$opcion['nombre']} con ID: $opcion_id</p>";
            echo "<ul>";
            foreach ($opcion['precios'] as $plazo_id => $precio) {
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
        echo "<p style='color:green;font-weight:bold;'>¡Opciones corregidas correctamente!</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
