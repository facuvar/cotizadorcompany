<?php
// Script para crear o corregir la tabla de presupuestos
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Verificación y corrección de la tabla presupuestos</h1>";
    
    // Verificar si la tabla existe
    $result = $conn->query("SHOW TABLES LIKE 'presupuestos'");
    $tablaExiste = $result->num_rows > 0;
    
    if ($tablaExiste) {
        echo "<p>La tabla presupuestos existe. Verificando estructura...</p>";
        
        // Verificar si la tabla tiene la estructura correcta
        $result = $conn->query("DESCRIBE presupuestos");
        $columnas = [];
        while ($row = $result->fetch_assoc()) {
            $columnas[$row['Field']] = $row['Type'];
        }
        
        echo "<h2>Estructura actual:</h2>";
        echo "<ul>";
        foreach ($columnas as $campo => $tipo) {
            echo "<li>$campo - $tipo</li>";
        }
        echo "</ul>";
        
        // Verificar si faltan columnas
        $columnasRequeridas = [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nombre' => 'VARCHAR(100) NOT NULL',
            'email' => 'VARCHAR(100) NOT NULL',
            'telefono' => 'VARCHAR(20) NOT NULL',
            'producto_id' => 'INT NOT NULL',
            'producto_nombre' => 'VARCHAR(100) NOT NULL',
            'opcion_id' => 'INT NOT NULL',
            'opcion_nombre' => 'VARCHAR(100) NOT NULL',
            'plazo_id' => 'INT NOT NULL',
            'plazo_nombre' => 'VARCHAR(50) NOT NULL',
            'forma_pago' => 'VARCHAR(100) NOT NULL',
            'descuento' => 'DECIMAL(10,2) NOT NULL',
            'adicionales' => 'TEXT',
            'subtotal' => 'DECIMAL(10,2) NOT NULL',
            'total' => 'DECIMAL(10,2) NOT NULL',
            'fecha_creacion' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ];
        
        $faltanColumnas = false;
        $columnasAgregar = [];
        
        foreach ($columnasRequeridas as $columna => $tipo) {
            if (!isset($columnas[$columna])) {
                $faltanColumnas = true;
                $columnasAgregar[$columna] = $tipo;
            }
        }
        
        if ($faltanColumnas) {
            echo "<p>Faltan columnas en la tabla. Agregando columnas faltantes...</p>";
            
            // Agregar columnas faltantes
            foreach ($columnasAgregar as $columna => $tipo) {
                $sql = "ALTER TABLE presupuestos ADD COLUMN $columna $tipo";
                if ($conn->query($sql)) {
                    echo "<p>Columna '$columna' agregada correctamente.</p>";
                } else {
                    echo "<p>Error al agregar columna '$columna': " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p>La tabla presupuestos tiene todas las columnas requeridas.</p>";
        }
    } else {
        echo "<p>La tabla presupuestos no existe. Creando tabla...</p>";
        
        // Crear la tabla
        $sql = "CREATE TABLE presupuestos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            producto_id INT NOT NULL,
            producto_nombre VARCHAR(100) NOT NULL,
            opcion_id INT NOT NULL,
            opcion_nombre VARCHAR(100) NOT NULL,
            plazo_id INT NOT NULL,
            plazo_nombre VARCHAR(50) NOT NULL,
            forma_pago VARCHAR(100) NOT NULL,
            descuento DECIMAL(10,2) NOT NULL,
            adicionales TEXT,
            subtotal DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            echo "<p>Tabla presupuestos creada correctamente.</p>";
        } else {
            echo "<p>Error al crear la tabla presupuestos: " . $conn->error . "</p>";
        }
    }
    
    echo "<p><a href='cotizador_con_pago.php'>Volver al cotizador</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
