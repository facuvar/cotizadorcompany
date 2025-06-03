<?php
// Script para verificar la conexión a la base de datos
require_once 'sistema/config.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Verificar Conexión a la Base de Datos</title>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .card { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Verificar Conexión a la Base de Datos</h1>";

// Verificar configuración
echo "<h2>Configuración actual</h2>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Usuario: " . DB_USER . "</li>";
echo "<li>Contraseña: " . (empty(DB_PASS) ? "No configurada" : "Configurada") . "</li>";
echo "<li>Base de datos: " . DB_NAME . "</li>";
echo "</ul>";

// Verificar si se solicitó la creación de la base de datos
if (isset($_POST['crear_db'])) {
    // Conectar sin especificar base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        mostrarMensaje("Error de conexión: " . $conn->connect_error, "error");
    } else {
        mostrarMensaje("Conexión al servidor MySQL exitosa", "success");
        
        // Verificar si la base de datos existe
        $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        
        if ($result->num_rows > 0) {
            mostrarMensaje("La base de datos '" . DB_NAME . "' ya existe", "info");
        } else {
            // Crear la base de datos
            if ($conn->query("CREATE DATABASE " . DB_NAME)) {
                mostrarMensaje("Base de datos '" . DB_NAME . "' creada correctamente", "success");
            } else {
                mostrarMensaje("Error al crear la base de datos: " . $conn->error, "error");
            }
        }
        
        // Seleccionar la base de datos
        if ($conn->select_db(DB_NAME)) {
            mostrarMensaje("Base de datos '" . DB_NAME . "' seleccionada correctamente", "success");
            
            // Verificar si se solicitó la creación de tablas
            if (isset($_POST['crear_tablas'])) {
                // Crear tablas necesarias
                $tablas = [
                    "categorias" => "CREATE TABLE categorias (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        nombre VARCHAR(255) NOT NULL,
                        descripcion TEXT,
                        orden INT(11) DEFAULT 0,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                    
                    "plazos_entrega" => "CREATE TABLE plazos_entrega (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        nombre VARCHAR(100) NOT NULL,
                        descripcion VARCHAR(255),
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                    
                    "opciones" => "CREATE TABLE opciones (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        categoria_id INT(11) NOT NULL,
                        nombre VARCHAR(255) NOT NULL,
                        descripcion TEXT,
                        precio DECIMAL(15,2) DEFAULT 0.00,
                        orden INT(11) DEFAULT 0,
                        PRIMARY KEY (id),
                        KEY categoria_id (categoria_id),
                        CONSTRAINT fk_opciones_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
                    
                    "opcion_precios" => "CREATE TABLE opcion_precios (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        opcion_id INT(11) NOT NULL,
                        plazo_entrega VARCHAR(100) NOT NULL,
                        precio DECIMAL(15,2) NOT NULL,
                        PRIMARY KEY (id),
                        KEY opcion_id (opcion_id),
                        CONSTRAINT fk_opcion_precios_opcion FOREIGN KEY (opcion_id) REFERENCES opciones (id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
                ];
                
                foreach ($tablas as $tabla => $sql) {
                    // Verificar si la tabla existe
                    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
                    
                    if ($result->num_rows > 0) {
                        mostrarMensaje("La tabla '$tabla' ya existe", "info");
                    } else {
                        // Crear la tabla
                        if ($conn->query($sql)) {
                            mostrarMensaje("Tabla '$tabla' creada correctamente", "success");
                        } else {
                            mostrarMensaje("Error al crear la tabla '$tabla': " . $conn->error, "error");
                        }
                    }
                }
                
                // Insertar datos de ejemplo
                // 1. Categorías
                $conn->query("INSERT INTO categorias (id, nombre, descripcion, orden) VALUES 
                    (1, 'ASCENSORES', 'Equipos electromecanicos', 1),
                    (2, 'ADICIONALES', 'Características adicionales', 2),
                    (3, 'DESCUENTOS', 'Descuentos aplicables', 3)");
                mostrarMensaje("Datos de ejemplo insertados en la tabla 'categorias'", "success");
                
                // 2. Plazos de entrega
                $conn->query("INSERT INTO plazos_entrega (nombre, descripcion) VALUES 
                    ('160-180 dias', 'Plazo estándar (160-180 días)'),
                    ('90 dias', 'Plazo rápido (90 días)'),
                    ('270 dias', 'Plazo económico (270 días)')");
                mostrarMensaje("Datos de ejemplo insertados en la tabla 'plazos_entrega'", "success");
                
                // 3. Opciones
                $conn->query("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden) VALUES 
                    (1, 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL - 4 PARADAS', 'Ascensor estándar para edificios residenciales', 2500000.00, 1),
                    (1, 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL - 6 PARADAS', 'Ascensor estándar para edificios residenciales', 3000000.00, 2),
                    (1, 'EQUIPO ELECTROMECANICO 750KG CARGA UTIL - 4 PARADAS', 'Ascensor de mayor capacidad para edificios comerciales', 3500000.00, 3),
                    (1, 'EQUIPO ELECTROMECANICO 750KG CARGA UTIL - 6 PARADAS', 'Ascensor de mayor capacidad para edificios comerciales', 4000000.00, 4),
                    (2, 'PARADA ADICIONAL', 'Costo por cada parada adicional', 500000.00, 1),
                    (2, 'ACABADO EN ACERO INOXIDABLE', 'Acabado premium para la cabina', 750000.00, 2),
                    (2, 'SISTEMA DE CONTROL DE ACCESO', 'Control de acceso por tarjeta o código', 350000.00, 3),
                    (2, 'SISTEMA DE MONITOREO REMOTO', 'Monitoreo y diagnóstico remoto', 250000.00, 4),
                    (3, 'DESCUENTO POR PAGO ANTICIPADO', 'Descuento del 5% por pago anticipado', -0.05, 1),
                    (3, 'DESCUENTO POR VOLUMEN', 'Descuento del 10% para proyectos con múltiples ascensores', -0.10, 2)");
                mostrarMensaje("Datos de ejemplo insertados en la tabla 'opciones'", "success");
                
                // 4. Precios por plazo
                $result = $conn->query("SELECT id, precio FROM opciones");
                $plazos = [
                    '160-180 dias' => 1.00,
                    '90 dias' => 1.30,
                    '270 dias' => 0.90
                ];
                
                if ($result && $result->num_rows > 0) {
                    while ($opcion = $result->fetch_assoc()) {
                        $opcionId = $opcion['id'];
                        $precioBase = $opcion['precio'];
                        
                        foreach ($plazos as $plazo => $factor) {
                            $precio = $precioBase * $factor;
                            $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_entrega, precio) VALUES (?, ?, ?)");
                            $stmt->bind_param("isd", $opcionId, $plazo, $precio);
                            $stmt->execute();
                        }
                    }
                    mostrarMensaje("Precios generados para todas las opciones y plazos", "success");
                }
            }
        } else {
            mostrarMensaje("Error al seleccionar la base de datos: " . $conn->error, "error");
        }
    }
} else {
    // Intentar conectar a la base de datos
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            mostrarMensaje("Error de conexión a la base de datos: " . $conn->connect_error, "error");
            
            // Verificar si la base de datos existe
            $connSinDB = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($connSinDB->connect_error) {
                mostrarMensaje("Error de conexión al servidor MySQL: " . $connSinDB->connect_error, "error");
            } else {
                $result = $connSinDB->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
                
                if ($result->num_rows == 0) {
                    mostrarMensaje("La base de datos '" . DB_NAME . "' no existe", "warning");
                    
                    // Mostrar formulario para crear la base de datos
                    echo "
                    <div class='card'>
                        <h3>Crear Base de Datos</h3>
                        <p>La base de datos '" . DB_NAME . "' no existe. ¿Desea crearla?</p>
                        <form method='post'>
                            <input type='hidden' name='crear_db' value='1'>
                            <label><input type='checkbox' name='crear_tablas' value='1' checked> Crear tablas necesarias</label><br><br>
                            <button type='submit' class='btn'>Crear Base de Datos</button>
                        </form>
                    </div>";
                }
            }
        } else {
            mostrarMensaje("Conexión a la base de datos exitosa", "success");
            
            // Verificar tablas necesarias
            $tablasNecesarias = ['categorias', 'plazos_entrega', 'opciones', 'opcion_precios'];
            $tablasFaltantes = [];
            
            foreach ($tablasNecesarias as $tabla) {
                $result = $conn->query("SHOW TABLES LIKE '$tabla'");
                
                if ($result->num_rows == 0) {
                    $tablasFaltantes[] = $tabla;
                }
            }
            
            if (count($tablasFaltantes) > 0) {
                mostrarMensaje("Faltan las siguientes tablas: " . implode(", ", $tablasFaltantes), "warning");
                
                // Mostrar formulario para crear las tablas
                echo "
                <div class='card'>
                    <h3>Crear Tablas</h3>
                    <p>Faltan algunas tablas necesarias para el cotizador. ¿Desea crearlas?</p>
                    <form method='post'>
                        <input type='hidden' name='crear_db' value='1'>
                        <input type='hidden' name='crear_tablas' value='1'>
                        <button type='submit' class='btn'>Crear Tablas</button>
                    </form>
                </div>";
            } else {
                mostrarMensaje("Todas las tablas necesarias existen", "success");
                
                // Verificar si hay datos
                $result = $conn->query("SELECT COUNT(*) as total FROM opciones");
                $row = $result->fetch_assoc();
                
                if ($row['total'] == 0) {
                    mostrarMensaje("No hay opciones en la tabla 'opciones'", "warning");
                    
                    // Mostrar formulario para insertar datos de ejemplo
                    echo "
                    <div class='card'>
                        <h3>Insertar Datos de Ejemplo</h3>
                        <p>No hay datos en la tabla 'opciones'. ¿Desea insertar datos de ejemplo?</p>
                        <form method='post'>
                            <input type='hidden' name='crear_db' value='1'>
                            <input type='hidden' name='crear_tablas' value='1'>
                            <button type='submit' class='btn'>Insertar Datos de Ejemplo</button>
                        </form>
                    </div>";
                } else {
                    mostrarMensaje("Hay " . $row['total'] . " opciones en la tabla 'opciones'", "success");
                    echo "<p><a href='sistema/cotizador.php' class='btn'>Ir al Cotizador</a></p>";
                }
            }
        }
    } catch (Exception $e) {
        mostrarMensaje("Error: " . $e->getMessage(), "error");
    }
}

echo "</body></html>";
?>
