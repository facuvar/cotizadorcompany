<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Inserción de Plazo Faltante</h1>";
    
    // Verificar si ya existe el plazo '160/180 dias'
    $stmt = $conn->prepare("SELECT id FROM plazos_entrega WHERE nombre = ?");
    $nombre = '160/180 dias';
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $plazo = $result->fetch_assoc();
        echo "<p>El plazo '{$nombre}' ya existe con ID: {$plazo['id']}</p>";
    } else {
        // Insertar el plazo faltante
        $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, orden) VALUES (?, ?)");
        $orden = 2;
        $stmt->bind_param("si", $nombre, $orden);
        
        if ($stmt->execute()) {
            $nuevoId = $conn->insert_id;
            echo "<p style='color: green;'>✅ Plazo '{$nombre}' insertado con ID: {$nuevoId}</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al insertar el plazo: {$stmt->error}</p>";
        }
    }
    
    // Mostrar todos los plazos
    echo "<h2>Plazos de entrega actuales</h2>";
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Orden</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['orden']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Obtener los IDs para el mapa
    $plazos = ['90 dias', '160/180 dias', '270 dias'];
    $plazosIds = [];
    
    foreach ($plazos as $nombre) {
        $stmt = $conn->prepare("SELECT id FROM plazos_entrega WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $plazosIds[$nombre] = $result->fetch_assoc()['id'];
        } else {
            $plazosIds[$nombre] = null;
        }
    }
    
    echo "<h2>Mapa de IDs para los scripts</h2>";
    echo "<pre>";
    echo "\$plazosIdMap = [\n";
    foreach ($plazosIds as $nombre => $id) {
        echo "    '{$nombre}' => " . ($id !== null ? $id : "null") . ",\n";
    }
    echo "];\n";
    echo "</pre>";
    
    // Actualizar los scripts automáticamente
    echo "<h2>Actualización de Scripts</h2>";
    
    if (isset($_POST['update_scripts'])) {
        $archivos = [
            'sistema/admin/import_giracoches.php',
            'sistema/admin/update_giracoches_model.php'
        ];
        
        $nuevosIds = [];
        foreach ($plazosIds as $nombre => $id) {
            if ($id !== null) {
                $nuevosIds[$nombre] = $id;
            }
        }
        
        if (empty($nuevosIds) || count($nuevosIds) < 3) {
            echo "<p style='color: red;'>❌ No se pudieron determinar todos los IDs necesarios.</p>";
        } else {
            foreach ($archivos as $archivo) {
                if (file_exists($archivo)) {
                    $contenido = file_get_contents($archivo);
                    
                    // Buscar el patrón del mapa de IDs
                    $pattern = '/\$plazosIdMap\s*=\s*\[\s*[\'"]90 dias[\'"].*?\];/s';
                    
                    $replacement = "\$plazosIdMap = [\n";
                    foreach ($nuevosIds as $nombre => $id) {
                        $replacement .= "        '{$nombre}' => {$id},\n";
                    }
                    $replacement .= "    ];";
                    
                    $nuevoContenido = preg_replace($pattern, $replacement, $contenido);
                    
                    if ($nuevoContenido !== $contenido) {
                        file_put_contents($archivo, $nuevoContenido);
                        echo "<p style='color: green;'>✅ Archivo {$archivo} actualizado correctamente</p>";
                    } else {
                        echo "<p style='color: red;'>❌ No se pudo actualizar el archivo {$archivo}. Patrón no encontrado.</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ El archivo {$archivo} no existe</p>";
                }
            }
        }
    }
    
    echo "<form method='post'>";
    echo "<button type='submit' name='update_scripts' style='padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer;'>Actualizar Scripts con IDs Correctos</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 