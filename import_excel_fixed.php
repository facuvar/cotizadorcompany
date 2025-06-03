<?php
/**
 * Script para importar datos del Excel a la base de datos (CORREGIDO)
 * Importa ASCENSORES, ADICIONALES y DESCUENTOS
 */

echo "<h1>📥 IMPORTAR EXCEL (CORREGIDO)</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; font-size: 16px; }
    .btn:hover { background: #45a049; }
    .progress { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
</style>";

echo "<div class='container'>";

// Verificar archivo Excel
$excelFile = 'uploads/xls-referencia.xlsx';

echo "<h2>📁 Verificar archivo Excel</h2>";

if (!file_exists($excelFile)) {
    echo "<div class='error'>❌ Archivo no encontrado: $excelFile</div>";
    echo "<div class='info'>Por favor, <a href='upload_excel.php' class='btn'>📤 Subir Archivo Excel</a> primero</div>";
    echo "</div>";
    exit;
}

echo "<div class='success'>✅ Archivo Excel encontrado</div>";

// Conectar a la base de datos
echo "<h2>🔌 Conectar a la base de datos</h2>";

require_once 'sistema/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Conexión exitosa a la base de datos: " . DB_NAME . "</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "</div>";
    exit;
}

// Función para leer Excel (reutilizamos la función corregida)
function readExcelFixed($filePath) {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== TRUE) {
        return ['error' => 'No se pudo abrir el archivo ZIP'];
    }
    
    $sheets = [];
    
    // Leer workbook.xml
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    if (!$workbookXml) {
        $zip->close();
        return ['error' => 'No se encontró workbook.xml'];
    }
    
    $workbook = simplexml_load_string($workbookXml);
    if (!$workbook) {
        $zip->close();
        return ['error' => 'No se pudo parsear workbook.xml'];
    }
    
    // Obtener hojas
    $sheetDefinitions = [];
    if (isset($workbook->sheets->sheet)) {
        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetDefinitions[] = [
                'name' => (string)$sheet['name']
            ];
        }
    }
    
    // Leer sharedStrings
    $sharedStrings = [];
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStringsXml) {
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
    }
    
    // Leer cada hoja
    $sheetIndex = 1;
    foreach ($sheetDefinitions as $sheetDef) {
        $sheetName = $sheetDef['name'];
        $physicalFile = "xl/worksheets/sheet{$sheetIndex}.xml";
        
        $sheetXml = $zip->getFromName($physicalFile);
        if ($sheetXml) {
            $sheet = simplexml_load_string($sheetXml);
            if ($sheet) {
                $rows = [];
                
                if (isset($sheet->sheetData->row)) {
                    foreach ($sheet->sheetData->row as $row) {
                        $rowData = [];
                        
                        if (isset($row->c)) {
                            foreach ($row->c as $cell) {
                                $value = '';
                                $cellType = isset($cell['t']) ? (string)$cell['t'] : '';
                                
                                if (isset($cell->v)) {
                                    $cellValue = (string)$cell->v;
                                    if ($cellType === 's' && isset($sharedStrings[$cellValue])) {
                                        $value = $sharedStrings[$cellValue];
                                    } else {
                                        $value = $cellValue;
                                    }
                                } elseif (isset($cell->is->t)) {
                                    $value = (string)$cell->is->t;
                                }
                                
                                $rowData[] = $value;
                            }
                        }
                        
                        if (!empty($rowData)) {
                            $rows[] = $rowData;
                        }
                    }
                }
                
                $sheets[$sheetName] = $rows;
            }
        }
        
        $sheetIndex++;
    }
    
    $zip->close();
    return ['sheets' => $sheets];
}

// Leer datos del Excel
echo "<h2>📖 Leer datos del Excel</h2>";

$result = readExcelFixed($excelFile);
if (isset($result['error'])) {
    echo "<div class='error'>❌ Error: " . $result['error'] . "</div>";
    echo "</div>";
    exit;
}

$sheets = $result['sheets'];
echo "<div class='success'>✅ Excel leído: " . count($sheets) . " hojas</div>";

// Limpiar solo las opciones (mantener categorías y plazos)
echo "<h2>🧹 Limpiar opciones existentes</h2>";

try {
    $pdo->exec("DELETE FROM opciones");
    $pdo->exec("ALTER TABLE opciones AUTO_INCREMENT = 1");
    echo "<div class='success'>✅ Opciones limpiadas</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error limpiando opciones: " . $e->getMessage() . "</div>";
}

// Obtener IDs de categorías existentes
$categoriaIds = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM categorias");
    while ($row = $stmt->fetch()) {
        $categoriaIds[$row['nombre']] = $row['id'];
    }
    echo "<div class='info'>✅ Categorías disponibles: " . implode(', ', array_keys($categoriaIds)) . "</div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error obteniendo categorías: " . $e->getMessage() . "</div>";
}

// Procesar hoja ASCENSORES
echo "<h2>🏗️ Importar ASCENSORES</h2>";

