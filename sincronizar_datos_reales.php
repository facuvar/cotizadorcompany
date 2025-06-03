<?php
/**
 * Script para sincronizar datos REALES de la base local con Railway
 * Este script toma todos los datos de tu base local y los sube a Railway
 * Versión mejorada con manejo robusto de conexiones
 */

// Configuración local
$local_config = [
    'host' => 'localhost',
    'database' => 'company_presupuestos',
    'username' => 'root',
    'password' => ''
];

// Configuración de Railway
$railway_config = [
    'host' => 'autorack.proxy.rlwy.net',
    'port' => '47470',
    'database' => 'railway',
    'username' => 'root',
    'password' => 'LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA'
];

// Función para conectar a Railway con reintentos
function conectarRailway($config, $reintentos = 3) {
    for ($i = 0; $i < $reintentos; $i++) {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 60,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=300",
                PDO::ATTR_PERSISTENT => false
            ]);
            
            // Verificar conexión
            $pdo->query("SELECT 1");
            return $pdo;
            
        } catch (Exception $e) {
            echo "<div class='warning'>⚠️ Intento " . ($i + 1) . " fallido: " . $e->getMessage() . "</div>";
            if ($i < $reintentos - 1) {
                echo "<div class='info'>🔄 Reintentando en 3 segundos...</div>";
                sleep(3);
            }
        }
    }
    throw new Exception("No se pudo conectar a Railway después de {$reintentos} intentos");
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sincronización de Datos Reales - Railway</title>
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
        .progress { background: #f0f0f0; border-radius: 10px; padding: 3px; margin: 10px 0; }
        .progress-bar { background: #007bff; height: 20px; border-radius: 7px; transition: width 0.3s; }
    </style>
    <script>
        // Auto-refresh cada 2 segundos para mostrar progreso
        let refreshCount = 0;
        function autoRefresh() {
            if (refreshCount < 30) { // Máximo 1 minuto de auto-refresh
                setTimeout(() => {
                    if (document.querySelector('.progress')) {
                        refreshCount++;
                        location.reload();
                    }
                }, 2000);
            }
        }
        window.onload = autoRefresh;
    </script>
</head>
<body>
<div class='container'>";

echo "<h1>🔄 Sincronización de Datos Reales con Railway (v2.0)</h1>";
echo "<div class='info'>Fecha y hora: " . date('Y-m-d H:i:s') . "</div>";

try {
    // Conectar a base local
    echo "<h2>💻 Conexión a Base de Datos Local</h2>";
    $local_dsn = "mysql:host={$local_config['host']};dbname={$local_config['database']};charset=utf8mb4";
    $local_pdo = new PDO($local_dsn, $local_config['username'], $local_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<div class='success'>✅ Conectado a base de datos local</div>";

    // Verificar datos locales
    $local_stats = [];
    $local_stats['categorias'] = $local_pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $local_stats['opciones'] = $local_pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    $local_stats['ascensores'] = $local_pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
    $local_stats['adicionales'] = $local_pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();

    echo "<div class='info'>📊 Datos locales encontrados:</div>";
    echo "<div class='stats'>";
    echo "<div class='stat-card'><div class='stat-number'>{$local_stats['categorias']}</div><div class='stat-label'>Categorías</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$local_stats['ascensores']}</div><div class='stat-label'>Ascensores</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$local_stats['adicionales']}</div><div class='stat-label'>Adicionales</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$local_stats['opciones']}</div><div class='stat-label'>Total Opciones</div></div>";
    echo "</div>";

    // Conectar a Railway con reintentos
    echo "<h2>🚀 Conexión a Railway (Mejorada)</h2>";
    echo "<div class='info'>🔄 Intentando conectar con configuración robusta...</div>";
    $railway_pdo = conectarRailway($railway_config);
    echo "<div class='success'>✅ Conectado exitosamente a Railway</div>";

    // Configurar Railway para operaciones largas
    echo "<h2>⚙️ Configurando Railway para Sincronización</h2>";
    $railway_pdo->exec("SET SESSION wait_timeout = 600");
    $railway_pdo->exec("SET SESSION interactive_timeout = 600");
    $railway_pdo->exec("SET SESSION max_execution_time = 0");
    echo "<div class='success'>✅ Timeouts configurados para operaciones largas</div>";

    // Crear estructura en Railway
    echo "<h2>🔧 Preparando Estructura en Railway</h2>";
    
    // Crear tabla categorias
    $railway_pdo->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            orden INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Crear tabla opciones
    $railway_pdo->exec("
        CREATE TABLE IF NOT EXISTS opciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            categoria_id INT NOT NULL,
            nombre VARCHAR(500) NOT NULL,
            precio_90_dias DECIMAL(10,2) DEFAULT 0,
            precio_160_dias DECIMAL(10,2) DEFAULT 0,
            precio_270_dias DECIMAL(10,2) DEFAULT 0,
            descuento DECIMAL(5,2) DEFAULT 0,
            orden INT DEFAULT 0,
            INDEX idx_categoria (categoria_id),
            INDEX idx_nombre (nombre(100))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "<div class='success'>✅ Estructura de tablas preparada</div>";

    // Limpiar datos existentes en Railway
    echo "<h2>🧹 Limpiando Datos Existentes en Railway</h2>";
    $railway_pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $railway_pdo->exec("DELETE FROM opciones");
    $railway_pdo->exec("DELETE FROM categorias");
    $railway_pdo->exec("ALTER TABLE categorias AUTO_INCREMENT = 1");
    $railway_pdo->exec("ALTER TABLE opciones AUTO_INCREMENT = 1");
    $railway_pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='success'>✅ Datos existentes eliminados</div>";

    // Sincronizar categorías
    echo "<h2>📂 Sincronizando Categorías</h2>";
    $categorias = $local_pdo->query("SELECT * FROM categorias ORDER BY orden, id")->fetchAll();
    
    foreach ($categorias as $categoria) {
        $stmt = $railway_pdo->prepare("
            INSERT INTO categorias (id, nombre, orden) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$categoria['id'], $categoria['nombre'], $categoria['orden']]);
        
        // Verificar conexión después de cada operación
        $railway_pdo->query("SELECT 1");
    }
    echo "<div class='success'>✅ " . count($categorias) . " categorías sincronizadas</div>";

    // Sincronizar opciones en lotes más pequeños
    echo "<h2>⚙️ Sincronizando Opciones (Datos Reales)</h2>";
    $opciones = $local_pdo->query("SELECT * FROM opciones ORDER BY categoria_id, orden, id")->fetchAll();
    
    $total_opciones = count($opciones);
    $batch_size = 10; // Lotes más pequeños para evitar timeouts
    $batches = array_chunk($opciones, $batch_size);
    $procesadas = 0;
    
    echo "<div class='info'>📦 Procesando {$total_opciones} opciones en " . count($batches) . " lotes pequeños...</div>";
    
    foreach ($batches as $batch_num => $batch) {
        $porcentaje = round(($batch_num / count($batches)) * 100);
        echo "<div class='progress'><div class='progress-bar' style='width: {$porcentaje}%'></div></div>";
        echo "<div class='info'>Procesando lote " . ($batch_num + 1) . "/" . count($batches) . " ({$porcentaje}%)</div>";
        
        // Verificar y reconectar si es necesario
        try {
            $railway_pdo->query("SELECT 1");
        } catch (Exception $e) {
            echo "<div class='warning'>🔄 Reconectando a Railway...</div>";
            $railway_pdo = conectarRailway($railway_config);
        }
        
        foreach ($batch as $opcion) {
            $stmt = $railway_pdo->prepare("
                INSERT INTO opciones (
                    id, categoria_id, nombre, precio_90_dias, precio_160_dias, 
                    precio_270_dias, descuento, orden
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $opcion['id'], $opcion['categoria_id'], $opcion['nombre'],
                $opcion['precio_90_dias'], $opcion['precio_160_dias'], $opcion['precio_270_dias'],
                $opcion['descuento'], $opcion['orden']
            ]);
            $procesadas++;
        }
        
        // Pausa más larga entre lotes
        sleep(1);
        
        // Flush output para mostrar progreso
        if (ob_get_level()) ob_flush();
        flush();
    }
    
    echo "<div class='progress'><div class='progress-bar' style='width: 100%'></div></div>";
    echo "<div class='success'>✅ {$procesadas} opciones sincronizadas exitosamente</div>";

    // Verificar sincronización
    echo "<h2>🔍 Verificando Sincronización</h2>";
    
    $railway_stats = [];
    $railway_stats['categorias'] = $railway_pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $railway_stats['opciones'] = $railway_pdo->query("SELECT COUNT(*) FROM opciones")->fetchColumn();
    $railway_stats['ascensores'] = $railway_pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 1")->fetchColumn();
    $railway_stats['adicionales'] = $railway_pdo->query("SELECT COUNT(*) FROM opciones WHERE categoria_id = 2")->fetchColumn();
    
    // Verificar funcionalidades del cotizador inteligente
    $railway_stats['electromecanicos'] = $railway_pdo->query("
        SELECT COUNT(*) FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%electromecanico%'
    ")->fetchColumn();
    
    $railway_stats['hidraulicos'] = $railway_pdo->query("
        SELECT COUNT(*) FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%hidraulico%'
    ")->fetchColumn();
    
    $railway_stats['montacargas'] = $railway_pdo->query("
        SELECT COUNT(*) FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%montacargas%'
    ")->fetchColumn();
    
    $railway_stats['salvaescaleras'] = $railway_pdo->query("
        SELECT COUNT(*) FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%salvaescaleras%'
    ")->fetchColumn();
    
    $railway_stats['que_restan'] = $railway_pdo->query("
        SELECT COUNT(*) FROM opciones 
        WHERE categoria_id = 2 AND LOWER(nombre) LIKE '%restar%'
    ")->fetchColumn();

    echo "<div class='info'>📊 Datos en Railway después de la sincronización:</div>";
    echo "<div class='stats'>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['categorias']}</div><div class='stat-label'>Categorías</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['ascensores']}</div><div class='stat-label'>Ascensores</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['adicionales']}</div><div class='stat-label'>Adicionales</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['electromecanicos']}</div><div class='stat-label'>Electromecánicos</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['hidraulicos']}</div><div class='stat-label'>Hidráulicos</div></div>";
    echo "<div class='stat-card'><div class='stat-number'>{$railway_stats['que_restan']}</div><div class='stat-label'>Que Restan</div></div>";
    echo "</div>";

    // Comparar resultados
    echo "<h2>📋 Comparación Local vs Railway</h2>";
    $coincidencias = true;
    
    if ($local_stats['categorias'] == $railway_stats['categorias']) {
        echo "<div class='success'>✅ Categorías: {$local_stats['categorias']} = {$railway_stats['categorias']}</div>";
    } else {
        echo "<div class='error'>❌ Categorías: {$local_stats['categorias']} ≠ {$railway_stats['categorias']}</div>";
        $coincidencias = false;
    }
    
    if ($local_stats['opciones'] == $railway_stats['opciones']) {
        echo "<div class='success'>✅ Opciones totales: {$local_stats['opciones']} = {$railway_stats['opciones']}</div>";
    } else {
        echo "<div class='error'>❌ Opciones totales: {$local_stats['opciones']} ≠ {$railway_stats['opciones']}</div>";
        $coincidencias = false;
    }
    
    if ($local_stats['ascensores'] == $railway_stats['ascensores']) {
        echo "<div class='success'>✅ Ascensores: {$local_stats['ascensores']} = {$railway_stats['ascensores']}</div>";
    } else {
        echo "<div class='error'>❌ Ascensores: {$local_stats['ascensores']} ≠ {$railway_stats['ascensores']}</div>";
        $coincidencias = false;
    }
    
    if ($local_stats['adicionales'] == $railway_stats['adicionales']) {
        echo "<div class='success'>✅ Adicionales: {$local_stats['adicionales']} = {$railway_stats['adicionales']}</div>";
    } else {
        echo "<div class='error'>❌ Adicionales: {$local_stats['adicionales']} ≠ {$railway_stats['adicionales']}</div>";
        $coincidencias = false;
    }

    // Resultado final
    echo "<h2>🎉 Resultado de la Sincronización</h2>";
    
    if ($coincidencias) {
        echo "<div class='success'>
            <strong>🎉 ¡SINCRONIZACIÓN PERFECTA!</strong><br>
            Todos los datos de tu base local han sido sincronizados correctamente con Railway.<br><br>
            <strong>Funcionalidades del Cotizador Inteligente:</strong><br>
            • Filtrado automático: {$railway_stats['electromecanicos']} + {$railway_stats['hidraulicos']} + {$railway_stats['montacargas']} + {$railway_stats['salvaescaleras']} adicionales por tipo ✅<br>
            • Adicionales que restan: {$railway_stats['que_restan']} configurados ✅<br>
            • Plazo unificado: 3 plazos (90, 160, 270 días) ✅<br>
            • Base de datos completa con {$railway_stats['opciones']} productos ✅
        </div>";
        
        echo "<div class='info'>
            <strong>🚀 Tu cotizador está listo en Railway:</strong><br>
            1. <a href='cotizador.php' target='_blank'>Acceder al Cotizador</a><br>
            2. <a href='test_simple.html' target='_blank'>Página de Pruebas</a><br>
            3. Todas las funcionalidades están operativas
        </div>";
    } else {
        echo "<div class='warning'>
            <strong>⚠️ Sincronización parcial</strong><br>
            Algunos datos no coinciden. Revisa los detalles arriba y ejecuta el script nuevamente si es necesario.
        </div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>❌ Error durante la sincronización: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='warning'>
        <strong>Posibles soluciones:</strong><br>
        • Verifica que XAMPP esté ejecutándose<br>
        • Confirma las credenciales de Railway<br>
        • Asegúrate de que ambas bases de datos estén accesibles<br>
        • Railway puede tener límites de conexión - intenta nuevamente en unos minutos
    </div>";
    
    // Información de debug
    echo "<div class='info'>
        <strong>Información de debug:</strong><br>
        • Datos locales detectados: {$local_stats['opciones']} opciones<br>
        • Error en: " . (isset($railway_pdo) ? "Sincronización" : "Conexión") . "<br>
        • Hora del error: " . date('Y-m-d H:i:s') . "
    </div>";
}

echo "</div></body></html>";
?> 