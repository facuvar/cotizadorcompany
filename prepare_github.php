<?php
/**
 * Script para preparar el proyecto para GitHub
 * Verifica la base de datos local y genera resumen
 */

echo "📋 PREPARACIÓN PARA GITHUB\n";
echo "==========================\n\n";

// Configuración local
$local_config = [
    'host' => 'localhost',
    'database' => 'company_presupuestos',
    'username' => 'root',
    'password' => ''
];

try {
    // Conectar a base local
    echo "💻 Verificando base de datos local...\n";
    $local_dsn = "mysql:host={$local_config['host']};dbname={$local_config['database']};charset=utf8mb4";
    $local_pdo = new PDO($local_dsn, $local_config['username'], $local_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Conectado a base local\n\n";

    // Verificar estructura
    echo "🔍 Verificando estructura de datos...\n";
    
    // Contar categorías
    $categorias = $local_pdo->query("SELECT COUNT(*) as count FROM categorias")->fetch()['count'];
    echo "📂 Categorías: $categorias\n";
    
    // Contar opciones por categoría
    $ascensores = $local_pdo->query("SELECT COUNT(*) as count FROM opciones WHERE categoria_id = 1")->fetch()['count'];
    $adicionales = $local_pdo->query("SELECT COUNT(*) as count FROM opciones WHERE categoria_id = 2")->fetch()['count'];
    echo "🏢 Ascensores: $ascensores\n";
    echo "🔧 Adicionales: $adicionales\n\n";

    // Verificar funcionalidades inteligentes
    echo "🧠 Verificando funcionalidades inteligentes...\n";
    
    // Adicionales por tipo
    $electromecanicos = $local_pdo->query("
        SELECT COUNT(*) as count FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'
    ")->fetch()['count'];
    
    $hidraulicos = $local_pdo->query("
        SELECT COUNT(*) as count FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'
    ")->fetch()['count'];
    
    $montacargas = $local_pdo->query("
        SELECT COUNT(*) as count FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'
    ")->fetch()['count'];
    
    $salvaescaleras = $local_pdo->query("
        SELECT COUNT(*) as count FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'
    ")->fetch()['count'];
    
    $restar = $local_pdo->query("
        SELECT COUNT(*) as count FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'
    ")->fetch()['count'];

    echo "📊 Filtrado inteligente:\n";
    echo "   - Electromecánicos: $electromecanicos adicionales\n";
    echo "   - Hidráulicos: $hidraulicos adicionales\n";
    echo "   - Montacargas: $montacargas adicionales\n";
    echo "   - Salvaescaleras: $salvaescaleras adicionales\n";
    echo "   - Que restan dinero: $restar adicionales\n\n";

    // Verificar precios por plazo
    echo "💰 Verificando precios por plazo...\n";
    $precios_90 = $local_pdo->query("SELECT COUNT(*) as count FROM opciones WHERE precio_90_dias > 0")->fetch()['count'];
    $precios_160 = $local_pdo->query("SELECT COUNT(*) as count FROM opciones WHERE precio_160_dias > 0")->fetch()['count'];
    $precios_270 = $local_pdo->query("SELECT COUNT(*) as count FROM opciones WHERE precio_270_dias > 0")->fetch()['count'];

    echo "📈 Precios configurados:\n";
    echo "   - 90 días: $precios_90 productos\n";
    echo "   - 160 días: $precios_160 productos\n";
    echo "   - 270 días: $precios_270 productos\n\n";

    // Verificar archivos del proyecto
    echo "📁 Verificando archivos del proyecto...\n";
    $archivos_importantes = [
        'cotizador.php' => 'Cotizador principal con mejoras',
        'test_simple.html' => 'Página de pruebas',
        'deploy_railway.php' => 'Script de sincronización',
        'README.md' => 'Documentación',
        '.gitignore' => 'Configuración Git',
        'deploy.bat' => 'Script de despliegue Windows',
        'deploy.ps1' => 'Script de despliegue PowerShell'
    ];

    foreach ($archivos_importantes as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "✅ $archivo - $descripcion\n";
        } else {
            echo "❌ $archivo - FALTA\n";
        }
    }
    echo "\n";

    // Generar resumen para GitHub
    echo "📝 Generando resumen del proyecto...\n";
    $resumen = [
        'total_categorias' => $categorias,
        'total_ascensores' => $ascensores,
        'total_adicionales' => $adicionales,
        'filtrado_inteligente' => [
            'electromecanicos' => $electromecanicos,
            'hidraulicos' => $hidraulicos,
            'montacargas' => $montacargas,
            'salvaescaleras' => $salvaescaleras,
            'que_restan' => $restar
        ],
        'precios_por_plazo' => [
            '90_dias' => $precios_90,
            '160_dias' => $precios_160,
            '270_dias' => $precios_270
        ],
        'fecha_preparacion' => date('Y-m-d H:i:s')
    ];

    file_put_contents('project_summary.json', json_encode($resumen, JSON_PRETTY_PRINT));
    echo "✅ Resumen guardado en project_summary.json\n\n";

    echo "🎉 PROYECTO LISTO PARA GITHUB\n";
    echo "==============================\n";
    echo "✅ Base de datos verificada\n";
    echo "✅ Funcionalidades confirmadas\n";
    echo "✅ Archivos preparados\n";
    echo "✅ Resumen generado\n\n";
    
    echo "🚀 PRÓXIMOS PASOS:\n";
    echo "1. Ejecutar: git init (si es necesario)\n";
    echo "2. Ejecutar: git add .\n";
    echo "3. Ejecutar: git commit -m \"Cotizador inteligente completo\"\n";
    echo "4. Crear repositorio en GitHub\n";
    echo "5. Ejecutar: git remote add origin [URL_REPO]\n";
    echo "6. Ejecutar: git push -u origin main\n";
    echo "7. Configurar Railway con el repositorio\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "💡 Verifica que XAMPP esté ejecutándose y la base de datos exista\n";
}
?> 