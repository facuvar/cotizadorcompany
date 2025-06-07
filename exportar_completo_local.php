<?php
/**
 * 🚀 EXPORTAR BASE DE DATOS LOCAL COMPLETA
 * 
 * Exporta toda la base de datos local que funciona perfectamente
 * para recrear Railway desde cero
 */

// Configuración local
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'company_presupuestos',
    'port' => 3306
];

function exportCompleteDatabase($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $output = "-- ========================================\n";
        $output .= "-- EXPORTACIÓN COMPLETA BASE DE DATOS LOCAL\n";
        $output .= "-- Base: {$config['name']}\n";
        $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Para recrear Railway desde cero\n";
        $output .= "-- ========================================\n\n";
        
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n\n";
        
        // Obtener todas las tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $output .= "-- ========================================\n";
        $output .= "-- ELIMINAR TODAS LAS TABLAS EXISTENTES\n";
        $output .= "-- ========================================\n\n";
        
        // Eliminar todas las tablas primero
        foreach ($tables as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
        }
        $output .= "\n";
        
        $output .= "-- ========================================\n";
        $output .= "-- CREAR ESTRUCTURA DE TODAS LAS TABLAS\n";
        $output .= "-- ========================================\n\n";
        
        // Crear estructura de todas las tablas
        foreach ($tables as $table) {
            $output .= "-- Estructura para tabla $table\n";
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
        }
        
        $output .= "-- ========================================\n";
        $output .= "-- INSERTAR TODOS LOS DATOS\n";
        $output .= "-- ========================================\n\n";
        
        // Insertar datos de todas las tablas
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $output .= "-- Datos para tabla $table ($count registros)\n";
                
                $stmt = $pdo->query("SELECT * FROM `$table`");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
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
                                $escaped = addslashes($value);
                                $escaped = str_replace(["\n", "\r", "\t"], ["\\n", "\\r", "\\t"], $escaped);
                                $rowValues[] = "'" . $escaped . "'";
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
        $output .= "\n-- ========================================\n";
        $output .= "-- BASE DE DATOS RECREADA COMPLETAMENTE\n";
        $output .= "-- Tablas: " . count($tables) . "\n";
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
    $export = exportCompleteDatabase($localConfig);
    
    if ($export['success']) {
        $filename = 'company_presupuestos_completo_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $export['sql']);
        $downloadReady = true;
    }
}

// Descargar archivo
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
    <title>🚀 Exportar DB Local Completa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
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
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover { background: #1e7e34; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        
        .highlight {
            background: #e8f5e9;
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
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
        
        .steps {
            counter-reset: step-counter;
        }
        
        .step {
            counter-increment: step-counter;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .step::before {
            content: "Paso " counter(step-counter);
            display: block;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Recrear Railway desde Cero</h1>
            <p>Exportar Base de Datos Local Completa</p>
        </div>
        
        <div class="content">
            <?php if (!$export): ?>
                <div class="highlight">
                    <h3>🎯 Estrategia: Empezar de Cero</h3>
                    <p>En lugar de intentar arreglar Railway, vamos a:</p>
                    <ul>
                        <li>✅ <strong>Exportar</strong> la base de datos local que funciona perfectamente</li>
                        <li>✅ <strong>Limpiar</strong> completamente Railway</li>
                        <li>✅ <strong>Importar</strong> todo desde cero</li>
                        <li>✅ <strong>Resultado:</strong> Railway idéntico al local</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>📋 ¿Qué incluye esta exportación?</h3>
                    <ul>
                        <li>🗂️ <strong>Todas las tablas</strong> (categorias, opciones, plazos_entrega, etc.)</li>
                        <li>📊 <strong>Todos los datos</strong> exactamente como están en local</li>
                        <li>🏗️ <strong>Estructura completa</strong> con índices y relaciones</li>
                        <li>🔧 <strong>Configuración optimizada</strong> para Railway</li>
                    </ul>
                </div>
                
                <div class="info-box warning">
                    <h3>⚠️ Importante</h3>
                    <p>Esta exportación <strong>eliminará todas las tablas existentes</strong> en Railway y las recreará desde cero. Es exactamente lo que necesitamos para solucionar el problema.</p>
                </div>
                
                <form method="POST" style="text-align: center; margin: 30px 0;">
                    <button type="submit" name="export" class="btn" style="font-size: 18px; padding: 20px 40px;">
                        🚀 EXPORTAR BASE DE DATOS COMPLETA
                    </button>
                </form>
                
            <?php elseif ($export['success']): ?>
                <div class="info-box success">
                    <h3>✅ ¡Exportación Completa Exitosa!</h3>
                    <table>
                        <tr><th>Parámetro</th><th>Valor</th></tr>
                        <tr><td>Tipo</td><td><strong>Exportación Completa</strong></td></tr>
                        <tr><td>Tablas exportadas</td><td><?php echo $export['tables']; ?></td></tr>
                        <tr><td>Tamaño del archivo</td><td><?php echo number_format($export['size'] / 1024, 2); ?> KB</td></tr>
                        <tr><td>Fecha</td><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
                    </table>
                </div>
                
                <?php if ($downloadReady): ?>
                    <?php $filename = 'company_presupuestos_completo_' . date('Y-m-d_H-i-s') . '.sql'; ?>
                    
                    <div class="info-box">
                        <h3>📥 Descargar Exportación Completa</h3>
                        <p style="text-align: center;">
                            <a href="?download=<?php echo $filename; ?>" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                                💾 DESCARGAR <?php echo basename($filename); ?>
                            </a>
                        </p>
                    </div>
                    
                    <div class="highlight">
                        <h3>🚀 Pasos para Recrear Railway</h3>
                        <div class="steps">
                            <div class="step">
                                <strong>Descargar el archivo completo</strong>
                                <p>El archivo contiene toda la base de datos local funcionando</p>
                            </div>
                            
                            <div class="step">
                                <strong>Ir a Railway Upload</strong>
                                <p><a href="https://cotizadorcompany-production.up.railway.app/upload_database_completa_standalone.php" target="_blank" class="btn btn-primary">🚂 Abrir Upload Railway</a></p>
                            </div>
                            
                            <div class="step">
                                <strong>Subir el archivo completo</strong>
                                <p>Este archivo eliminará todo y recreará la base de datos desde cero</p>
                            </div>
                            
                            <div class="step">
                                <strong>Verificar funcionamiento</strong>
                                <p>El cotizador debería funcionar exactamente igual que en local</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <h3>🎯 Resultado Esperado</h3>
                        <ul>
                            <li>✅ <strong>Categorías cargando correctamente</strong></li>
                            <li>✅ <strong>Opciones disponibles por categoría</strong></li>
                            <li>✅ <strong>Plazos de entrega funcionando</strong></li>
                            <li>✅ <strong>Cotizador idéntico al local</strong></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="info-box error">
                    <h3>❌ Error en la Exportación</h3>
                    <p><strong>Error:</strong> <?php echo $export['error']; ?></p>
                    <p><a href="?" class="btn">🔄 Intentar de Nuevo</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 