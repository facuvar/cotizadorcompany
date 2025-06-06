<?php
/**
 * Script para agregar la columna ubicacion_obra a la tabla presupuestos
 * Ejecutar una sola vez para actualizar bases de datos existentes
 */

// Cargar configuración
$configPath = __DIR__ . '/sistema/config.php';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado");
}
require_once $configPath;

// Cargar DB
$dbPath = __DIR__ . '/sistema/includes/db.php';
if (!file_exists($dbPath)) {
    die("Error: Archivo de base de datos no encontrado");
}
require_once $dbPath;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "Conectado a la base de datos...\n";
    
    // Verificar si la tabla presupuestos existe
    $table_check = $conn->query("SHOW TABLES LIKE 'presupuestos'");
    
    if ($table_check->num_rows == 0) {
        echo "La tabla 'presupuestos' no existe. No es necesario hacer nada.\n";
        exit;
    }
    
    echo "Tabla 'presupuestos' encontrada.\n";
    
    // Verificar si la columna ubicacion_obra ya existe
    $column_check = $conn->query("SHOW COLUMNS FROM presupuestos LIKE 'ubicacion_obra'");
    
    if ($column_check->num_rows > 0) {
        echo "La columna 'ubicacion_obra' ya existe. No es necesario hacer nada.\n";
        exit;
    }
    
    echo "Agregando columna 'ubicacion_obra'...\n";
    
    // Agregar la columna ubicacion_obra
    $alter_table = "ALTER TABLE presupuestos ADD COLUMN ubicacion_obra TEXT AFTER cliente_empresa";
    
    if ($conn->query($alter_table)) {
        echo "✅ Columna 'ubicacion_obra' agregada exitosamente.\n";
    } else {
        throw new Exception('Error al agregar la columna: ' . $conn->error);
    }
    
    echo "✅ Actualización completada.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 