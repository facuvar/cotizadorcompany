<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Limpieza de Referencias Huérfanas</h1>";
    
    // Buscar registros en presupuesto_detalles que referencian opciones inexistentes
    $result = $conn->query("
        SELECT pd.*, o.id as opcion_existe
        FROM presupuesto_detalles pd 
        LEFT JOIN opciones o ON pd.opcion_id = o.id 
        WHERE o.id IS NULL
    ");
    
    $huerfanos = [];
    while ($row = $result->fetch_assoc()) {
        $huerfanos[] = $row;
    }
    
    if (count($huerfanos) > 0) {
        echo "<p style='color: orange;'>Se encontraron " . count($huerfanos) . " registros huérfanos:</p>";
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Presupuesto ID</th><th>Opción ID (inexistente)</th><th>Precio</th></tr>";
        foreach ($huerfanos as $huerfano) {
            echo "<tr>";
            echo "<td>" . $huerfano['id'] . "</td>";
            echo "<td>" . $huerfano['presupuesto_id'] . "</td>";
            echo "<td>" . $huerfano['opcion_id'] . "</td>";
            echo "<td>" . $huerfano['precio'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (isset($_POST['limpiar'])) {
            // Eliminar registros huérfanos
            $conn->query("
                DELETE pd FROM presupuesto_detalles pd 
                LEFT JOIN opciones o ON pd.opcion_id = o.id 
                WHERE o.id IS NULL
            ");
            
            echo "<p style='color: green;'>✅ Se eliminaron " . count($huerfanos) . " registros huérfanos.</p>";
            echo "<p><a href='admin/gestionar_datos.php'>Volver a Gestionar Datos</a></p>";
        } else {
            echo "<form method='POST'>";
            echo "<p><button type='submit' name='limpiar' class='btn' style='background-color: #f44336; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;'>Eliminar Registros Huérfanos</button></p>";
            echo "</form>";
        }
    } else {
        echo "<p style='color: green;'>✅ No se encontraron registros huérfanos.</p>";
        echo "<p><a href='admin/gestionar_datos.php'>Volver a Gestionar Datos</a></p>";
    }
    
    // Mostrar estadísticas generales
    echo "<h2>Estadísticas</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM presupuesto_detalles");
    $total_detalles = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM opciones");
    $total_opciones = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM presupuestos");
    $total_presupuestos = $result->fetch_assoc()['total'];
    
    echo "<ul>";
    echo "<li>Total de presupuestos: $total_presupuestos</li>";
    echo "<li>Total de opciones: $total_opciones</li>";
    echo "<li>Total de detalles de presupuestos: $total_detalles</li>";
    echo "<li>Registros huérfanos: " . count($huerfanos) . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 