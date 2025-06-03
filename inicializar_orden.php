<?php
/**
 * Script para inicializar los campos de orden en las tablas categorias y opciones
 * Ejecutar una sola vez para configurar el ordenamiento inicial
 */

// Cargar configuración
$configPath = __DIR__ . '/sistema/config.php';
if (!file_exists($configPath)) {
    die("Error: Archivo de configuración no encontrado");
}
require_once $configPath;

// Cargar DB
$dbPath = __DIR__ . '/sistema/includes/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h2>Inicializando campos de orden...</h2>";
    
    // Verificar si el campo orden existe en categorias
    $result = $conn->query("SHOW COLUMNS FROM categorias LIKE 'orden'");
    if ($result->num_rows == 0) {
        echo "<p>Agregando campo 'orden' a tabla categorias...</p>";
        $conn->query("ALTER TABLE categorias ADD COLUMN orden INT DEFAULT 0");
    } else {
        echo "<p>✓ Campo 'orden' ya existe en tabla categorias</p>";
    }
    
    // Verificar si el campo orden existe en opciones
    $result = $conn->query("SHOW COLUMNS FROM opciones LIKE 'orden'");
    if ($result->num_rows == 0) {
        echo "<p>Agregando campo 'orden' a tabla opciones...</p>";
        $conn->query("ALTER TABLE opciones ADD COLUMN orden INT DEFAULT 0");
    } else {
        echo "<p>✓ Campo 'orden' ya existe en tabla opciones</p>";
    }
    
    // Inicializar orden en categorías
    echo "<p>Inicializando orden en categorías...</p>";
    $categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
    $orden = 1;
    while ($cat = $categorias->fetch_assoc()) {
        $conn->query("UPDATE categorias SET orden = $orden WHERE id = " . $cat['id']);
        echo "<p>- {$cat['nombre']}: orden $orden</p>";
        $orden++;
    }
    
    // Inicializar orden en opciones por categoría
    echo "<p>Inicializando orden en opciones...</p>";
    $categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY orden");
    while ($cat = $categorias->fetch_assoc()) {
        echo "<p><strong>Categoría: {$cat['nombre']}</strong></p>";
        $opciones = $conn->query("SELECT id, nombre FROM opciones WHERE categoria_id = " . $cat['id'] . " ORDER BY nombre");
        $orden = 1;
        while ($opcion = $opciones->fetch_assoc()) {
            $conn->query("UPDATE opciones SET orden = $orden WHERE id = " . $opcion['id']);
            echo "<p>  - {$opcion['nombre']}: orden $orden</p>";
            $orden++;
        }
    }
    
    echo "<h3>✅ Inicialización completada exitosamente</h3>";
    echo "<p><a href='admin/gestionar_datos.php'>Ir al panel de administración</a></p>";
    echo "<p><a href='cotizador_ordenado.php'>Ver cotizador ordenado</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante la inicialización</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 