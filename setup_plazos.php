<?php
// Script para configurar los plazos de entrega
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Configuración de Plazos de Entrega</h1>";
    
    // Verificar si existe la tabla plazos_entrega
    $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
    
    if ($result->num_rows == 0) {
        mostrarMensaje("La tabla plazos_entrega no existe. Creando tabla...", "warning");
        
        // Crear la tabla
        $sql = "CREATE TABLE plazos_entrega (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            orden INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            mostrarMensaje("Tabla plazos_entrega creada correctamente.", "success");
        } else {
            mostrarMensaje("Error al crear la tabla: " . $conn->error, "error");
        }
    } else {
        mostrarMensaje("La tabla plazos_entrega ya existe.", "info");
    }
    
    // Verificar si hay plazos registrados
    $result = $conn->query("SELECT COUNT(*) as total FROM plazos_entrega");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        mostrarMensaje("No hay plazos registrados. Agregando plazos predeterminados...", "warning");
        
        // Plazos predeterminados
        $plazos = [
            ['nombre' => '30-60 días', 'descripcion' => 'Entrega rápida (30-60 días)', 'orden' => 1],
            ['nombre' => '60-90 días', 'descripcion' => 'Entrega estándar (60-90 días)', 'orden' => 2],
            ['nombre' => '90-120 días', 'descripcion' => 'Entrega normal (90-120 días)', 'orden' => 3],
            ['nombre' => '120-150 días', 'descripcion' => 'Entrega programada (120-150 días)', 'orden' => 4],
            ['nombre' => '150-180 días', 'descripcion' => 'Entrega extendida (150-180 días)', 'orden' => 5],
            ['nombre' => '180-210 días', 'descripcion' => 'Entrega económica (180-210 días)', 'orden' => 6]
        ];
        
        foreach ($plazos as $plazo) {
            $sql = "INSERT INTO plazos_entrega (nombre, descripcion, orden) VALUES (
                '" . $conn->real_escape_string($plazo['nombre']) . "',
                '" . $conn->real_escape_string($plazo['descripcion']) . "',
                " . intval($plazo['orden']) . "
            )";
            
            if ($conn->query($sql)) {
                mostrarMensaje("Plazo '{$plazo['nombre']}' agregado correctamente.", "success");
            } else {
                mostrarMensaje("Error al agregar plazo: " . $conn->error, "error");
            }
        }
    } else {
        mostrarMensaje("Ya existen " . $row['total'] . " plazos registrados.", "info");
    }
    
    // Mostrar plazos existentes
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY orden");
    
    if ($result->num_rows > 0) {
        echo "<h2>Plazos de entrega configurados:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['descripcion'] . "</td>";
            echo "<td>" . $row['orden'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<p><a href='sistema/cotizador.php'>Ir al Cotizador</a></p>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
}
?>
