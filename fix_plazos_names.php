<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Correspondencia entre nombres
    $plazosCorrespondencia = [
        '90 dias' => '90 días',
        '160/180 dias' => '160-180 días',
        '270 dias' => '270 días'
    ];
    
    // Mostrar plazos actuales
    echo "<h1>Plazos de entrega registrados</h1>";
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Orden</th></tr>";
        
        $plazosExistentes = [];
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
            echo "</tr>";
            
            $plazosExistentes[$row['nombre']] = $row['id'];
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay plazos de entrega registrados.</p>";
        $plazosExistentes = [];
    }
    
    // Paso 1: Agregar plazos faltantes
    echo "<h2>Agregando plazos faltantes</h2>";
    
    $plazosNecesarios = [
        '90 dias' => 1,
        '160/180 dias' => 2, 
        '270 dias' => 3
    ];
    
    foreach ($plazosNecesarios as $plazo => $orden) {
        // Verificar si ya existe por nombre exacto
        $query = "SELECT id FROM plazos_entrega WHERE nombre = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $plazo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Verificar si existe con el nombre corregido
            $nombreCorregido = isset($plazosCorrespondencia[$plazo]) ? $plazosCorrespondencia[$plazo] : $plazo;
            $plazoId = isset($plazosExistentes[$nombreCorregido]) ? $plazosExistentes[$nombreCorregido] : null;
            
            if ($plazoId) {
                echo "<p>El plazo '$plazo' corresponde a '$nombreCorregido' que ya existe con ID: $plazoId</p>";
            } else {
                // Insertar el plazo
                $query = "INSERT INTO plazos_entrega (nombre, orden) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $plazo, $orden);
                $stmt->execute();
                
                $plazoId = $conn->insert_id;
                
                echo "<p>Plazo '$plazo' agregado con ID: $plazoId</p>";
                $plazosExistentes[$plazo] = $plazoId;
            }
        } else {
            $plazoId = $result->fetch_assoc()['id'];
            echo "<p>El plazo '$plazo' ya existe con ID: $plazoId</p>";
        }
    }
    
    // Paso 2: Crear un mapa de IDs para cada tipo de plazo
    $plazoIdMap = [];
    foreach ($plazosNecesarios as $plazo => $orden) {
        // Buscar ID directo
        $query = "SELECT id FROM plazos_entrega WHERE nombre = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $plazo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $plazoIdMap[$plazo] = $result->fetch_assoc()['id'];
        } else {
            // Buscar ID del nombre corregido
            $nombreCorregido = isset($plazosCorrespondencia[$plazo]) ? $plazosCorrespondencia[$plazo] : $plazo;
            $query = "SELECT id FROM plazos_entrega WHERE nombre = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $nombreCorregido);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $plazoIdMap[$plazo] = $result->fetch_assoc()['id'];
            } else {
                echo "<p>ERROR: No se pudo encontrar el ID para el plazo '$plazo'</p>";
            }
        }
    }
    
    echo "<h2>Mapa de IDs de plazos</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Nombre del plazo</th><th>ID asignado</th></tr>";
    foreach ($plazoIdMap as $nombre => $id) {
        echo "<tr><td>$nombre</td><td>$id</td></tr>";
    }
    echo "</table>";
    
    // Actualizar el script de importación de GIRACOCHES
    echo "<h2>Instrucciones de actualización</h2>";
    echo "<p>Para resolver el error de claves foráneas, asegúrate de que el archivo sistema/admin/import_giracoches.php esté utilizando estos IDs:</p>";
    echo "<ul>";
    foreach ($plazoIdMap as $nombre => $id) {
        echo "<li>Para el plazo '$nombre': usar ID $id</li>";
    }
    echo "</ul>";
    
    echo "<p>Cuando sincronices los datos, asegúrate de que los IDs de plazos sean correctos o que los nombres sean exactamente iguales a los de la base de datos.</p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 