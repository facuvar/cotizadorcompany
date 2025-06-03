<?php
echo "<h1>🚀 Configuración y Prueba del Sistema de Presupuestos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #2196F3; }
</style>";

// Paso 1: Verificar configuración
echo "<div class='step'>";
echo "<h2>📋 Paso 1: Verificando Configuración</h2>";

require_once 'sistema/config.php';

echo "<div class='info'>✓ Archivo de configuración cargado correctamente</div>";
echo "<div class='info'>✓ Base de datos configurada: " . DB_NAME . "</div>";
echo "<div class='info'>✓ Host: " . DB_HOST . "</div>";
echo "<div class='info'>✓ Usuario: " . DB_USER . "</div>";
echo "</div>";

// Paso 2: Verificar conexión MySQL
echo "<div class='step'>";
echo "<h2>🔌 Paso 2: Verificando Conexión a MySQL</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✓ Conexión a MySQL exitosa</div>";
    
    // Verificar si la base de datos existe
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    
    if ($result->num_rows == 0) {
        echo "<div class='warning'>⚠ La base de datos '" . DB_NAME . "' no existe. Creando...</div>";
        
        if ($conn->query("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
            echo "<div class='success'>✓ Base de datos '" . DB_NAME . "' creada exitosamente</div>";
        } else {
            throw new Exception("Error al crear la base de datos: " . $conn->error);
        }
    } else {
        echo "<div class='success'>✓ La base de datos '" . DB_NAME . "' ya existe</div>";
    }
    
    // Seleccionar la base de datos
    $conn->select_db(DB_NAME);
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>🔧 Solución: Asegúrate de que XAMPP esté ejecutándose y MySQL esté activo</div>";
    exit;
}
echo "</div>";

// Paso 3: Crear tablas
echo "<div class='step'>";
echo "<h2>🗄️ Paso 3: Configurando Base de Datos</h2>";

try {
    // Leer el esquema SQL
    $sqlFile = 'sistema/db_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo de esquema SQL no encontrado: " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Ejecutar las consultas SQL
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "<div class='success'>✓ Esquema de base de datos aplicado correctamente</div>";
    } else {
        echo "<div class='warning'>⚠ Algunas tablas ya pueden existir: " . $conn->error . "</div>";
    }
    
    // Verificar tablas creadas
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<div class='info'>📊 Tablas en la base de datos: " . implode(', ', $tables) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error configurando base de datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Paso 4: Verificar directorios
echo "<div class='step'>";
echo "<h2>📁 Paso 4: Verificando Directorios</h2>";

$directories = [
    'sistema/uploads',
    'sistema/uploads/xls',
    'presupuestos',
    'sistema/temp'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='success'>✓ Directorio creado: $dir</div>";
        } else {
            echo "<div class='error'>❌ Error creando directorio: $dir</div>";
        }
    } else {
        echo "<div class='info'>✓ Directorio existe: $dir</div>";
    }
    
    // Verificar permisos
    if (is_writable($dir)) {
        echo "<div class='success'>✓ Permisos de escritura OK: $dir</div>";
    } else {
        echo "<div class='warning'>⚠ Sin permisos de escritura: $dir</div>";
    }
}
echo "</div>";

// Paso 5: Enlaces de prueba
echo "<div class='step'>";
echo "<h2>🔗 Paso 5: Enlaces de Prueba</h2>";

$baseUrl = "http://localhost/company-presupuestos-online-2";

echo "<div class='info'>";
echo "<h3>🎯 Prueba el Sistema:</h3>";
echo "<p><strong>Página Principal:</strong> <a href='$baseUrl/index.html' target='_blank'>$baseUrl/index.html</a></p>";
echo "<p><strong>Sistema Principal:</strong> <a href='$baseUrl/sistema/index.php' target='_blank'>$baseUrl/sistema/index.php</a></p>";
echo "<p><strong>Cotizador:</strong> <a href='$baseUrl/sistema/cotizador.php' target='_blank'>$baseUrl/sistema/cotizador.php</a></p>";
echo "<p><strong>Panel Admin:</strong> <a href='$baseUrl/admin/index.php' target='_blank'>$baseUrl/admin/index.php</a></p>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>🔑 Credenciales de Administrador:</h3>";
echo "<p><strong>Usuario:</strong> admin</p>";
echo "<p><strong>Contraseña:</strong> admin123</p>";
echo "</div>";
echo "</div>";

// Paso 6: Datos de prueba
echo "<div class='step'>";
echo "<h2>📊 Paso 6: Datos de Prueba</h2>";

try {
    // Verificar si hay datos
    $result = $conn->query("SELECT COUNT(*) as total FROM categorias");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<div class='warning'>⚠ No hay datos de prueba. Necesitas importar datos desde el panel de administración.</div>";
        echo "<div class='info'>💡 Ve al panel de administración y usa la función de importar para cargar datos desde Excel o Google Sheets.</div>";
    } else {
        echo "<div class='success'>✓ Hay {$row['total']} categorías en el sistema</div>";
        
        // Mostrar algunas categorías
        $result = $conn->query("SELECT nombre FROM categorias LIMIT 5");
        echo "<div class='info'>📋 Categorías disponibles: ";
        $cats = [];
        while ($row = $result->fetch_assoc()) {
            $cats[] = $row['nombre'];
        }
        echo implode(', ', $cats);
        if (count($cats) == 5) echo "...";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h2>🎉 ¡Configuración Completada!</h2>";
echo "<div class='success'>";
echo "<h3>✅ El sistema está listo para usar</h3>";
echo "<p>1. Haz clic en los enlaces de arriba para probar el sistema</p>";
echo "<p>2. Si no hay datos, ve al panel de administración para importarlos</p>";
echo "<p>3. Usa el cotizador para generar presupuestos de prueba</p>";
echo "</div>";
echo "</div>";

$conn->close();
?> 