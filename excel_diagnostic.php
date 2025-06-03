<?php
/**
 * Script de diagnóstico para analizar la estructura del archivo Excel
 */

echo "<h1>🔍 DIAGNÓSTICO DEL ARCHIVO EXCEL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #45a049; }
    .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
    .xml-content { background: #f8f8f8; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 11px; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
</style>";

echo "<div class='container'>";

// Verificar si existe el archivo
$excelFile = 'uploads/xls-referencia.xlsx';

echo "<h2>📁 Verificar archivo Excel</h2>";

if (!file_exists($excelFile)) {
    echo "<div class='error'>❌ Archivo no encontrado: $excelFile</div>";
    echo "<div class='info'>Por favor, <a href='upload_excel.php' class='btn'>📤 Subir Archivo Excel</a> primero</div>";
    echo "</div>";
    exit;
}

$fileSize = filesize($excelFile);
echo "<div class='success'>✅ Archivo encontrado: $excelFile</div>";
echo "<div class='info'>• Tamaño: " . number_format($fileSize) . " bytes</div>";

// Función para diagnosticar el archivo Excel
function diagnoseExcel($filePath) {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== TRUE) {
        return ['error' => 'No se pudo abrir el archivo ZIP'];
    }
    
    $diagnosis = [];
    
    // Listar todos los archivos en el ZIP
    $diagnosis['files'] = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $diagnosis['files'][] = [
            'name' => $stat['name'],
            'size' => $stat['size'],
            'compressed_size' => $stat['comp_size']
        ];
    }
    
    // Analizar workbook.xml
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    if ($workbookXml) {
        $diagnosis['workbook'] = [
            'found' => true,
            'size' => strlen($workbookXml),
            'content' => $workbookXml
        ];
        
        $workbook = simplexml_load_string($workbookXml);
        if ($workbook) {
            $diagnosis['sheets'] = [];
            if (isset($workbook->sheets->sheet)) {
                foreach ($workbook->sheets->sheet as $sheet) {
                    $sheetId = (string)$sheet['sheetId'];
                    $sheetName = (string)$sheet['name'];
                    $rId = (string)$sheet['r:id'];
                    
                    $diagnosis['sheets'][] = [
                        'id' => $sheetId,
                        'name' => $sheetName,
                        'rId' => $rId
                    ];
                }
            }
        }
    } else {
        $diagnosis['workbook'] = ['found' => false];
    }
    
    // Analizar app.xml para metadatos
    $appXml = $zip->getFromName('docProps/app.xml');
    if ($appXml) {
        $diagnosis['app'] = [
            'found' => true,
            'size' => strlen($appXml),
            'content' => $appXml
        ];
    }
    
    // Analizar core.xml para metadatos
    $coreXml = $zip->getFromName('docProps/core.xml');
    if ($coreXml) {
        $diagnosis['core'] = [
            'found' => true,
            'size' => strlen($coreXml),
            'content' => $coreXml
        ];
    }
    
    // Analizar sharedStrings.xml
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml) {
        $diagnosis['sharedStrings'] = [
            'found' => true,
            'size' => strlen($sharedStringsXml),
            'content' => $sharedStringsXml
        ];
    } else {
        $diagnosis['sharedStrings'] = ['found' => false];
    }
    
    // Analizar cada hoja individual
    $diagnosis['worksheets'] = [];
    if (isset($diagnosis['sheets'])) {
        foreach ($diagnosis['sheets'] as $sheetInfo) {
            $sheetFile = "xl/worksheets/sheet{$sheetInfo['id']}.xml";
            $sheetXml = $zip->getFromName($sheetFile);
            
            if ($sheetXml) {
                $diagnosis['worksheets'][$sheetInfo['name']] = [
                    'found' => true,
                    'file' => $sheetFile,
                    'size' => strlen($sheetXml),
                    'content' => $sheetXml
                ];
            } else {
                $diagnosis['worksheets'][$sheetInfo['name']] = [
                    'found' => false,
                    'file' => $sheetFile
                ];
            }
        }
    }
    
    $zip->close();
    return $diagnosis;
}

// Ejecutar diagnóstico
echo "<h2>🔍 Ejecutando diagnóstico</h2>";

$diagnosis = diagnoseExcel($excelFile);

if (isset($diagnosis['error'])) {
    echo "<div class='error'>❌ Error: " . $diagnosis['error'] . "</div>";
    echo "</div>";
    exit;
}

