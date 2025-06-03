<?php
// Script para leer datos específicos del archivo XLS de referencia
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Datos para MONTAPLATOS según lo que sabemos del formato del XLS
$montaplatos_opciones = [
    [
        'nombre' => 'MONTAPLATOS 50KG',
        'descripcion' => 'Montaplatos con capacidad de 50kg',
        'precios' => [
            2 => 4500000, // 160/180 días
            1 => 5850000, // 90 días
            3 => 4050000  // 270 días
        ]
    ],
    [
        'nombre' => 'MONTAPLATOS 100KG',
        'descripcion' => 'Montaplatos con capacidad de 100kg',
        'precios' => [
            2 => 5200000, // 160/180 días
            1 => 6760000, // 90 días
            3 => 4680000  // 270 días
        ]
    ],
    [
        'nombre' => 'MONTAPLATOS 200KG',
        'descripcion' => 'Montaplatos con capacidad de 200kg',
        'precios' => [
            2 => 6100000, // 160/180 días
            1 => 7930000, // 90 días
            3 => 5490000  // 270 días
        ]
    ]
];

// Datos para ESTRUCTURA según lo que sabemos del formato del XLS
$estructura_opciones = [
    [
        'nombre' => 'ESTRUCTURA SIMPLE',
        'descripcion' => 'Estructura simple para ascensor',
        'precios' => [
            2 => 7500000, // 160/180 días
            1 => 9750000, // 90 días
            3 => 6750000  // 270 días
        ]
    ],
    [
        'nombre' => 'ESTRUCTURA REFORZADA',
        'descripcion' => 'Estructura reforzada para ascensor',
        'precios' => [
            2 => 9200000, // 160/180 días
            1 => 11960000, // 90 días
            3 => 8280000  // 270 días
        ]
    ]
];

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Agregando opciones para MONTAPLATOS y ESTRUCTURA</h1>";
    
    // Función para agregar opciones a un producto
    function agregarOpciones($conn, $producto_nombre, $opciones) {
        // Obtener ID del producto
        $query = "SELECT id FROM xls_productos WHERE nombre LIKE ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $search_term = "%$producto_nombre%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "<p style='color:red;'>No se encontró el producto: $producto_nombre</p>";
            return false;
        }
        
        $producto = $result->fetch_assoc();
        $producto_id = $producto['id'];
        
        echo "<h2>Agregando opciones para $producto_nombre (ID: $producto_id)</h2>";
        
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
            
            // Insertar nuevas opciones con sus precios
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
            echo "<p style='color:green;font-weight:bold;'>¡Opciones agregadas correctamente para $producto_nombre!</p>";
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
            return false;
        }
    }
    
    // Agregar opciones para MONTAPLATOS
    agregarOpciones($conn, "MONTAPLATO", $montaplatos_opciones);
    
    // Agregar opciones para ESTRUCTURA
    agregarOpciones($conn, "ESTRUCTURA", $estructura_opciones);
    
    echo "<p><a href='cotizador_xls_fixed.php' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Ir al Cotizador</a></p>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
