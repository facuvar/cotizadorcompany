<?php
// Script para crear la tabla de relaciones entre productos y opciones
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Crear Relaciones Producto-Opción</title>
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
        <h1>Crear Relaciones Producto-Opción</h1>";
    
    // Verificar si se solicitó la creación
    if (isset($_POST['crear'])) {
        // Desactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Crear tabla producto_opciones si no existe
        mostrarMensaje("Verificando tabla de relaciones producto-opción...", "info");
        
        $result = $conn->query("SHOW TABLES LIKE 'producto_opciones'");
        if ($result->num_rows == 0) {
            // Crear la tabla producto_opciones
            $conn->query("CREATE TABLE producto_opciones (
                id INT(11) NOT NULL AUTO_INCREMENT,
                producto_id INT(11) NOT NULL,
                opcion_id INT(11) NOT NULL,
                PRIMARY KEY (id),
                KEY producto_id (producto_id),
                KEY opcion_id (opcion_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla producto_opciones creada", "success");
        } else {
            // Limpiar tabla producto_opciones
            $conn->query("TRUNCATE TABLE producto_opciones");
            mostrarMensaje("Tabla producto_opciones limpiada", "success");
        }
        
        // 2. Obtener productos de la categoría ASCENSORES
        $query = "SELECT id FROM opciones o 
                  INNER JOIN categorias c ON o.categoria_id = c.id 
                  WHERE c.nombre = 'ASCENSORES'";
        $result = $conn->query($query);
        
        $productosCount = 0;
        $relacionesCount = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($producto = $result->fetch_assoc()) {
                $productoId = $producto['id'];
                $productosCount++;
                
                // 3. Obtener opciones de la categoría ADICIONALES
                $query = "SELECT id FROM opciones o 
                          INNER JOIN categorias c ON o.categoria_id = c.id 
                          WHERE c.nombre = 'ADICIONALES'";
                $opcionesResult = $conn->query($query);
                
                if ($opcionesResult && $opcionesResult->num_rows > 0) {
                    while ($opcion = $opcionesResult->fetch_assoc()) {
                        $opcionId = $opcion['id'];
                        
                        // Crear relación entre producto y opción
                        $stmt = $conn->prepare("INSERT INTO producto_opciones (producto_id, opcion_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $productoId, $opcionId);
                        $stmt->execute();
                        $relacionesCount++;
                    }
                }
            }
            
            mostrarMensaje("$productosCount productos procesados", "success");
            mostrarMensaje("$relacionesCount relaciones creadas", "success");
        } else {
            mostrarMensaje("No se encontraron productos en la categoría ASCENSORES", "warning");
        }
        
        // Reactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        mostrarMensaje("Proceso completado correctamente", "success");
        echo "<p><a href='cotizador_xls_fixed.php' class='btn'>Ir al Cotizador</a></p>";
        
    } else {
        // Mostrar formulario de creación
        echo "
        <div class='card'>
            <p>Este script creará las relaciones entre productos y opciones para el cotizador.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Crear la tabla producto_opciones si no existe</li>
                <li>Relacionar cada producto de la categoría ASCENSORES con todas las opciones de la categoría ADICIONALES</li>
            </ul>
            
            <form method='post'>
                <button type='submit' name='crear' class='btn'>Crear Relaciones</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
