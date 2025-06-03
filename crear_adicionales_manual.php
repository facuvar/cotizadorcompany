<?php
// Script para crear adicionales manualmente
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

// Definición de adicionales por tipo de producto
$adicionalesPorProducto = [
    'EQUIPO ELECTROMECANICO 450KG CARGA UTIL' => [
        ['nombre' => 'ADICIONAL 750KG MAQUINA', 'descripcion' => 'Adicional para máquina de 750KG', 'precios' => [1 => 3194919, 2 => 2211867, 3 => 2457630]],
        ['nombre' => 'ADICIONAL CABINA 2,25M3', 'descripcion' => 'Adicional para cabina de 2,25M3', 'precios' => [1 => 363490, 2 => 251647, 3 => 279608]],
        ['nombre' => 'ADICIONAL 1000KG MAQUINA', 'descripcion' => 'Adicional para máquina de 1000KG', 'precios' => [1 => 3991286, 2 => 2763198, 3 => 3070220]],
        ['nombre' => 'ADICIONAL CABINA 2,66', 'descripcion' => 'Adicional para cabina de 2,66', 'precios' => [1 => 641234, 2 => 443931, 3 => 493257]],
        ['nombre' => 'ADICIONAL ACCESO CABINA EN ACERO', 'descripcion' => 'Adicional para acceso de cabina en acero', 'precios' => [1 => 1696292, 2 => 1174356, 3 => 1304840]],
        ['nombre' => 'ADICIONAL ACERO PISOS', 'descripcion' => 'Adicional para acero en pisos', 'precios' => [1 => 374674, 2 => 259390, 3 => 288211]],
        ['nombre' => 'ADICIONAL LATERAL PANORAMICO', 'descripcion' => 'Adicional para lateral panorámico', 'precios' => [1 => 544302, 2 => 376825, 3 => 418694]],
        ['nombre' => 'CABINA EN CHAPA C/DETALLES RESTAR', 'descripcion' => 'Cabina en chapa con detalles', 'precios' => [1 => 351680, 2 => 243471, 3 => 270523]],
        ['nombre' => 'PB Y PUERTA DE CABINA EN CHAPA RESTAR', 'descripcion' => 'PB y puerta de cabina en chapa', 'precios' => [1 => 510899, 2 => 353699, 3 => 392999]],
        ['nombre' => 'PUERTAS DE 900', 'descripcion' => 'Puertas de 900', 'precios' => [1 => 579721, 2 => 401345, 3 => 445939]],
        ['nombre' => 'PUERTAS DE 1000', 'descripcion' => 'Puertas de 1000', 'precios' => [1 => 1161306, 2 => 803981, 3 => 893312]],
        ['nombre' => 'PUERTAS DE 1300', 'descripcion' => 'Puertas de 1300', 'precios' => [1 => 1453964, 2 => 1006591, 3 => 1118434]],
        ['nombre' => 'PUERTAS DE 1800', 'descripcion' => 'Puertas de 1800', 'precios' => [1 => 1645961, 2 => 1139512, 3 => 1266124]],
        ['nombre' => 'PUERTA PANORAMICA CABINA + PB', 'descripcion' => 'Puerta panorámica cabina + PB', 'precios' => [1 => 1817287, 2 => 1258122, 3 => 1397913]],
        ['nombre' => 'PUERTA PANORAMICA PISOS', 'descripcion' => 'Puerta panorámica pisos', 'precios' => [1 => 848145, 2 => 587177, 3 => 652419]],
        ['nombre' => 'TARJETA CHIP KEYPASS', 'descripcion' => 'Tarjeta chip keypass', 'precios' => [1 => 18639, 2 => 12904, 3 => 14338]],
        ['nombre' => 'SISTEMA KEYPASS COMPLETO (UN COD POR PISO)', 'descripcion' => 'Sistema keypass completo', 'precios' => [1 => 1099794, 2 => 761396, 3 => 845995]],
        ['nombre' => 'SISTEMA KEYPASS SIMPLE (UN COD UNIVERSAL)', 'descripcion' => 'Sistema keypass simple', 'precios' => [1 => 484654, 2 => 335530, 3 => 372811]],
        ['nombre' => 'SISTEMA UPS', 'descripcion' => 'Sistema UPS', 'precios' => [1 => 253510, 2 => 175507, 3 => 195008]],
        ['nombre' => 'INDICADOR LED ALFA NUM 1, 2', 'descripcion' => 'Indicador LED alfanumérico 1, 2', 'precios' => [1 => 102522, 2 => 70977, 3 => 78863]],
        ['nombre' => 'INDICADOR LED ALFA NUM 0, 8', 'descripcion' => 'Indicador LED alfanumérico 0, 8', 'precios' => [1 => 76424, 2 => 52909, 3 => 58788]],
        ['nombre' => 'INDICADOR LCD COLOR 5"', 'descripcion' => 'Indicador LCD color 5 pulgadas', 'precios' => [1 => 311247, 2 => 215479, 3 => 239421]],
        ['nombre' => 'BALANZA', 'descripcion' => 'Balanza', 'precios' => [1 => 1047599, 2 => 725261, 3 => 805845]],
        ['nombre' => 'INTERCOMUNICADOR', 'descripcion' => 'Intercomunicador', 'precios' => [1 => 838824, 2 => 580724, 3 => 645249]],
        ['nombre' => 'FASE I / FASE II BOMBERIOS', 'descripcion' => 'Fase I / Fase II bomberos', 'precios' => [1 => 419411, 2 => 290362, 3 => 322624]],
        ['nombre' => 'EXTENSION DE PANEL CABINA A 2,30', 'descripcion' => 'Extensión de panel cabina a 2,30', 'precios' => [1 => 838824, 2 => 580724, 3 => 645249]],
        ['nombre' => 'PARADA ADICIONAL CHAPA (PRECIO POR CADA UNA)', 'descripcion' => 'Parada adicional chapa (precio por cada una)', 'precios' => [1 => 2110112, 2 => 1460847, 3 => 1623163]]
    ],
    'ASCENSORES HIDRAULICOS' => [
        ['nombre' => 'ADICIONAL 2 TRAMOS', 'descripcion' => 'Adicional 2 tramos', 'precios' => [1 => 1519206, 2 => 1367285, 3 => 1168620]],
        ['nombre' => 'ADICIONAL 750KG CENTRAL Y PISTON', 'descripcion' => 'Adicional 750KG central y pistón', 'precios' => [1 => 932027, 2 => 838824, 3 => 716944]],
        ['nombre' => 'ADICIONAL CABINA 2,25M3', 'descripcion' => 'Adicional cabina 2,25M3', 'precios' => [1 => 363490, 2 => 327141, 3 => 279608]],
        ['nombre' => 'ADICIONAL 1000KG CENTRAL Y PISTON', 'descripcion' => 'Adicional 1000KG central y pistón', 'precios' => [1 => 2264829, 2 => 2038346, 3 => 1742176]],
        ['nombre' => 'ADICIONAL CABINA 2,66', 'descripcion' => 'Adicional cabina 2,66', 'precios' => [1 => 642444, 2 => 578200, 3 => 494188]],
        ['nombre' => 'ADICIONAL PISO EN ACERO', 'descripcion' => 'Adicional piso en acero', 'precios' => [1 => 374674, 2 => 337207, 3 => 288211]],
        ['nombre' => 'ADICIONAL PANORAMICO', 'descripcion' => 'Adicional panorámico', 'precios' => [1 => 545234, 2 => 490711, 3 => 419411]],
        ['nombre' => 'RESTAR CABINA EN CHAPA', 'descripcion' => 'Restar cabina en chapa', 'precios' => [1 => 374674, 2 => 337207, 3 => 288211]],
        ['nombre' => 'RESTAR PUERTA CABINA Y PB A CHAPA', 'descripcion' => 'Restar puerta cabina y PB a chapa', 'precios' => [1 => 544304, 2 => 489873, 3 => 418695]],
        ['nombre' => 'RESTAR SIN PUERTAS EXT X4', 'descripcion' => 'Restar sin puertas ext x4', 'precios' => [1 => 2251781, 2 => 2026603, 3 => 1732139]],
        ['nombre' => 'RESTAR OPERADOR Y DEJAR PUERTA PLEGADIZA CHAPÀ', 'descripcion' => 'Restar operador y dejar puerta plegadiza chapa', 'precios' => [1 => 665467, 2 => 598921, 3 => 511898]],
        ['nombre' => 'PUERTAS DE 900', 'descripcion' => 'Puertas de 900', 'precios' => [1 => 572991, 2 => 515692, 3 => 440762]],
        ['nombre' => 'PUERTAS DE 1000', 'descripcion' => 'Puertas de 1000', 'precios' => [1 => 1144146, 2 => 1029731, 3 => 880112]],
        ['nombre' => 'PUERTAS DE 1200', 'descripcion' => 'Puertas de 1200', 'precios' => [1 => 1432478, 2 => 1289230, 3 => 1101906]],
        ['nombre' => 'PUERTAS DE 1800', 'descripcion' => 'Puertas de 1800', 'precios' => [1 => 1623474, 2 => 1461126, 3 => 1248826]],
        ['nombre' => 'ADICIONAL ACCESO EN CABINA EN ACERO', 'descripcion' => 'Adicional acceso en cabina en acero', 'precios' => [1 => 1694427, 2 => 1524984, 3 => 1303405]],
        ['nombre' => 'PUERTA PANORAMICA CABINA + PB', 'descripcion' => 'Puerta panorámica cabina + PB', 'precios' => [1 => 1817287, 2 => 1635558, 3 => 1397913]],
        ['nombre' => 'PUERTA PANORAMICA PISOS', 'descripcion' => 'Puerta panorámica pisos', 'precios' => [1 => 848145, 2 => 763330, 3 => 652419]],
        ['nombre' => 'TARJETA CHIP KEYPASS', 'descripcion' => 'Tarjeta chip keypass', 'precios' => [1 => 18639, 2 => 16775, 3 => 14338]],
        ['nombre' => 'SISTEMA KEYPASS COMPLETO (UN COD POR PISO)', 'descripcion' => 'Sistema keypass completo (un código por piso)', 'precios' => [1 => 1099794, 2 => 989814, 3 => 845995]],
        ['nombre' => 'SISTEMA KEYPASS SIMPLE (UN COD UNIVERSAL)', 'descripcion' => 'Sistema keypass simple (un código universal)', 'precios' => [1 => 484654, 2 => 436189, 3 => 372811]],
        ['nombre' => 'SISTEMA UPS', 'descripcion' => 'Sistema UPS', 'precios' => [1 => 253510, 2 => 228159, 3 => 195008]]
    ],
    'MONTACARGAS' => [
        ['nombre' => 'PUERTA GUILLOTINA - PRECIO UNITARIO', 'descripcion' => 'Puerta guillotina - precio unitario', 'precios' => [1 => 2889289, 2 => 2600360, 3 => 2222530]],
        ['nombre' => 'PUERTA TIJERA - PRECIO UNITARIO', 'descripcion' => 'Puerta tijera - precio unitario', 'precios' => [1 => 2170373, 2 => 1953336, 3 => 1669518]]
    ],
    'SALVAESCALERAS' => [
        ['nombre' => 'ADICIONAL EN ACERO', 'descripcion' => 'Adicional en acero', 'precios' => [1 => 2846415, 2 => 2561774, 3 => 2189550]]
    ]
];

