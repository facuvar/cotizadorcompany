<?php
/**
 * Script para subir el archivo xls-referencia.xlsx a Railway
 */

echo "<h1>📤 SUBIR ARCHIVO EXCEL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .upload-area { border: 2px dashed #ddd; padding: 40px; text-align: center; margin: 20px 0; border-radius: 10px; background: #fafafa; }
    .upload-area:hover { border-color: #4CAF50; background: #f0f8f0; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #45a049; }
    input[type=\"file\"] { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }
</style>";

echo "<div class='container'>";

// Verificar directorio uploads
echo "<h2>📁 Verificar directorio uploads</h2>";

$uploadsDir = 'uploads';
if (!is_dir($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<div class='success'>✅ Directorio uploads creado</div>";
    } else {
        echo "<div class='error'>❌ No se pudo crear el directorio uploads</div>";
        exit;
    }
} else {
    echo "<div class='success'>✅ Directorio uploads existe</div>";
}

// Verificar permisos
if (is_writable($uploadsDir)) {
    echo "<div class='success'>✅ Directorio uploads tiene permisos de escritura</div>";
} else {
    echo "<div class='warning'>⚠️ Directorio uploads no tiene permisos de escritura</div>";
}

// Mostrar archivos actuales
echo "<h2>📋 Archivos actuales en uploads</h2>";

$files = scandir($uploadsDir);
$excelFiles = array_filter($files, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'xlsx';
});

if (count($excelFiles) > 0) {
    echo "<div class='info'>Archivos Excel encontrados:</div>";
    echo "<ul>";
    foreach ($excelFiles as $file) {
        $filePath = $uploadsDir . '/' . $file;
        $fileSize = filesize($filePath);
        echo "<li><strong>" . htmlspecialchars($file) . "</strong> (" . number_format($fileSize) . " bytes)</li>";
    }
    echo "</ul>";
} else {
    echo "<div class='warning'>⚠️ No hay archivos Excel en el directorio uploads</div>";
}

// Procesar subida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    echo "<h2>📤 Procesando subida</h2>";
    
    $uploadedFile = $_FILES['excel_file'];
    
    // Verificar errores
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='error'>❌ Error en la subida: " . $uploadedFile['error'] . "</div>";
    } else {
        // Verificar tipo de archivo
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'xlsx') {
            echo "<div class='error'>❌ Solo se permiten archivos .xlsx</div>";
        } else {
            // Mover archivo
            $targetPath = $uploadsDir . '/xls-referencia.xlsx';
            
            if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
                $fileSize = filesize($targetPath);
                echo "<div class='success'>✅ Archivo subido exitosamente</div>";
                echo "<div class='info'>• Nombre: xls-referencia.xlsx</div>";
                echo "<div class='info'>• Tamaño: " . number_format($fileSize) . " bytes</div>";
                echo "<div class='info'>• Ubicación: " . $targetPath . "</div>";
                
                // Enlaces para continuar
                echo "<h3>🔗 Próximos pasos</h3>";
                echo "<div class='info'>";
                echo "<a href='read_excel_data.php' class='btn'>📖 Leer Datos del Excel</a><br><br>";
                echo "<a href='import_excel_structure.php' class='btn'>🏗️ Reestructurar Base de Datos</a><br><br>";
                echo "</div>";
                
            } else {
                echo "<div class='error'>❌ Error moviendo el archivo</div>";
            }
        }
    }
}

// Formulario de subida
echo "<h2>📤 Subir archivo Excel</h2>";

echo "<div class='info'>";
echo "<strong>Instrucciones:</strong><br>";
echo "1. Selecciona tu archivo <strong>xls-referencia.xlsx</strong><br>";
echo "2. Haz clic en 'Subir Archivo'<br>";
echo "3. Una vez subido, podrás leer los datos e importarlos<br>";
echo "</div>";

echo "<form method='post' enctype='multipart/form-data'>";
echo "<div class='upload-area'>";
echo "<h3>📁 Seleccionar archivo Excel</h3>";
echo "<input type='file' name='excel_file' accept='.xlsx' required>";
echo "<br><br>";
echo "<button type='submit' class='btn'>📤 Subir Archivo</button>";
echo "</div>";
echo "</form>";

// Información adicional
echo "<h2>ℹ️ Información del archivo esperado</h2>";

echo "<div class='info'>";
echo "<strong>El archivo debe contener las siguientes hojas:</strong><br>";
echo "• <strong>ASCENSORES:</strong> Modelos principales con precios por plazo<br>";
echo "• <strong>ADICIONALES:</strong> Opciones adicionales (1A-30A, 32A-55A, 57A-59A)<br>";
echo "• <strong>DESCUENTOS:</strong> Formas de pago y descuentos<br>";
echo "</div>";

// Enlaces útiles
echo "<h2>🔗 Enlaces útiles</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Cotizador</a><br>";
echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>🔐 Panel Admin</a><br>";
echo "<a href='railway_debug.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico Railway</a><br>";
echo "</div>";

echo "</div>";
?> 