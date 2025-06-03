<?php
// Script para importar adicionales desde el archivo Excel
require_once 'vendor/autoload.php';
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo == 'error' ? 'red' : ($tipo == 'success' ? 'green' : 'blue');
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid $color; border-radius: 5px; color: $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Función para limpiar valores monetarios
function limpiarValorMonetario($valor) {
    if (is_string($valor)) {
        $valor = str_replace(['$', ',', ' '], '', $valor);
    }
    return floatval($valor);
}

// Función para obtener el ID del plazo
function obtenerPlazoId($conn, $nombrePlazo) {
    // Limpiar el nombre del plazo
    $nombrePlazo = trim(str_replace(['precio', 'dias', 'día', 'dias', 'plazo'], '', strtolower($nombrePlazo)));
    $nombrePlazo = trim($nombrePlazo);
    
    // Buscar el plazo por nombre
    $query = "SELECT id FROM xls_plazos WHERE nombre LIKE ?";
    $stmt = $conn->prepare($query);
    $nombrePlazoBusqueda = "%$nombrePlazo%";
    $stmt->bind_param("s", $nombrePlazoBusqueda);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    
    // Si no se encuentra, crear un nuevo plazo
    $query = "INSERT INTO xls_plazos (nombre) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombrePlazo);
    $stmt->execute();
    
    return $conn->insert_id;
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

// Verificar si se envió el formulario
if (isset($_POST['importar'])) {
    // Cargar el archivo Excel
    $excelFile = 'xls/cotizador-xls.xlsx';
    
    if (!file_exists($excelFile)) {
        mostrarMensaje("El archivo $excelFile no existe", "error");
    } else {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(false); // Importante: leer fórmulas
            $spreadsheet = $reader->load($excelFile);
            
            // Buscar la hoja ADICIONALES
            $hojaAdicionales = null;
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                if (strtoupper($sheetName) === 'ADICIONALES') {
                    $hojaAdicionales = $sheetName;
                    break;
                }
            }
            
            if (!$hojaAdicionales) {
                mostrarMensaje("No se encontró la hoja ADICIONALES en el archivo", "error");
            } else {
                $worksheet = $spreadsheet->getSheetByName($hojaAdicionales);
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                
                // Identificar columnas de plazos en la primera fila
                $plazos = [];
                for ($col = 'C'; $col <= $highestColumn; $col++) {
                    $header = $worksheet->getCell($col . '1')->getValue();
                    if (!empty($header) && (
                        strpos(strtolower($header), 'dia') !== false || 
                        strpos(strtolower($header), 'plazo') !== false || 
                        is_numeric(trim(str_replace(['días', 'dias', 'día', 'dia'], '', $header)))
                    )) {
                        $plazos[$col] = $header;
                    }
                }
                
                if (empty($plazos)) {
                    mostrarMensaje("No se encontraron columnas de plazos en la hoja ADICIONALES", "error");
                } else {
                    // Iniciar transacción
                    $conn->begin_transaction();
                    
                    try {
                        // Productos que pueden tener adicionales
                        $productosConAdicionales = [
                            'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                            'ASCENSORES HIDRAULICOS',
                            'MONTACARGAS',
                            'MONTACARGAS - MAQUINA TAMBOR',
                            'SALVAESCALERAS'
                        ];
                        
                        // IMPORTANTE: Forzar la importación de todos los adicionales para ASCENSORES HIDRAULICOS
                        // Obtener el ID del producto ASCENSORES HIDRAULICOS
                        $queryHidraulicos = "SELECT id FROM xls_productos WHERE nombre LIKE '%HIDRAULIC%'";
                        $resultHidraulicos = $conn->query($queryHidraulicos);
                        $hidraulicosIds = [];
                        
                        if ($resultHidraulicos && $resultHidraulicos->num_rows > 0) {
                            while ($row = $resultHidraulicos->fetch_assoc()) {
                                $hidraulicosIds[] = $row['id'];
                                mostrarMensaje("Encontrado producto hidráulico con ID: {$row['id']}", "success");
                            }
                        } else {
                            mostrarMensaje("No se encontraron productos hidráulicos", "error");
                        }
                        
                        // Array para almacenar estadísticas
                        $estadisticas = [
                            'adicionales' => 0,
                            'precios' => 0,
                            'asociaciones' => 0
                        ];
                        
                        // Procesar cada fila
                        for ($row = 2; $row <= $highestRow; $row++) {
                            // Verificar si la celda A existe
                            if (!$worksheet->cellExists('A' . $row)) {
                                continue;
                            }
                            
                            // Obtener valores
                            $adicionalName = trim($worksheet->getCell('A' . $row)->getValue());
                            
                            // Verificar si la celda B existe
                            $descripcion = '';
                            if ($worksheet->cellExists('B' . $row)) {
                                $descripcion = trim($worksheet->getCell('B' . $row)->getValue());
                            }
                            
                            // Si hay un adicional en columna A, procesarlo
                            // Ignorar filas con encabezados o valores no válidos
                            if (!empty($adicionalName) && 
                                $adicionalName !== 'ADICIONALES ASCENSORES ELECTROMECANICOS' && 
                                strpos(strtolower($adicionalName), 'adicional') !== false && 
                                strpos(strtolower($adicionalName), 'precio') === false && 
                                strpos(strtolower($adicionalName), 'dias') === false) {
                                // Verificar si el adicional ya existe
                                $query = "SELECT id FROM xls_adicionales WHERE nombre = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("s", $adicionalName);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows === 0) {
                                    // Crear el adicional
                                    $query = "INSERT INTO xls_adicionales (nombre, descripcion) VALUES (?, ?)";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("ss", $adicionalName, $descripcion);
                                    $stmt->execute();
                                    $adicionalId = $conn->insert_id;
                                    
                                    mostrarMensaje("Adicional creado: $adicionalName (ID: $adicionalId)", "success");
                                    $estadisticas['adicionales']++;
                                } else {
                                    $row = $result->fetch_assoc();
                                    $adicionalId = $row['id'];
                                    
                                    // Actualizar la descripción
                                    $query = "UPDATE xls_adicionales SET descripcion = ? WHERE id = ?";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("si", $descripcion, $adicionalId);
                                    $stmt->execute();
                                    
                                    mostrarMensaje("Adicional actualizado: $adicionalName (ID: $adicionalId)", "info");
                                }
                                
                                // IMPORTANTE: Asociar automáticamente todos los adicionales a productos hidráulicos
                                if (!empty($hidraulicosIds)) {
                                    foreach ($hidraulicosIds as $hidraulicoId) {
                                        // Verificar si ya existe la asociación
                                        $queryCheckAsociacion = "SELECT id FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                                        $stmtCheckAsociacion = $conn->prepare($queryCheckAsociacion);
                                        $stmtCheckAsociacion->bind_param("ii", $hidraulicoId, $adicionalId);
                                        $stmtCheckAsociacion->execute();
                                        $resultCheckAsociacion = $stmtCheckAsociacion->get_result();
                                        
                                        if ($resultCheckAsociacion->num_rows === 0) {
                                            // Crear la asociación
                                            $queryAsociacion = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                                            $stmtAsociacion = $conn->prepare($queryAsociacion);
                                            $stmtAsociacion->bind_param("ii", $hidraulicoId, $adicionalId);
                                            $stmtAsociacion->execute();
                                            
                                            mostrarMensaje("Adicional '$adicionalName' asociado automáticamente al producto hidráulico ID $hidraulicoId", "success");
                                            $estadisticas['asociaciones']++;
                                        }
                                    }
                                }
                                
                                // Procesar precios para cada plazo
                                foreach ($plazos as $col => $plazoNombre) {
                                    try {
                                        // Verificar si la celda existe y es válida
                                        $cellCoordinate = $col . $row;
                                        if (!$worksheet->cellExists($cellCoordinate)) {
                                            continue;
                                        }
                                        
                                        // Intentar obtener el valor
                                        try {
                                            $precioCalculado = $worksheet->getCell($cellCoordinate)->getCalculatedValue();
                                            
                                            // Si es un array o no es un valor válido, saltarlo
                                            if (is_array($precioCalculado)) {
                                                continue;
                                            }
                                            
                                            // Convertir a string y luego a número para evitar problemas con arrays
                                            $precioCalculado = (string)$precioCalculado;
                                            if (!is_numeric($precioCalculado)) {
                                                continue;
                                            }
                                            
                                            // Convertir a número
                                            $precioCalculado = floatval($precioCalculado);
                                        } catch (Exception $e) {
                                            // Si hay error al obtener el valor, saltarlo
                                            continue;
                                        }
                                        
                                        // Limpiar y convertir el valor
                                        $precio = limpiarValorMonetario($precioCalculado);
                                        
                                        // Verificar que el precio sea válido
                                        if ($precio <= 0) {
                                            continue;
                                        }
                                        
                                        // Obtener el ID del plazo
                                        $plazoId = obtenerPlazoId($conn, $plazoNombre);
                                        
                                        // Guardar el precio
                                        $query = "INSERT INTO xls_adicionales_precios (adicional_id, plazo_id, precio) 
                                                 VALUES (?, ?, ?) 
                                                 ON DUPLICATE KEY UPDATE precio = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("iidd", $adicionalId, $plazoId, $precio, $precio);
                                        $stmt->execute();
                                        
                                        mostrarMensaje("Precio guardado para adicional '$adicionalName', plazo '$plazoNombre': $precio", "success");
                                        $estadisticas['precios']++;
                                    } catch (Exception $e) {
                                        // Registrar el error pero continuar con el siguiente precio
                                        mostrarMensaje("Error al procesar precio de adicional: " . $e->getMessage(), "error");
                                    }
                                }
                                
                                // Asociar el adicional con los productos específicos
                                foreach ($productosConAdicionales as $productoNombre) {
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
                                                
                                                mostrarMensaje("Adicional '$adicionalName' asociado al producto ID $productoId ($productoNombre)", "success");
                                                $estadisticas['asociaciones']++;
                                            }
                                        }
                                    } else {
                                        mostrarMensaje("No se encontró el producto: $productoNombre", "error");
                                    }
                                }
                            }
                        }
                        
                        // Confirmar transacción
                        $conn->commit();
                        
                        // Mostrar resumen
                        echo "<div style='background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0;'>";
                        echo "<h3 style='margin-top: 0;'>Resumen de la importación</h3>";
                        echo "<ul>";
                        echo "<li><strong>Adicionales importados:</strong> {$estadisticas['adicionales']}</li>";
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
                        mostrarMensaje("Error al procesar la hoja de adicionales: " . $e->getMessage(), "error");
                    }
                }
            }
        } catch (Exception $e) {
            mostrarMensaje("Error al leer el archivo Excel: " . $e->getMessage(), "error");
        }
    }
}

// Mostrar formulario de importación
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Importar Adicionales</title>
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
    </style>
</head>
<body>
    <div class='container'>
        <h1>Importar Adicionales</h1>
        <p>Este script importará los adicionales desde el archivo <strong>xls/cotizador-xls.xlsx</strong> y los asociará a los productos específicos.</p>
        
        <form method='post'>
            <button type='submit' name='importar' value='si' class='btn btn-success'>Importar Adicionales</button>
        </form>
        
        <div style='margin-top: 20px;'>
            <a href='cotizador_simple.php' class='btn'>Ir al Cotizador</a>
        </div>
    </div>
</body>
</html>";
?>
