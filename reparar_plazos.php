<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Reparación de Plazos de Entrega</h1>";
    
    // 1. Verificar el estado actual de la tabla plazos_entrega
    echo "<h2>Estado Actual de Plazos</h2>";
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    
    if (!$result || $result->num_rows === 0) {
        echo "<p>No hay plazos de entrega registrados en la base de datos.</p>";
        
        // Si no hay plazos, vamos a crearlos
        echo "<h3>Creando plazos básicos...</h3>";
        
        $plazosBasicos = [
            ['nombre' => '90 dias', 'orden' => 1],
            ['nombre' => '90 días', 'orden' => 1],
            ['nombre' => '160/180 dias', 'orden' => 2],
            ['nombre' => '160-180 días', 'orden' => 2],
            ['nombre' => '270 dias', 'orden' => 3],
            ['nombre' => '270 días', 'orden' => 3]
        ];
        
        foreach ($plazosBasicos as $plazo) {
            $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, orden) VALUES (?, ?)");
            $stmt->bind_param("si", $plazo['nombre'], $plazo['orden']);
            
            if ($stmt->execute()) {
                echo "<p>✅ Plazo '{$plazo['nombre']}' creado con ID: {$conn->insert_id}</p>";
            } else {
                echo "<p>❌ Error al crear plazo '{$plazo['nombre']}': {$stmt->error}</p>";
            }
        }
        
        // Verificar de nuevo
        $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id");
    }
    
    // Mostrar plazos existentes
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Orden</th></tr>";
        
        $plazosExistentes = [];
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['orden']}</td>";
            echo "</tr>";
            
            $plazosExistentes[$row['nombre']] = $row['id'];
        }
        
        echo "</table>";
        
        // 2. Verificar qué plazos se están utilizando en opcion_precios
        echo "<h2>Plazos Referenciados en opcion_precios</h2>";
        $result = $conn->query("SELECT DISTINCT plazo_id, plazo_entrega FROM opcion_precios ORDER BY plazo_id");
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID Plazo</th><th>Nombre Plazo</th><th>¿Existe en plazos_entrega?</th></tr>";
            
            $plazosProblematicos = [];
            while ($row = $result->fetch_assoc()) {
                $existe = $conn->query("SELECT id FROM plazos_entrega WHERE id = {$row['plazo_id']}")->num_rows > 0;
                
                echo "<tr>";
                echo "<td>{$row['plazo_id']}</td>";
                echo "<td>{$row['plazo_entrega']}</td>";
                echo "<td>" . ($existe ? "✅ Sí" : "❌ No") . "</td>";
                echo "</tr>";
                
                if (!$existe) {
                    $plazosProblematicos[$row['plazo_id']] = $row['plazo_entrega'];
                }
            }
            
            echo "</table>";
            
            // 3. Reparar plazos problemáticos
            if (!empty($plazosProblematicos)) {
                echo "<h2>Reparación de Plazos Problemáticos</h2>";
                
                foreach ($plazosProblematicos as $id => $nombre) {
                    echo "<h3>Reparando plazo ID {$id}: {$nombre}</h3>";
                    
                    // Buscar si existe un plazo con nombre similar
                    $nombreSimilar = null;
                    $idSimilar = null;
                    
                    foreach ($plazosExistentes as $nombreExistente => $idExistente) {
                        $nombreNormalizado = strtolower(str_replace(['días', 'dias', '-', '/'], '', $nombreExistente));
                        $nombreProblematico = strtolower(str_replace(['días', 'dias', '-', '/'], '', $nombre));
                        
                        if ($nombreNormalizado == $nombreProblematico) {
                            $nombreSimilar = $nombreExistente;
                            $idSimilar = $idExistente;
                            break;
                        }
                    }
                    
                    if ($nombreSimilar) {
                        echo "<p>Encontrado plazo similar: '{$nombreSimilar}' (ID: {$idSimilar})</p>";
                        
                        // Actualizar referencias en opcion_precios
                        $stmt = $conn->prepare("UPDATE opcion_precios SET plazo_id = ? WHERE plazo_id = ?");
                        $stmt->bind_param("ii", $idSimilar, $id);
                        
                        if ($stmt->execute()) {
                            echo "<p>✅ Referencias actualizadas correctamente</p>";
                        } else {
                            echo "<p>❌ Error al actualizar referencias: {$stmt->error}</p>";
                        }
                    } else {
                        // Crear un nuevo plazo con el ID correcto
                        echo "<p>No se encontró un plazo similar. Creando uno nuevo...</p>";
                        
                        // Determinar el orden según el nombre
                        $orden = 0;
                        if (strpos($nombre, '90') !== false) {
                            $orden = 1;
                        } elseif (strpos($nombre, '160') !== false || strpos($nombre, '180') !== false) {
                            $orden = 2;
                        } elseif (strpos($nombre, '270') !== false) {
                            $orden = 3;
                        }
                        
                        // Intentar crear el plazo con el ID específico
                        $stmt = $conn->prepare("INSERT INTO plazos_entrega (id, nombre, orden) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $id, $nombre, $orden);
                        
                        if ($stmt->execute()) {
                            echo "<p>✅ Plazo creado con ID forzado: {$id}</p>";
                        } else {
                            echo "<p>❌ Error al crear plazo con ID forzado: {$stmt->error}</p>";
                            
                            // Intentar una alternativa: crear un nuevo plazo y actualizar referencias
                            $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, orden) VALUES (?, ?)");
                            $stmt->bind_param("si", $nombre, $orden);
                            
                            if ($stmt->execute()) {
                                $nuevoId = $conn->insert_id;
                                echo "<p>✅ Plazo creado con nuevo ID: {$nuevoId}</p>";
                                
                                // Actualizar referencias
                                $stmt = $conn->prepare("UPDATE opcion_precios SET plazo_id = ? WHERE plazo_id = ?");
                                $stmt->bind_param("ii", $nuevoId, $id);
                                
                                if ($stmt->execute()) {
                                    echo "<p>✅ Referencias actualizadas al nuevo ID</p>";
                                } else {
                                    echo "<p>❌ Error al actualizar referencias: {$stmt->error}</p>";
                                }
                            } else {
                                echo "<p>❌ Error al crear plazo alternativo: {$stmt->error}</p>";
                            }
                        }
                    }
                }
            }
        } else {
            echo "<p>No hay plazos referenciados en opcion_precios.</p>";
        }
        
        // 4. Instrucciones para actualizar los scripts
        echo "<h2>Actualización de Scripts</h2>";
        
        // Obtener IDs actuales de los plazos principales
        $plazoIds = [
            '90 dias' => null,
            '160/180 dias' => null,
            '270 dias' => null
        ];
        
        foreach ($plazoIds as $nombre => $id) {
            // Búsqueda exacta
            $stmt = $conn->prepare("SELECT id FROM plazos_entrega WHERE nombre = ?");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $plazoIds[$nombre] = $result->fetch_assoc()['id'];
            } else {
                // Búsqueda aproximada
                $nombreSimplificado = strtolower(preg_replace('/[^0-9]/', '', $nombre));
                $query = "SELECT id, nombre FROM plazos_entrega WHERE nombre LIKE '%{$nombreSimplificado}%'";
                $result = $conn->query($query);
                
                if ($result->num_rows > 0) {
                    $plazoEncontrado = $result->fetch_assoc();
                    $plazoIds[$nombre] = $plazoEncontrado['id'];
                }
            }
        }
        
        echo "<p>Actualiza los siguientes archivos con estos IDs:</p>";
        echo "<ul>";
        echo "<li>sistema/admin/import_giracoches.php</li>";
        echo "<li>sistema/admin/update_giracoches_model.php</li>";
        echo "</ul>";
        
        echo "<p>Cambia el mapa de IDs a:</p>";
        echo "<pre>";
        echo "\$plazosIdMap = [\n";
        foreach ($plazoIds as $nombre => $id) {
            echo "    '{$nombre}' => " . ($id !== null ? $id : "null") . ",\n";
        }
        echo "];\n";
        echo "</pre>";
        
        if (in_array(null, $plazoIds)) {
            echo "<p style='color: red;'>⚠️ Algunos plazos no se encontraron. Verifica los IDs manualmente.</p>";
        }
        
        // 5. Ofrecer reparación automática
        echo "<h2>Reparación Automática de Scripts</h2>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='repair_scripts' value='1'>";
        echo "<button type='submit' style='padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer;'>Actualizar Automáticamente los Scripts</button>";
        echo "</form>";
        
        // Procesar la actualización automática
        if (isset($_POST['repair_scripts'])) {
            $archivos = [
                'sistema/admin/import_giracoches.php',
                'sistema/admin/update_giracoches_model.php'
            ];
            
            $nuevosIds = [];
            foreach ($plazoIds as $nombre => $id) {
                if ($id !== null) {
                    $nuevosIds[$nombre] = $id;
                }
            }
            
            if (empty($nuevosIds)) {
                echo "<p style='color: red;'>No se pudieron determinar los IDs correctos para actualizar los scripts.</p>";
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
                            echo "<p>✅ Archivo {$archivo} actualizado correctamente</p>";
                        } else {
                            echo "<p>❌ No se pudo actualizar el archivo {$archivo}. Patrón no encontrado.</p>";
                        }
                    } else {
                        echo "<p>❌ El archivo {$archivo} no existe</p>";
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 