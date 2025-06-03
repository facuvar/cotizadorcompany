<?php
// Script para corregir la tabla de presupuestos
require_once 'sistema/config.php';

// Conectar directamente a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h1>Corrección de la tabla presupuestos</h1>";

// Verificar si la tabla existe
$result = $conn->query("SHOW TABLES LIKE 'presupuestos'");
if ($result->num_rows > 0) {
    echo "<p>La tabla presupuestos existe. Verificando estructura...</p>";
    
    // Verificar la estructura actual
    $result = $conn->query("DESCRIBE presupuestos");
    $columnas = [];
    while ($row = $result->fetch_assoc()) {
        $columnas[$row['Field']] = $row['Type'];
    }
    
    echo "<h2>Estructura actual:</h2>";
    echo "<ul>";
    foreach ($columnas as $campo => $tipo) {
        echo "<li>$campo - $tipo</li>";
    }
    echo "</ul>";
    
    // Eliminar la tabla y volver a crearla
    echo "<p>Recreando la tabla para asegurar la estructura correcta...</p>";
    
    $conn->query("DROP TABLE presupuestos");
    
    $createTableSQL = "CREATE TABLE presupuestos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        producto_id INT NOT NULL,
        producto_nombre VARCHAR(100) NOT NULL,
        opcion_id INT NOT NULL,
        opcion_nombre VARCHAR(100) NOT NULL,
        plazo_id INT NOT NULL,
        plazo_nombre VARCHAR(50) NOT NULL,
        forma_pago VARCHAR(100) NOT NULL,
        descuento DECIMAL(10,2) NOT NULL,
        adicionales TEXT,
        subtotal DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL)) {
        echo "<p>Tabla presupuestos recreada correctamente con la estructura adecuada.</p>";
    } else {
        echo "<p>Error al recrear la tabla: " . $conn->error . "</p>";
    }
} else {
    echo "<p>La tabla presupuestos no existe. Creando tabla...</p>";
    
    // Crear la tabla
    $createTableSQL = "CREATE TABLE presupuestos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        producto_id INT NOT NULL,
        producto_nombre VARCHAR(100) NOT NULL,
        opcion_id INT NOT NULL,
        opcion_nombre VARCHAR(100) NOT NULL,
        plazo_id INT NOT NULL,
        plazo_nombre VARCHAR(50) NOT NULL,
        forma_pago VARCHAR(100) NOT NULL,
        descuento DECIMAL(10,2) NOT NULL,
        adicionales TEXT,
        subtotal DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL)) {
        echo "<p>Tabla presupuestos creada correctamente.</p>";
    } else {
        echo "<p>Error al crear la tabla: " . $conn->error . "</p>";
    }
}

// Verificar que la tabla ahora tenga la estructura correcta
$result = $conn->query("DESCRIBE presupuestos");
echo "<h2>Estructura final:</h2>";
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
}
echo "</ul>";

echo "<p><a href='cotizador_con_pago.php'>Volver al cotizador</a></p>";

$conn->close();
?>
