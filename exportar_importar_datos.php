<?php
/**
 * Script para exportar datos de la base de datos local e importarlos a Railway
 * Genera un archivo SQL con los datos de categorías, opciones y plazos
 */

echo "<h1>🔄 Sincronización de Datos entre Local y Railway</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; overflow-x: auto; max-height: 400px; overflow-y: auto; }
    .btn { background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
    .btn:hover { background: #45a049; }
    textarea { width: 100%; height: 300px; font-family: monospace; margin: 10px 0; }
</style>";

echo "<div class='container'>";

// Incluir configuración
require_once 'sistema/config.php';

// Acción a realizar
$action = $_GET['action'] ?? '';

// Función para escapar valores SQL
function escapeSqlValue($value) {
    if ($value === null) return "NULL";
    if (is_numeric($value)) return $value;
    return "'" . str_replace("'", "''", $value) . "'";
}

// Función para generar la sentencia INSERT
function generateInsertStatement($table, $data) {
    if (empty($data)) return "";
    
    $columns = array_keys($data[0]);
    $sql = "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES\n";
    
    $rows = [];
    foreach ($data as $row) {
        $values = [];
        foreach ($columns as $column) {
            $values[] = escapeSqlValue($row[$column] ?? null);
        }
        $rows[] = "(" . implode(", ", $values) . ")";
    }
    
    $sql .= implode(",\n", $rows) . ";\n\n";
    return $sql;
}

try {
    // Conectar a la base de datos local
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos local: " . DB_NAME . "</div>";
    
    if ($action === 'export') {
        // Exportar datos
        echo "<h2>📤 Exportando datos de la base de datos local</h2>";
        
        // 1. Exportar categorías
        $stmt = $pdo->query("SELECT * FROM categorias");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>ℹ️ Se encontraron " . count($categorias) . " categorías</div>";
        
        // 2. Exportar plazos de entrega
        $stmt = $pdo->query("SELECT * FROM plazos_entrega");
        $plazos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>ℹ️ Se encontraron " . count($plazos) . " plazos de entrega</div>";
        
        // 3. Exportar opciones
        $stmt = $pdo->query("SELECT * FROM opciones");
        $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>ℹ️ Se encontraron " . count($opciones) . " opciones</div>";
        
        // 4. Exportar configuración
        $stmt = $pdo->query("SELECT * FROM configuracion");
        $configuracion = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>ℹ️ Se encontraron " . count($configuracion) . " registros de configuración</div>";
        
        // Generar SQL
        $sql = "-- Script de sincronización de datos generado el " . date('Y-m-d H:i:s') . "\n\n";
        
        // Limpiar tablas existentes
        $sql .= "-- Limpiar tablas existentes\n";
        $sql .= "DELETE FROM opciones;\n";
        $sql .= "DELETE FROM categorias;\n";
        $sql .= "DELETE FROM plazos_entrega;\n";
        $sql .= "DELETE FROM configuracion;\n\n";
        
        // Restablecer AUTO_INCREMENT
        $sql .= "-- Restablecer AUTO_INCREMENT\n";
        $sql .= "ALTER TABLE categorias AUTO_INCREMENT = 1;\n";
        $sql .= "ALTER TABLE opciones AUTO_INCREMENT = 1;\n";
        $sql .= "ALTER TABLE plazos_entrega AUTO_INCREMENT = 1;\n";
        $sql .= "ALTER TABLE configuracion AUTO_INCREMENT = 1;\n\n";
        
        // Insertar datos
        $sql .= "-- Insertar categorías\n";
        $sql .= generateInsertStatement('categorias', $categorias);
        
        $sql .= "-- Insertar plazos de entrega\n";
        $sql .= generateInsertStatement('plazos_entrega', $plazos);
        
        $sql .= "-- Insertar opciones\n";
        $sql .= generateInsertStatement('opciones', $opciones);
        
        $sql .= "-- Insertar configuración\n";
        $sql .= generateInsertStatement('configuracion', $configuracion);
        
        // Guardar SQL en archivo
        $filename = 'datos_para_railway_' . date('Ymd_His') . '.sql';
        file_put_contents($filename, $sql);
        
        echo "<div class='success'>✅ Datos exportados exitosamente a $filename</div>";
        echo "<div class='code'>" . htmlspecialchars($sql) . "</div>";
        
        echo "<div class='info'>
            Para importar estos datos en Railway:
            <ol>
                <li>Copia el SQL generado arriba</li>
                <li>Accede a la herramienta de actualización manual en Railway: <code>https://tu-app-railway.up.railway.app/actualizar_railway_manual.php</code></li>
                <li>Introduce la clave de acceso: <code>company2024</code></li>
                <li>Ve a la pestaña 'Base de Datos'</li>
                <li>Pega este SQL en el área de texto y ejecuta</li>
            </ol>
        </div>";
        
        echo "<a href='actualizar_railway_manual.php?clave=company2024&sql_file=$filename' class='btn'>Ir a la herramienta de actualización manual</a>";
        
    } else {
        // Mostrar form para exportar
        echo "<h2>🔄 Sincronización de Datos</h2>";
        echo "<div class='info'>Este script te permite exportar los datos de tu base de datos local para importarlos en Railway.</div>";
        
        echo "<p>¿Qué deseas hacer?</p>";
        echo "<a href='?action=export' class='btn'>Exportar datos para Railway</a>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div>";
?> 