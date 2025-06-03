<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener categoría GIRACOCHES
    $result = $conn->query('SELECT id FROM categorias WHERE nombre="GIRACOCHES"');
    
    if (!$result || $result->num_rows === 0) {
        echo "<p>No se encontró la categoría GIRACOCHES</p>";
        exit;
    }
    
    $categoria = $result->fetch_assoc();
    $categoriaId = $categoria['id'];
    
    // Obtener modelos
    $result = $conn->query("SELECT * FROM opciones WHERE categoria_id = $categoriaId");
    
    echo "<h1>Modelos GIRACOCHES importados</h1>";
    echo "<p>Total de modelos: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>ID: " . $row['id'] . " - Nombre: " . $row['nombre'] . "</li>";
            
            // Obtener precios para este modelo
            $preciosResult = $conn->query("SELECT op.*, pe.nombre AS plazo_nombre 
                                          FROM opcion_precios op 
                                          LEFT JOIN plazos_entrega pe ON op.plazo_id = pe.id
                                          WHERE op.opcion_id = " . $row['id']);
            
            if ($preciosResult && $preciosResult->num_rows > 0) {
                echo "<ul>";
                while ($precio = $preciosResult->fetch_assoc()) {
                    echo "<li>Plazo: " . $precio['plazo_entrega'] . 
                         " (ID: " . $precio['plazo_id'] . ") - " .
                         "Precio: $" . number_format($precio['precio'], 2, ',', '.') . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No hay precios registrados para este modelo</p>";
            }
        }
        echo "</ul>";
    }
    
    // Verificar la coincidencia de IDs en plazos
    echo "<h2>Verificación de IDs de plazos</h2>";
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    
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
        
        // Verificar IDs en el script
        echo "<h3>IDs utilizados en los scripts</h3>";
        echo "<p>Los IDs actuales en import_giracoches.php y update_giracoches_model.php son:</p>";
        echo "<ul>";
        echo "<li>'90 dias' => 60</li>";
        echo "<li>'160/180 dias' => 62</li>";
        echo "<li>'270 dias' => 61</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 