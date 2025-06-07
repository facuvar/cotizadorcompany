<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "=== Estructura de la tabla presupuestos ===\n";
    $result = $conn->query('DESCRIBE presupuestos');
    
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\n=== Verificando datos de prueba ===\n";
    $result = $conn->query('SELECT id, cliente_nombre, ubicacion_obra FROM presupuestos ORDER BY id DESC LIMIT 3');
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Cliente: " . $row['cliente_nombre'] . " | Ubicación: " . ($row['ubicacion_obra'] ?? 'NULL') . "\n";
        }
    } else {
        echo "No hay presupuestos en la base de datos.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 