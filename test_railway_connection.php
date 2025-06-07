<?php
/**
 * Test de Conexión a Railway MySQL
 * Verifica que las credenciales funcionen correctamente
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Conexión Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Test de Conexión Railway</h1>";

// Tus credenciales específicas de Railway
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'dmlTgjGinHTObFPvTZZGrfbxXopMCAmv';
$name = 'railway';
$port = 3306;

echo "<div class='info'>📡 Probando conexión con tus credenciales...</div>";
echo "<table>";
echo "<tr><th>Parámetro</th><th>Valor</th></tr>";
echo "<tr><td>Host</td><td>{$host}</td></tr>";
echo "<tr><td>Puerto</td><td>{$port}</td></tr>";
echo "<tr><td>Usuario</td><td>{$user}</td></tr>";
echo "<tr><td>Contraseña</td><td>" . str_repeat('*', strlen($pass)) . "</td></tr>";
echo "<tr><td>Base de datos</td><td>{$name}</td></tr>";
echo "</table>";

try {
    // Test 1: Conexión básica
    echo "<h3>🔌 Test 1: Conexión Básica</h3>";
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", 
        $user, 
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]
    );
    echo "<div class='success'>✅ Conexión exitosa a Railway MySQL</div>";
    
    // Test 2: Información del servidor
    echo "<h3>📊 Test 2: Información del Servidor</h3>";
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as database_name, USER() as current_user");
    $info = $stmt->fetch();
    
    echo "<table>";
    echo "<tr><th>Información</th><th>Valor</th></tr>";
    echo "<tr><td>Versión MySQL</td><td>{$info['version']}</td></tr>";
    echo "<tr><td>Base de datos actual</td><td>{$info['database_name']}</td></tr>";
    echo "<tr><td>Usuario actual</td><td>{$info['current_user']}</td></tr>";
    echo "</table>";
    
    // Test 3: Listar tablas existentes
    echo "<h3>🗂️ Test 3: Tablas Existentes</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div class='info'>ℹ️ No hay tablas en la base de datos. Necesitas ejecutar la instalación.</div>";
        echo "<p><a href='install_railway_db.php' class='btn'>🚀 Instalar Base de Datos</a></p>";
    } else {
        echo "<div class='success'>✅ Tablas encontradas:</div>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
        
        // Test 4: Verificar datos en tablas principales
        if (in_array('categorias', $tables) && in_array('opciones', $tables)) {
            echo "<h3>📋 Test 4: Datos en Tablas</h3>";
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
            $categorias = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM opciones");
            $opciones = $stmt->fetch()['total'];
            
            echo "<table>";
            echo "<tr><th>Tabla</th><th>Registros</th></tr>";
            echo "<tr><td>categorias</td><td>{$categorias}</td></tr>";
            echo "<tr><td>opciones</td><td>{$opciones}</td></tr>";
            echo "</table>";
            
            if ($categorias > 0 && $opciones > 0) {
                echo "<div class='success'>✅ Base de datos configurada y con datos</div>";
                echo "<p><a href='cotizador.php' class='btn'>🧮 Ir al Cotizador</a></p>";
            } else {
                echo "<div class='info'>ℹ️ Tablas creadas pero sin datos. Ejecuta la instalación completa.</div>";
                echo "<p><a href='install_railway_db.php' class='btn'>🚀 Instalar Datos</a></p>";
            }
        }
    }
    
    // Test 5: Verificar configuración del sistema
    echo "<h3>⚙️ Test 5: Configuración del Sistema</h3>";
    require_once 'sistema/config.php';
    
    echo "<table>";
    echo "<tr><th>Constante</th><th>Valor</th></tr>";
    echo "<tr><td>DB_HOST</td><td>" . DB_HOST . "</td></tr>";
    echo "<tr><td>DB_USER</td><td>" . DB_USER . "</td></tr>";
    echo "<tr><td>DB_NAME</td><td>" . DB_NAME . "</td></tr>";
    echo "<tr><td>DB_PORT</td><td>" . DB_PORT . "</td></tr>";
    echo "<tr><td>IS_RAILWAY</td><td>" . (IS_RAILWAY ? 'SÍ' : 'NO') . "</td></tr>";
    echo "<tr><td>BASE_URL</td><td>" . BASE_URL . "</td></tr>";
    echo "</table>";
    
    echo "<div class='success'>🎉 ¡Todo funciona correctamente!</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    
    // Sugerencias de solución
    echo "<h3>🔧 Posibles Soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que el servicio MySQL esté activo en Railway</li>";
    echo "<li>Confirma que las credenciales sean correctas</li>";
    echo "<li>Asegúrate de que las variables de entorno estén configuradas</li>";
    echo "<li>Verifica que no haya restricciones de firewall</li>";
    echo "</ul>";
    
    echo "<div class='info'>💡 Si el problema persiste, contacta al soporte de Railway</div>";
}

echo "    </div>
</body>
</html>";
?> 