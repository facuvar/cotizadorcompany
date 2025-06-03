<?php
/**
 * Script para diagnosticar y corregir el problema de categorías vacías en Railway
 */

echo "<h1>🔧 DIAGNÓSTICO Y CORRECCIÓN DE CATEGORÍAS</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; }
    .btn:hover { background: #45a049; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

echo "<div class='container'>";

// Paso 1: Cargar configuración
echo "<h2>📋 Paso 1: Cargar configuración</h2>";

try {
    require_once 'sistema/config.php';
    echo "<div class='success'>✅ Configuración cargada correctamente</div>";
    
    echo "<div class='info'>";
    echo "<strong>Configuración detectada:</strong><br>";
    echo "• IS_RAILWAY: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'true' : 'false') . "<br>";
    echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
    echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
    echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando configuración: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 2: Conectar a la base de datos
echo "<h2>🔌 Paso 2: Conectar a la base de datos</h2>";

try {
    require_once 'sistema/includes/db.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn && !$conn->connect_error) {
        echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
        echo "<div class='info'>Versión del servidor: " . $conn->server_info . "</div>";
    } else {
        echo "<div class='error'>❌ Error de conexión: " . ($conn ? $conn->connect_error : 'Conexión nula') . "</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error conectando: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 3: Verificar estructura de tablas
echo "<h2>📊 Paso 3: Verificar estructura de tablas</h2>";

$tablas = ['categorias', 'opciones', 'plazos_entrega'];
$tablasExistentes = [];

foreach ($tablas as $tabla) {
    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Tabla '$tabla' existe</div>";
        $tablasExistentes[] = $tabla;
        
        // Contar registros
        $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tabla`");
        if ($countResult) {
            $count = $countResult->fetch_assoc()['count'];
            echo "<div class='info'>📊 Tabla '$tabla': $count registros</div>";
        }
    } else {
        echo "<div class='error'>❌ Tabla '$tabla' no existe</div>";
    }
}

// Paso 4: Mostrar contenido actual de categorías
echo "<h2>📋 Paso 4: Contenido actual de categorías</h2>";

if (in_array('categorias', $tablasExistentes)) {
    $result = $conn->query("SELECT * FROM categorias ORDER BY orden ASC, id ASC");
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Categorías encontradas: " . $result->num_rows . "</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['descripcion'] ?? '') . "</td>";
            echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No hay categorías en la base de datos</div>";
        
        // Botón para crear categorías
        echo "<form method='post' style='margin: 20px 0;'>";
        echo "<button type='submit' name='crear_categorias' class='btn'>🚀 Crear Categorías Básicas</button>";
        echo "</form>";
    }
}

// Paso 5: Procesar creación de categorías
if (isset($_POST['crear_categorias'])) {
    echo "<h2>🚀 Paso 5: Creando categorías básicas</h2>";
    
    try {
        // Limpiar tabla categorías
        $conn->query("DELETE FROM categorias");
        echo "<div class='info'>🧹 Tabla categorías limpiada</div>";
        
        // Categorías básicas para el cotizador
        $categorias = [
            ['nombre' => 'ASCENSORES ELECTROMECÁNICOS', 'descripcion' => 'Ascensores con motor eléctrico para edificios residenciales y comerciales', 'orden' => 1],
            ['nombre' => 'ASCENSORES HIDRÁULICOS', 'descripcion' => 'Ascensores con sistema hidráulico para edificios de baja altura', 'orden' => 2],
            ['nombre' => 'GIRACOCHES', 'descripcion' => 'Plataformas giratorias para vehículos', 'orden' => 3],
            ['nombre' => 'Opciones Adicionales', 'descripcion' => 'Accesorios y opciones adicionales para ascensores', 'orden' => 4],
            ['nombre' => 'Formas de Pago', 'descripcion' => 'Descuentos disponibles según forma de pago', 'orden' => 5]
        ];
        
        foreach ($categorias as $cat) {
            $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $cat['nombre'], $cat['descripcion'], $cat['orden']);
            
            if ($stmt->execute()) {
                $categoriaId = $conn->insert_id;
                echo "<div class='success'>✅ Categoría creada: " . $cat['nombre'] . " (ID: $categoriaId)</div>";
            } else {
                echo "<div class='error'>❌ Error creando categoría " . $cat['nombre'] . ": " . $stmt->error . "</div>";
            }
        }
        
        echo "<div class='info'>🔄 Recargando página para mostrar cambios...</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creando categorías: " . $e->getMessage() . "</div>";
    }
}

// Paso 6: Crear opciones básicas si hay categorías
if (isset($_POST['crear_opciones'])) {
    echo "<h2>🛠️ Paso 6: Creando opciones básicas</h2>";
    
    try {
        // Limpiar tabla opciones
        $conn->query("DELETE FROM opciones");
        echo "<div class='info'>🧹 Tabla opciones limpiada</div>";
        
        // Obtener IDs de categorías
        $categoriaIds = [];
        $result = $conn->query("SELECT id, nombre FROM categorias");
        while ($row = $result->fetch_assoc()) {
            $categoriaIds[$row['nombre']] = $row['id'];
        }
        
        // Opciones para ASCENSORES ELECTROMECÁNICOS
        if (isset($categoriaIds['ASCENSORES ELECTROMECÁNICOS'])) {
            $opciones = [
                ['nombre' => 'Ascensor 4 personas - 4 paradas', 'descripcion' => 'Ascensor electromecánico para 4 personas, 4 paradas', 'precio' => 2500000.00, 'orden' => 1],
                ['nombre' => 'Ascensor 6 personas - 4 paradas', 'descripcion' => 'Ascensor electromecánico para 6 personas, 4 paradas', 'precio' => 3000000.00, 'orden' => 2],
                ['nombre' => 'Ascensor 4 personas - 6 paradas', 'descripcion' => 'Ascensor electromecánico para 4 personas, 6 paradas', 'precio' => 3200000.00, 'orden' => 3],
                ['nombre' => 'Ascensor 6 personas - 6 paradas', 'descripcion' => 'Ascensor electromecánico para 6 personas, 6 paradas', 'precio' => 3800000.00, 'orden' => 4]
            ];
            
            foreach ($opciones as $opcion) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['ASCENSORES ELECTROMECÁNICOS'], $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Opción creada: " . $opcion['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando opción: " . $stmt->error . "</div>";
                }
            }
        }
        
        // Opciones para ASCENSORES HIDRÁULICOS
        if (isset($categoriaIds['ASCENSORES HIDRÁULICOS'])) {
            $opciones = [
                ['nombre' => 'Hidráulico 450kg - 3 paradas', 'descripcion' => 'Ascensor hidráulico 450kg, 3 paradas', 'precio' => 2200000.00, 'orden' => 1],
                ['nombre' => 'Hidráulico 630kg - 3 paradas', 'descripcion' => 'Ascensor hidráulico 630kg, 3 paradas', 'precio' => 2600000.00, 'orden' => 2],
                ['nombre' => 'Hidráulico 450kg - 4 paradas', 'descripcion' => 'Ascensor hidráulico 450kg, 4 paradas', 'precio' => 2800000.00, 'orden' => 3]
            ];
            
            foreach ($opciones as $opcion) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['ASCENSORES HIDRÁULICOS'], $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Opción creada: " . $opcion['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando opción: " . $stmt->error . "</div>";
                }
            }
        }
        
        // Opciones para GIRACOCHES
        if (isset($categoriaIds['GIRACOCHES'])) {
            $opciones = [
                ['nombre' => 'Giracoches 2000kg', 'descripcion' => 'Plataforma giratoria para vehículos hasta 2000kg', 'precio' => 1800000.00, 'orden' => 1],
                ['nombre' => 'Giracoches 3000kg', 'descripcion' => 'Plataforma giratoria para vehículos hasta 3000kg', 'precio' => 2200000.00, 'orden' => 2]
            ];
            
            foreach ($opciones as $opcion) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['GIRACOCHES'], $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Opción creada: " . $opcion['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando opción: " . $stmt->error . "</div>";
                }
            }
        }
        
        // Opciones adicionales
        if (isset($categoriaIds['Opciones Adicionales'])) {
            $opciones = [
                ['nombre' => 'Puertas automáticas', 'descripcion' => 'Sistema de puertas automáticas', 'precio' => 150000.00, 'orden' => 1],
                ['nombre' => 'Espejo en cabina', 'descripcion' => 'Espejo decorativo en cabina', 'precio' => 25000.00, 'orden' => 2],
                ['nombre' => 'Iluminación LED', 'descripcion' => 'Sistema de iluminación LED', 'precio' => 80000.00, 'orden' => 3]
            ];
            
            foreach ($opciones as $opcion) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['Opciones Adicionales'], $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Opción adicional creada: " . $opcion['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando opción adicional: " . $stmt->error . "</div>";
                }
            }
        }
        
        // Formas de pago
        if (isset($categoriaIds['Formas de Pago'])) {
            $opciones = [
                ['nombre' => 'Contado (10% descuento)', 'descripcion' => 'Pago al contado con 10% de descuento', 'precio' => -10.00, 'orden' => 1],
                ['nombre' => 'Financiado 12 meses', 'descripcion' => 'Financiación a 12 meses sin interés', 'precio' => 0.00, 'orden' => 2],
                ['nombre' => 'Financiado 24 meses', 'descripcion' => 'Financiación a 24 meses con 5% de recargo', 'precio' => 5.00, 'orden' => 3]
            ];
            
            foreach ($opciones as $opcion) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['Formas de Pago'], $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Forma de pago creada: " . $opcion['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando forma de pago: " . $stmt->error . "</div>";
                }
            }
        }
        
        echo "<div class='info'>🔄 Recargando página para mostrar cambios...</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creando opciones: " . $e->getMessage() . "</div>";
    }
}

// Mostrar botón para crear opciones si hay categorías pero no opciones
if (in_array('categorias', $tablasExistentes) && in_array('opciones', $tablasExistentes)) {
    $categoriaCount = $conn->query("SELECT COUNT(*) as count FROM categorias")->fetch_assoc()['count'];
    $opcionCount = $conn->query("SELECT COUNT(*) as count FROM opciones")->fetch_assoc()['count'];
    
    if ($categoriaCount > 0 && $opcionCount == 0) {
        echo "<h2>🛠️ Crear opciones básicas</h2>";
        echo "<div class='warning'>⚠️ Hay categorías pero no hay opciones. Es necesario crear opciones para que el cotizador funcione.</div>";
        echo "<form method='post' style='margin: 20px 0;'>";
        echo "<button type='submit' name='crear_opciones' class='btn'>🛠️ Crear Opciones Básicas</button>";
        echo "</form>";
    }
}

// Paso 7: Verificar plazos de entrega
echo "<h2>⏰ Paso 7: Verificar plazos de entrega</h2>";

if (in_array('plazos_entrega', $tablasExistentes)) {
    $result = $conn->query("SELECT * FROM plazos_entrega ORDER BY id ASC");
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Plazos de entrega encontrados: " . $result->num_rows . "</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Multiplicador</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . ($row['multiplicador'] ?? '1.00') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No hay plazos de entrega configurados</div>";
        
        // Crear plazos básicos
        echo "<form method='post' style='margin: 20px 0;'>";
        echo "<button type='submit' name='crear_plazos' class='btn'>⏰ Crear Plazos Básicos</button>";
        echo "</form>";
    }
}

// Procesar creación de plazos
if (isset($_POST['crear_plazos'])) {
    echo "<h2>⏰ Creando plazos básicos</h2>";
    
    try {
        $conn->query("DELETE FROM plazos_entrega");
        echo "<div class='info'>🧹 Tabla plazos_entrega limpiada</div>";
        
        $plazos = [
            ['nombre' => '90 días', 'multiplicador' => 1.15],
            ['nombre' => '160-180 días', 'multiplicador' => 1.00],
            ['nombre' => '270 días', 'multiplicador' => 0.85]
        ];
        
        foreach ($plazos as $plazo) {
            $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, multiplicador) VALUES (?, ?)");
            $stmt->bind_param('sd', $plazo['nombre'], $plazo['multiplicador']);
            
            if ($stmt->execute()) {
                echo "<div class='success'>✅ Plazo creado: " . $plazo['nombre'] . "</div>";
            } else {
                echo "<div class='error'>❌ Error creando plazo: " . $stmt->error . "</div>";
            }
        }
        
        echo "<div class='info'>🔄 Recargando página para mostrar cambios...</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error creando plazos: " . $e->getMessage() . "</div>";
    }
}

// Paso 8: Enlaces de prueba
echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<strong>Prueba el cotizador:</strong><br>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Ir al Cotizador</a><br>";
echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>🔐 Panel Admin</a><br>";
echo "<a href='railway_debug.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico Railway</a><br>";
echo "</div>";

echo "</div>";
?> 