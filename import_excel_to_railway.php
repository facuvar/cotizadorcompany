<?php
/**
 * Script para importar datos del Excel a la base de datos de Railway
 * Importa ASCENSORES, ADICIONALES y DESCUENTOS
 */

echo "<h1>📥 IMPORTAR EXCEL A RAILWAY</h1>";
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
echo "<h2>🔌 Conectar a Railway</h2>";

// Auto-detectar configuración de Railway
$isRailway = false;
$config = [];

// Intentar variables de Railway
if (getenv('MYSQLHOST')) {
    $isRailway = true;
    $config = [
        'host' => getenv('MYSQLHOST'),
        'user' => getenv('MYSQLUSER'),
        'pass' => getenv('MYSQLPASSWORD'),
        'name' => getenv('MYSQLDATABASE'),
        'port' => getenv('MYSQLPORT') ?: 3306
    ];
    echo "<div class='info'>🚂 Detectado entorno Railway</div>";
} elseif (getenv('DB_HOST')) {
    $isRailway = true;
    $config = [
        'host' => getenv('DB_HOST'),
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
        'name' => getenv('DB_NAME'),
        'port' => getenv('DB_PORT') ?: 3306
    ];
    echo "<div class='info'>🚂 Detectado Railway con variables personalizadas</div>";
} else {
    // Configuración local
    $config = [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'presupuestos_ascensores',
        'port' => 3306
    ];
    echo "<div class='info'>🏠 Usando configuración local</div>";
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
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

// Limpiar tablas existentes
echo "<h2>🧹 Limpiar datos existentes</h2>";

try {
    $pdo->exec("DELETE FROM opciones");
    $pdo->exec("DELETE FROM categorias");
    $pdo->exec("DELETE FROM plazos_entrega");
    $pdo->exec("ALTER TABLE categorias AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE opciones AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE plazos_entrega AUTO_INCREMENT = 1");
    echo "<div class='success'>✅ Tablas limpiadas</div>";
} catch (PDOException $e) {
    echo "<div class='warning'>⚠️ Error limpiando tablas: " . $e->getMessage() . "</div>";
}

// Crear plazos de entrega
echo "<h2>⏰ Crear plazos de entrega</h2>";

$plazos = [
    ['nombre' => '90 dias', 'dias' => 90, 'orden' => 1],
    ['nombre' => '160-180 dias', 'dias' => 170, 'orden' => 2],
    ['nombre' => '270 dias', 'dias' => 270, 'orden' => 3]
];

$plazoIds = [];
foreach ($plazos as $plazo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO plazos_entrega (nombre, dias, orden) VALUES (?, ?, ?)");
        $stmt->execute([$plazo['nombre'], $plazo['dias'], $plazo['orden']]);
        $plazoIds[$plazo['nombre']] = $pdo->lastInsertId();
        echo "<div class='progress'>✅ Plazo creado: {$plazo['nombre']}</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creando plazo {$plazo['nombre']}: " . $e->getMessage() . "</div>";
    }
}

// Procesar hoja ASCENSORES
echo "<h2>🏗️ Importar ASCENSORES</h2>";

