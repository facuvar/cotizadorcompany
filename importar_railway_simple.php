<?php
/**
 * IMPORTADOR SIMPLE PARA RAILWAY
 * Copia y pega este código en Railway como importar_desde_sql.php
 */

// Configuración Railway
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'autorack.proxy.rlwy.net',
    'port' => $_ENV['DB_PORT'] ?? '47470', 
    'database' => $_ENV['DB_NAME'] ?? 'railway',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

echo "<h1>🚀 Importador Railway</h1>";

try {
    // Conectar
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p>✅ Conectado a Railway</p>";

    // Buscar archivos SQL
    $sql_files = glob('*.sql');
    
    if (empty($sql_files)) {
        echo "<p>❌ No se encontraron archivos .sql</p>";
        echo "<p>Sube tu archivo cotizador_datos_XXXX.sql al directorio raíz</p>";
    } else {
        $sql_file = $sql_files[0];
        echo "<p>📁 Archivo encontrado: {$sql_file}</p>";
        
        // Leer y ejecutar SQL
        $sql_content = file_get_contents($sql_file);
        $statements = array_filter(explode(';', $sql_content));
        
        echo "<p>🔄 Ejecutando " . count($statements) . " statements...</p>";
        
        foreach ($statements as $statement) {
            if (trim($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Verificar
        $categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
        $opciones = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
        
        echo "<p>✅ Importación exitosa!</p>";
        echo "<p>📊 {$categorias} categorías, {$opciones} opciones</p>";
        echo "<p>🎉 <a href='cotizador.php'>Ir al Cotizador</a></p>";
        
        // Limpiar archivo
        unlink($sql_file);
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?> 