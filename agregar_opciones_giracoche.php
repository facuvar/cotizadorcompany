<?php
// Agregar opciones para GIRACOCHE
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener ID del producto GIRACOCHE
    $query = "SELECT id FROM xls_productos WHERE nombre LIKE '%GIRACOCHE%' LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows == 0) {
        die("No se encontró el producto GIRACOCHE");
    }
    
    $producto = $result->fetch_assoc();
    $producto_id = $producto['id'];
    
    echo "<h1>Agregando opciones para GIRACOCHE (ID: $producto_id)</h1>";
    
    // Opciones para GIRACOCHE
    $opciones = [
        [
            'nombre' => 'GIRACOCHE MANUAL 2,5 TN',
            'descripcion' => 'Giracoche manual con capacidad de 2,5 toneladas',
            'precio_base' => 1500000
        ],
        [
            'nombre' => 'GIRACOCHE MANUAL 3,5 TN',
            'descripcion' => 'Giracoche manual con capacidad de 3,5 toneladas',
            'precio_base' => 1850000
        ],
        [
            'nombre' => 'GIRACOCHE ELECTRICO 2,5 TN',
            'descripcion' => 'Giracoche eléctrico con capacidad de 2,5 toneladas',
            'precio_base' => 2300000
        ],
        [
            'nombre' => 'GIRACOCHE ELECTRICO 3,5 TN',
            'descripcion' => 'Giracoche eléctrico con capacidad de 3,5 toneladas',
            'precio_base' => 2750000
        ]
    ];
    
    // Obtener plazos de entrega para asignar precios
    $query = "SELECT id, factor FROM xls_plazos ORDER BY orden ASC";
    $plazos_result = $conn->query($query);
    $plazos = [];
    
    while ($plazo = $plazos_result->fetch_assoc()) {
        $plazos[] = $plazo;
    }
    
    // Insertar opciones y sus precios
    $conn->begin_transaction();
    
    try {
        foreach ($opciones as $opcion) {
            // Insertar opción
            $stmt = $conn->prepare("INSERT INTO xls_opciones (producto_id, nombre, descripcion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $producto_id, $opcion['nombre'], $opcion['descripcion']);
            $stmt->execute();
            
            $opcion_id = $conn->insert_id;
            
            // Insertar precios para cada plazo
            foreach ($plazos as $plazo) {
                $precio = $opcion['precio_base'] * $plazo['factor'];
                
                $stmt = $conn->prepare("INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
                $stmt->bind_param("iid", $opcion_id, $plazo['id'], $precio);
                $stmt->execute();
            }
            
            echo "<p>Agregada opción: {$opcion['nombre']} con ID: $opcion_id</p>";
        }
        
        $conn->commit();
        echo "<p style='color:green;font-weight:bold;'>¡Opciones agregadas correctamente!</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
