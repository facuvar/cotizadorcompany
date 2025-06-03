<?php
// Script de diagnóstico para adicionales
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo == 'error' ? 'red' : ($tipo == 'success' ? 'green' : 'blue');
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid $color; border-radius: 5px; color: $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Función para crear adicionales de prueba
function crearAdicionalesPrueba($conn) {
    // Verificar si ya existen adicionales
    $result = $conn->query("SELECT COUNT(*) as total FROM xls_adicionales");
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        mostrarMensaje("Ya existen {$row['total']} adicionales en la base de datos", "info");
        return;
    }
    
    // Crear adicionales de prueba
    $adicionales = [
        ['nombre' => 'Adicional de prueba 1', 'descripcion' => 'Descripción del adicional 1'],
        ['nombre' => 'Adicional de prueba 2', 'descripcion' => 'Descripción del adicional 2'],
        ['nombre' => 'Adicional de prueba 3', 'descripcion' => 'Descripción del adicional 3']
    ];
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Crear los adicionales
        foreach ($adicionales as $adicional) {
            $query = "INSERT INTO xls_adicionales (nombre, descripcion) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $adicional['nombre'], $adicional['descripcion']);
            $stmt->execute();
            $adicionalId = $conn->insert_id;
            
            mostrarMensaje("Adicional creado: {$adicional['nombre']} (ID: $adicionalId)", "success");
            
            // Crear precios para cada plazo
            $query = "SELECT id FROM xls_plazos";
            $result = $conn->query($query);
            
            while ($row = $result->fetch_assoc()) {
                $plazoId = $row['id'];
                $precio = rand(1000, 5000);
                
                $query = "INSERT INTO xls_adicionales_precios (adicional_id, plazo_id, precio) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iid", $adicionalId, $plazoId, $precio);
                $stmt->execute();
                
                mostrarMensaje("Precio creado para adicional ID $adicionalId, plazo ID $plazoId: $precio", "success");
            }
            
            // Asociar con los productos específicos
            $productosConAdicionales = [
                'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                'ASCENSORES HIDRAULICOS',
                'MONTACARGAS',
                'SALVAESCALERAS'
            ];
            
            foreach ($productosConAdicionales as $productoNombre) {
                $query = "SELECT id FROM xls_productos WHERE nombre LIKE ?";
                $stmt = $conn->prepare($query);
                $productoNombreBusqueda = "%$productoNombre%";
                $stmt->bind_param("s", $productoNombreBusqueda);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productoId = $row['id'];
                        
                        // Verificar si ya existe la relación
                        $query = "SELECT * FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("ii", $productoId, $adicionalId);
                        $stmt->execute();
                        $result2 = $stmt->get_result();
                        
                        if ($result2->num_rows === 0) {
                            // Crear la relación
                            $query = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $productoId, $adicionalId);
                            $stmt->execute();
                            
                            mostrarMensaje("Adicional ID $adicionalId asociado al producto ID $productoId ($productoNombre)", "success");
                        }
                    }
                } else {
                    mostrarMensaje("No se encontró el producto: $productoNombre", "error");
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        mostrarMensaje("Adicionales de prueba creados correctamente", "success");
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        mostrarMensaje("Error al crear adicionales de prueba: " . $e->getMessage(), "error");
    }
}

// Función para verificar las tablas
function verificarTablas($conn) {
    $tablas = [
        'xls_adicionales' => "CREATE TABLE IF NOT EXISTS xls_adicionales (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'xls_adicionales_precios' => "CREATE TABLE IF NOT EXISTS xls_adicionales_precios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            adicional_id INT(11) NOT NULL,
            plazo_id INT(11) NOT NULL,
            precio DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY adicional_plazo (adicional_id, plazo_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'xls_productos_adicionales' => "CREATE TABLE IF NOT EXISTS xls_productos_adicionales (
            id INT(11) NOT NULL AUTO_INCREMENT,
            producto_id INT(11) NOT NULL,
            adicional_id INT(11) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY producto_adicional (producto_id, adicional_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    foreach ($tablas as $tabla => $sql) {
        $result = $conn->query("SHOW TABLES LIKE '$tabla'");
        if ($result->num_rows == 0) {
            mostrarMensaje("Creando tabla $tabla...", "info");
            $conn->query($sql);
            mostrarMensaje("Tabla $tabla creada correctamente", "success");
        } else {
            mostrarMensaje("Tabla $tabla ya existe", "info");
        }
    }
}

// Función para mostrar estadísticas
function mostrarEstadisticas($conn) {
    // Verificar productos con adicionales
    $query = "SELECT p.id, p.nombre, COUNT(pa.id) as total_adicionales 
              FROM xls_productos p 
              LEFT JOIN xls_productos_adicionales pa ON p.id = pa.producto_id 
              WHERE p.nombre LIKE '%EQUIPO ELECTROMECANICO%' OR 
                    p.nombre LIKE '%ASCENSORES HIDRAULICOS%' OR 
                    p.nombre LIKE '%MONTACARGAS%' OR 
                    p.nombre LIKE '%SALVAESCALERAS%' 
              GROUP BY p.id";
    $result = $conn->query($query);
    
    echo "<h3>Productos que pueden tener adicionales:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Adicionales</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['total_adicionales']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar adicionales
    $query = "SELECT a.id, a.nombre, a.descripcion, COUNT(pa.id) as total_productos 
              FROM xls_adicionales a 
              LEFT JOIN xls_productos_adicionales pa ON a.id = pa.adicional_id 
              GROUP BY a.id";
    $result = $conn->query($query);
    
    echo "<h3>Adicionales disponibles:</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Productos asociados</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['descripcion']}</td>";
            echo "<td>{$row['total_productos']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        mostrarMensaje("No hay adicionales en la base de datos", "error");
    }
}

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres
$conn->set_charset("utf8mb4");

// Encabezado HTML
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico de Adicionales</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            cursor: pointer;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico de Adicionales</h1>";

// Verificar si se ha enviado el formulario
if (isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case 'verificar_tablas':
            verificarTablas($conn);
            break;
        case 'crear_adicionales':
            crearAdicionalesPrueba($conn);
            break;
        case 'limpiar_adicionales':
            $conn->query("DELETE FROM xls_adicionales_precios");
            $conn->query("DELETE FROM xls_productos_adicionales");
            $conn->query("DELETE FROM xls_adicionales");
            mostrarMensaje("Tablas de adicionales limpiadas correctamente", "success");
            break;
    }
}

// Mostrar estadísticas
mostrarEstadisticas($conn);

// Formulario de acciones
echo "<h2>Acciones disponibles</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='accion' value='verificar_tablas' class='btn'>Verificar y crear tablas</button>";
echo "<button type='submit' name='accion' value='crear_adicionales' class='btn btn-success'>Crear adicionales de prueba</button>";
echo "<button type='submit' name='accion' value='limpiar_adicionales' class='btn btn-danger'>Limpiar adicionales</button>";
echo "</form>";

// Enlaces
echo "<h2>Enlaces útiles</h2>";
echo "<a href='cotizador_simple.php' class='btn'>Ir al Cotizador</a>";
echo "<a href='importar_xls_formulas_v2.php' class='btn'>Ir al Importador</a>";

// Cerrar la conexión
$conn->close();

// Pie de página
echo "</body></html>";
?>