// Combinar todos los adicionales en una sola lista
$adicionales = [];
$precios = [1 => [], 2 => [], 3 => []];

foreach ($adicionalesPorProducto as $producto => $listaAdicionales) {
    foreach ($listaAdicionales as $adicional) {
        $adicionales[] = ['nombre' => $adicional['nombre'], 'descripcion' => $adicional['descripcion']];
        
        // Guardar precios por plazo
        foreach ($adicional['precios'] as $plazoId => $precio) {
            $precios[$plazoId][$adicional['nombre']] = $precio;
        }
    }
}

// Productos que pueden tener adicionales con sus adicionales específicos
$productosConAdicionales = array_keys($adicionalesPorProducto);

// Verificar si se envió el formulario
if (isset($_POST['crear'])) {
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Limpiar tablas existentes si se solicita
        if (isset($_POST['limpiar']) && $_POST['limpiar'] == 'si') {
            $conn->query("DELETE FROM xls_adicionales_precios");
            $conn->query("DELETE FROM xls_productos_adicionales");
            $conn->query("DELETE FROM xls_adicionales");
            mostrarMensaje("Tablas de adicionales limpiadas correctamente", "success");
        }
        
        // Estadísticas
        $estadisticas = [
            'adicionales' => 0,
            'precios' => 0,
            'asociaciones' => 0
        ];
        
        // Crear los adicionales
        foreach ($adicionales as $adicional) {
            // Verificar si el adicional ya existe
            $query = "SELECT id FROM xls_adicionales WHERE nombre = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $adicional['nombre']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Crear el adicional
                $query = "INSERT INTO xls_adicionales (nombre, descripcion) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ss", $adicional['nombre'], $adicional['descripcion']);
                $stmt->execute();
                $adicionalId = $conn->insert_id;
                
                mostrarMensaje("Adicional creado: {$adicional['nombre']} (ID: $adicionalId)", "success");
                $estadisticas['adicionales']++;
            } else {
                $row = $result->fetch_assoc();
                $adicionalId = $row['id'];
                
                // Actualizar la descripción
                $query = "UPDATE xls_adicionales SET descripcion = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $adicional['descripcion'], $adicionalId);
                $stmt->execute();
                
                mostrarMensaje("Adicional actualizado: {$adicional['nombre']} (ID: $adicionalId)", "info");
            }
            
            // Guardar precios para cada plazo
            foreach ($precios as $plazoId => $preciosPlazo) {
                if (isset($preciosPlazo[$adicional['nombre']])) {
                    $precio = $preciosPlazo[$adicional['nombre']];
                    
                    // Guardar el precio
                    $query = "INSERT INTO xls_adicionales_precios (adicional_id, plazo_id, precio) 
                             VALUES (?, ?, ?) 
                             ON DUPLICATE KEY UPDATE precio = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iidd", $adicionalId, $plazoId, $precio, $precio);
                    $stmt->execute();
                    
                    mostrarMensaje("Precio guardado para adicional '{$adicional['nombre']}', plazo ID $plazoId: $precio", "success");
                    $estadisticas['precios']++;
                }
            }
            
            // Asociar solo con los productos específicos que deben tener este adicional
            foreach ($adicionalesPorProducto as $productoNombre => $listaAdicionales) {
                // Verificar si este adicional pertenece a este producto
                $perteneceAProducto = false;
                foreach ($listaAdicionales as $adicionalInfo) {
                    if ($adicionalInfo['nombre'] === $adicional['nombre']) {
                        $perteneceAProducto = true;
                        break;
                    }
                }
                
                if (!$perteneceAProducto) {
                    continue; // Este adicional no pertenece a este producto, continuar con el siguiente
                }
                
                // Buscar el ID del producto
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
                            
                            mostrarMensaje("Adicional '{$adicional['nombre']}' asociado al producto ID $productoId ($productoNombre)", "success");
                            $estadisticas['asociaciones']++;
                        }
                    }
                } else {
                    mostrarMensaje("No se encontró el producto: $productoNombre", "error");
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        // Mostrar resumen
        echo "<div style='background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0;'>";
        echo "<h3 style='margin-top: 0;'>Resumen de la creación de adicionales</h3>";
        echo "<ul>";
        echo "<li><strong>Adicionales creados/actualizados:</strong> {$estadisticas['adicionales']}</li>";
        echo "<li><strong>Precios guardados:</strong> {$estadisticas['precios']}</li>";
        echo "<li><strong>Asociaciones creadas:</strong> {$estadisticas['asociaciones']}</li>";
        echo "</ul>";
        
        echo "<div style='margin-top: 20px;'>";
        echo "<a href='cotizador_simple.php' class='btn' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Cotizador Simplificado</a>";
        echo "</div>";
        echo "</div>";
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        mostrarMensaje("Error al crear adicionales: " . $e->getMessage(), "error");
    }
}

// Mostrar formulario
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Crear Adicionales Manualmente</title>
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
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .checkbox-container {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Crear Adicionales Manualmente</h1>
        <p>Este script creará los adicionales predefinidos y los asociará a los productos específicos.</p>
        
        <form method='post'>
            <div class='checkbox-container'>
                <input type='checkbox' id='limpiar' name='limpiar' value='si'>
                <label for='limpiar'>Limpiar adicionales existentes antes de crear nuevos</label>
            </div>
            
            <button type='submit' name='crear' value='si' class='btn btn-success'>Crear Adicionales</button>
        </form>
        
        <div style='margin-top: 20px;'>
            <a href='cotizador_simple.php' class='btn'>Ir al Cotizador</a>
        </div>
    </div>
</body>
</html>";
?>
