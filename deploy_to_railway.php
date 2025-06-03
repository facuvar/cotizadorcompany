<?php
/**
 * Script para verificar la instalación en Railway
 * Permite confirmar qué versión está actualmente desplegada
 */

echo "<h1>🚂 Verificación de Despliegue en Railway</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
    .file-list { margin: 10px 0; background: #f9f9f9; padding: 10px; border-radius: 5px; }
    .file-list li { margin: 5px 0; }
</style>";

echo "<div class='container'>";

// Información del entorno
echo "<h2>🔍 Información del Entorno</h2>";
echo "<div class='info'>Fecha y hora actual: " . date('Y-m-d H:i:s') . "</div>";
echo "<div class='info'>Versión de PHP: " . phpversion() . "</div>";
echo "<div class='info'>Sistema operativo: " . PHP_OS . "</div>";

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
             getenv('RAILWAY_ENVIRONMENT') !== false ||
             isset($_ENV['MYSQLHOST']) ||
             isset($_SERVER['MYSQLHOST']);

if ($isRailway) {
    echo "<div class='success'>✅ Entorno detectado: Railway</div>";
    
    // Mostrar variables de entorno relevantes
    $railwayVars = [
        'RAILWAY_ENVIRONMENT',
        'RAILWAY_SERVICE_NAME',
        'RAILWAY_STATIC_URL',
        'RAILWAY_GIT_COMMIT_SHA',
        'RAILWAY_GIT_AUTHOR',
        'RAILWAY_GIT_BRANCH',
        'RAILWAY_GIT_REPO_NAME',
        'RAILWAY_GIT_REPO_OWNER',
        'RAILWAY_GIT_COMMIT_MESSAGE'
    ];
    
    echo "<div class='info'>Variables de entorno de Railway:</div>";
    echo "<ul>";
    foreach ($railwayVars as $var) {
        $value = getenv($var) ?: $_ENV[$var] ?? $_SERVER[$var] ?? 'No definida';
        echo "<li><strong>$var:</strong> $value</li>";
    }
    echo "</ul>";
    
} else {
    echo "<div class='warning'>⚠️ Entorno detectado: Local (No es Railway)</div>";
}

// Verificar archivos clave
echo "<h2>📁 Verificación de Archivos</h2>";

$archivosClaves = [
    'cotizador.php' => 'Nuevo cotizador con checkboxes y moneda en ARS',
    'actualizar_db_railway.php' => 'Script para actualizar la moneda en la base de datos',
    'sistema/config.php' => 'Archivo de configuración',
    'index.php' => 'Página principal',
    'index_moderno.php' => 'Nueva interfaz moderna (si existe)',
];

echo "<div class='file-list'><ul>";
foreach ($archivosClaves as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        $ultimaModificacion = date('Y-m-d H:i:s', filemtime($archivo));
        $tamaño = filesize($archivo);
        echo "<li>✅ <strong>$archivo</strong> - $descripcion (Modificado: $ultimaModificacion, Tamaño: $tamaño bytes)</li>";
        
        // Verificar contenido de cotizador.php para confirmar versión
        if ($archivo === 'cotizador.php') {
            $contenido = file_get_contents($archivo);
            if (strpos($contenido, 'AR$') !== false) {
                echo "<li class='success'>✅ El archivo cotizador.php contiene la nueva moneda (AR$)</li>";
            } else if (strpos($contenido, '€') !== false) {
                echo "<li class='warning'>⚠️ El archivo cotizador.php contiene la moneda antigua (€)</li>";
            }
            
            if (strpos($contenido, 'z-index: 10') !== false) {
                echo "<li class='success'>✅ El archivo cotizador.php contiene las mejoras para checkboxes clickeables</li>";
            } else {
                echo "<li class='warning'>⚠️ El archivo cotizador.php NO contiene las mejoras para checkboxes clickeables</li>";
            }
        }
    } else {
        echo "<li class='error'>❌ <strong>$archivo</strong> - $descripcion (NO EXISTE)</li>";
    }
}
echo "</ul></div>";

