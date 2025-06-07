<?php
/**
 * 🚀 SOLUCIÓN RÁPIDA PARA RAILWAY V2
 * 
 * Versión mejorada que genera SQL más compatible
 * con el script de upload de Railway
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function exportDatabaseV2($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- EXPORTACIÓN COMPLETA DE BASE DE DATOS V2\n";
        $output .= "-- Base: {$config['name']}\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Versión optimizada para Railway\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n\n";
        
        // Obtener todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // PASO 1: Eliminar todas las tablas primero
        $output .= "-- ========================================\n";
        $output .= "-- PASO 1: ELIMINAR TABLAS EXISTENTES\n";
        $output .= "-- ========================================\n\n";
        
        foreach ($tables as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
        }
        $output .= "\n";
        
        // PASO 2: Crear estructura de tablas
        $output .= "-- ========================================\n";
        $output .= "-- PASO 2: CREAR ESTRUCTURA DE TABLAS\n";
        $output .= "-- ========================================\n\n";
        
        foreach ($tables as $table) {
            $output .= "-- Estructura para tabla $table\n";
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
        }
        
        // PASO 3: Insertar datos
        $output .= "-- ========================================\n";
        $output .= "-- PASO 3: INSERTAR DATOS\n";
        $output .= "-- ========================================\n\n";
        
        foreach ($tables as $table) {
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
                    
                    // Insertar en lotes de 50 registros para evitar statements muy largos
                    $chunks = array_chunk($rows, 50);
                    
                    foreach ($chunks as $chunk) {
                        $output .= "INSERT INTO `$table` ($columnsList) VALUES\n";
                        
                        $values = [];
                        foreach ($chunk as $row) {
                            $rowValues = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    // Escapar comillas y caracteres especiales
                                    $escaped = str_replace(
                                        ["\\", "'", "\n", "\r", "\t"],
                                        ["\\\\", "\\'", "\\n", "\\r", "\\t"],
                                        $value
                                    );
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
        $output .= "-- IMPORTACIÓN COMPLETADA\n";
        $output .= "-- ========================================\n";
        
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
    $export = exportDatabaseV2($localConfig);
    
    if ($export['success']) {
        // Guardar archivo SQL
        $filename = 'company_presupuestos_v2_' . date('Y-m-d_H-i-s') . '.sql';
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
    <title>🚀 Solución Rápida Railway V2</title>
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
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #e74c3c;
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
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover { background: #c0392b; }
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
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Solución Rápida Railway V2</h1>
            <p>Versión Mejorada - SQL Optimizado para Railway</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <!-- Información sobre la versión V2 -->
                <div class="highlight">
                    <h3>🆕 ¿Qué hay de nuevo en V2?</h3>
                    <ul>
                        <li>✅ <strong>Eliminación completa de tablas:</strong> Primero elimina todas las tablas existentes</li>
                        <li>✅ <strong>Recreación limpia:</strong> Crea toda la estructura desde cero</li>
                        <li>✅ <strong>Inserción en lotes:</strong> Divide los datos en chunks más pequeños</li>
                        <li>✅ <strong>Mejor escape de caracteres:</strong> Maneja caracteres especiales correctamente</li>
                        <li>✅ <strong>Compatibilidad mejorada:</strong> Optimizado para el script de upload de Railway</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>📋 Instrucciones</h3>
                    <p>Esta versión mejorada resuelve los problemas de tablas existentes y genera un SQL más compatible.</p>
                    
                    <div class="steps">
                        <div class="step">
                            <strong>Exportar con V2</strong>
                            <p>Genera un archivo SQL optimizado que elimina y recrea todas las tablas</p>
                        </div>
                        
                        <div class="step">
                            <strong>Descargar archivo mejorado</strong>
                            <p>El archivo incluye comandos DROP TABLE separados para mejor compatibilidad</p>
                        </div>
                        
                        <div class="step">
                            <strong>Subir a Railway</strong>
                            <p>Usa el nuevo archivo en la página de upload de Railway</p>
                        </div>
                    </div>
                </div>
                
                <div class="info-box">
                    <h3>🎯 Mejoras de V2</h3>
                    <ul>
                        <li>🔥 <strong>Eliminación forzada:</strong> DROP TABLE IF EXISTS para cada tabla</li>
                        <li>🏗️ <strong>Estructura separada:</strong> CREATE TABLE en sección independiente</li>
                        <li>📦 <strong>Datos en lotes:</strong> INSERT en chunks de 50 registros</li>
                        <li>🛡️ <strong>Escape mejorado:</strong> Manejo correcto de comillas y caracteres especiales</li>
                        <li>⚡ <strong>Transacciones:</strong> Todo en una transacción para consistencia</li>
                    </ul>
                </div>
                
                <form method="POST" style="text-align: center;">
                    <button type="submit" name="export" class="btn btn-success" style="font-size: 18px; padding: 20px 40px;">
                        🚀 EXPORTAR CON V2 MEJORADA
                    </button>
                </form>
                
            <?php elseif ($export['success']): ?>
                <!-- Exportación exitosa -->
                <div class="info-box success">
                    <h3>✅ ¡Exportación V2 Exitosa!</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Versión</td><td><strong>V2 Mejorada</strong></td></tr>
                        <tr><td>Tablas exportadas</td><td><?php echo $export['tables']; ?></td></tr>
                        <tr><td>Tamaño del archivo</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha de exportación</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = 'company_presupuestos_v2_' . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Archivo V2</h3>
                        <p>Tu archivo SQL V2 optimizado está listo:</p>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="info-box warning">
                        <h3>🚀 Siguiente Paso: Subir V2 a Railway</h3>
                        <div class="steps">
                            <div class="step">
                                <strong>Ir a la página de upload de Railway</strong>
                                <p><a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn">🚂 Abrir Upload Railway</a></p>
                            </div>
                            
                            <div class="step">
                                <strong>Subir el archivo V2</strong>
                                <p>Arrastra el archivo <code><?php echo basename($filename); ?></code> (versión mejorada)</p>
                            </div>
                            
                            <div class="step">
                                <strong>Ejecutar importación</strong>
                                <p>Esta vez debería funcionar sin errores de tablas existentes</p>
                            </div>
                            
                            <div class="step">
                                <strong>Verificar cotizador</strong>
                                <p>El cotizador debería funcionar idéntico al local</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="highlight">
                        <h4>🔧 ¿Qué hace diferente V2?</h4>
                        <p>El archivo V2 incluye:</p>
                        <ol>
                            <li><strong>Eliminación completa:</strong> DROP TABLE para todas las tablas</li>
                            <li><strong>Recreación limpia:</strong> CREATE TABLE con estructura completa</li>
                            <li><strong>Inserción optimizada:</strong> Datos en lotes más pequeños</li>
                        </ol>
                        <p>Esto debería resolver los errores de "Table already exists".</p>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Error en exportación -->
                <div class="info-box error">
                    <h3>❌ Error en la Exportación V2</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>🔗 Enlaces Útiles</h3>
                <a href="solucion_rapida_railway.php" class="btn">📤 Versión V1</a>
                <a href="diagnostico_railway.php" class="btn">🔍 Diagnóstico Local</a>
                <a href="https://cotizadorcompany-production.up.railway.app/diagnostico_railway.php" class="btn" target="_blank">🚂 Diagnóstico Railway</a>
                <a href="cotizador.php" class="btn">💼 Cotizador Local</a>
            </div>
        </div>
    </div>
</body>
</html> 