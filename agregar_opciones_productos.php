<?php
// Script para agregar opciones a los demás productos
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
        <title>Agregar Opciones a Productos</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
            .btn-blue { background-color: #2196F3; }
        </style>
    </head>
    <body>
    <h1>Agregar Opciones a Productos</h1>";
    
    // 1. Opciones para OPCION GEARLESS
    echo "<div class='section'>";
    echo "<h2>Agregando opciones para OPCION GEARLESS</h2>";
    
    // Obtener ID del producto
    $result = $conn->query("SELECT id FROM xls_productos WHERE nombre = 'OPCION GEARLESS'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productoId = $row['id'];
        
        // Verificar si ya existen opciones para este producto
        $result = $conn->query("SELECT COUNT(*) as count FROM xls_opciones WHERE producto_id = $productoId");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insertar opciones
            $opciones = [
                ["nombre" => "4 PARADAS", "precio_base" => 7500000.00, "orden" => 1],
                ["nombre" => "5 PARADAS", "precio_base" => 7800000.00, "orden" => 2],
                ["nombre" => "6 PARADAS", "precio_base" => 8100000.00, "orden" => 3],
                ["nombre" => "7 PARADAS", "precio_base" => 8400000.00, "orden" => 4],
                ["nombre" => "8 PARADAS", "precio_base" => 8700000.00, "orden" => 5]
            ];
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO xls_opciones (producto_id, nombre, precio_base, orden) 
                        VALUES ($productoId, '{$opcion['nombre']}', {$opcion['precio_base']}, {$opcion['orden']})";
                
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Opción '{$opcion['nombre']}' agregada correctamente.", "success");
                    
                    // Obtener el ID de la opción recién insertada
                    $opcionId = $conn->insert_id;
                    
                    // Insertar precios para cada plazo
                    $plazosResult = $conn->query("SELECT id, factor FROM xls_plazos ORDER BY orden");
                    if ($plazosResult && $plazosResult->num_rows > 0) {
                        while ($plazo = $plazosResult->fetch_assoc()) {
                            $plazoId = $plazo['id'];
                            $factor = $plazo['factor'];
                            $precio = $opcion['precio_base'] * $factor;
                            
                            $sql = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) 
                                    VALUES ($opcionId, $plazoId, $precio)";
                            
                            if ($conn->query($sql) === TRUE) {
                                mostrarMensaje("Precio para plazo $plazoId agregado correctamente.", "success");
                            } else {
                                mostrarMensaje("Error al agregar el precio: " . $conn->error, "error");
                            }
                        }
                    }
                } else {
                    mostrarMensaje("Error al agregar la opción '{$opcion['nombre']}': " . $conn->error, "error");
                }
            }
        } else {
            mostrarMensaje("Ya existen opciones para este producto.", "info");
        }
    } else {
        mostrarMensaje("No se encontró el producto.", "error");
    }
    
    echo "</div>";
    
    // 2. Opciones para HIDRAULICO 450KG CENTRAL 13HP PISTON 1 TRAMO
    echo "<div class='section'>";
    echo "<h2>Agregando opciones para HIDRAULICO 450KG CENTRAL 13HP PISTON 1 TRAMO</h2>";
    
    // Obtener ID del producto
    $result = $conn->query("SELECT id FROM xls_productos WHERE nombre = 'HIDRAULICO 450KG CENTRAL 13HP PISTON 1 TRAMO'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productoId = $row['id'];
        
        // Verificar si ya existen opciones para este producto
        $result = $conn->query("SELECT COUNT(*) as count FROM xls_opciones WHERE producto_id = $productoId");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insertar opciones
            $opciones = [
                ["nombre" => "2 PARADAS", "precio_base" => 5500000.00, "orden" => 1],
                ["nombre" => "3 PARADAS", "precio_base" => 6000000.00, "orden" => 2],
                ["nombre" => "4 PARADAS", "precio_base" => 6500000.00, "orden" => 3],
                ["nombre" => "5 PARADAS", "precio_base" => 7000000.00, "orden" => 4]
            ];
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO xls_opciones (producto_id, nombre, precio_base, orden) 
                        VALUES ($productoId, '{$opcion['nombre']}', {$opcion['precio_base']}, {$opcion['orden']})";
                
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Opción '{$opcion['nombre']}' agregada correctamente.", "success");
                    
                    // Obtener el ID de la opción recién insertada
                    $opcionId = $conn->insert_id;
                    
                    // Insertar precios para cada plazo
                    $plazosResult = $conn->query("SELECT id, factor FROM xls_plazos ORDER BY orden");
                    if ($plazosResult && $plazosResult->num_rows > 0) {
                        while ($plazo = $plazosResult->fetch_assoc()) {
                            $plazoId = $plazo['id'];
                            $factor = $plazo['factor'];
                            $precio = $opcion['precio_base'] * $factor;
                            
                            $sql = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) 
                                    VALUES ($opcionId, $plazoId, $precio)";
                            
                            if ($conn->query($sql) === TRUE) {
                                mostrarMensaje("Precio para plazo $plazoId agregado correctamente.", "success");
                            } else {
                                mostrarMensaje("Error al agregar el precio: " . $conn->error, "error");
                            }
                        }
                    }
                } else {
                    mostrarMensaje("Error al agregar la opción '{$opcion['nombre']}': " . $conn->error, "error");
                }
            }
        } else {
            mostrarMensaje("Ya existen opciones para este producto.", "info");
        }
    } else {
        mostrarMensaje("No se encontró el producto.", "error");
    }
    
    echo "</div>";
    
    // 3. Opciones para HIDRAULICO 450KG CENTRAL 25LTS 4HP
    echo "<div class='section'>";
    echo "<h2>Agregando opciones para HIDRAULICO 450KG CENTRAL 25LTS 4HP</h2>";
    
    // Obtener ID del producto
    $result = $conn->query("SELECT id FROM xls_productos WHERE nombre = 'HIDRAULICO 450KG CENTRAL 25LTS 4HP'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productoId = $row['id'];
        
        // Verificar si ya existen opciones para este producto
        $result = $conn->query("SELECT COUNT(*) as count FROM xls_opciones WHERE producto_id = $productoId");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insertar opciones
            $opciones = [
                ["nombre" => "2 PARADAS", "precio_base" => 5200000.00, "orden" => 1],
                ["nombre" => "3 PARADAS", "precio_base" => 5700000.00, "orden" => 2],
                ["nombre" => "4 PARADAS", "precio_base" => 6200000.00, "orden" => 3]
            ];
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO xls_opciones (producto_id, nombre, precio_base, orden) 
                        VALUES ($productoId, '{$opcion['nombre']}', {$opcion['precio_base']}, {$opcion['orden']})";
                
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Opción '{$opcion['nombre']}' agregada correctamente.", "success");
                    
                    // Obtener el ID de la opción recién insertada
                    $opcionId = $conn->insert_id;
                    
                    // Insertar precios para cada plazo
                    $plazosResult = $conn->query("SELECT id, factor FROM xls_plazos ORDER BY orden");
                    if ($plazosResult && $plazosResult->num_rows > 0) {
                        while ($plazo = $plazosResult->fetch_assoc()) {
                            $plazoId = $plazo['id'];
                            $factor = $plazo['factor'];
                            $precio = $opcion['precio_base'] * $factor;
                            
                            $sql = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) 
                                    VALUES ($opcionId, $plazoId, $precio)";
                            
                            if ($conn->query($sql) === TRUE) {
                                mostrarMensaje("Precio para plazo $plazoId agregado correctamente.", "success");
                            } else {
                                mostrarMensaje("Error al agregar el precio: " . $conn->error, "error");
                            }
                        }
                    }
                } else {
                    mostrarMensaje("Error al agregar la opción '{$opcion['nombre']}': " . $conn->error, "error");
                }
            }
        } else {
            mostrarMensaje("Ya existen opciones para este producto.", "info");
        }
    } else {
        mostrarMensaje("No se encontró el producto.", "error");
    }
    
    echo "</div>";
    
    // 4. Opciones para MONTACARGAS - MAQUINA TAMBOR
    echo "<div class='section'>";
    echo "<h2>Agregando opciones para MONTACARGAS - MAQUINA TAMBOR</h2>";
    
    // Obtener ID del producto
    $result = $conn->query("SELECT id FROM xls_productos WHERE nombre = 'MONTACARGAS - MAQUINA TAMBOR'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productoId = $row['id'];
        
        // Verificar si ya existen opciones para este producto
        $result = $conn->query("SELECT COUNT(*) as count FROM xls_opciones WHERE producto_id = $productoId");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insertar opciones
            $opciones = [
                ["nombre" => "1000KG", "precio_base" => 9500000.00, "orden" => 1],
                ["nombre" => "1500KG", "precio_base" => 10500000.00, "orden" => 2],
                ["nombre" => "2000KG", "precio_base" => 11500000.00, "orden" => 3],
                ["nombre" => "2500KG", "precio_base" => 12500000.00, "orden" => 4],
                ["nombre" => "3000KG", "precio_base" => 13500000.00, "orden" => 5]
            ];
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO xls_opciones (producto_id, nombre, precio_base, orden) 
                        VALUES ($productoId, '{$opcion['nombre']}', {$opcion['precio_base']}, {$opcion['orden']})";
                
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Opción '{$opcion['nombre']}' agregada correctamente.", "success");
                    
                    // Obtener el ID de la opción recién insertada
                    $opcionId = $conn->insert_id;
                    
                    // Insertar precios para cada plazo
                    $plazosResult = $conn->query("SELECT id, factor FROM xls_plazos ORDER BY orden");
                    if ($plazosResult && $plazosResult->num_rows > 0) {
                        while ($plazo = $plazosResult->fetch_assoc()) {
                            $plazoId = $plazo['id'];
                            $factor = $plazo['factor'];
                            $precio = $opcion['precio_base'] * $factor;
                            
                            $sql = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) 
                                    VALUES ($opcionId, $plazoId, $precio)";
                            
                            if ($conn->query($sql) === TRUE) {
                                mostrarMensaje("Precio para plazo $plazoId agregado correctamente.", "success");
                            } else {
                                mostrarMensaje("Error al agregar el precio: " . $conn->error, "error");
                            }
                        }
                    }
                } else {
                    mostrarMensaje("Error al agregar la opción '{$opcion['nombre']}': " . $conn->error, "error");
                }
            }
        } else {
            mostrarMensaje("Ya existen opciones para este producto.", "info");
        }
    } else {
        mostrarMensaje("No se encontró el producto.", "error");
    }
    
    echo "</div>";
    
    // Enlace al cotizador
    echo "<div class='section'>";
    echo "<h2>Opciones agregadas correctamente</h2>";
    echo "<p>Se han agregado opciones para varios productos.</p>";
    echo "<p>Ahora puede acceder al cotizador XLS:</p>";
    echo "<a href='cotizador_xls.php' class='btn'>Ir al Cotizador XLS</a>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
