<?php
echo "🔍 VERIFICANDO CONEXIÓN A BASE DE DATOS\n";
echo "=======================================\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=company_presupuestos', 'root', '');
    echo "✅ Conexión exitosa a la base de datos\n";
    
    $categorias = $pdo->query("SELECT COUNT(*) as count FROM categorias")->fetch()['count'];
    echo "📂 Categorías encontradas: $categorias\n";
    
    $opciones = $pdo->query("SELECT COUNT(*) as count FROM opciones")->fetch()['count'];
    echo "⚙️ Opciones encontradas: $opciones\n";
    
} catch(Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "💡 Verifica que XAMPP esté ejecutándose\n";
}
?> 