// Mostrar archivos en el ZIP
echo "<h3>📁 Archivos en el ZIP</h3>";
echo "<table>";
echo "<tr><th>Archivo</th><th>Tamaño</th><th>Comprimido</th></tr>";
foreach ($diagnosis['files'] as $file) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($file['name']) . "</td>";
    echo "<td>" . number_format($file['size']) . " bytes</td>";
    echo "<td>" . number_format($file['compressed_size']) . " bytes</td>";
    echo "</tr>";
}
echo "</table>";

// Mostrar información del workbook
echo "<h3>📊 Información del Workbook</h3>";
if ($diagnosis['workbook']['found']) {
    echo "<div class='success'>✅ workbook.xml encontrado (" . number_format($diagnosis['workbook']['size']) . " bytes)</div>";
    
    if (isset($diagnosis['sheets'])) {
        echo "<div class='info'><strong>Hojas definidas:</strong></div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Relación ID</th></tr>";
        foreach ($diagnosis['sheets'] as $sheet) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($sheet['id']) . "</td>";
            echo "<td>" . htmlspecialchars($sheet['name']) . "</td>";
            echo "<td>" . htmlspecialchars($sheet['rId']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h4>📄 Contenido del workbook.xml</h4>";
    echo "<div class='xml-content'>" . htmlspecialchars($diagnosis['workbook']['content']) . "</div>";
} else {
    echo "<div class='error'>❌ workbook.xml no encontrado</div>";
}

// Mostrar información de metadatos
if (isset($diagnosis['app']) && $diagnosis['app']['found']) {
    echo "<h3>📋 Metadatos de la aplicación</h3>";
    echo "<div class='success'>✅ app.xml encontrado (" . number_format($diagnosis['app']['size']) . " bytes)</div>";
    echo "<div class='xml-content'>" . htmlspecialchars($diagnosis['app']['content']) . "</div>";
}

if (isset($diagnosis['core']) && $diagnosis['core']['found']) {
    echo "<h3>📋 Metadatos principales</h3>";
    echo "<div class='success'>✅ core.xml encontrado (" . number_format($diagnosis['core']['size']) . " bytes)</div>";
    echo "<div class='xml-content'>" . htmlspecialchars($diagnosis['core']['content']) . "</div>";
}

// Mostrar información de strings compartidos
echo "<h3>📝 Strings Compartidos</h3>";
if ($diagnosis['sharedStrings']['found']) {
    echo "<div class='success'>✅ sharedStrings.xml encontrado (" . number_format($diagnosis['sharedStrings']['size']) . " bytes)</div>";
    echo "<div class='xml-content'>" . htmlspecialchars($diagnosis['sharedStrings']['content']) . "</div>";
} else {
    echo "<div class='warning'>⚠️ sharedStrings.xml no encontrado (puede ser normal si no hay strings compartidos)</div>";
}

// Mostrar información de cada hoja
echo "<h3>📋 Análisis de Hojas Individuales</h3>";
if (isset($diagnosis['worksheets'])) {
    foreach ($diagnosis['worksheets'] as $sheetName => $sheetInfo) {
        echo "<h4>📄 Hoja: " . htmlspecialchars($sheetName) . "</h4>";
        
        if ($sheetInfo['found']) {
            echo "<div class='success'>✅ " . htmlspecialchars($sheetInfo['file']) . " encontrado (" . number_format($sheetInfo['size']) . " bytes)</div>";
            echo "<div class='xml-content'>" . htmlspecialchars(substr($sheetInfo['content'], 0, 2000)) . "...</div>";
        } else {
            echo "<div class='error'>❌ " . htmlspecialchars($sheetInfo['file']) . " no encontrado</div>";
        }
    }
} else {
    echo "<div class='warning'>⚠️ No se encontraron definiciones de hojas</div>";
}

// Enlaces útiles
echo "<h2>🔗 Próximos pasos</h2>";
echo "<div class='info'>";
echo "<a href='read_excel_data_improved.php' class='btn'>📖 Lector Mejorado</a><br><br>";
echo "<a href='read_excel_data.php' class='btn'>📖 Lector Original</a><br><br>";
echo "<a href='upload_excel.php' class='btn'>📤 Subir Otro Archivo</a><br><br>";
echo "</div>";

echo "</div>";
?> 