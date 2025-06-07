<?php
echo "<h1>Test de Conexión Local</h1>";

try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p>✅ Conexión MySQL exitosa</p>";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cotizador_company");
    echo "<p>✅ Base de datos verificada</p>";
    
    // Conectar a la base específica
    $pdo = new PDO("mysql:host=localhost;dbname=cotizador_company;charset=utf8mb4", 'root', '');
    echo "<p>✅ Conexión a cotizador_company exitosa</p>";
    
    // Mostrar tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    echo "<p>Tablas encontradas: " . count($tables) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?> 