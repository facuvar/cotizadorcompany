<?php
// Instalación rápida para Railway
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'dmlTgjGinHTObFPvTZZGrfbxXopMCAmv';
$name = 'railway';
$port = 3306;

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass);
    echo "✅ Conexión exitosa<br>";
    
    // Crear tablas básicas
    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS opciones (id INT AUTO_INCREMENT PRIMARY KEY, categoria_id INT, nombre VARCHAR(255), precio DECIMAL(10,2))");
    
    echo "✅ Tablas creadas<br>";
    echo "<a href='cotizador.php'>Ir al Cotizador</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
