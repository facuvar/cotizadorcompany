<?php
/**
 * Script simple para exportar la base de datos local a Railway
 * Genera un archivo SQL listo para importar
 */

// Configuración de la base de datos local
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'company_presupuestos';

try {
    // Conectar a la base de datos local
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conectado a la base de datos local\n";
    
    // Iniciar la exportación
    $sql_output = "-- ========================================\n";
    $sql_output .= "-- EXPORTACIÓN PARA RAILWAY\n";
    $sql_output .= "-- Base de datos: $database\n";
    $sql_output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    $sql_output .= "-- ========================================\n\n";
    
    $sql_output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $sql_output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql_output .= "SET AUTOCOMMIT = 0;\n";
    $sql_output .= "START TRANSACTION;\n\n";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tablas encontradas: " . implode(', ', $tables) . "\n";
    
    // Orden específico para evitar problemas de dependencias
    $ordered_tables = [];
    
    // Primero las tablas principales
    $priority_tables = ['categorias', 'plazos_entrega', 'configuracion', 'usuarios'];
    foreach ($priority_tables as $table) {
        if (in_array($table, $tables)) {
            $ordered_tables[] = $table;
        }
    }
    
    // Luego las tablas dependientes
    $dependent_tables = ['opciones', 'presupuestos', 'presupuesto_detalles', 'presupuesto_items'];
    foreach ($dependent_tables as $table) {
        if (in_array($table, $tables)) {
            $ordered_tables[] = $table;
        }
    }
    
    // Resto de tablas
    foreach ($tables as $table) {
        if (!in_array($table, $ordered_tables)) {
            $ordered_tables[] = $table;
        }
    }
    
    echo "🔄 Orden de exportación: " . implode(' → ', $ordered_tables) . "\n\n";
    
    // Eliminar tablas existentes en orden inverso
    $sql_output .= "-- Eliminar tablas existentes\n";
    foreach (array_reverse($ordered_tables) as $table) {
        $sql_output .= "DROP TABLE IF EXISTS `$table`;\n";
    }
    $sql_output .= "\n";
    
    // Crear estructura y datos para cada tabla
    foreach ($ordered_tables as $table) {
        echo "📦 Procesando tabla: $table\n";
        
        // Obtener estructura de la tabla
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql_output .= "-- Estructura para tabla $table\n";
        $sql_output .= $create_table['Create Table'] . ";\n\n";
        
        // Obtener datos de la tabla
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "   └─ $count registros encontrados\n";
            
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columns_list = '`' . implode('`, `', $columns) . '`';
                
                $sql_output .= "-- Datos para tabla $table\n";
                $sql_output .= "INSERT INTO `$table` ($columns_list) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $row_values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $row_values[] = 'NULL';
                        } else {
                            $escaped = addslashes($value);
                            $escaped = str_replace(["\n", "\r", "\t"], ["\\n", "\\r", "\\t"], $escaped);
                            $row_values[] = "'" . $escaped . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $row_values) . ')';
                }
                
                $sql_output .= implode(",\n", $values) . ";\n\n";
            }
        } else {
            echo "   └─ Tabla vacía\n";
            $sql_output .= "-- Tabla $table está vacía\n\n";
        }
    }
    
    $sql_output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $sql_output .= "COMMIT;\n";
    $sql_output .= "\n-- Exportación completada: " . date('Y-m-d H:i:s') . "\n";
    
    // Guardar archivo
    $filename = 'export_railway_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($filename, $sql_output);
    
    echo "\n🎉 ¡Exportación completada!\n";
    echo "📁 Archivo generado: $filename\n";
    echo "📊 Tamaño: " . number_format(strlen($sql_output)) . " caracteres\n";
    echo "📋 Tablas exportadas: " . count($ordered_tables) . "\n\n";
    
    echo "🚀 SIGUIENTE PASO:\n";
    echo "1. Ve a tu panel de Railway: https://railway.app/dashboard\n";
    echo "2. Abre tu base de datos MySQL\n";
    echo "3. Ejecuta el contenido del archivo: $filename\n";
    echo "4. ¡Tu base de datos estará sincronizada!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 