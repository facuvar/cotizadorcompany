<?php
/**
 * 🔧 CREAR DEBUG RAILWAY
 * 
 * Genera el archivo de diagnóstico para subir a Railway
 */

// Leer el contenido del archivo de diagnóstico
$debugContent = file_get_contents('debug_categorias_railway.php');

// Crear el archivo para subir
$filename = 'debug_railway_upload.php';
file_put_contents($filename, $debugContent);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔧 Crear Debug Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f2f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #17a2b8; }
        .btn { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 600; }
        .btn:hover { background: #0056b3; }
        h1 { text-align: center; color: #dc3545; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Debug Railway Generado</h1>
        
        <div class='success'>
            ✅ Archivo de diagnóstico creado: <strong>$filename</strong>
        </div>
        
        <div class='info'>
            <h3>📋 Pasos para usar el diagnóstico:</h3>
            <ol>
                <li><strong>Descargar el archivo:</strong> <a href='$filename' download class='btn'>📥 Descargar $filename</a></li>
                <li><strong>Ir a Railway Upload:</strong> <a href='https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php' target='_blank' class='btn'>🚂 Upload Railway</a></li>
                <li><strong>Subir el archivo PHP</strong> (no como SQL, sino como archivo PHP)</li>
                <li><strong>Acceder al diagnóstico:</strong> <code>https://cotizadorcompany-production.up.railway.app/debug_railway_upload.php</code></li>
            </ol>
        </div>
        
        <div class='info'>
            <h3>🎯 ¿Qué hará el diagnóstico?</h3>
            <ul>
                <li>✅ Verificar la conexión a base de datos en Railway</li>
                <li>✅ Mostrar la estructura de la tabla categorías</li>
                <li>✅ Probar diferentes consultas SQL</li>
                <li>✅ Identificar exactamente por qué falla el cotizador</li>
            </ul>
        </div>
        
        <div class='info'>
            <strong>💡 Alternativa más rápida:</strong><br>
            También puedes copiar y pegar el contenido del archivo <code>debug_categorias_railway.php</code> 
            directamente en un nuevo archivo en Railway.
        </div>
    </div>
</body>
</html>";
?> 