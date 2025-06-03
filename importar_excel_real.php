<?php
session_start();
require_once 'sistema/config.php';

// Establecer sesión de admin
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_user'] = 'admin';

echo "<h2>📊 IMPORTANDO DATOS REALES DESDE EXCEL</h2>";
echo "<p>Archivo: <strong>uploads/xls-referencia.xlsx</strong></p>";

// Función para leer Excel (basada en read_excel_data_fixed.php)
function readExcelReal($filePath) {
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) !== TRUE) {
        return ['error' => 'No se pudo abrir el archivo ZIP'];
    }
    
    $sheets = [];
    
    // Leer workbook.xml para obtener nombres de hojas
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
    
    // Obtener información de las hojas del workbook
    $sheetDefinitions = [];
    if (isset($workbook->sheets->sheet)) {
        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetId = (string)$sheet['sheetId'];
            $sheetName = (string)$sheet['name'];
            $rId = (string)$sheet['r:id'];
            
            $sheetDefinitions[] = [
                'id' => $sheetId,
                'name' => $sheetName,
                'rId' => $rId
            ];
        }
    }
    
    // Leer sharedStrings.xml para obtener strings compartidos
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
    
    // Mapear hojas definidas con archivos físicos
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
    return ['success' => true, 'sheets' => $sheets];
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Verificar que el archivo existe
    $archivo = 'uploads/xls-referencia.xlsx';
    if (!file_exists($archivo)) {
        throw new Exception("El archivo $archivo no existe");
    }
    
    echo "<h3>1. Leyendo archivo Excel...</h3>";
    $result = readExcelReal($archivo);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }
    
    $sheets = $result['sheets'];
    echo "✅ Archivo leído exitosamente<br>";
    echo "📋 Hojas encontradas: " . implode(', ', array_keys($sheets)) . "<br><br>";
    
    // Mostrar preview de datos
    foreach ($sheets as $sheetName => $rows) {
        echo "<h4>📄 Hoja: $sheetName</h4>";
        echo "<p>Total filas: " . count($rows) . "</p>";
        
        if (count($rows) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr style='background: #f0f0f0;'>";
            
            // Mostrar encabezados (primera fila)
            if (isset($rows[0])) {
                foreach ($rows[0] as $i => $header) {
                    echo "<th style='padding: 5px; border: 1px solid #ccc;'>Col " . chr(65 + $i) . "</th>";
                }
            }
            echo "</tr>";
            
            // Mostrar primeras 10 filas
            $maxRows = min(10, count($rows));
            for ($i = 0; $i < $maxRows; $i++) {
                echo "<tr>";
                foreach ($rows[$i] as $cell) {
                    $cellValue = htmlspecialchars($cell);
                    if (strlen($cellValue) > 50) {
                        $cellValue = substr($cellValue, 0, 50) . '...';
                    }
                    echo "<td style='padding: 5px; border: 1px solid #ccc;'>$cellValue</td>";
                }
                echo "</tr>";
            }
            
            if (count($rows) > 10) {
                echo "<tr><td colspan='" . count($rows[0]) . "' style='text-align: center; font-style: italic; padding: 10px;'>... y " . (count($rows) - 10) . " filas más</td></tr>";
            }
            
            echo "</table>";
        }
        echo "<hr>";
    }
    
    echo "<h3>2. ¿Proceder con la importación?</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0; border-radius: 5px;'>";
    echo "⚠️ <strong>IMPORTANTE:</strong> Esto eliminará todos los datos actuales y los reemplazará con los datos del Excel.<br>";
    echo "Revisa los datos mostrados arriba para asegurarte de que son correctos.";
    echo "</div>";
    
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='confirmar_importacion' value='1'>";
    echo "<button type='submit' style='background: #dc3545; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🔄 SÍ, IMPORTAR DATOS REALES</button>";
    echo "</form>";
    
    echo "<a href='admin/gestionar_datos.php' style='background: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>❌ Cancelar y volver al panel</a>";
    
    // Procesar importación si se confirma
    if (isset($_POST['confirmar_importacion'])) {
        echo "<h3>3. Importando datos...</h3>";
        
        // Limpiar datos existentes
        $pdo->exec('DELETE FROM opciones');
        echo "🗑️ Datos existentes eliminados<br>";
        
        // Verificar/crear categorías
        $categorias = [
            1 => 'ASCENSORES',
            2 => 'ADICIONALES', 
            3 => 'DESCUENTOS'
        ];
        
        foreach ($categorias as $id => $nombre) {
            $stmt = $pdo->prepare('SELECT id FROM categorias WHERE id = ?');
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO categorias (id, nombre, descripcion, orden, activo, created_at) VALUES (?, ?, ?, ?, 1, NOW())');
                $stmt->execute([$id, $nombre, "Categoría de $nombre", $id]);
                echo "✅ Categoría '$nombre' creada<br>";
            }
        }
        
        // Mapeo de hojas a categorías
        $mapeoHojas = [
            'ASCENSORES' => 1,
            'ADICIONALES' => 2,
            'DESCUENTOS' => 3
        ];
        
        $totalImportados = 0;
        
        // Procesar cada hoja
        foreach ($sheets as $nombreHoja => $rows) {
            $nombreHojaUpper = strtoupper($nombreHoja);
            
            // Buscar coincidencia parcial
            $categoriaId = null;
            foreach ($mapeoHojas as $patron => $id) {
                if (strpos($nombreHojaUpper, $patron) !== false) {
                    $categoriaId = $id;
                    break;
                }
            }
            
            if (!$categoriaId) {
                echo "⚠️ Hoja '$nombreHoja' no reconocida, saltando...<br>";
                continue;
            }
            
            echo "<h4>📊 Procesando hoja: $nombreHoja (Categoría ID: $categoriaId)</h4>";
            
            // Saltar la primera fila (encabezados) y procesar datos
            $contadorFila = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Verificar que hay datos en la fila
                $hayDatos = false;
                foreach ($row as $cell) {
                    if (!empty(trim($cell))) {
                        $hayDatos = true;
                        break;
                    }
                }
                
                if (!$hayDatos) {
                    continue; // Saltar filas vacías
                }
                
                // Extraer información básica
                $nombre = isset($row[0]) ? trim($row[0]) : '';
                if (empty($nombre)) {
                    continue;
                }
                
                // Determinar si es título (buscar patrones)
                $esTitulo = 0;
                $patronesTitulo = ['EQUIPOS', 'SERVICIOS', 'DESCUENTOS', 'ADICIONALES', 'SISTEMAS', 'CONFORT', 'ILUMINACIÓN', 'ACABADOS', 'PROMOCIONES'];
                foreach ($patronesTitulo as $patron) {
                    if (stripos($nombre, $patron) !== false) {
                        $esTitulo = 1;
                        break;
                    }
                }
                
                // Extraer precios (columnas 1, 2, 3 típicamente)
                $precio90 = 0;
                $precio160 = 0;
                $precio270 = 0;
                
                // Buscar precios en las columnas
                for ($col = 1; $col < min(6, count($row)); $col++) {
                    $valor = isset($row[$col]) ? trim($row[$col]) : '';
                    if (is_numeric($valor) && $valor != 0) {
                        if ($col == 1) {
                            $precio90 = floatval($valor);
                        } elseif ($col == 2) {
                            $precio160 = floatval($valor);
                        } elseif ($col == 3) {
                            $precio270 = floatval($valor);
                        }
                    }
                }
                
                // Si es título, limpiar precios
                if ($esTitulo) {
                    $precio90 = 0;
                    $precio160 = 0;
                    $precio270 = 0;
                }
                
                // Insertar en la base de datos
                $stmt = $pdo->prepare('
                    INSERT INTO opciones (
                        categoria_id, nombre, descripcion, precio, 
                        precio_90_dias, precio_160_dias, precio_270_dias, 
                        descuento, orden, activo, es_titulo, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, 1, ?, NOW())
                ');
                
                $stmt->execute([
                    $categoriaId,
                    $nombre,
                    isset($row[4]) ? trim($row[4]) : null, // Descripción en columna E
                    $precio90,
                    $precio90,
                    $precio160,
                    $precio270,
                    $i, // Orden basado en la fila
                    $esTitulo
                ]);
                
                $tipo = $esTitulo ? '📂 TÍTULO' : '⚙️ OPCIÓN';
                $precios = $esTitulo ? '-' : "($precio90, $precio160, $precio270)";
                echo "✅ $tipo: $nombre $precios<br>";
                
                $contadorFila++;
                $totalImportados++;
            }
            
            echo "📊 Total importados de '$nombreHoja': $contadorFila<br><br>";
        }
        
        // Resumen final
        echo "<h3>4. Resumen de importación</h3>";
        
        foreach ($categorias as $id => $nombre) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM opciones WHERE categoria_id = ?');
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            echo "📁 $nombre: $count opciones<br>";
        }
        
        $stmt = $pdo->query('SELECT COUNT(*) FROM opciones WHERE es_titulo = 1');
        $titulos = $stmt->fetchColumn();
        
        $stmt = $pdo->query('SELECT COUNT(*) FROM opciones WHERE es_titulo = 0');
        $opciones_regulares = $stmt->fetchColumn();
        
        echo "<br><strong>📊 RESUMEN FINAL:</strong><br>";
        echo "- Total importados: $totalImportados<br>";
        echo "- Títulos: $titulos<br>";
        echo "- Opciones regulares: $opciones_regulares<br>";
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 20px 0; border-radius: 5px;'>";
        echo "🎉 <strong>IMPORTACIÓN REAL COMPLETADA EXITOSAMENTE</strong><br>";
        echo "Los datos reales del Excel han sido importados correctamente.";
        echo "</div>";
        
        echo "<hr>";
        echo "<h3>🌐 ACCESO AL PANEL:</h3>";
        echo "<a href='admin/gestionar_datos.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>IR A GESTIONAR DATOS</a><br><br>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; margin: 20px 0; border-radius: 5px;'>";
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}
?> 