<?php
// Script para configurar la base de datos para el cotizador completo
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
        <title>Configuración del Cotizador Completo</title>
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
    <h1>Configuración del Cotizador Completo</h1>";
    
    // 1. Crear tabla de productos si no existe
    echo "<div class='section'>";
    echo "<h2>Configurando tabla de productos</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS productos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        orden INT(11) DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'productos' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'productos': " . $conn->error, "error");
    }
    
    // 2. Verificar si la tabla opciones ya existe
    $result = $conn->query("SHOW TABLES LIKE 'opciones'");
    if ($result->num_rows > 0) {
        mostrarMensaje("La tabla 'opciones' ya existe, verificando estructura...", "info");
        
        // Verificar si la tabla tiene la columna categoria_id
        $result = $conn->query("SHOW COLUMNS FROM opciones LIKE 'categoria_id'");
        if ($result->num_rows > 0) {
            mostrarMensaje("La tabla 'opciones' ya tiene la columna 'categoria_id'", "info");
        } else {
            // Verificar si tiene producto_id
            $result = $conn->query("SHOW COLUMNS FROM opciones LIKE 'producto_id'");
            if ($result->num_rows > 0) {
                // Renombrar la columna de producto_id a categoria_id
                $sql = "ALTER TABLE opciones CHANGE producto_id categoria_id INT(11) NOT NULL";
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Columna 'producto_id' renombrada a 'categoria_id'", "success");
                } else {
                    mostrarMensaje("Error al renombrar la columna: " . $conn->error, "error");
                }
            } else {
                // Agregar la columna categoria_id
                $sql = "ALTER TABLE opciones ADD categoria_id INT(11) NOT NULL AFTER id";
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Columna 'categoria_id' agregada", "success");
                } else {
                    mostrarMensaje("Error al agregar la columna: " . $conn->error, "error");
                }
            }
        }
    } else {
        // Crear la tabla opciones con categoria_id
        $sql = "CREATE TABLE IF NOT EXISTS opciones (
            id INT(11) NOT NULL AUTO_INCREMENT,
            categoria_id INT(11) NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            precio_base DECIMAL(10,2) DEFAULT 0,
            orden INT(11) DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql) === TRUE) {
            mostrarMensaje("Tabla 'opciones' creada correctamente", "success");
        } else {
            mostrarMensaje("Error al crear la tabla 'opciones': " . $conn->error, "error");
        }
    }
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'opciones' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'opciones': " . $conn->error, "error");
    }
    
    // 3. Crear tabla de plazos de entrega si no existe
    $sql = "CREATE TABLE IF NOT EXISTS plazos_entrega (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        factor DECIMAL(10,2) DEFAULT 1.00,
        orden INT(11) DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'plazos_entrega' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'plazos_entrega': " . $conn->error, "error");
    }
    
    // 4. Crear tabla de adicionales si no existe
    $sql = "CREATE TABLE IF NOT EXISTS adicionales (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio_base DECIMAL(10,2) DEFAULT 0,
        tipo VARCHAR(50) NOT NULL,
        orden INT(11) DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'adicionales' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'adicionales': " . $conn->error, "error");
    }
    
    // 5. Crear tabla de relación entre adicionales y productos
    $sql = "CREATE TABLE IF NOT EXISTS adicionales_productos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        adicional_id INT(11) NOT NULL,
        producto_id INT(11) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (adicional_id) REFERENCES adicionales(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'adicionales_productos' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'adicionales_productos': " . $conn->error, "error");
    }
    
    // 6. Crear tabla de precios según plazo
    $sql = "CREATE TABLE IF NOT EXISTS precios (
        id INT(11) NOT NULL AUTO_INCREMENT,
        opcion_id INT(11) NOT NULL,
        adicional_id INT(11) DEFAULT NULL,
        plazo_id INT(11) NOT NULL,
        precio DECIMAL(10,2) DEFAULT 0,
        PRIMARY KEY (id),
        FOREIGN KEY (opcion_id) REFERENCES opciones(id) ON DELETE CASCADE,
        FOREIGN KEY (adicional_id) REFERENCES adicionales(id) ON DELETE CASCADE,
        FOREIGN KEY (plazo_id) REFERENCES plazos_entrega(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        mostrarMensaje("Tabla 'precios' creada o ya existente.", "success");
    } else {
        mostrarMensaje("Error al crear la tabla 'precios': " . $conn->error, "error");
    }
    
    echo "</div>";
    
    // Insertar plazos de entrega predeterminados
    echo "<div class='section'>";
    echo "<h2>Configurando plazos de entrega</h2>";
    
    // Verificar si ya existen plazos
    $result = $conn->query("SELECT COUNT(*) as count FROM plazos_entrega");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insertar plazos predeterminados
        $plazos = [
            ["nombre" => "90 días", "descripcion" => "Entrega rápida (90 días)", "factor" => 1.30, "orden" => 1],
            ["nombre" => "160-180 días", "descripcion" => "Entrega estándar (160-180 días)", "factor" => 1.00, "orden" => 2],
            ["nombre" => "270 días", "descripcion" => "Entrega económica (270 días)", "factor" => 0.90, "orden" => 3]
        ];
        
        foreach ($plazos as $plazo) {
            $sql = "INSERT INTO plazos_entrega (nombre, descripcion, factor, orden) 
                    VALUES ('{$plazo['nombre']}', '{$plazo['descripcion']}', {$plazo['factor']}, {$plazo['orden']})";
            
            if ($conn->query($sql) === TRUE) {
                mostrarMensaje("Plazo '{$plazo['nombre']}' agregado correctamente.", "success");
            } else {
                mostrarMensaje("Error al agregar el plazo '{$plazo['nombre']}': " . $conn->error, "error");
            }
        }
    } else {
        mostrarMensaje("Los plazos de entrega ya están configurados.", "info");
    }
    
    echo "</div>";
    
    // Insertar productos predeterminados
    echo "<div class='section'>";
    echo "<h2>Configurando productos</h2>";
    
    // Verificar si ya existen productos
    $result = $conn->query("SELECT COUNT(*) as count FROM productos");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insertar productos predeterminados
        $productos = [
            ["nombre" => "EQUIPO ELECTROMECANICO 450KG CARGA UTIL", "orden" => 1],
            ["nombre" => "OPCION GEARLESS", "orden" => 2],
            ["nombre" => "HIDRAULICO 450KG CENTRAL 13HP PISTON 1 TRAMO", "orden" => 3],
            ["nombre" => "HIDRAULICO 450KG CENTRAL 25LTS 4HP", "orden" => 4],
            ["nombre" => "MISMA CARACT PERO DIRECTO", "orden" => 5],
            ["nombre" => "DOMICILIARIO PUERTA PLEGADIZA CABINA EXT SIN PUERTAS", "orden" => 6],
            ["nombre" => "MONTAVEHICULOS", "orden" => 7],
            ["nombre" => "MONTACARGAS - MAQUINA TAMBOR", "orden" => 8],
            ["nombre" => "SALVAESCALERAS", "orden" => 9],
            ["nombre" => "ESCALERAS MECANICAS - VIDRIADO - FALDON ACERO", "orden" => 10],
            ["nombre" => "MONTAPLATOS", "orden" => 11],
            ["nombre" => "GIRACOCHES", "orden" => 12],
            ["nombre" => "ESTRUCTURA", "orden" => 13],
            ["nombre" => "PEFIL DIVISORIO", "orden" => 14]
        ];
        
        foreach ($productos as $producto) {
            $sql = "INSERT INTO productos (nombre, orden) 
                    VALUES ('{$producto['nombre']}', {$producto['orden']})";
            
            if ($conn->query($sql) === TRUE) {
                mostrarMensaje("Producto '{$producto['nombre']}' agregado correctamente.", "success");
            } else {
                mostrarMensaje("Error al agregar el producto '{$producto['nombre']}': " . $conn->error, "error");
            }
        }
    } else {
        mostrarMensaje("Los productos ya están configurados.", "info");
    }
    
    echo "</div>";
    
    // Insertar opciones para el primer producto (EQUIPO ELECTROMECANICO)
    echo "<div class='section'>";
    echo "<h2>Configurando opciones para EQUIPO ELECTROMECANICO</h2>";
    
    // Obtener ID del producto EQUIPO ELECTROMECANICO
    $result = $conn->query("SELECT id FROM productos WHERE nombre = 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productoId = $row['id'];
        
        // Verificar si ya existen opciones para este producto
        $result = $conn->query("SELECT COUNT(*) as count FROM opciones WHERE producto_id = $productoId");
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insertar opciones predeterminadas
            $opciones = [
                ["nombre" => "4 PARADAS", "precio_base" => 6541500.00, "orden" => 1],
                ["nombre" => "5 PARADAS", "precio_base" => 6786500.00, "orden" => 2],
                ["nombre" => "6 PARADAS", "precio_base" => 7031500.00, "orden" => 3],
                ["nombre" => "7 PARADAS", "precio_base" => 7252000.00, "orden" => 4],
                ["nombre" => "8 PARADAS", "precio_base" => 7497000.00, "orden" => 5],
                ["nombre" => "9 PARADAS", "precio_base" => 7742000.00, "orden" => 6],
                ["nombre" => "10 PARADAS", "precio_base" => 7987000.00, "orden" => 7],
                ["nombre" => "11 PARADAS", "precio_base" => 8207500.00, "orden" => 8],
                ["nombre" => "12 PARADAS", "precio_base" => 8452500.00, "orden" => 9],
                ["nombre" => "13 PARADAS", "precio_base" => 8697500.00, "orden" => 10],
                ["nombre" => "14 PARADAS", "precio_base" => 8918000.00, "orden" => 11],
                ["nombre" => "15 PARADAS", "precio_base" => 9163000.00, "orden" => 12]
            ];
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO opciones (producto_id, nombre, precio_base, orden) 
                        VALUES ($productoId, '{$opcion['nombre']}', {$opcion['precio_base']}, {$opcion['orden']})";
                
                if ($conn->query($sql) === TRUE) {
                    mostrarMensaje("Opción '{$opcion['nombre']}' agregada correctamente.", "success");
                } else {
                    mostrarMensaje("Error al agregar la opción '{$opcion['nombre']}': " . $conn->error, "error");
                }
            }
        } else {
            mostrarMensaje("Las opciones para EQUIPO ELECTROMECANICO ya están configuradas.", "info");
        }
    } else {
        mostrarMensaje("No se encontró el producto EQUIPO ELECTROMECANICO.", "error");
    }
    
    echo "</div>";
    
    // Insertar adicionales para ASCENSORES ELECTROMECANICOS
    echo "<div class='section'>";
    echo "<h2>Configurando adicionales para ASCENSORES ELECTROMECANICOS</h2>";
    
    // Verificar si ya existen adicionales
    $result = $conn->query("SELECT COUNT(*) as count FROM adicionales WHERE tipo = 'ELECTROMECANICOS'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insertar adicionales predeterminados
        $adicionales = [
            ["nombre" => "ADICIONAL 750KG MAQUINA", "precio_base" => 637000.00, "orden" => 1],
            ["nombre" => "ADICIONAL CABINA 2,25M3", "precio_base" => 73500.00, "orden" => 2],
            ["nombre" => "ADICIONAL 1000KG MAQUINA", "precio_base" => 796250.00, "orden" => 3],
            ["nombre" => "ADICIONAL CABINA 2,66", "precio_base" => 129850.00, "orden" => 4],
            ["nombre" => "ADICIONAL ACCESO CABINA EN ACERO", "precio_base" => 343000.00, "orden" => 5],
            ["nombre" => "ADICIONAL ACERO PISOS", "precio_base" => 75950.00, "orden" => 6],
            ["nombre" => "ADICIONAL LATERAL PANORAMICO", "precio_base" => 110250.00, "orden" => 7]
        ];
        
        foreach ($adicionales as $adicional) {
            $sql = "INSERT INTO adicionales (nombre, precio_base, tipo, orden) 
                    VALUES ('{$adicional['nombre']}', {$adicional['precio_base']}, 'ELECTROMECANICOS', {$adicional['orden']})";
            
            if ($conn->query($sql) === TRUE) {
                mostrarMensaje("Adicional '{$adicional['nombre']}' agregado correctamente.", "success");
                
                // Relacionar con el producto EQUIPO ELECTROMECANICO
                $adicionalId = $conn->insert_id;
                $result = $conn->query("SELECT id FROM productos WHERE nombre = 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL'");
                
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $productoId = $row['id'];
                    
                    $sql = "INSERT INTO adicionales_productos (adicional_id, producto_id) 
                            VALUES ($adicionalId, $productoId)";
                    
                    if ($conn->query($sql) === TRUE) {
                        mostrarMensaje("Adicional relacionado con EQUIPO ELECTROMECANICO.", "success");
                    } else {
                        mostrarMensaje("Error al relacionar el adicional: " . $conn->error, "error");
                    }
                }
            } else {
                mostrarMensaje("Error al agregar el adicional '{$adicional['nombre']}': " . $conn->error, "error");
            }
        }
    } else {
        mostrarMensaje("Los adicionales para ASCENSORES ELECTROMECANICOS ya están configurados.", "info");
    }
    
    echo "</div>";
    
    // Configurar precios según plazos
    echo "<div class='section'>";
    echo "<h2>Configurando precios según plazos</h2>";
    
    // Obtener plazos
    $plazosResult = $conn->query("SELECT id, nombre, factor FROM plazos_entrega ORDER BY orden");
    
    if ($plazosResult && $plazosResult->num_rows > 0) {
        // Obtener opciones del producto EQUIPO ELECTROMECANICO
        $result = $conn->query("SELECT id FROM productos WHERE nombre = 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL'");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $productoId = $row['id'];
            
            $opcionesResult = $conn->query("SELECT id, nombre, precio_base FROM opciones WHERE producto_id = $productoId ORDER BY orden");
            
            if ($opcionesResult && $opcionesResult->num_rows > 0) {
                // Para cada opción, configurar precios según plazos
                while ($opcion = $opcionesResult->fetch_assoc()) {
                    $opcionId = $opcion['id'];
                    $precioBase = $opcion['precio_base'];
                    
                    // Verificar si ya existen precios para esta opción
                    $result = $conn->query("SELECT COUNT(*) as count FROM precios WHERE opcion_id = $opcionId");
                    $row = $result->fetch_assoc();
                    
                    if ($row['count'] == 0) {
                        // Para cada plazo, calcular el precio
                        $plazosResult->data_seek(0); // Reiniciar el puntero del resultado
                        while ($plazo = $plazosResult->fetch_assoc()) {
                            $plazoId = $plazo['id'];
                            $factor = $plazo['factor'];
                            $precio = $precioBase * $factor;
                            
                            $sql = "INSERT INTO precios (opcion_id, plazo_id, precio) 
                                    VALUES ($opcionId, $plazoId, $precio)";
                            
                            if ($conn->query($sql) === TRUE) {
                                mostrarMensaje("Precio para '{$opcion['nombre']}' con plazo '{$plazo['nombre']}' configurado.", "success");
                            } else {
                                mostrarMensaje("Error al configurar el precio: " . $conn->error, "error");
                            }
                        }
                    } else {
                        mostrarMensaje("Los precios para '{$opcion['nombre']}' ya están configurados.", "info");
                    }
                }
            } else {
                mostrarMensaje("No se encontraron opciones para EQUIPO ELECTROMECANICO.", "error");
            }
        } else {
            mostrarMensaje("No se encontró el producto EQUIPO ELECTROMECANICO.", "error");
        }
    } else {
        mostrarMensaje("No se encontraron plazos de entrega.", "error");
    }
    
    echo "</div>";
    
    // Configurar precios para adicionales
    echo "<div class='section'>";
    echo "<h2>Configurando precios para adicionales</h2>";
    
    // Obtener adicionales
    $adicionalesResult = $conn->query("SELECT id, nombre, precio_base FROM adicionales WHERE tipo = 'ELECTROMECANICOS' ORDER BY orden");
    
    if ($adicionalesResult && $adicionalesResult->num_rows > 0) {
        // Obtener plazos
        $plazosResult = $conn->query("SELECT id, nombre, factor FROM plazos_entrega ORDER BY orden");
        
        if ($plazosResult && $plazosResult->num_rows > 0) {
            // Para cada adicional, configurar precios según plazos
            while ($adicional = $adicionalesResult->fetch_assoc()) {
                $adicionalId = $adicional['id'];
                $precioBase = $adicional['precio_base'];
                
                // Verificar si ya existen precios para este adicional
                $result = $conn->query("SELECT COUNT(*) as count FROM precios WHERE adicional_id = $adicionalId");
                $row = $result->fetch_assoc();
                
                if ($row['count'] == 0) {
                    // Para cada plazo, calcular el precio
                    $plazosResult->data_seek(0); // Reiniciar el puntero del resultado
                    while ($plazo = $plazosResult->fetch_assoc()) {
                        $plazoId = $plazo['id'];
                        $factor = $plazo['factor'];
                        $precio = $precioBase * $factor;
                        
                        $sql = "INSERT INTO precios (adicional_id, plazo_id, precio) 
                                VALUES ($adicionalId, $plazoId, $precio)";
                        
                        if ($conn->query($sql) === TRUE) {
                            mostrarMensaje("Precio para '{$adicional['nombre']}' con plazo '{$plazo['nombre']}' configurado.", "success");
                        } else {
                            mostrarMensaje("Error al configurar el precio: " . $conn->error, "error");
                        }
                    }
                } else {
                    mostrarMensaje("Los precios para '{$adicional['nombre']}' ya están configurados.", "info");
                }
            }
        } else {
            mostrarMensaje("No se encontraron plazos de entrega.", "error");
        }
    } else {
        mostrarMensaje("No se encontraron adicionales para ASCENSORES ELECTROMECANICOS.", "error");
    }
    
    echo "</div>";
    
    // Enlace al cotizador
    echo "<div class='section'>";
    echo "<h2>Configuración completada</h2>";
    echo "<p>La configuración del cotizador completo ha sido realizada correctamente.</p>";
    echo "<p>Ahora puede acceder al cotizador completo:</p>";
    echo "<a href='sistema/cotizador_completo.php' class='btn'>Ir al Cotizador Completo</a>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
