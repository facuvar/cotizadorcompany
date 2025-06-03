<?php
/**
 * Script de diagnóstico específico para el cotizador
 */

echo "<h1>🔍 DIAGNÓSTICO DEL COTIZADOR</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Paso 1: Verificar config.php
echo "<h2>📋 Paso 1: Verificar config.php</h2>";
try {
    if (file_exists('sistema/config.php')) {
        echo "<div class='success'>✅ sistema/config.php existe</div>";
        
        require_once 'sistema/config.php';
        echo "<div class='success'>✅ config.php cargado correctamente</div>";
        
        echo "<div class='info'>";
        echo "<strong>Configuración detectada:</strong><br>";
        echo "• Entorno: " . (defined('IS_RAILWAY') && IS_RAILWAY ? 'Railway' : 'Local') . "<br>";
        echo "• DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'No definido') . "<br>";
        echo "• DB_USER: " . (defined('DB_USER') ? DB_USER : 'No definido') . "<br>";
        echo "• DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'No definido') . "<br>";
        echo "• BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'No definido') . "<br>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ sistema/config.php no existe</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando config.php: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 2: Verificar db.php
echo "<h2>🗄️ Paso 2: Verificar db.php</h2>";
try {
    if (file_exists('sistema/includes/db.php')) {
        echo "<div class='success'>✅ sistema/includes/db.php existe</div>";
        
        require_once 'sistema/includes/db.php';
        echo "<div class='success'>✅ db.php cargado correctamente</div>";
        
        // Probar conexión Database
        $db = Database::getInstance();
        $conn = $db->getConnection();
        echo "<div class='success'>✅ Conexión Database establecida</div>";
        
        // Probar una consulta simple
        $result = $conn->query("SELECT COUNT(*) as total FROM categorias");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<div class='success'>✅ Consulta de prueba exitosa - Categorías: " . $row['total'] . "</div>";
        } else {
            echo "<div class='error'>❌ Error en consulta de prueba: " . $conn->error . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ sistema/includes/db.php no existe</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error con db.php: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 3: Verificar functions.php
echo "<h2>⚙️ Paso 3: Verificar functions.php</h2>";
try {
    if (file_exists('sistema/includes/functions.php')) {
        echo "<div class='success'>✅ sistema/includes/functions.php existe</div>";
        
        require_once 'sistema/includes/functions.php';
        echo "<div class='success'>✅ functions.php cargado correctamente</div>";
        
        // Probar función redirect
        if (function_exists('redirect')) {
            echo "<div class='success'>✅ Función redirect() disponible</div>";
        } else {
            echo "<div class='error'>❌ Función redirect() no disponible</div>";
        }
        
    } else {
        echo "<div class='error'>❌ sistema/includes/functions.php no existe</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error con functions.php: " . $e->getMessage() . "</div>";
    exit;
}

// Paso 4: Verificar tabla fuente_datos
echo "<h2>📊 Paso 4: Verificar tabla fuente_datos</h2>";
try {
    $query = "SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='success'>✅ Tabla fuente_datos tiene datos</div>";
        echo "<div class='info'>";
        echo "<strong>Último registro:</strong><br>";
        echo "• ID: " . $row['id'] . "<br>";
        echo "• Fecha: " . $row['fecha_actualizacion'] . "<br>";
        echo "• Archivo: " . ($row['archivo_origen'] ?? 'No especificado') . "<br>";
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ Tabla fuente_datos está vacía</div>";
        echo "<div class='info'>Esto causará que el cotizador redirija a la página principal</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando fuente_datos: " . $e->getMessage() . "</div>";
}

// Paso 5: Verificar plazos_entrega
echo "<h2>⏰ Paso 5: Verificar plazos_entrega</h2>";
try {
    $query = "SELECT * FROM plazos_entrega ORDER BY orden ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Tabla plazos_entrega tiene " . $result->num_rows . " registros</div>";
        echo "<div class='info'><strong>Plazos disponibles:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "• " . $row['nombre'] . " (orden: " . $row['orden'] . ")<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Tabla plazos_entrega está vacía</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando plazos_entrega: " . $e->getMessage() . "</div>";
}

// Paso 6: Verificar categorías
echo "<h2>📂 Paso 6: Verificar categorías</h2>";
try {
    $query = "SELECT * FROM categorias ORDER BY orden ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Tabla categorias tiene " . $result->num_rows . " registros</div>";
        echo "<div class='info'><strong>Categorías disponibles:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "• " . $row['nombre'] . " (ID: " . $row['id'] . ", orden: " . $row['orden'] . ")<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Tabla categorias está vacía</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando categorias: " . $e->getMessage() . "</div>";
}

// Paso 7: Simular el inicio del cotizador
echo "<h2>🧪 Paso 7: Simular inicio del cotizador</h2>";
try {
    // Simular el código del cotizador.php
    echo "<div class='info'>Simulando el código del cotizador...</div>";
    
    // Verificar si hay datos cargados
    $query = "SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC LIMIT 1";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        echo "<div class='warning'>⚠️ El cotizador redirigirá porque no hay datos en fuente_datos</div>";
        echo "<div class='info'>URL de redirección: " . (defined('SITE_URL') ? SITE_URL : 'SITE_URL no definido') . "</div>";
    } else {
        echo "<div class='success'>✅ El cotizador puede continuar normalmente</div>";
    }
    
    // Obtener plazos de entrega
    $query = "SELECT * FROM plazos_entrega ORDER BY orden ASC";
    $plazosResult = $conn->query($query);
    $plazos = [];
    
    if ($plazosResult && $plazosResult->num_rows > 0) {
        while ($plazo = $plazosResult->fetch_assoc()) {
            $plazos[] = $plazo;
        }
        echo "<div class='success'>✅ Plazos cargados: " . count($plazos) . "</div>";
    } else {
        echo "<div class='error'>❌ No se pudieron cargar los plazos</div>";
    }
    
    // Plazo por defecto
    $plazoSeleccionado = isset($plazos[0]) ? $plazos[0]['nombre'] : '160-180 días';
    echo "<div class='info'>Plazo por defecto: " . $plazoSeleccionado . "</div>";
    
    // Obtener categorías
    $query = "SELECT * FROM categorias ORDER BY orden ASC";
    $categorias = $conn->query($query);
    
    if ($categorias && $categorias->num_rows > 0) {
        echo "<div class='success'>✅ Categorías cargadas: " . $categorias->num_rows . "</div>";
    } else {
        echo "<div class='error'>❌ No se pudieron cargar las categorías</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error simulando cotizador: " . $e->getMessage() . "</div>";
}

echo "<h2>🔗 Enlaces de prueba</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank'>🧪 Probar cotizador real</a><br>";
echo "<a href='admin/' target='_blank'>👤 Probar panel admin</a><br>";
echo "<a href='verify_config.php' target='_blank'>✅ Verificación completa</a><br>";
echo "</div>";

echo "</div>";
?> 