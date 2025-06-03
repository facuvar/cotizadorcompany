<?php
// Script para verificar las tablas de adicionales
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Verificación de Tablas de Adicionales</h1>";
    
    // Verificar tabla xls_adicionales
    $result = $conn->query("SELECT COUNT(*) as total FROM xls_adicionales");
    $row = $result->fetch_assoc();
    echo "<p>Total de adicionales en xls_adicionales: <strong>" . $row['total'] . "</strong></p>";
    
    if ($row['total'] > 0) {
        echo "<h2>Lista de Adicionales</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th></tr>";
        
        $result = $conn->query("SELECT * FROM xls_adicionales");
        while ($adicional = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $adicional['id'] . "</td>";
            echo "<td>" . $adicional['nombre'] . "</td>";
            echo "<td>" . $adicional['descripcion'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Verificar tabla xls_productos_adicionales
    $result = $conn->query("SELECT COUNT(*) as total FROM xls_productos_adicionales");
    $row = $result->fetch_assoc();
    echo "<p>Total de relaciones en xls_productos_adicionales: <strong>" . $row['total'] . "</strong></p>";
    
    if ($row['total'] > 0) {
        echo "<h2>Relaciones Producto-Adicional</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Producto ID</th><th>Producto Nombre</th><th>Adicional ID</th><th>Adicional Nombre</th></tr>";
        
        $result = $conn->query("
            SELECT pa.id, pa.producto_id, p.nombre as producto_nombre, pa.adicional_id, a.nombre as adicional_nombre
            FROM xls_productos_adicionales pa
            JOIN xls_productos p ON pa.producto_id = p.id
            JOIN xls_adicionales a ON pa.adicional_id = a.id
        ");
        
        while ($relacion = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $relacion['id'] . "</td>";
            echo "<td>" . $relacion['producto_id'] . "</td>";
            echo "<td>" . $relacion['producto_nombre'] . "</td>";
            echo "<td>" . $relacion['adicional_id'] . "</td>";
            echo "<td>" . $relacion['adicional_nombre'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Verificar tabla xls_adicionales_precios
    $result = $conn->query("SELECT COUNT(*) as total FROM xls_adicionales_precios");
    $row = $result->fetch_assoc();
    echo "<p>Total de precios en xls_adicionales_precios: <strong>" . $row['total'] . "</strong></p>";
    
    if ($row['total'] > 0) {
        echo "<h2>Precios de Adicionales</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Adicional ID</th><th>Adicional Nombre</th><th>Plazo ID</th><th>Precio</th></tr>";
        
        $result = $conn->query("
            SELECT ap.id, ap.adicional_id, a.nombre as adicional_nombre, ap.plazo_id, ap.precio
            FROM xls_adicionales_precios ap
            JOIN xls_adicionales a ON ap.adicional_id = a.id
        ");
        
        while ($precio = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $precio['id'] . "</td>";
            echo "<td>" . $precio['adicional_id'] . "</td>";
            echo "<td>" . $precio['adicional_nombre'] . "</td>";
            echo "<td>" . $precio['plazo_id'] . "</td>";
            echo "<td>$" . number_format($precio['precio'], 2, ',', '.') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Verificar los productos que pueden tener adicionales
    $productosConAdicionales = [
        'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
        'ASCENSORES HIDRAULICOS',
        'MONTACARGAS',
        'SALVAESCALERAS'
    ];
    
    echo "<h2>Productos que pueden tener adicionales</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Existe en BD</th></tr>";
    
    foreach ($productosConAdicionales as $productoNombre) {
        $stmt = $conn->prepare("SELECT id FROM xls_productos WHERE nombre LIKE ?");
        $busqueda = "%$productoNombre%";
        $stmt->bind_param("s", $busqueda);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $producto = $result->fetch_assoc();
            echo "<tr>";
            echo "<td>" . $producto['id'] . "</td>";
            echo "<td>" . $productoNombre . "</td>";
            echo "<td style='color: green;'>Sí</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td>-</td>";
            echo "<td>" . $productoNombre . "</td>";
            echo "<td style='color: red;'>No</td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    
    // Verificar si hay adicionales para cada producto
    echo "<h2>Adicionales por Producto</h2>";
    
    foreach ($productosConAdicionales as $productoNombre) {
        $stmt = $conn->prepare("SELECT id FROM xls_productos WHERE nombre LIKE ?");
        $busqueda = "%$productoNombre%";
        $stmt->bind_param("s", $busqueda);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $producto = $result->fetch_assoc();
            $productoId = $producto['id'];
            
            echo "<h3>Producto: " . $productoNombre . " (ID: " . $productoId . ")</h3>";
            
            $stmt = $conn->prepare("
                SELECT a.id, a.nombre
                FROM xls_adicionales a
                JOIN xls_productos_adicionales pa ON a.id = pa.adicional_id
                WHERE pa.producto_id = ?
            ");
            $stmt->bind_param("i", $productoId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<ul>";
                while ($adicional = $result->fetch_assoc()) {
                    echo "<li>" . $adicional['nombre'] . " (ID: " . $adicional['id'] . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: red;'>No hay adicionales asociados a este producto</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
