<?php
// Script para sincronizar precios desde xls_precios a opcion_precios
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
        <title>Sincronizar Precios</title>
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
        <h1>Sincronizar Precios</h1>";
    
    // Verificar si se solicitó la sincronización
    if (isset($_POST['sincronizar'])) {
        // Desactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Sincronizar plazos_entrega desde xls_plazos
        mostrarMensaje("Sincronizando plazos de entrega...", "info");
        
        // Verificar si la tabla plazos_entrega existe
        $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
        if ($result->num_rows == 0) {
            // Crear la tabla plazos_entrega
            $conn->query("CREATE TABLE plazos_entrega (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                descripcion VARCHAR(255),
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla plazos_entrega creada", "success");
        }
        
        // Limpiar tabla plazos_entrega
        $conn->query("TRUNCATE TABLE plazos_entrega");
        
        // Obtener plazos de xls_plazos
        $result = $conn->query("SELECT * FROM xls_plazos");
        $plazosCount = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($plazo = $result->fetch_assoc()) {
                $plazoNombre = $plazo['nombre'];
                $multiplicador = 1.0;
                
                // Determinar multiplicador según el nombre
                if (strpos($plazoNombre, "90") !== false) {
                    $multiplicador = 1.3; // 30% adicional
                } else if (strpos($plazoNombre, "270") !== false) {
                    $multiplicador = 0.9; // 10% descuento
                }
                
                // Insertar en la tabla plazos_entrega
                $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, descripcion) VALUES (?, ?)");
                $descripcion = "Plazo de entrega: $plazoNombre";
                $stmt->bind_param("ss", $plazoNombre, $descripcion);
                $stmt->execute();
                $plazosCount++;
            }
            mostrarMensaje("$plazosCount plazos de entrega sincronizados", "success");
        } else {
            // Insertar plazos predeterminados
            $conn->query("INSERT INTO plazos_entrega (nombre, descripcion) VALUES 
                ('160-180 dias', 'Plazo estándar (160-180 días)'),
                ('90 dias', 'Plazo rápido (90 días)'),
                ('270 dias', 'Plazo económico (270 días)')");
            mostrarMensaje("Plazos predeterminados insertados", "success");
        }
        
        // 2. Sincronizar opciones desde xls_opciones a opciones
        mostrarMensaje("Sincronizando opciones...", "info");
        
        // Verificar si la tabla opciones existe
        $result = $conn->query("SHOW TABLES LIKE 'opciones'");
        if ($result->num_rows == 0) {
            // Crear la tabla opciones
            $conn->query("CREATE TABLE opciones (
                id INT(11) NOT NULL AUTO_INCREMENT,
                categoria_id INT(11) NOT NULL,
                nombre VARCHAR(255) NOT NULL,
                descripcion TEXT,
                precio DECIMAL(15,2) DEFAULT 0.00,
                orden INT(11) DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla opciones creada", "success");
        }
        
        // Limpiar tabla opciones
        $conn->query("TRUNCATE TABLE opciones");
        
        // Obtener categorías
        $categorias = [];
        $result = $conn->query("SELECT * FROM categorias");
        if ($result && $result->num_rows > 0) {
            while ($categoria = $result->fetch_assoc()) {
                $categorias[$categoria['nombre']] = $categoria['id'];
            }
        } else {
            // Crear categorías predeterminadas
            $conn->query("INSERT INTO categorias (id, nombre, descripcion, orden) VALUES 
                (1, 'ASCENSORES', 'Equipos electromecanicos', 1),
                (2, 'ADICIONALES', 'Características adicionales', 2),
                (3, 'DESCUENTOS', 'Descuentos aplicables', 3)");
            
            $categorias['ASCENSORES'] = 1;
            $categorias['ADICIONALES'] = 2;
            $categorias['DESCUENTOS'] = 3;
            
            mostrarMensaje("Categorías predeterminadas creadas", "success");
        }
        
        // Obtener productos y opciones
        $result = $conn->query("
            SELECT p.id as producto_id, p.nombre as producto_nombre, o.id as opcion_id, o.nombre as opcion_nombre
            FROM xls_productos p
            LEFT JOIN xls_opciones o ON o.producto_id = p.id
            ORDER BY p.id, o.id
        ");
        
        $opcionesCount = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productoNombre = $row['producto_nombre'];
                $opcionNombre = $row['opcion_nombre'];
                $opcionId = $row['opcion_id'];
                
                // Determinar la categoría según el nombre del producto
                $categoriaId = 1; // Por defecto, categoría ASCENSORES
                
                if (strpos(strtoupper($productoNombre), 'ADICIONAL') !== false) {
                    $categoriaId = 2; // Categoría ADICIONALES
                } else if (strpos(strtoupper($productoNombre), 'DESCUENTO') !== false) {
                    $categoriaId = 3; // Categoría DESCUENTOS
                }
                
                if ($opcionId) {
                    // Obtener el precio base (usamos el primer precio que encontremos)
                    $precioBase = 0;
                    $precioResult = $conn->query("SELECT precio FROM xls_precios WHERE opcion_id = $opcionId LIMIT 1");
                    
                    if ($precioResult && $precioResult->num_rows > 0) {
                        $precioRow = $precioResult->fetch_assoc();
                        $precioBase = $precioRow['precio'];
                    }
                    
                    // Insertar en la tabla opciones
                    $stmt = $conn->prepare("INSERT INTO opciones (id, categoria_id, nombre, descripcion, precio, orden) VALUES (?, ?, ?, ?, ?, ?)");
                    $descripcion = "Producto: $productoNombre";
                    $orden = $opcionId;
                    $stmt->bind_param("iissdi", $opcionId, $categoriaId, $opcionNombre, $descripcion, $precioBase, $orden);
                    $stmt->execute();
                    $opcionesCount++;
                }
            }
            mostrarMensaje("$opcionesCount opciones sincronizadas", "success");
        } else {
            mostrarMensaje("No se encontraron opciones para sincronizar", "warning");
        }
        
        // 3. Sincronizar opcion_precios desde xls_precios
        mostrarMensaje("Sincronizando precios...", "info");
        
        // Verificar si la tabla opcion_precios existe
        $result = $conn->query("SHOW TABLES LIKE 'opcion_precios'");
        if ($result->num_rows == 0) {
            // Crear la tabla opcion_precios
            $conn->query("CREATE TABLE opcion_precios (
                id INT(11) NOT NULL AUTO_INCREMENT,
                opcion_id INT(11) NOT NULL,
                plazo_entrega VARCHAR(100) NOT NULL,
                precio DECIMAL(15,2) NOT NULL,
                PRIMARY KEY (id),
                KEY opcion_id (opcion_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            mostrarMensaje("Tabla opcion_precios creada", "success");
        }
        
        // Limpiar tabla opcion_precios
        $conn->query("TRUNCATE TABLE opcion_precios");
        
        // Obtener precios de xls_precios
        $result = $conn->query("
            SELECT xp.opcion_id, xpl.nombre AS plazo_entrega, xp.precio
            FROM xls_precios xp
            JOIN xls_plazos xpl ON xp.plazo_id = xpl.id
        ");
        
        $preciosCount = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $opcionId = $row['opcion_id'];
                $plazoEntrega = $row['plazo_entrega'];
                $precio = $row['precio'];
                
                // Insertar en la tabla opcion_precios
                $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_entrega, precio) VALUES (?, ?, ?)");
                $stmt->bind_param("isd", $opcionId, $plazoEntrega, $precio);
                $stmt->execute();
                $preciosCount++;
            }
            mostrarMensaje("$preciosCount precios sincronizados", "success");
        } else {
            mostrarMensaje("No se encontraron precios para sincronizar", "warning");
        }
        
        // Reactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        mostrarMensaje("Sincronización completada correctamente", "success");
        echo "<p><a href='cotizador_xls_fixed.php' class='btn'>Ir al Cotizador</a></p>";
        
    } else {
        // Mostrar formulario de sincronización
        echo "
        <div class='card'>
            <p>Este script sincronizará los datos importados desde Excel con las tablas necesarias para el cotizador.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Sincronizar plazos de entrega desde xls_plazos a plazos_entrega</li>
                <li>Sincronizar opciones desde xls_opciones a opciones</li>
                <li>Sincronizar precios desde xls_precios a opcion_precios</li>
            </ul>
            
            <form method='post'>
                <button type='submit' name='sincronizar' class='btn'>Sincronizar Precios</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
