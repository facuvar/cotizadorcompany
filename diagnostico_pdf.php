<?php
// Script de diagnóstico para identificar problemas con la generación de PDF
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Diagnóstico de Generación de PDF</h1>";

// 1. Verificar permisos de directorios
echo "<h2>1. Verificación de permisos de directorios</h2>";
$directorios = [
    __DIR__,
    __DIR__ . '/presupuestos'
];

foreach ($directorios as $dir) {
    if (!file_exists($dir)) {
        echo "<p>El directorio <code>$dir</code> no existe. Intentando crear...</p>";
        if (mkdir($dir, 0777, true)) {
            echo "<p style='color:green'>Directorio creado correctamente.</p>";
        } else {
            echo "<p style='color:red'>No se pudo crear el directorio. Error de permisos.</p>";
        }
    } else {
        echo "<p>El directorio <code>$dir</code> existe.</p>";
        if (is_writable($dir)) {
            echo "<p style='color:green'>El directorio tiene permisos de escritura.</p>";
        } else {
            echo "<p style='color:red'>El directorio NO tiene permisos de escritura.</p>";
        }
    }
}

// 2. Verificar JSON
echo "<h2>2. Prueba de procesamiento JSON</h2>";
$testJson = '{"nombre":"Test","email":"test@example.com","telefono":"123456789","producto":{"id":1,"nombre":"Producto Test"},"opcion":{"id":1,"nombre":"Opción Test","precio":1000},"plazo":{"id":1,"nombre":"90 días"},"formaPago":{"nombre":"Efectivo","descuento":8,"descuentoMonto":80},"adicionales":[],"subtotal":1000,"total":920}';

echo "<p>JSON de prueba:</p>";
echo "<pre>" . htmlspecialchars($testJson) . "</pre>";

$data = json_decode($testJson, true);
if ($data === null) {
    echo "<p style='color:red'>Error al decodificar JSON: " . json_last_error_msg() . "</p>";
} else {
    echo "<p style='color:green'>JSON decodificado correctamente.</p>";
    echo "<p>Datos decodificados:</p>";
    echo "<pre>" . print_r($data, true) . "</pre>";
}

// 3. Prueba de escritura de archivo
echo "<h2>3. Prueba de escritura de archivo</h2>";
$testFile = __DIR__ . '/presupuestos/test_' . time() . '.html';
$testContent = "<html><body><h1>Test</h1><p>Esto es una prueba.</p></body></html>";

if (file_put_contents($testFile, $testContent)) {
    echo "<p style='color:green'>Archivo de prueba creado correctamente en <code>$testFile</code>.</p>";
    echo "<p>Contenido del archivo:</p>";
    echo "<pre>" . htmlspecialchars(file_get_contents($testFile)) . "</pre>";
} else {
    echo "<p style='color:red'>No se pudo crear el archivo de prueba. Error de permisos.</p>";
}

// 4. Verificar si existe TCPDF
echo "<h2>4. Verificación de TCPDF</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p style='color:green'>El archivo vendor/autoload.php existe.</p>";
    
    if (file_exists(__DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
        echo "<p style='color:green'>TCPDF encontrado.</p>";
    } else {
        echo "<p style='color:orange'>TCPDF no encontrado. Se usará el método simple.</p>";
    }
} else {
    echo "<p style='color:orange'>El archivo vendor/autoload.php no existe. Se usará el método simple.</p>";
}

// 5. Verificar configuración PHP
echo "<h2>5. Configuración PHP</h2>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";
echo "<p>max_execution_time: " . ini_get('max_execution_time') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";

// 6. Verificar conexión a la base de datos
echo "<h2>6. Verificación de conexión a la base de datos</h2>";
try {
    require_once 'sistema/config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "<p style='color:red'>Error de conexión a la base de datos: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color:green'>Conexión a la base de datos exitosa.</p>";
        
        // Verificar tabla presupuestos
        $result = $conn->query("SHOW TABLES LIKE 'presupuestos'");
        if ($result->num_rows > 0) {
            echo "<p style='color:green'>La tabla presupuestos existe.</p>";
            
            // Verificar estructura
            $result = $conn->query("DESCRIBE presupuestos");
            echo "<p>Estructura de la tabla:</p>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>La tabla presupuestos no existe.</p>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// 7. Verificar el script de generación de PDF
echo "<h2>7. Análisis del script generar_pdf.php</h2>";
$pdfScript = file_get_contents(__DIR__ . '/generar_pdf.php');
if ($pdfScript) {
    // Verificar si tiene las cabeceras JSON correctas
    if (strpos($pdfScript, "header('Content-Type: application/json')") !== false) {
        echo "<p style='color:green'>El script tiene cabeceras JSON correctas.</p>";
    } else {
        echo "<p style='color:red'>El script no tiene cabeceras JSON correctas.</p>";
    }
    
    // Verificar si crea el directorio presupuestos
    if (strpos($pdfScript, "mkdir(__DIR__ . '/presupuestos'") !== false) {
        echo "<p style='color:green'>El script crea el directorio presupuestos si no existe.</p>";
    } else {
        echo "<p style='color:red'>El script no crea el directorio presupuestos.</p>";
    }
    
    // Verificar si maneja errores
    if (strpos($pdfScript, "try") !== false && strpos($pdfScript, "catch") !== false) {
        echo "<p style='color:green'>El script tiene manejo de errores.</p>";
    } else {
        echo "<p style='color:orange'>El script no tiene manejo de errores con try/catch.</p>";
    }
} else {
    echo "<p style='color:red'>No se pudo leer el script generar_pdf.php.</p>";
}

echo "<h2>Recomendaciones</h2>";
echo "<p>Basado en el diagnóstico, aquí hay algunas recomendaciones:</p>";
echo "<ol>";
echo "<li>Asegúrate de que el directorio 'presupuestos' tenga permisos de escritura.</li>";
echo "<li>Verifica que el JSON enviado desde el cotizador sea válido.</li>";
echo "<li>Añade manejo de errores con try/catch en el script generar_pdf.php.</li>";
echo "<li>Verifica que todas las respuestas tengan la cabecera 'Content-Type: application/json'.</li>";
echo "<li>Revisa los logs de PHP para errores adicionales.</li>";
echo "</ol>";

echo "<p><a href='cotizador_con_pago.php'>Volver al cotizador</a></p>";
?>
