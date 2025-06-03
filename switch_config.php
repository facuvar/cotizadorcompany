<?php
/**
 * Script para cambiar entre configuración local y Railway
 * Uso: php switch_config.php [local|railway]
 */

$mode = $argv[1] ?? 'local';

if (!in_array($mode, ['local', 'railway'])) {
    echo "❌ Modo inválido. Usa: php switch_config.php [local|railway]\n";
    exit(1);
}

$configFile = 'sistema/config.php';
$localConfig = 'sistema/config_local.php';
$railwayConfig = 'sistema/config_railway.php';

// Crear backup de la configuración actual si no existe config_local.php
if (!file_exists($localConfig) && file_exists($configFile)) {
    copy($configFile, $localConfig);
    echo "✅ Backup de configuración local creado\n";
}

if ($mode === 'local') {
    if (file_exists($localConfig)) {
        copy($localConfig, $configFile);
        echo "✅ Configuración cambiada a LOCAL\n";
        echo "🔧 Base de datos: localhost\n";
    } else {
        echo "❌ No se encontró archivo de configuración local\n";
        exit(1);
    }
} else {
    if (file_exists($railwayConfig)) {
        copy($railwayConfig, $configFile);
        echo "✅ Configuración cambiada a RAILWAY\n";
        echo "☁️ Base de datos: Variables de entorno\n";
        echo "📝 Recuerda configurar las variables de entorno en Railway:\n";
        echo "   - DB_HOST\n";
        echo "   - DB_USER\n";
        echo "   - DB_PASS\n";
        echo "   - DB_NAME\n";
        echo "   - DB_PORT\n";
    } else {
        echo "❌ No se encontró archivo de configuración Railway\n";
        exit(1);
    }
}

echo "\n🎯 Configuración actual: " . strtoupper($mode) . "\n";
?> 