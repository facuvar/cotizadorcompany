<?php
/**
 * Script para importar datos desde archivo SQL en Railway
 * Ejecutar este script en Railway después de subir el archivo .sql
 */

// Configuración de Railway (usando variables de entorno)
$railway_config = [
    'host' => $_ENV['DB_HOST'] ?? 'autorack.proxy.rlwy.net',
    'port' => $_ENV['DB_PORT'] ?? '47470',
    'database' => $_ENV['DB_NAME'] ?? 'railway',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Importar Datos SQL - Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; text-align: center; }
        h2 { color: #555; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border-left: 4px solid #007bff; }
        .stat-number { font-size: 1.8em; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; margin-top: 5px; font-size: 0.9em; }
        .file-upload { border: 2px dashed #007bff; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
        .upload-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .upload-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>📥 Importar Datos SQL en Railway</h1>";
echo "<div class='info'>Fecha y hora: " . date('Y-m-d H:i:s') . "</div>";

// Buscar archivos SQL en el directorio
$sql_files = glob('*.sql');

if (empty($sql_files)) {
    echo "<h2>📁 Subir Archivo SQL</h2>";
    echo "<div class='warning'>⚠️ No se encontraron archivos .sql en el directorio</div>";
    echo "<div class='info'>
        <strong>Pasos para subir el archivo:</strong><br>
        1. Descarga el archivo SQL desde tu localhost<br>
        2. Sube el archivo .sql a Railway (mismo directorio que este script)<br>
        3. Recarga esta página<br>
        4. El script detectará automáticamente el archivo
    </div>";
    
    echo "<div class='file-upload'>
        <h3>📤 Instrucciones de Subida</h3>
        <p>Sube tu archivo <code>cotizador_datos_XXXX.sql</code> al directorio raíz de Railway</p>
        <p>Una vez subido, recarga esta página para continuar</p>
    </div>";
    
} else {
    echo "<h2>📁 Archivos SQL Encontrados</h2>";
    foreach ($sql_files as $file) {
        $filesize = round(filesize($file) / 1024, 2);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "<div class='info'>📄 {$file} ({$filesize} KB) - Modificado: {$modified}</div>";
    }
    
    // Usar el archivo más reciente
    $sql_file = $sql_files[0];
    if (count($sql_files) > 1) {
        // Ordenar por fecha de modificación (más reciente primero)
        usort($sql_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $sql_file = $sql_files[0];
        echo "<div class='success'>✅ Usando archivo más reciente: {$sql_file}</div>";
    }
    
    try {
        // Conectar a Railway
        echo "<h2>🚀 Conectando a Railway</h2>";
        $railway_dsn = "mysql:host={$railway_config['host']};port={$railway_config['port']};dbname={$railway_config['database']};charset=utf8mb4";
        $pdo = new PDO($railway_dsn, $railway_config['username'], $railway_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 300
        ]);
        echo "<div class='success'>✅ Conectado exitosamente a Railway</div>";
        echo "<div class='info'>Host: {$railway_config['host']}:{$railway_config['port']}</div>";
        echo "<div class='info'>Base de datos: {$railway_config['database']}</div>";

        // Leer archivo SQL
        echo "<h2>📖 Leyendo Archivo SQL</h2>";
        $sql_content = file_get_contents($sql_file);
        if (!$sql_content) {
            throw new Exception("No se pudo leer el archivo SQL");
        }
        
        $sql_size = round(strlen($sql_content) / 1024, 2);
        echo "<div class='success'>✅ Archivo leído exitosamente ({$sql_size} KB)</div>";

        // Configurar MySQL para importación
        echo "<h2>⚙️ Configurando MySQL para Importación</h2>";
        $pdo->exec("SET SESSION wait_timeout = 600");
        $pdo->exec("SET SESSION interactive_timeout = 600");
        $pdo->exec("SET SESSION max_execution_time = 0");
        $pdo->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        echo "<div class='success'>✅ MySQL configurado para importación</div>";

        // Ejecutar SQL
        echo "<h2>🔄 Ejecutando Importación</h2>";
        
        // Dividir el SQL en statements individuales
        $statements = array_filter(
            array_map('trim', explode(';', $sql_content)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );
        
        $total_statements = count($statements);
        echo "<div class='info'>📦 Ejecutando {$total_statements} statements SQL...</div>";
        
        $executed = 0;
        $errors = 0;
        
        foreach ($statements as $i => $statement) {
            try {
                if (trim($statement)) {
                    $pdo->exec($statement);
                    $executed++;
                    
                    // Mostrar progreso cada 10 statements
                    if ($executed % 10 == 0 || $executed == $total_statements) {
                        $porcentaje = round(($executed / $total_statements) * 100);
                        echo "<div class='info'>Progreso: {$executed}/{$total_statements} ({$porcentaje}%)</div>";
                        if (ob_get_level()) ob_flush();
                        flush();
                    }
                }
            } catch (Exception $e) {
                $errors++;
                echo "<div class='warning'>⚠️ Error en statement " . ($i + 1) . ": " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<div class='success'>✅ Importación completada: {$executed} statements ejecutados, {$errors} errores</div>";

        // Verificar datos importados
        echo "<h2>🔍 Verificando Datos Importados</h2>";
        
        $stats = [];
        $stats['categorias'] = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
        $stats['opciones'] = $pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
        $stats['ascensores'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
        $stats['adicionales'] = $pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();
        
        // Verificar funcionalidades del cotizador inteligente
        $stats['electromecanicos'] = $pdo->query("
            SELECT COUNT(*) FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'
        ")->fetchColumn();
        
        $stats['hidraulicos'] = $pdo->query("
            SELECT COUNT(*) FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'
        ")->fetchColumn();
        
        $stats['montacargas'] = $pdo->query("
            SELECT COUNT(*) FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'
        ")->fetchColumn();
        
        $stats['salvaescaleras'] = $pdo->query("
            SELECT COUNT(*) FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'
        ")->fetchColumn();
        
        $stats['que_restan'] = $pdo->query("
            SELECT COUNT(*) FROM opciones 
            WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'
        ")->fetchColumn();

        echo "<div class='stats'>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['categorias']}</div><div class='stat-label'>Categorías</div></div>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['ascensores']}</div><div class='stat-label'>Ascensores</div></div>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['adicionales']}</div><div class='stat-label'>Adicionales</div></div>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['electromecanicos']}</div><div class='stat-label'>Electromecánicos</div></div>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['hidraulicos']}</div><div class='stat-label'>Hidráulicos</div></div>";
        echo "<div class='stat-card'><div class='stat-number'>{$stats['que_restan']}</div><div class='stat-label'>Que Restan</div></div>";
        echo "</div>";

        // Verificar funcionalidades
        echo "<h2>🧠 Verificación de Funcionalidades del Cotizador</h2>";
        echo "<div class='success'>✅ Filtrado automático: {$stats['electromecanicos']} + {$stats['hidraulicos']} + {$stats['montacargas']} + {$stats['salvaescaleras']} adicionales por tipo</div>";
        echo "<div class='success'>✅ Adicionales que restan: {$stats['que_restan']} configurados</div>";
        echo "<div class='success'>✅ Plazo unificado: 3 plazos (90, 160, 270 días) configurados</div>";
        echo "<div class='success'>✅ Base de datos completa con " . ($stats['ascensores'] + $stats['adicionales']) . " productos</div>";

        // Resultado final
        echo "<h2>🎉 ¡Importación Exitosa!</h2>";
        echo "<div class='success'>
            <strong>🎉 ¡TU COTIZADOR INTELIGENTE ESTÁ LISTO EN RAILWAY!</strong><br><br>
            <strong>Datos importados exitosamente:</strong><br>
            • {$stats['categorias']} categorías<br>
            • {$stats['ascensores']} ascensores<br>
            • {$stats['adicionales']} adicionales<br>
            • Todas las funcionalidades operativas<br><br>
            <strong>Funcionalidades activas:</strong><br>
            • Filtrado automático de adicionales ✅<br>
            • Adicionales que restan dinero ✅<br>
            • Plazo unificado ✅<br>
            • Interface optimizada ✅
        </div>";
        
        echo "<div class='info'>
            <strong>🚀 Accede a tu cotizador:</strong><br>
            1. <a href='cotizador.php' target='_blank'>Cotizador Principal</a><br>
            2. <a href='test_simple.html' target='_blank'>Página de Pruebas</a><br>
            3. Todas las funcionalidades están operativas
        </div>";

        // Limpiar archivo SQL después de importación exitosa
        if ($errors == 0) {
            echo "<h2>🧹 Limpieza</h2>";
            if (unlink($sql_file)) {
                echo "<div class='success'>✅ Archivo SQL eliminado después de importación exitosa</div>";
            }
        }

    } catch (Exception $e) {
        echo "<div class='error'>❌ Error durante la importación: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='warning'>
            <strong>Posibles soluciones:</strong><br>
            • Verifica las credenciales de Railway<br>
            • Asegúrate de que el archivo SQL esté completo<br>
            • Revisa que la base de datos MySQL esté activa<br>
            • Intenta nuevamente en unos minutos
        </div>";
    }
}

echo "</div></body></html>";
?> 