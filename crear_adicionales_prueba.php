<?php
// Script para crear adicionales de prueba
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo == 'error' ? 'red' : ($tipo == 'success' ? 'green' : 'blue');
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid $color; border-radius: 5px; color: $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Verificar tablas necesarias
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

// Crear tablas si no existen
foreach ($tablas as $tabla => $sql) {
    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
    if ($result->num_rows == 0) {
        mostrarMensaje("Creando tabla $tabla...", "info");
        $conn->query($sql);
    }
}

// Verificar si ya existen adicionales
$result = $conn->query("SELECT COUNT(*) as total FROM xls_adicionales");
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    mostrarMensaje("Ya existen {$row['total']} adicionales en la base de datos. ¿Desea eliminarlos y crear nuevos?", "info");
    echo "<form method='post'>";
    echo "<button type='submit' name='accion' value='limpiar' style='background-color: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;'>Eliminar y crear nuevos</button>";
    echo "<button type='submit' name='accion' value='mantener' style='background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Mantener existentes</button>";
    echo "</form>";
    
    if (isset($_POST['accion']) && $_POST['accion'] == 'limpiar') {
        // Eliminar adicionales existentes
        $conn->query("DELETE FROM xls_adicionales_precios");
        $conn->query("DELETE FROM xls_productos_adicionales");
        $conn->query("DELETE FROM xls_adicionales");
        mostrarMensaje("Adicionales eliminados correctamente", "success");
    } else if (isset($_POST['accion']) && $_POST['accion'] == 'mantener') {
        // Mostrar adicionales existentes
        $query = "SELECT a.id, a.nombre, a.descripcion, COUNT(pa.id) as total_productos 
                  FROM xls_adicionales a 
                  LEFT JOIN xls_productos_adicionales pa ON a.id = pa.adicional_id 
                  GROUP BY a.id";
        $result = $conn->query($query);
        
        echo "<h3>Adicionales existentes:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Nombre</th><th>Descripción</th><th>Productos asociados</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['descripcion']}</td>";
            echo "<td>{$row['total_productos']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    }
}

// Crear adicionales de prueba
$adicionales = [
    ['nombre' => 'Adicional de prueba 1', 'descripcion' => 'Descripción del adicional 1'],
    ['nombre' => 'Adicional de prueba 2', 'descripcion' => 'Descripción del adicional 2'],
    ['nombre' => 'Adicional de prueba 3', 'descripcion' => 'Descripción del adicional 3']
];

// Productos que pueden tener adicionales
$productosConAdicionales = [
    'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
    'ASCENSORES HIDRAULICOS',
    'MONTACARGAS',
    'SALVAESCALERAS'
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
    
    // Mostrar resumen
    echo "<h3>Resumen de productos con adicionales:</h3>";
    $query = "SELECT p.id, p.nombre, COUNT(pa.id) as total_adicionales 
              FROM xls_productos p 
              LEFT JOIN xls_productos_adicionales pa ON p.id = pa.producto_id 
              WHERE p.nombre LIKE '%EQUIPO ELECTROMECANICO%' OR 
                    p.nombre LIKE '%ASCENSORES HIDRAULICOS%' OR 
                    p.nombre LIKE '%MONTACARGAS%' OR 
                    p.nombre LIKE '%SALVAESCALERAS%' 
              GROUP BY p.id";
    $result = $conn->query($query);
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Nombre</th><th>Adicionales</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['total_adicionales']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='cotizador_simple.php' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Cotizador</a>";
    echo "</div>";
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    mostrarMensaje("Error al crear adicionales de prueba: " . $e->getMessage(), "error");
}
?>