if (isset($sheets['ASCENSORES'])) {
    $ascensores = $sheets['ASCENSORES'];
    
    // Crear categoría ASCENSORES
    try {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
        $stmt->execute(['ASCENSORES', 'Equipos electromecánicos de ascensores', 1]);
        $categoriaAscensores = $pdo->lastInsertId();
        echo "<div class='success'>✅ Categoría ASCENSORES creada (ID: $categoriaAscensores)</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creando categoría ASCENSORES: " . $e->getMessage() . "</div>";
    }
    
    // Procesar filas de ascensores (saltar encabezado)
    $procesados = 0;
    for ($i = 1; $i < count($ascensores); $i++) {
        $row = $ascensores[$i];
        
        if (count($row) >= 7 && !empty($row[0])) {
            $nombre = $row[0];
            $precio90 = isset($row[5]) ? floatval($row[5]) : 0;
            $precio160 = isset($row[4]) ? floatval($row[4]) : 0;
            $precio270 = isset($row[6]) ? floatval($row[6]) : 0;
            
            if ($precio90 > 0 || $precio160 > 0 || $precio270 > 0) {
                try {
                    // Insertar opción
                    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, orden) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$categoriaAscensores, $nombre, $precio90, $precio160, $precio270, $i]);
                    $procesados++;
                } catch (PDOException $e) {
                    echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    
    echo "<div class='success'>✅ ASCENSORES procesados: $procesados registros</div>";
}

// Procesar hoja ADICIONALES
echo "<h2>🔧 Importar ADICIONALES</h2>";

if (isset($sheets['ADICIONALES'])) {
    $adicionales = $sheets['ADICIONALES'];
    
    // Crear categoría ADICIONALES
    try {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
        $stmt->execute(['ADICIONALES', 'Opciones adicionales para ascensores', 2]);
        $categoriaAdicionales = $pdo->lastInsertId();
        echo "<div class='success'>✅ Categoría ADICIONALES creada (ID: $categoriaAdicionales)</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creando categoría ADICIONALES: " . $e->getMessage() . "</div>";
    }
    
    // Procesar filas de adicionales (saltar encabezados)
    $procesados = 0;
    for ($i = 2; $i < count($adicionales); $i++) {
        $row = $adicionales[$i];
        
        if (count($row) >= 4 && !empty($row[0])) {
            $nombre = $row[0];
            $precio90 = isset($row[2]) ? floatval($row[2]) : 0;
            $precio160 = isset($row[1]) ? floatval($row[1]) : 0;
            $precio270 = isset($row[3]) ? floatval($row[3]) : 0;
            
            if ($precio90 > 0 || $precio160 > 0 || $precio270 > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, precio_90_dias, precio_160_dias, precio_270_dias, orden) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$categoriaAdicionales, $nombre, $precio90, $precio160, $precio270, $i]);
                    $procesados++;
                } catch (PDOException $e) {
                    echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    
    echo "<div class='success'>✅ ADICIONALES procesados: $procesados registros</div>";
}

// Procesar hoja DESCUENTOS
echo "<h2>💰 Importar DESCUENTOS</h2>";

if (isset($sheets['DESCUENTOS'])) {
    $descuentos = $sheets['DESCUENTOS'];
    
    // Crear categoría DESCUENTOS
    try {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
        $stmt->execute(['DESCUENTOS', 'Formas de pago y descuentos', 3]);
        $categoriaDescuentos = $pdo->lastInsertId();
        echo "<div class='success'>✅ Categoría DESCUENTOS creada (ID: $categoriaDescuentos)</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error creando categoría DESCUENTOS: " . $e->getMessage() . "</div>";
    }
    
    // Procesar descuentos
    $procesados = 0;
    for ($i = 1; $i < count($descuentos); $i++) {
        $row = $descuentos[$i];
        
        if (count($row) >= 2 && !empty($row[0])) {
            $nombre = $row[0];
            $descuento = isset($row[1]) ? floatval($row[1]) : 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO opciones (categoria_id, nombre, descuento, orden) VALUES (?, ?, ?, ?)");
                $stmt->execute([$categoriaDescuentos, $nombre, $descuento, $i]);
                $procesados++;
            } catch (PDOException $e) {
                echo "<div class='warning'>⚠️ Error insertando $nombre: " . $e->getMessage() . "</div>";
            }
        }
    }
    
    echo "<div class='success'>✅ DESCUENTOS procesados: $procesados registros</div>";
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
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error verificando: " . $e->getMessage() . "</div>";
}

// Enlaces útiles
echo "<h2>🔗 Próximos pasos</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' class='btn'>🚀 Probar Cotizador</a><br><br>";
echo "<a href='admin/' target='_blank' class='btn'>🔐 Panel Admin</a><br><br>";
echo "<a href='railway_debug.php' target='_blank' class='btn'>🔍 Diagnóstico Railway</a><br><br>";
echo "</div>";

echo "</div>";
?> 