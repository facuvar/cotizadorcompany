<?php
/**
 * Script para normalizar los valores de orden en las tablas categorias y opciones
 * Corrige valores duplicados, nulos o inconsistentes
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
    
    echo "<h2>Normalizando valores de orden...</h2>";
    
    // Normalizar categorías
    echo "<h3>Normalizando categorías:</h3>";
    $categorias = $conn->query("SELECT id, nombre, orden FROM categorias ORDER BY COALESCE(orden, 0), nombre");
    $orden = 1;
    while ($cat = $categorias->fetch_assoc()) {
        $conn->query("UPDATE categorias SET orden = $orden WHERE id = " . $cat['id']);
        echo "<p>- {$cat['nombre']}: orden actualizado a $orden (era: " . ($cat['orden'] ?? 'NULL') . ")</p>";
        $orden++;
    }
    
    // Normalizar opciones por categoría
    echo "<h3>Normalizando opciones:</h3>";
    $categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY orden");
    while ($cat = $categorias->fetch_assoc()) {
        echo "<p><strong>Categoría: {$cat['nombre']}</strong></p>";
        $opciones = $conn->query("SELECT id, nombre, orden FROM opciones WHERE categoria_id = " . $cat['id'] . " ORDER BY COALESCE(orden, 0), nombre");
        $orden = 1;
        while ($opcion = $opciones->fetch_assoc()) {
            $conn->query("UPDATE opciones SET orden = $orden WHERE id = " . $opcion['id']);
            echo "<p>  - {$opcion['nombre']}: orden actualizado a $orden (era: " . ($opcion['orden'] ?? 'NULL') . ")</p>";
            $orden++;
        }
    }
    
    // Verificar resultados
    echo "<h3>Verificación final:</h3>";
    
    // Verificar categorías
    $result = $conn->query("SELECT COUNT(*) as total, COUNT(DISTINCT orden) as unicos FROM categorias");
    $stats = $result->fetch_assoc();
    echo "<p>Categorías: {$stats['total']} total, {$stats['unicos']} órdenes únicos</p>";
    
    // Verificar opciones por categoría
    $categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY orden");
    while ($cat = $categorias->fetch_assoc()) {
        $result = $conn->query("SELECT COUNT(*) as total, COUNT(DISTINCT orden) as unicos FROM opciones WHERE categoria_id = " . $cat['id']);
        $stats = $result->fetch_assoc();
        echo "<p>Opciones en {$cat['nombre']}: {$stats['total']} total, {$stats['unicos']} órdenes únicos</p>";
    }
    
    echo "<h3>✅ Normalización completada exitosamente</h3>";
    echo "<p><a href='admin/gestionar_datos.php'>Ir al panel de administración</a></p>";
    echo "<p><a href='cotizador_ordenado.php'>Ver cotizador ordenado</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante la normalización</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 