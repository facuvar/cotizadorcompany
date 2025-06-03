<?php
require_once 'sistema/config.php';

echo "🔍 VERIFICACIÓN RÁPIDA DE DATOS\n\n";

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS
    );
    
    echo "✅ Conexión exitosa\n\n";
    
    // Verificar categorías
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM categorias');
    $categorias = $stmt->fetch()['total'];
    echo "📂 Categorías: $categorias\n";
    
    // Verificar opciones
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM opciones');
    $opciones = $stmt->fetch()['total'];
    echo "🔧 Opciones: $opciones\n\n";
    
    if ($opciones > 0) {
        echo "📋 Detalle por categoría:\n";
        $stmt = $pdo->query('
            SELECT c.nombre, COUNT(o.id) as total 
            FROM categorias c 
            LEFT JOIN opciones o ON c.id = o.categoria_id 
            GROUP BY c.id 
            ORDER BY c.orden
        ');
        
        while ($row = $stmt->fetch()) {
            echo "- {$row['nombre']}: {$row['total']} opciones\n";
        }
        
        echo "\n🎯 Primeras 5 opciones:\n";
        $stmt = $pdo->query('
            SELECT o.nombre, c.nombre as categoria, o.precio_90_dias, o.precio_160_dias, o.precio_270_dias
            FROM opciones o 
            JOIN categorias c ON o.categoria_id = c.id 
            ORDER BY c.orden, o.orden 
            LIMIT 5
        ');
        
        while ($row = $stmt->fetch()) {
            echo "- {$row['categoria']}: {$row['nombre']} (90d: {$row['precio_90_dias']}, 160d: {$row['precio_160_dias']}, 270d: {$row['precio_270_dias']})\n";
        }
    } else {
        echo "❌ No hay opciones en la base de datos\n";
        echo "💡 Ejecuta: import_excel_fixed.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 