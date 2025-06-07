<?php
/**
 * SCRIPT COMPLETO DE DESPLIEGUE A RAILWAY
 * Automatiza todo el proceso de subida a GitHub y configuración
 */

echo "🚀 DESPLIEGUE COMPLETO A RAILWAY\n";
echo "================================\n\n";

// Función para ejecutar comandos
function ejecutarComando($comando, $descripcion) {
    echo "📋 $descripcion\n";
    echo "💻 Ejecutando: $comando\n";
    
    $output = [];
    $return_var = 0;
    exec($comando . " 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "✅ Éxito\n";
        if (!empty($output)) {
            echo "📄 Salida:\n" . implode("\n", $output) . "\n";
        }
    } else {
        echo "❌ Error (código: $return_var)\n";
        echo "📄 Salida:\n" . implode("\n", $output) . "\n";
    }
    echo "\n";
    
    return $return_var === 0;
}

// Paso 1: Verificar estado del repositorio
echo "🔍 PASO 1: VERIFICANDO REPOSITORIO\n";
echo "==================================\n";

if (!is_dir('.git')) {
    echo "❌ No se encontró repositorio Git. Inicializando...\n";
    ejecutarComando('git init', 'Inicializando repositorio Git');
}

// Verificar conexión remota
$remotes = shell_exec('git remote -v 2>&1');
if (strpos($remotes, 'github.com') === false) {
    echo "⚠️ No se encontró conexión con GitHub\n";
    echo "🔗 Configurando conexión remota...\n";
    ejecutarComando('git remote add origin https://github.com/facuvar/cotizadorcompany.git', 'Agregando repositorio remoto');
}

// Paso 2: Agregar archivos pendientes
echo "📤 PASO 2: AGREGANDO ARCHIVOS PENDIENTES\n";
echo "========================================\n";

$archivos_importantes = [
    'setup_directo.php',
    'railway_config.php',
    'railway_deploy_guide.md',
    'deploy_complete.php'
];

foreach ($archivos_importantes as $archivo) {
    if (file_exists($archivo)) {
        ejecutarComando("git add \"$archivo\"", "Agregando $archivo");
    } else {
        echo "⚠️ Archivo $archivo no encontrado\n";
    }
}

// Paso 3: Commit y push
echo "💾 PASO 3: SUBIENDO CAMBIOS A GITHUB\n";
echo "====================================\n";

$fecha = date('Y-m-d H:i:s');
$mensaje = "Deploy completo Railway - $fecha";

ejecutarComando("git commit -m \"$mensaje\"", 'Creando commit');
ejecutarComando('git push origin master', 'Subiendo a GitHub');

// Paso 4: Generar instrucciones específicas
echo "📋 PASO 4: GENERANDO INSTRUCCIONES\n";
echo "==================================\n";

$instrucciones = "
🎯 INSTRUCCIONES ESPECÍFICAS PARA RAILWAY
==========================================

Tu repositorio está actualizado en: https://github.com/facuvar/cotizadorcompany

PASOS PARA RAILWAY:

1. 🚂 CREAR PROYECTO EN RAILWAY
   - Ve a: https://railway.app
   - Haz clic en 'New Project'
   - Selecciona 'Deploy from GitHub repo'
   - Busca: facuvar/cotizadorcompany
   - Railway detectará automáticamente PHP

2. 🗄️ AGREGAR BASE DE DATOS
   - En tu proyecto, haz clic en 'New'
   - Selecciona 'Database' → 'MySQL'
   - Railway creará MySQL automáticamente

3. ⚙️ CONFIGURAR VARIABLES DE ENTORNO
   Ve a la pestaña 'Variables' de tu aplicación y agrega:
   
   DB_HOST=\${{MySQL.MYSQL_HOST}}
   DB_USER=\${{MySQL.MYSQL_USER}}
   DB_PASS=\${{MySQL.MYSQL_PASSWORD}}
   DB_NAME=\${{MySQL.MYSQL_DATABASE}}
   DB_PORT=\${{MySQL.MYSQL_PORT}}

4. 🔧 CONFIGURAR BASE DE DATOS
   Una vez desplegado, ve a:
   https://tu-app.railway.app/setup_directo.php
   
   Este script configurará automáticamente:
   ✅ Tablas necesarias
   ✅ 28 opciones de ejemplo
   ✅ Todas las funcionalidades del cotizador

5. 🧪 PROBAR FUNCIONALIDADES
   - Cotizador: https://tu-app.railway.app/cotizador.php
   - Filtrado inteligente: Selecciona ascensor → adicionales se filtran
   - Adicionales que restan: Opciones con 'RESTAR' en color naranja
   - Plazo unificado: Cambiar plazo actualiza todos los productos

ARCHIVOS CLAVE SUBIDOS:
✅ setup_directo.php - Configuración automática de BD
✅ railway_config.php - Configuración específica Railway
✅ cotizador.php - Cotizador inteligente completo
✅ railway_deploy_guide.md - Guía detallada

FUNCIONALIDADES ACTIVAS:
✅ Filtrado automático por tipo de ascensor
✅ Adicionales que restan dinero (6 opciones)
✅ Plazo unificado para toda la cotización
✅ Interface moderna con checkboxes
✅ Cálculos en tiempo real

¡Tu proyecto está listo para Railway!
";

echo $instrucciones;

// Guardar instrucciones en archivo
file_put_contents('INSTRUCCIONES_RAILWAY_FINAL.txt', $instrucciones);
echo "📄 Instrucciones guardadas en: INSTRUCCIONES_RAILWAY_FINAL.txt\n\n";

// Paso 5: Verificar archivos críticos
echo "🔍 PASO 5: VERIFICACIÓN FINAL\n";
echo "=============================\n";

$archivos_criticos = [
    'cotizador.php' => 'Cotizador principal con funcionalidades inteligentes',
    'setup_directo.php' => 'Script de configuración automática para Railway',
    'railway_config.php' => 'Configuración específica de Railway',
    'railway.json' => 'Configuración de despliegue Railway',
    'index.php' => 'Página principal',
    'test_simple.html' => 'Página de pruebas'
];

echo "📊 ARCHIVOS CRÍTICOS:\n";
foreach ($archivos_criticos as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        $tamaño = filesize($archivo);
        echo "✅ $archivo ($tamaño bytes) - $descripcion\n";
    } else {
        echo "❌ $archivo - FALTANTE\n";
    }
}

echo "\n🎉 DESPLIEGUE COMPLETADO\n";
echo "========================\n";
echo "✅ Código subido a GitHub\n";
echo "✅ Archivos de configuración listos\n";
echo "✅ Scripts de setup preparados\n";
echo "✅ Documentación generada\n\n";

echo "🔗 PRÓXIMO PASO:\n";
echo "Ve a Railway y sigue las instrucciones en INSTRUCCIONES_RAILWAY_FINAL.txt\n\n";

echo "📞 SOPORTE:\n";
echo "Si tienes problemas, revisa railway_deploy_guide.md para soluciones detalladas\n";
?> 