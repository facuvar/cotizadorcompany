<?php
// Agregar opciones para todos los productos que no tienen opciones
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener productos sin opciones
    $query = "SELECT p.id, p.nombre 
              FROM xls_productos p 
              LEFT JOIN xls_opciones o ON p.id = o.producto_id 
              GROUP BY p.id 
              HAVING COUNT(o.id) = 0 
              ORDER BY p.orden ASC";
    
    $result = $conn->query($query);
    
    echo "<h1>Agregando opciones para productos sin opciones</h1>";
    
    if ($result->num_rows == 0) {
        echo "<p>Todos los productos ya tienen opciones.</p>";
        exit;
    }
    
    // Obtener plazos de entrega para asignar precios
    $query = "SELECT id, factor FROM xls_plazos ORDER BY orden ASC";
    $plazos_result = $conn->query($query);
    $plazos = [];
    
    while ($plazo = $plazos_result->fetch_assoc()) {
        $plazos[] = $plazo;
    }
    
    // Definir opciones para cada tipo de producto
    $opciones_por_producto = [
        'GIRACOCHE' => [
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
        ],
        'MONTACARGAS' => [
            [
                'nombre' => 'MONTACARGAS 500KG',
                'descripcion' => 'Montacargas con capacidad de 500kg',
                'precio_base' => 2800000
            ],
            [
                'nombre' => 'MONTACARGAS 1000KG',
                'descripcion' => 'Montacargas con capacidad de 1000kg',
                'precio_base' => 3500000
            ],
            [
                'nombre' => 'MONTACARGAS 1500KG',
                'descripcion' => 'Montacargas con capacidad de 1500kg',
                'precio_base' => 4200000
            ],
            [
                'nombre' => 'MONTACARGAS 2000KG',
                'descripcion' => 'Montacargas con capacidad de 2000kg',
                'precio_base' => 4900000
            ]
        ],
        'HIDRAULICO' => [
            [
                'nombre' => 'HIDRAULICO 320KG',
                'descripcion' => 'Ascensor hidráulico con capacidad de 320kg',
                'precio_base' => 3200000
            ],
            [
                'nombre' => 'HIDRAULICO 450KG',
                'descripcion' => 'Ascensor hidráulico con capacidad de 450kg',
                'precio_base' => 3800000
            ],
            [
                'nombre' => 'HIDRAULICO 600KG',
                'descripcion' => 'Ascensor hidráulico con capacidad de 600kg',
                'precio_base' => 4500000
            ],
            [
                'nombre' => 'HIDRAULICO 1000KG',
                'descripcion' => 'Ascensor hidráulico con capacidad de 1000kg',
                'precio_base' => 5200000
            ]
        ],
        'SALVAESCALERAS' => [
            [
                'nombre' => 'SALVAESCALERAS TRAMO RECTO',
                'descripcion' => 'Salvaescaleras para tramo recto',
                'precio_base' => 1800000
            ],
            [
                'nombre' => 'SALVAESCALERAS TRAMO CURVO',
                'descripcion' => 'Salvaescaleras para tramo curvo',
                'precio_base' => 2500000
            ]
        ],
        'DEFAULT' => [
            [
                'nombre' => 'OPCION BASICA',
                'descripcion' => 'Opción básica para este producto',
                'precio_base' => 1500000
            ],
            [
                'nombre' => 'OPCION ESTANDAR',
                'descripcion' => 'Opción estándar para este producto',
                'precio_base' => 2000000
            ],
            [
                'nombre' => 'OPCION PREMIUM',
                'descripcion' => 'Opción premium para este producto',
                'precio_base' => 2500000
            ]
        ]
    ];
    
    // Insertar opciones para cada producto sin opciones
    $conn->begin_transaction();
    
    try {
        while ($producto = $result->fetch_assoc()) {
            $producto_id = $producto['id'];
            $producto_nombre = $producto['nombre'];
            
            echo "<h2>Producto: {$producto_nombre} (ID: {$producto_id})</h2>";
            
            // Determinar qué opciones usar para este producto
            $opciones_a_usar = null;
            foreach ($opciones_por_producto as $key => $opciones) {
                if (stripos($producto_nombre, $key) !== false) {
                    $opciones_a_usar = $opciones;
                    break;
                }
            }
            
            // Si no se encontró un conjunto específico, usar el predeterminado
            if ($opciones_a_usar === null) {
                $opciones_a_usar = $opciones_por_producto['DEFAULT'];
            }
            
            // Insertar opciones y sus precios
            foreach ($opciones_a_usar as $opcion) {
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
        }
        
        $conn->commit();
        echo "<p style='color:green;font-weight:bold;'>¡Opciones agregadas correctamente para todos los productos!</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red;font-weight:bold;'>Error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
