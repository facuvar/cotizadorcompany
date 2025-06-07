<?php
/**
 * 🚀 SOLUCIÓN RÁPIDA PARA RAILWAY
 * 
 * Exporta la base de datos local completa y genera un archivo
 * SQL listo para subir a Railway
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function exportDatabase($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- EXPORTACIÓN COMPLETA DE BASE DE DATOS\n";
        $output .= "-- Base: {$config['name']}\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n\n";
        
        // Obtener todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $output .= "-- ========================================\n";
            $output .= "-- Tabla: $table\n";
            $output .= "-- ========================================\n\n";
            
            // Eliminar tabla si existe
            $output .= "DROP TABLE IF EXISTS `$table`;\n\n";
            
            // Obtener estructura de la tabla
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
            
            // Obtener datos de la tabla
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $output .= "-- Datos de la tabla $table ($count registros)\n";
                
                $stmt = $pdo->query("SELECT * FROM `$table`");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    // Obtener nombres de columnas
                    $columns = array_keys($rows[0]);
                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    
                    $output .= "INSERT INTO `$table` ($columnsList) VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    
                    $output .= implode(",\n", $values) . ";\n\n";
                }
            } else {
                $output .= "-- Tabla $table está vacía\n\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $output .= "COMMIT;\n";
        
        return [
            'success' => true,
            'sql' => $output,
            'tables' => count($tables),
            'size' => strlen($output)
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
    $export = exportDatabase($localConfig);
    
    if ($export['success']) {
        // Guardar archivo SQL
        $filename = 'company_presupuestos_completo_' . date('Y-m-d_H-i-s') . '.sql';
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
    <title>🚀 Solución Rápida Railway</title>
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
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #27ae60;
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
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover { background: #219a52; }
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
        
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Solución Rápida para Railway</h1>
            <p>Exporta y sincroniza tu base de datos local con Railway</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <!-- Paso 1: Exportar -->
                <div class="info-box">
                    <h3>📋 Instrucciones</h3>
                    <p>Esta herramienta exportará tu base de datos local completa y generará un archivo SQL listo para subir a Railway.</p>
                    
                    <div class="steps">
                        <div class="step">
                            <strong>Exportar base de datos local</strong>
                            <p>Haz clic en el botón de abajo para exportar toda la base de datos <code>company_presupuestos</code></p>
                        </div>
                        
                        <div class="step">
                            <strong>Descargar archivo SQL</strong>
                            <p>Se generará un archivo SQL con toda la estructura y datos</p>
                        </div>
                        
                        <div class="step">
                            <strong>Subir a Railway</strong>
                            <p>Usa el archivo generado en la página de upload de Railway</p>
                        </div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h3>🎯 ¿Qué incluye la exportación?</h3>
                    <ul>
                        <li>✅ Todas las tablas con estructura completa</li>
                        <li>✅ Todos los datos (categorías, opciones, precios, etc.)</li>
                        <li>✅ Índices y claves foráneas</li>
                        <li>✅ Configuración optimizada para Railway</li>
                    </ul>
                </div>
                
                <form method="POST" style="text-align: center;">
                    <button type="submit" name="export" class="btn btn-success" style="font-size: 18px; padding: 20px 40px;">
                        📤 EXPORTAR BASE DE DATOS COMPLETA
                    </button>
                </form>
                
            <?php elseif ($export['success']): ?>
                <!-- Exportación exitosa -->
                <div class="info-box success">
                    <h3>✅ ¡Exportación Exitosa!</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Tablas exportadas</td><td><?php echo $export['tables']; ?></td></tr>
                        <tr><td>Tamaño del archivo</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha de exportación</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = 'company_presupuestos_completo_' . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Archivo</h3>
                        <p>Tu archivo SQL está listo para descargar:</p>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="info-box warning">
                        <h3>🚀 Siguiente Paso: Subir a Railway</h3>
                        <div class="steps">
                            <div class="step">
                                <strong>Ir a la página de upload de Railway</strong>
                                <p><a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn">🚂 Abrir Upload Railway</a></p>
                            </div>
                            
                            <div class="step">
                                <strong>Subir el archivo descargado</strong>
                                <p>Arrastra o selecciona el archivo <code><?php echo basename($filename); ?></code> que acabas de descargar</p>
                            </div>
                            
                            <div class="step">
                                <strong>Ejecutar la importación</strong>
                                <p>Haz clic en "SUBIR Y EJECUTAR SQL" y espera a que termine</p>
                            </div>
                            
                            <div class="step">
                                <strong>Verificar el cotizador</strong>
                                <p>Una vez completado, verifica que el cotizador funcione igual que en local</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Error en exportación -->
                <div class="info-box error">
                    <h3>❌ Error en la Exportación</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>🔗 Enlaces Útiles</h3>
                <a href="diagnostico_railway.php" class="btn">🔍 Diagnóstico Local</a>
                <a href="https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php" class="btn" target="_blank">🚂 Diagnóstico Railway</a>
                <a href="upload_database_completa_standalone.php" class="btn">📤 Upload Local</a>
                <a href="cotizador.php" class="btn">💼 Cotizador Local</a>
            </div>
        </div>
    </div>
</body>
</html> 