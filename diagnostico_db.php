<?php
// Configuración para mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar resultados
function printResult($title, $data) {
    echo "<h3>$title</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<hr>";
}

// Obtener instancia de la base de datos
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h2>Diagnóstico de la Base de Datos</h2>";
    
    // Verificar conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    echo "<p>✅ Conexión exitosa a la base de datos '" . DB_NAME . "'</p>";
    
    // Listar todas las tablas
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    printResult("Tablas disponibles en la base de datos", $tables);
    
    // Verificar si existe la tabla presupuestos
    $presupuestosExists = in_array('presupuestos', $tables);
    
    if ($presupuestosExists) {
        echo "<p>✅ La tabla 'presupuestos' existe</p>";
        
        // Mostrar estructura de la tabla
        $columns = [];
        $result = $conn->query("DESCRIBE presupuestos");
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
        
        printResult("Estructura de la tabla 'presupuestos'", $columns);
        
        // Contar registros
        $result = $conn->query("SELECT COUNT(*) as total FROM presupuestos");
        $count = $result->fetch_assoc()['total'];
        echo "<p>La tabla 'presupuestos' tiene $count registros</p>";
        
        // Mostrar algunos registros
        if ($count > 0) {
            $result = $conn->query("SELECT * FROM presupuestos LIMIT 5");
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            printResult("Últimos 5 registros de 'presupuestos'", $rows);
        }
    } else {
        echo "<p>❌ La tabla 'presupuestos' NO existe</p>";
        
        // Crear la tabla
        echo "<h3>Creando tabla 'presupuestos'...</h3>";
        
        $createTable = "CREATE TABLE presupuestos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_presupuesto VARCHAR(20) NOT NULL,
            cliente_nombre VARCHAR(100) NOT NULL,
            cliente_email VARCHAR(100) NOT NULL,
            cliente_telefono VARCHAR(20),
            cliente_empresa VARCHAR(100),
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
            descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
            descuento_monto DECIMAL(10,2) NOT NULL DEFAULT 0,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            plazo_entrega VARCHAR(10),
            estado VARCHAR(20) DEFAULT 'pendiente',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createTable)) {
            echo "<p>✅ Tabla 'presupuestos' creada exitosamente</p>";
        } else {
            echo "<p>❌ Error al crear la tabla 'presupuestos': " . $conn->error . "</p>";
        }
    }
    
    // Verificar si existe la tabla presupuesto_detalles
    $detallesExists = in_array('presupuesto_detalles', $tables);
    
    if ($detallesExists) {
        echo "<p>✅ La tabla 'presupuesto_detalles' existe</p>";
        
        // Mostrar estructura de la tabla
        $columns = [];
        $result = $conn->query("DESCRIBE presupuesto_detalles");
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
        
        printResult("Estructura de la tabla 'presupuesto_detalles'", $columns);
    } else {
        echo "<p>❌ La tabla 'presupuesto_detalles' NO existe</p>";
        
        // Crear la tabla
        echo "<h3>Creando tabla 'presupuesto_detalles'...</h3>";
        
        $createTable = "CREATE TABLE presupuesto_detalles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            presupuesto_id INT NOT NULL,
            opcion_id INT NOT NULL,
            precio DECIMAL(10,2) NOT NULL DEFAULT 0,
            FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id) ON DELETE CASCADE,
            FOREIGN KEY (opcion_id) REFERENCES opciones(id) ON DELETE CASCADE
        )";
        
        if ($conn->query($createTable)) {
            echo "<p>✅ Tabla 'presupuesto_detalles' creada exitosamente</p>";
        } else {
            echo "<p>❌ Error al crear la tabla 'presupuesto_detalles': " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Ha ocurrido un error: " . $e->getMessage() . "</p>";
    echo "<p>En archivo: " . $e->getFile() . ", línea: " . $e->getLine() . "</p>";
}
?> 