if (isset($sheets['ASCENSORES'])) {
    $ascensores = $sheets['ASCENSORES'];
    $categoriaId = $categoriaIds['ASCENSORES'] ?? null;
    
    if (!$categoriaId) {
        echo "<div class='error'>❌ Categoría ASCENSORES no encontrada</div>";
    } else {
        echo "<div class='info'>✅ Usando categoría ASCENSORES (ID: $categoriaId)</div>";
        
        // Procesar filas de ascensores (saltar encabezado)
        $procesados = 0;
        for ($i = 1; $i < count($ascensores); $i++) {
            $row = $ascensores[$i];
            
            if (count($row) >= 7 && !empty($row[0])) {
                $nombre = trim($row[0]);
                $precio90 = isset($row[5]) ? floatval($row[5]) : 0;
                $precio160 = isset($row[4]) ? floatval($row[4]) : 0;
                $precio270 = isset($row[6]) ? floatval($row[6]) : 0;
                
                if ($precio90 > 0 || $precio160 > 0 || $precio270 > 0) {
                    try {
                        // Insertar opción con las columnas correctas
                        $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, orden) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$categoriaId, $nombre, $precio90, $precio160, $precio270, $i]);
                        $procesados++;
                        
                        if ($procesados <= 5) {
                            echo "<div class='progress'>✅ $nombre - 90d: $precio90, 160d: $precio160, 270d: $precio270</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
                    }
                }
            }
        }
        
        echo "<div class='success'>✅ ASCENSORES procesados: $procesados registros</div>";
    }
}

// Procesar hoja ADICIONALES
echo "<h2>🔧 Importar ADICIONALES</h2>";

if (isset($sheets['ADICIONALES'])) {
    $adicionales = $sheets['ADICIONALES'];
    $categoriaId = $categoriaIds['ADICIONALES'] ?? null;
    
    if (!$categoriaId) {
        echo "<div class='error'>❌ Categoría ADICIONALES no encontrada</div>";
    } else {
        echo "<div class='info'>✅ Usando categoría ADICIONALES (ID: $categoriaId)</div>";
        
        // Procesar filas de adicionales (saltar encabezados)
        $procesados = 0;
        for ($i = 2; $i < count($adicionales); $i++) {
            $row = $adicionales[$i];
            
            if (count($row) >= 4 && !empty($row[0])) {
                $nombre = trim($row[0]);
                $precio90 = isset($row[2]) ? floatval($row[2]) : 0;
                $precio160 = isset($row[1]) ? floatval($row[1]) : 0;
                $precio270 = isset($row[3]) ? floatval($row[3]) : 0;
                
                if ($precio90 > 0 || $precio160 > 0 || $precio270 > 0) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, orden) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$categoriaId, $nombre, $precio90, $precio160, $precio270, $i]);
                        $procesados++;
                        
                        if ($procesados <= 5) {
                            echo "<div class='progress'>✅ $nombre - 90d: $precio90, 160d: $precio160, 270d: $precio270</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
                    }
                }
            }
        }
        
        echo "<div class='success'>✅ ADICIONALES procesados: $procesados registros</div>";
    }
}

// Procesar hoja DESCUENTOS
echo "<h2>💰 Importar DESCUENTOS</h2>";

if (isset($sheets['DESCUENTOS'])) {
    $descuentos = $sheets['DESCUENTOS'];
    $categoriaId = $categoriaIds['DESCUENTOS'] ?? null;
    
    if (!$categoriaId) {
        echo "<div class='error'>❌ Categoría DESCUENTOS no encontrada</div>";
    } else {
        echo "<div class='info'>✅ Usando categoría DESCUENTOS (ID: $categoriaId)</div>";
        
        // Procesar descuentos
        $procesados = 0;
        for ($i = 1; $i < count($descuentos); $i++) {
            $row = $descuentos[$i];
            
            if (count($row) >= 2 && !empty($row[0])) {
                $nombre = trim($row[0]);
                $descuento = isset($row[1]) ? floatval($row[1]) : 0;
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, descuento, orden) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$categoriaId, $nombre, $descuento, $i]);
                    $procesados++;
                    
                    echo "<div class='progress'>✅ $nombre - Descuento: {$descuento}%</div>";
                } catch (PDOException $e) {
                    echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
                }
            }
        }
        
        echo "<div class='success'>✅ DESCUENTOS procesados: $procesados registros</div>";
    }
}

// Verificar resultados
echo "<h2>📊 Verificar importación</h2>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $categorias = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM opciones");
    $opciones = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM plazos_entrega");
    $plazos = $stmt->fetch()['total'];
    
    echo "<div class='success'>✅ Importación completada:</div>";
    echo "<div class='info'>• Categorías: $categorias</div>";
    echo "<div class='info'>• Opciones: $opciones</div>";
    echo "<div class='info'>• Plazos de entrega: $plazos</div>";
    
    // Mostrar resumen por categoría
    $stmt = $pdo->query("
        SELECT c.nombre, COUNT(o.id) as total 
        FROM categorias c 
        LEFT JOIN opciones o ON c.id = o.categoria_id 
        GROUP BY c.id, c.nombre 
        ORDER BY c.orden
    ");
    
    echo "<h3>📋 Resumen por categoría:</h3>";
    while ($row = $stmt->fetch()) {
        echo "<div class='info'>• {$row['nombre']}: {$row['total']} opciones</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error verificando: " . $e->getMessage() . "</div>";
}

// Enlaces útiles
echo "<h2>🔗 Próximos pasos</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' class='btn'>🚀 Probar Cotizador</a><br><br>";
echo "<a href='admin/' target='_blank' class='btn'>🔐 Panel Admin</a><br><br>";
echo "<a href='check_table_structure.php' target='_blank' class='btn'>🔍 Ver Estructura BD</a><br><br>";
echo "</div>";

echo "</div>";
?> 