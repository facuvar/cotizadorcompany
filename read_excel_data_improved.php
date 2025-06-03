<?php
/**
 * Script mejorado para leer datos del archivo xls-referencia.xlsx
 * Versión con mejor soporte para múltiples hojas
 */

echo "<h1>📖 LEER DATOS DEL EXCEL (MEJORADO)</h1>";
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
    .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
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

// Función mejorada para leer Excel usando ZipArchive
function readExcelImproved($filePath) {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== TRUE) {
        return ['error' => 'No se pudo abrir el archivo ZIP'];
    }
    
    $sheets = [];
    $debug = [];
    
    // Leer workbook.xml para obtener nombres de hojas
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    if (!$workbookXml) {
        $zip->close();
        return ['error' => 'No se encontró workbook.xml'];
    }
    
    $debug[] = "workbook.xml encontrado (" . strlen($workbookXml) . " bytes)";
    
    $workbook = simplexml_load_string($workbookXml);
    if (!$workbook) {
        $zip->close();
        return ['error' => 'No se pudo parsear workbook.xml'];
    }
    
    $sheetNames = [];
    $sheetCount = 0;
    
    // Obtener información de las hojas
    if (isset($workbook->sheets->sheet)) {
        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetId = (string)$sheet['sheetId'];
            $sheetName = (string)$sheet['name'];
            $rId = (string)$sheet['r:id'];
            
            $sheetNames[$sheetId] = [
                'name' => $sheetName,
                'rId' => $rId
            ];
            $sheetCount++;
            $debug[] = "Hoja encontrada: ID=$sheetId, Nombre='$sheetName', rId='$rId'";
        }
    }
    
    $debug[] = "Total hojas en workbook: $sheetCount";
    
    // Leer sharedStrings.xml para obtener strings compartidos
    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml) {
        $debug[] = "sharedStrings.xml encontrado (" . strlen($sharedStringsXml) . " bytes)";
        $sharedStringsDoc = simplexml_load_string($sharedStringsXml);
        if ($sharedStringsDoc && isset($sharedStringsDoc->si)) {
            foreach ($sharedStringsDoc->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
        $debug[] = "Strings compartidos cargados: " . count($sharedStrings);
    }
    
    // Leer cada hoja
    foreach ($sheetNames as $sheetId => $sheetInfo) {
        $sheetName = $sheetInfo['name'];
        $sheetFile = "xl/worksheets/sheet{$sheetId}.xml";
        
        $debug[] = "Intentando leer: $sheetFile";
        
        $sheetXml = $zip->getFromName($sheetFile);
        if ($sheetXml) {
            $debug[] = "Archivo de hoja encontrado (" . strlen($sheetXml) . " bytes)";
            
            $sheet = simplexml_load_string($sheetXml);
            if ($sheet) {
                $rows = [];
                $rowCount = 0;
                
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $rowData = [];
                        $cellCount = 0;
                        
                        if (isset($row->c)) {
                            foreach ($row->c as $cell) {
                                $value = '';
                                
                                // Obtener el tipo de celda
                                $cellType = isset($cell['t']) ? (string)$cell['t'] : '';
                                
                                if (isset($cell->v)) {
                                    $cellValue = (string)$cell->v;
                                    
                                    // Si es un string compartido
                                    if ($cellType === 's' && isset($sharedStrings[$cellValue])) {
                                        $value = $sharedStrings[$cellValue];
                                    } else {
                                        $value = $cellValue;
                                    }
                                } elseif (isset($cell->is->t)) {
                                    // String inline
                                    $value = (string)$cell->is->t;
                                }
                                
                                $rowData[] = $value;
                                $cellCount++;
                            }
                        }
                        
                        if ($cellCount > 0) {
                            $rows[] = $rowData;
                            $rowCount++;
                        }
                    }
                }
                
                $sheets[$sheetName] = $rows;
                $debug[] = "Hoja '$sheetName' procesada: $rowCount filas";
            } else {
                $debug[] = "Error parseando XML de hoja '$sheetName'";
            }
        } else {
            $debug[] = "No se encontró archivo: $sheetFile";
        }
    }
    
    $zip->close();
    
    return [
        'sheets' => $sheets,
        'debug' => $debug
    ];
}

// Intentar leer el archivo Excel
echo "<h2>📊 Leyendo datos del Excel</h2>";

$result = readExcelImproved($excelFile);

if (isset($result['error'])) {
    echo "<div class='error'>❌ Error: " . $result['error'] . "</div>";
    echo "</div>";
    exit;
}

$sheets = $result['sheets'];
$debug = $result['debug'];

// Mostrar información de debug
echo "<h3>🔍 Información de debug</h3>";
echo "<div class='debug'>";
foreach ($debug as $line) {
    echo htmlspecialchars($line) . "<br>";
}
echo "</div>";

if (empty($sheets)) {
    echo "<div class='error'>❌ No se pudieron leer las hojas del Excel</div>";
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
    
    // Mostrar preview de los primeros 15 registros
    echo "<div class='data-preview'>";
    echo "<table>";
    
    $maxRows = min(15, count($rows));
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
    
    if (count($rows) > 15) {
        echo "<tr><td colspan='" . count($rows[0]) . "'><em>... y " . (count($rows) - 15) . " filas más</em></td></tr>";
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
if (empty($extraSheets)) {
    echo "• ℹ️ Ninguna hoja adicional<br>";
} else {
    foreach ($extraSheets as $extra) {
        echo "• ℹ️ <strong>$extra:</strong> Hoja adicional<br>";
    }
}
echo "</div>";

// Próximos pasos
echo "<h2>🔗 Próximos pasos</h2>";
echo "<div class='info'>";
if (count($foundSheets) >= 2) {
    echo "<a href='import_excel_data.php' class='btn'>📥 Importar Datos a BD</a><br><br>";
}
echo "<a href='upload_excel.php' class='btn'>📤 Subir Otro Archivo</a><br><br>";
echo "<a href='read_excel_data.php' class='btn'>📖 Script Original</a><br><br>";
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