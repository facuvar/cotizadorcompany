<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar los plazos existentes
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    
    echo "<h1>Plazos de entrega registrados</h1>";
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Orden</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay plazos de entrega registrados.</p>";
    }
    
    // Verificar los plazos referenciados en opcion_precios
    echo "<h1>Plazos referenciados en opcion_precios</h1>";
    
    $result = $conn->query("SELECT DISTINCT plazo_id, plazo_entrega FROM opcion_precios ORDER BY plazo_id");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Plazo ID</th><th>Plazo Entrega</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['plazo_id'] . "</td>";
            echo "<td>" . $row['plazo_entrega'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay plazos referenciados en opcion_precios.</p>";
    }
    
    // Verificar si hay plazos en opcion_precios que no existen en plazos_entrega
    echo "<h1>Plazos faltantes (en opcion_precios pero no en plazos_entrega)</h1>";
    
    $result = $conn->query("
        SELECT DISTINCT op.plazo_id, op.plazo_entrega 
        FROM opcion_precios op
        LEFT JOIN plazos_entrega pe ON op.plazo_id = pe.id
        WHERE pe.id IS NULL
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Plazo ID</th><th>Plazo Entrega</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['plazo_id'] . "</td>";
            echo "<td>" . $row['plazo_entrega'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><strong>Acción requerida:</strong> Estos plazos necesitan ser creados en la tabla plazos_entrega.</p>";
    } else {
        echo "<p>No hay plazos faltantes. Todos los plazos referenciados en opcion_precios existen en plazos_entrega.</p>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 