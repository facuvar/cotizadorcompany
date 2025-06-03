<?php
/**
 * Script para leer datos del archivo xls-referencia.xlsx
 */

echo "<h1>📖 LEER DATOS DEL EXCEL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #45a049; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    .sheet-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
    .sheet-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
    .data-preview { max-height: 400px; overflow-y: auto; }
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

// Función para leer Excel usando SimpleXLSX (si está disponible)
function readExcelWithSimpleXLSX($filePath) {
    // Verificar si SimpleXLSX está disponible
    if (!class_exists('SimpleXLSX')) {
        return false;
    }
    
    try {
        $xlsx = SimpleXLSX::parse($filePath);
        if (!$xlsx) {
            return false;
        }
        
        $sheets = [];
        $sheetNames = $xlsx->sheetNames();
        
        foreach ($sheetNames as $index => $sheetName) {
            $rows = $xlsx->rows($index);
            $sheets[$sheetName] = $rows;
        }
        
        return $sheets;
    } catch (Exception $e) {
        return false;
    }
}

// Función para leer Excel usando ZipArchive (método manual)
function readExcelWithZip($filePath) {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== TRUE) {
        return false;
    }
    
    $sheets = [];
    
    // Leer workbook.xml para obtener nombres de hojas
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    if ($workbookXml) {
        $workbook = simplexml_load_string($workbookXml);
        $sheetNames = [];
        
        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetNames[(string)$sheet['sheetId']] = (string)$sheet['name'];
        }
        
        // Leer cada hoja
        foreach ($sheetNames as $sheetId => $sheetName) {
            $sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetId}.xml");
            if ($sheetXml) {
                $sheet = simplexml_load_string($sheetXml);
                $rows = [];
                
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $rowData = [];
                        if (isset($row->c)) {
                            foreach ($row->c as $cell) {
                                $value = '';
                                if (isset($cell->v)) {
                                    $value = (string)$cell->v;
                                }
                                $rowData[] = $value;
                            }
                        }
                        $rows[] = $rowData;
                    }
                }
                
                $sheets[$sheetName] = $rows;
            }
        }
    }
    
    $zip->close();
    return $sheets;
}

// Intentar leer el archivo Excel
echo "<h2>📊 Leyendo datos del Excel</h2>";

$sheets = readExcelWithSimpleXLSX($excelFile);

if ($sheets === false) {
    echo "<div class='warning'>⚠️ SimpleXLSX no disponible, intentando método alternativo...</div>";
    $sheets = readExcelWithZip($excelFile);
}

if ($sheets === false || empty($sheets)) {
    echo "<div class='error'>❌ No se pudo leer el archivo Excel</div>";
    echo "<div class='info'>Posibles soluciones:</div>";
    echo "<div class='info'>1. Verificar que el archivo sea un Excel válido (.xlsx)</div>";
    echo "<div class='info'>2. Instalar SimpleXLSX: <code>composer require shuchkin/simplexlsx</code></div>";
    echo "<div class='info'>3. Convertir el archivo a CSV y usar un script de importación CSV</div>";
    echo "</div>";
    exit;
}

echo "<div class='success'>✅ Archivo Excel leído exitosamente</div>";
echo "<div class='info'>• Hojas encontradas: " . count($sheets) . "</div>";

// Mostrar datos de cada hoja
foreach ($sheets as $sheetName => $rows) {
    echo "<div class='sheet-section'>";
    echo "<div class='sheet-title'>📋 Hoja: " . htmlspecialchars($sheetName) . "</div>";
    
    if (empty($rows)) {
        echo "<div class='warning'>⚠️ Hoja vacía</div>";
        continue;
    }
    
    echo "<div class='info'>• Filas: " . count($rows) . "</div>";
    
    // Mostrar preview de los primeros 10 registros
    echo "<div class='data-preview'>";
    echo "<table>";
    
    $maxRows = min(10, count($rows));
    for ($i = 0; $i < $maxRows; $i++) {
        $row = $rows[$i];
        echo "<tr>";
        
        if ($i === 0) {
            // Primera fila como encabezados
            foreach ($row as $cell) {
                echo "<th>" . htmlspecialchars($cell) . "</th>";
            }
        } else {
            // Datos
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
        }
        
        echo "</tr>";
    }
    
    if (count($rows) > 10) {
        echo "<tr><td colspan='" . count($rows[0]) . "'><em>... y " . (count($rows) - 10) . " filas más</em></td></tr>";
    }
    
    echo "</table>";
    echo "</div>";
    echo "</div>";
}

// Análisis de estructura esperada
echo "<h2>🔍 Análisis de estructura</h2>";

$expectedSheets = ['ASCENSORES', 'ADICIONALES', 'DESCUENTOS'];
$foundSheets = array_keys($sheets);

echo "<div class='info'>";
echo "<strong>Hojas esperadas vs encontradas:</strong><br>";

foreach ($expectedSheets as $expected) {
    $found = in_array($expected, $foundSheets);
    $status = $found ? "✅" : "❌";
    echo "• $status <strong>$expected:</strong> " . ($found ? "Encontrada" : "No encontrada") . "<br>";
}

echo "<br><strong>Hojas adicionales encontradas:</strong><br>";
$extraSheets = array_diff($foundSheets, $expectedSheets);
foreach ($extraSheets as $extra) {
    echo "• ℹ️ <strong>$extra:</strong> Hoja adicional<br>";
}
echo "</div>";

// Próximos pasos
echo "<h2>🔗 Próximos pasos</h2>";
echo "<div class='info'>";
echo "<a href='import_excel_structure.php' class='btn'>🏗️ Reestructurar Base de Datos</a><br><br>";
echo "<a href='import_excel_data.php' class='btn'>📥 Importar Datos a BD</a><br><br>";
echo "<a href='upload_excel.php' class='btn'>📤 Subir Otro Archivo</a><br><br>";
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