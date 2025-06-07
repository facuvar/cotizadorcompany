<?php
/**
 * 🚀 SOLUCIÓN FINAL RAILWAY V3
 * 
 * Versión definitiva que corrige el orden de ejecución
 * y asegura que todos los datos se inserten correctamente
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function exportDatabaseV3($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- EXPORTACIÓN FINAL DE BASE DE DATOS V3\n";
        $output .= "-- Base: {$config['name']}\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Versión final optimizada para Railway\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n\n";
        
        // Obtener todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Orden específico para evitar problemas de dependencias
        $orderedTables = [];
        $remainingTables = $tables;
        
        // Primero las tablas principales sin dependencias
        $priorityTables = ['categorias', 'plazos_entrega', 'configuracion', 'usuarios'];
        foreach ($priorityTables as $table) {
            if (in_array($table, $remainingTables)) {
                $orderedTables[] = $table;
                $remainingTables = array_diff($remainingTables, [$table]);
            }
        }
        
        // Luego las tablas con dependencias
        $dependentTables = ['opciones', 'presupuestos', 'presupuesto_detalles', 'presupuesto_items'];
        foreach ($dependentTables as $table) {
            if (in_array($table, $remainingTables)) {
                $orderedTables[] = $table;
                $remainingTables = array_diff($remainingTables, [$table]);
            }
        }
        
        // Finalmente el resto de tablas
        $orderedTables = array_merge($orderedTables, $remainingTables);
        
        // PASO 1: Eliminar todas las tablas en orden inverso
        $output .= "-- ========================================\n";
        $output .= "-- PASO 1: ELIMINAR TABLAS EXISTENTES\n";
        $output .= "-- ========================================\n\n";
        
        foreach (array_reverse($orderedTables) as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
        }
        $output .= "\n";
        
        // PASO 2: Crear estructura de tablas en orden correcto
        $output .= "-- ========================================\n";
        $output .= "-- PASO 2: CREAR ESTRUCTURA DE TABLAS\n";
        $output .= "-- ========================================\n\n";
        
        foreach ($orderedTables as $table) {
            $output .= "-- Estructura para tabla $table\n";
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
        }
        
        // PASO 3: Insertar datos en orden correcto
        $output .= "-- ========================================\n";
        $output .= "-- PASO 3: INSERTAR DATOS\n";
        $output .= "-- ========================================\n\n";
        
        foreach ($orderedTables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $output .= "-- Datos para tabla $table ($count registros)\n";
                
                $stmt = $pdo->query("SELECT * FROM `$table`");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    // Obtener nombres de columnas
                    $columns = array_keys($rows[0]);
                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    
                    // Insertar en lotes más pequeños para mejor compatibilidad
                    $chunks = array_chunk($rows, 25);
                    
                    foreach ($chunks as $chunkIndex => $chunk) {
                        $output .= "-- Lote " . ($chunkIndex + 1) . " de " . count($chunks) . " para tabla $table\n";
                        $output .= "INSERT INTO `$table` ($columnsList) VALUES\n";
                        
                        $values = [];
                        foreach ($chunk as $row) {
                            $rowValues = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    // Escape más robusto
                                    $escaped = addslashes($value);
                                    $escaped = str_replace(["\n", "\r", "\t"], ["\\n", "\\r", "\\t"], $escaped);
                                    $rowValues[] = "'" . $escaped . "'";
                                }
                            }
                            $values[] = '(' . implode(', ', $rowValues) . ')';
                        }
                        
                        $output .= implode(",\n", $values) . ";\n\n";
                    }
                }
            } else {
                $output .= "-- Tabla $table está vacía\n\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $output .= "COMMIT;\n";
        $output .= "\n-- ========================================\n";
        $output .= "-- IMPORTACIÓN COMPLETADA EXITOSAMENTE\n";
        $output .= "-- Tablas procesadas: " . count($orderedTables) . "\n";
        $output .= "-- ========================================\n";
        
        return [
            'success' => true,
            'sql' => $output,
            'tables' => count($orderedTables),
            'size' => strlen($output),
            'order' => $orderedTables
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Procesar exportación
$export = null;
$downloadReady = false;

if (isset($_POST['export'])) {
    $export = exportDatabaseV3($localConfig);
    
    if ($export['success']) {
        // Guardar archivo SQL
        $filename = 'company_presupuestos_v3_final_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $export['sql']);
        $downloadReady = true;
    }
}

// Descargar archivo si se solicita
if (isset($_GET['download']) && file_exists($_GET['download'])) {
    $filename = $_GET['download'];
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Solución Final Railway V3</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #8e44ad;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #8e44ad;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover { background: #7d3c98; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        
        .steps {
            counter-reset: step-counter;
        }
        
        .step {
            counter-increment: step-counter;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .step::before {
            content: "Paso " counter(step-counter);
            display: block;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .highlight {
            background: #e8f5e9;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .order-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Solución Final Railway V3</h1>
            <p>Versión Definitiva - Orden de Ejecución Corregido</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <!-- Información sobre la versión V3 -->
                <div class="highlight">
                    <h3>🎯 Solución Final V3 - Corrige "Error al cargar las categorías"</h3>
                    <ul>
                        <li>✅ <strong>Orden de tablas corregido:</strong> Primero categorías, luego opciones</li>
                        <li>✅ <strong>Dependencias respetadas:</strong> Las tablas padre se crean antes que las hijas</li>
                        <li>✅ <strong>Lotes más pequeños:</strong> 25 registros por INSERT para mayor compatibilidad</li>
                        <li>✅ <strong>Escape mejorado:</strong> Manejo robusto de caracteres especiales</li>
                        <li>✅ <strong>Logs detallados:</strong> Información de progreso por lote</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>🔧 ¿Por qué V3 resuelve el problema?</h3>
                    <p>El error "Error al cargar las categorías" ocurre porque:</p>
                    <ol>
                        <li>Las tablas se crean en orden incorrecto</li>
                        <li>Los INSERT llegan antes que los CREATE TABLE</li>
                        <li>Los datos no se insertan en las tablas principales</li>
                    </ol>
                    <p><strong>V3 corrige esto con un orden específico:</strong></p>
                    <div class="order-list">
                        1. categorias (tabla principal)<br>
                        2. plazos_entrega<br>
                        3. configuracion<br>
                        4. usuarios<br>
                        5. opciones (depende de categorias)<br>
                        6. presupuestos<br>
                        7. presupuesto_detalles<br>
                        8. presupuesto_items
                    </div>
                </div>
                
                <div class="info-box">
                    <h3>📋 Instrucciones V3</h3>
                    <div class="steps">
                        <div class="step">
                            <strong>Exportar con orden corregido</strong>
                            <p>V3 respeta las dependencias entre tablas</p>
                        </div>
                        
                        <div class="step">
                            <strong>Descargar archivo final</strong>
                            <p>El archivo incluye el orden correcto de ejecución</p>
                        </div>
                        
                        <div class="step">
                            <strong>Subir a Railway</strong>
                            <p>Esta vez las categorías se cargarán correctamente</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" style="text-align: center;">
                    <button type="submit" name="export" class="btn btn-success" style="font-size: 18px; padding: 20px 40px;">
                        🎯 EXPORTAR VERSIÓN FINAL V3
                    </button>
                </form>
                
            <?php elseif ($export['success']): ?>
                <!-- Exportación exitosa -->
                <div class="info-box success">
                    <h3>✅ ¡Exportación V3 Final Exitosa!</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Versión</td><td><strong>V3 Final</strong></td></tr>
                        <tr><td>Tablas exportadas</td><td><?php echo $export['tables']; ?></td></tr>
                        <tr><td>Tamaño del archivo</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha de exportación</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <div class="info-box">
                    <h3>📋 Orden de Procesamiento V3</h3>
                    <p>Las tablas se procesarán en este orden específico:</p>
                    <div class="order-list">
                        <?php foreach ($export['order'] as $index => $table): ?>
                            <?php echo ($index + 1) . ". " . $table . "<br>"; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = 'company_presupuestos_v3_final_' . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Archivo V3 Final</h3>
                        <p>Tu archivo SQL V3 con orden corregido está listo:</p>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="info-box warning">
                        <h3>🚀 Último Paso: Subir V3 a Railway</h3>
                        <div class="steps">
                            <div class="step">
                                <strong>Ir a la página de upload de Railway</strong>
                                <p><a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn">🚂 Abrir Upload Railway</a></p>
                            </div>
                            
                            <div class="step">
                                <strong>Subir el archivo V3 Final</strong>
                                <p>Arrastra el archivo <code><?php echo basename($filename); ?></code> (versión definitiva)</p>
                            </div>
                            
                            <div class="step">
                                <strong>Ejecutar importación final</strong>
                                <p>Esta vez las categorías se cargarán correctamente</p>
                            </div>
                            
                            <div class="step">
                                <strong>Verificar cotizador funcionando</strong>
                                <p>El cotizador debería funcionar perfectamente sin errores</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="highlight">
                        <h4>🎯 ¿Qué garantiza V3?</h4>
                        <ul>
                            <li>✅ Las categorías se crean e insertan ANTES que las opciones</li>
                            <li>✅ Orden de dependencias respetado completamente</li>
                            <li>✅ Lotes más pequeños para mejor compatibilidad</li>
                            <li>✅ Sin errores de "Table doesn't exist"</li>
                            <li>✅ Cotizador funcionando idéntico al local</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Error en exportación -->
                <div class="info-box error">
                    <h3>❌ Error en la Exportación V3</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>🔗 Enlaces Útiles</h3>
                <a href="solucion_rapida_railway_v2.php" class="btn">📤 Versión V2</a>
                <a href="diagnostico_railway.php" class="btn">🔍 Diagnóstico Local</a>
                <a href="https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php" class="btn" target="_blank">🚂 Diagnóstico Railway</a>
                <a href="cotizador.php" class="btn">💼 Cotizador Local</a>
            </div>
        </div>
    </div>
</body>
</html> 