// Información de Git
echo "<h2>🔄 Información de Git</h2>";

if (function_exists('exec')) {
    $gitBranch = [];
    exec('git branch --show-current 2>&1', $gitBranch);
    $currentBranch = !empty($gitBranch) ? $gitBranch[0] : 'No disponible';
    
    $gitLog = [];
    exec('git log -1 --pretty=format:"%h - %an, %ar : %s" 2>&1', $gitLog);
    $lastCommit = !empty($gitLog) ? $gitLog[0] : 'No disponible';
    
    echo "<div class='info'>Rama actual: $currentBranch</div>";
    echo "<div class='info'>Último commit: $lastCommit</div>";
} else {
    echo "<div class='warning'>⚠️ Función exec() no disponible. No se puede obtener información de Git.</div>";
}

// Verificar la base de datos
echo "<h2>🗄️ Verificación de Base de Datos</h2>";

if (file_exists('sistema/config.php')) {
    require_once 'sistema/config.php';
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<div class='success'>✅ Conexión exitosa a la base de datos: " . DB_NAME . "</div>";
        echo "<div class='info'>Host: " . DB_HOST . ":" . DB_PORT . "</div>";
        
        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='info'>Tablas encontradas: " . count($tables) . "</div>";
        
        // Verificar configuración de moneda
        if (in_array('configuracion', $tables)) {
            $stmt = $pdo->query("SELECT * FROM configuracion WHERE nombre = 'moneda'");
            $moneda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($moneda) {
                echo "<div class='info'>Configuración de moneda: " . $moneda['valor'] . " (" . $moneda['descripcion'] . ")</div>";
                
                if ($moneda['valor'] === 'ARS') {
                    echo "<div class='success'>✅ La moneda está configurada correctamente como Pesos Argentinos (ARS)</div>";
                } else {
                    echo "<div class='warning'>⚠️ La moneda NO está configurada como Pesos Argentinos. Valor actual: " . $moneda['valor'] . "</div>";
                    echo "<div class='info'>Para actualizar la moneda, accede a <a href='actualizar_db_railway.php'>actualizar_db_railway.php</a></div>";
                }
            } else {
                echo "<div class='warning'>⚠️ No se encontró configuración de moneda</div>";
                echo "<div class='info'>Para crear la configuración de moneda, accede a <a href='actualizar_db_railway.php'>actualizar_db_railway.php</a></div>";
            }
        } else {
            echo "<div class='warning'>⚠️ No existe la tabla 'configuracion'</div>";
            echo "<div class='info'>Para crear la tabla y configuración, accede a <a href='actualizar_db_railway.php'>actualizar_db_railway.php</a></div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error de conexión a la base de datos: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ No se encontró el archivo de configuración sistema/config.php</div>";
}

// Instrucciones para forzar actualización
echo "<h2>🔄 Instrucciones para Forzar Actualización</h2>";
echo "<div class='info'>Si los archivos no corresponden a la última versión, puedes intentar:</div>";
echo "<ol>
    <li>Verificar que el repositorio GitHub está correctamente configurado en Railway</li>
    <li>Hacer un nuevo commit vacío en GitHub para forzar un redeploy:
        <div class='code'>git commit --allow-empty -m 'Forzar redeploy en Railway'</div>
        <div class='code'>git push origin main</div>
    </li>
    <li>Desactivar y reactivar el despliegue automático en Railway</li>
    <li>Hacer un despliegue manual desde Railway Dashboard</li>
    <li>Verificar los logs de despliegue en Railway para detectar posibles errores</li>
</ol>";

// Enlaces útiles
echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<ul>
    <li><a href='index.php'>Página principal</a></li>
    <li><a href='cotizador.php'>Cotizador</a></li>
    <li><a href='actualizar_db_railway.php'>Actualizar configuración de moneda</a></li>
    <li><a href='admin/index.php'>Panel de administración</a></li>
</ul>";

echo "</div>";
?> 