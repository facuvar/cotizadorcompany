<?php
// Script para importar datos desde un archivo Excel con fórmulas (versión 2)
require_once 'vendor/autoload.php';
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;

// Variables globales para estadísticas
$estadisticas = [
    'productos' => 0,
    'opciones' => 0,
    'precios' => 0,
    'errores' => 0,
    'log' => []
];

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info', $mostrar = true) {
    global $estadisticas;
    
    // Registrar el mensaje en el log
    $estadisticas['log'][] = ['mensaje' => $mensaje, 'tipo' => $tipo];
    
    // Actualizar estadísticas
    if (strpos($mensaje, 'Producto creado') !== false || strpos($mensaje, 'Producto encontrado') !== false) {
        $estadisticas['productos']++;
    } else if (strpos($mensaje, 'Opción creada') !== false) {
        $estadisticas['opciones']++;
    } else if (strpos($mensaje, 'Precio guardado') !== false) {
        $estadisticas['precios']++;
    } else if ($tipo == 'error') {
        $estadisticas['errores']++;
    }
    
    // Solo mostrar si es necesario
    if ($mostrar) {
        $color = 'black';
        if ($tipo == 'success') $color = 'green';
        if ($tipo == 'error') $color = 'red';
        if ($tipo == 'warning') $color = 'orange';
        
        echo "<p style='color: $color;'>$mensaje</p>";
    }
}

// Función para limpiar y convertir valores monetarios
function limpiarValorMonetario($valor) {
    // Si es una cadena, limpiar caracteres no numéricos
    if (is_string($valor)) {
        $valor = preg_replace('/[^0-9.,]/', '', $valor);
        $valor = str_replace(['.', ','], ['', '.'], $valor);
    }
    
    return floatval($valor);
}

// Función para obtener el ID del plazo según su nombre
function obtenerPlazoId($conn, $nombrePlazo) {
    // Extraer el plazo del nombre (por ejemplo, "Precio 90 dias" -> "90 dias")
    $plazo = str_replace("Precio ", "", $nombrePlazo);
    
    // Buscar el plazo en la base de datos
    $stmt = $conn->prepare("SELECT id FROM xls_plazos WHERE nombre LIKE ?");
    $busqueda = "%$plazo%";
    $stmt->bind_param("s", $busqueda);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    
    // Si no se encuentra, crear el plazo
    $multiplicador = 1.0;
    if (strpos($plazo, "90") !== false) {
        $multiplicador = 1.3; // 30% adicional
    } else if (strpos($plazo, "270") !== false) {
        $multiplicador = 0.9; // 10% descuento
    }
    
    $stmt = $conn->prepare("INSERT INTO xls_plazos (nombre, multiplicador) VALUES (?, ?)");
    $stmt->bind_param("sd", $plazo, $multiplicador);
    $stmt->execute();
    
    return $conn->insert_id;
}

// Función para procesar la hoja de adicionales
function procesarHojaAdicionales($worksheet, $conn) {
    global $estadisticas;
    
    // Crear un archivo de registro para depuración
    $logFile = fopen('importacion_adicionales_debug.log', 'a');
    fwrite($logFile, "\n[" . date('Y-m-d H:i:s') . "] Iniciando procesamiento de hoja de adicionales\n");
    
    // Variables para el procesamiento
    $adicionales = [];
    $productosConAdicionales = [
        'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
        'ASCENSORES HIDRAULICOS',
        'MONTACARGAS',
        'MONTACARGAS - MAQUINA TAMBOR',
        'SALVAESCALERAS'
    ];
    
    // Lista de adicionales específicos para productos hidráulicos
    $adicionalesHidraulicos = [
        'ADICIONAL 2 TRAMOS',
        'ADICIONAL 750KG CENTRAL Y PISTON',
        'ADICIONAL CABINA 2,25M3',
        'ADICIONAL 1000KG CENTRAL Y PISTON',
        'ADICIONAL CABINA 2,66',
        'ADICIONAL PISO EN ACERO',
        'ADICIONAL PANORAMICO',
        'RESTAR CABINA EN CHAPA',
        'RESTAR PUERTA CABINA Y PB A CHAPA',
        'RESTAR SIN PUERTAS EXT X4',
        'RESTAR OPERADOR Y DEJAR PUERTA PLEGADIZA CHAPÀ',
        'PUERTAS DE 900',
        'PUERTAS DE 1000',
        'PUERTAS DE 1200',
        'PUERTAS DE 1800',
        'ADICIONAL ACCESO EN CABINA EN ACERO',
        'PUERTA PANORAMICA CABINA + PB',
        'PUERTA PANORAMICA PISOS',
        'TARJETA CHIP KEYPASS',
        'SISTEMA KEYPASS COMPLETO (UN COD POR PISO)',
        'SISTEMA KEYPASS SIMPLE (UN COD UNIVERSAL)',
        'SISTEMA UPS'
    ];
    
    $plazos = [];
    
    fwrite($logFile, "Productos que pueden tener adicionales: " . implode(", ", $productosConAdicionales) . "\n");
    
    // Obtener el rango de datos
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    fwrite($logFile, "Rango de datos: filas=$highestRow, columnas=$highestColumn\n");
    
    // Identificar columnas de plazos en la primera fila
    for ($col = 'C'; $col <= $highestColumn; $col++) {
        $header = $worksheet->getCell($col . '1')->getValue();
        if (strpos(strtolower($header), 'precio') !== false) {
            $plazos[$col] = $header;
        }
    }
    
    if (empty($plazos)) {
        $mensaje = "No se encontraron columnas de precios en la hoja de adicionales";
        fwrite($logFile, "ERROR: $mensaje\n");
        mostrarMensaje($mensaje, "error", false);
        fclose($logFile);
        return;
    }
    
    mostrarMensaje("Plazos de entrega detectados para adicionales: " . implode(", ", $plazos), "info", false);
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Procesar cada fila
        $currentAdicional = null;
        $adicionalId = null;
        $adicionalesImportados = 0;
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $adicionalName = trim($worksheet->getCell('A' . $row)->getValue());
            $descripcion = trim($worksheet->getCell('B' . $row)->getValue());
            
            fwrite($logFile, "Fila $row: Adicional='$adicionalName', Descripción='$descripcion'\n");
            
            // Si hay un adicional en columna A, usarlo como adicional actual
            if (!empty($adicionalName)) {
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
                    mostrarMensaje("Adicional creado: $adicionalName (ID: $adicionalId)", "success", false);
                    $estadisticas['opciones']++; // Contar como opción para las estadísticas
                } else {
                    $rowData = $result->fetch_assoc();
                    $adicionalId = $rowData['id'];
                    
                    // Actualizar la descripción
                    $query = "UPDATE xls_adicionales SET descripcion = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("si", $descripcion, $adicionalId);
                    $stmt->execute();
                    
                    mostrarMensaje("Adicional encontrado: $adicionalName (ID: $adicionalId)", "info", false);
                }
                
                // Procesar precios para cada plazo
                foreach ($plazos as $col => $plazoNombre) {
                    try {
                        // Obtener el valor calculado (no la fórmula)
                        $precioCalculado = $worksheet->getCell($col . $row)->getCalculatedValue();
                        
                        // Si es un array o no es un valor válido, saltarlo
                        if (is_array($precioCalculado) || !is_numeric($precioCalculado)) {
                            continue;
                        }
                        
                        // Limpiar y convertir el valor
                        $precio = limpiarValorMonetario($precioCalculado);
                        
                        // Obtener el ID del plazo
                        $plazoId = obtenerPlazoId($conn, $plazoNombre);
                        
                        // Guardar el precio
                        $query = "INSERT INTO xls_adicionales_precios (adicional_id, plazo_id, precio) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE precio = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("iidd", $adicionalId, $plazoId, $precio, $precio);
                        $stmt->execute();
                        
                        mostrarMensaje("Precio guardado para adicional '$adicionalName', plazo ID '$plazoId': $precio", "success", false);
                        $estadisticas['precios']++; // Contar como precio para las estadísticas
                    } catch (Exception $e) {
                        // Registrar el error pero continuar con el siguiente precio
                        mostrarMensaje("Error al procesar precio de adicional: " . $e->getMessage(), "error", false);
                    }
                }
                
                // Asociar el adicional con los productos específicos
                
                // Verificar si este adicional es para productos hidráulicos
                $esAdicionalHidraulico = in_array($adicionalName, $adicionalesHidraulicos);
                
                // Si es un adicional hidráulico, asociarlo solo a productos hidráulicos
                if ($esAdicionalHidraulico) {
                    // Buscar todos los productos hidráulicos
                    $query = "SELECT id, nombre FROM xls_productos WHERE nombre LIKE '%HIDRAULIC%'";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    fwrite($logFile, "Adicional hidráulico detectado: '$adicionalName'. Buscando productos hidráulicos...\n");
                    
                    if ($result->num_rows > 0) {
                        while ($productoRow = $result->fetch_assoc()) {
                            $productoId = $productoRow['id'];
                            $productoNombre = $productoRow['nombre'];
                            
                            fwrite($logFile, "  - Producto hidráulico encontrado: ID=$productoId, Nombre=$productoNombre\n");
                            
                            // Verificar si ya existe la relación
                            $query = "SELECT * FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $productoId, $adicionalId);
                            $stmt->execute();
                            $resultCheck = $stmt->get_result();
                            
                            if ($resultCheck->num_rows === 0) {
                                // Crear la relación
                                $query = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $productoId, $adicionalId);
                                $stmt->execute();
                                
                                $mensaje = "Adicional hidráulico '$adicionalName' asociado al producto hidráulico ID $productoId ($productoNombre)";
                                fwrite($logFile, "  - $mensaje\n");
                                mostrarMensaje($mensaje, "success", false);
                                $adicionalesImportados++;
                            } else {
                                fwrite($logFile, "  - Relación ya existente entre adicional ID=$adicionalId y producto hidráulico ID=$productoId\n");
                            }
                        }
                    } else {
                        fwrite($logFile, "  - No se encontraron productos hidráulicos para asociar con el adicional '$adicionalName'\n");
                    }
                } else {
                    // Para otros adicionales, usar la lógica original
                    foreach ($productosConAdicionales as $productoNombre) {
                        // Buscar el ID del producto
                        $query = "SELECT id FROM xls_productos WHERE nombre LIKE ?";
                        $stmt = $conn->prepare($query);
                        $productoNombreBusqueda = "%$productoNombre%";
                        $stmt->bind_param("s", $productoNombreBusqueda);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        fwrite($logFile, "Buscando producto '$productoNombre' con patrón '%$productoNombre%'\n");
                        
                        if ($result->num_rows > 0) {
                            $productoRow = $result->fetch_assoc();
                            $productoId = $productoRow['id'];
                            
                            fwrite($logFile, "  - Producto encontrado: ID=$productoId\n");
                            
                            // Verificar si ya existe la relación
                            $query = "SELECT * FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $productoId, $adicionalId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows === 0) {
                                // Crear la relación
                                $query = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $productoId, $adicionalId);
                                $stmt->execute();
                                
                                $mensaje = "Adicional '$adicionalName' asociado al producto ID $productoId";
                                fwrite($logFile, "  - $mensaje\n");
                                mostrarMensaje($mensaje, "success", false);
                                $adicionalesImportados++;
                            } else {
                                fwrite($logFile, "  - Relación ya existente entre adicional ID=$adicionalId y producto ID=$productoId\n");
                            }
                        } else {
                            fwrite($logFile, "  - No se encontró el producto '$productoNombre'\n");
                        }
                    }
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        mostrarMensaje("Importación de la hoja de adicionales completada con éxito", "success", false);
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        $mensaje = "Error al procesar la hoja de adicionales: " . $e->getMessage();
        fwrite($logFile, "ERROR: $mensaje\n");
        mostrarMensaje($mensaje, "error", false);
    }
    
    fwrite($logFile, "Total de adicionales importados: $adicionalesImportados\n");
    fwrite($logFile, "[" . date('Y-m-d H:i:s') . "] Finalizado procesamiento de hoja de adicionales\n");
    fclose($logFile);
}

// Función para procesar la hoja de productos
function procesarHoja($worksheet, $conn) {
    global $estadisticas;
    
    // Variables para el procesamiento
    $productos = [];
    $currentProduct = null;
    $productoId = null;
    $opciones = [];
    $plazos = [];
    
    // Obtener el rango de datos
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    // Identificar columnas de plazos en la primera fila
    for ($col = 'C'; $col <= $highestColumn; $col++) {
        $header = $worksheet->getCell($col . '1')->getValue();
        if (strpos(strtolower($header), 'precio') !== false) {
            $plazos[$col] = $header;
        }
    }
    
    if (empty($plazos)) {
        mostrarMensaje("No se encontraron columnas de precios en la hoja", "error", true);
        return;
    }
    
    mostrarMensaje("Plazos de entrega detectados: " . implode(", ", $plazos), "info", true);
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Procesar cada fila
        $lastProductName = '';
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $productName = trim($worksheet->getCell('A' . $row)->getValue());
            $opcionName = trim($worksheet->getCell('B' . $row)->getValue());
            
            // Si hay un producto en columna A, usarlo como producto actual
            if (!empty($productName)) {
                $currentProduct = $productName;
                
                // Si es un nuevo producto (diferente al anterior)
                if ($currentProduct != $lastProductName) {
                    // Verificar si el producto ya existe
                    $query = "SELECT id FROM xls_productos WHERE nombre = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $currentProduct);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        // Crear el producto
                        $query = "INSERT INTO xls_productos (nombre, orden) VALUES (?, ?)";
                        $stmt = $conn->prepare($query);
                        $orden = 0;
                        $stmt->bind_param("si", $currentProduct, $orden);
                        $stmt->execute();
                        $productoId = $conn->insert_id;
                        mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success", false);
                    } else {
                        $row = $result->fetch_assoc();
                        $productoId = $row['id'];
                        mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info", false);
                    }
                    
                    $lastProductName = $currentProduct;
                }
            }
            
            // Si hay una opción en columna B y tenemos un producto actual
            if (!empty($opcionName) && !empty($productoId)) {
                // Crear la opción
                $query = "INSERT INTO xls_opciones (producto_id, nombre) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $productoId, $opcionName);
                $stmt->execute();
                $opcionId = $conn->insert_id;
                
                mostrarMensaje("Opción creada: $opcionName para producto $currentProduct", "success", false);
                
                // Procesar precios para cada plazo
                foreach ($plazos as $col => $plazoNombre) {
                    try {
                        // Obtener el valor calculado (no la fórmula)
                        $precioCalculado = $worksheet->getCell($col . $row)->getCalculatedValue();
                        
                        // Si es un array o no es un valor válido, saltarlo
                        if (is_array($precioCalculado) || !is_numeric($precioCalculado)) {
                            continue;
                        }
                        
                        // Limpiar y convertir el valor
                        $precio = limpiarValorMonetario($precioCalculado);
                        
                        // Obtener el ID del plazo
                        $plazoId = obtenerPlazoId($conn, $plazoNombre);
                        
                        // Guardar el precio
                        $query = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("iid", $opcionId, $plazoId, $precio);
                        $stmt->execute();
                        
                        mostrarMensaje("Precio guardado para opción '$opcionName', plazo ID '$plazoId': $precio", "success", false);
                    } catch (Exception $e) {
                        // Registrar el error pero continuar con el siguiente precio
                        mostrarMensaje("Error al procesar precio: " . $e->getMessage(), "error", false);
                    }
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        mostrarMensaje("Importación de la hoja {$worksheet->getTitle()} completada con éxito", "success", true);
    } catch (Exception $e) {
        $conn->rollback();
        mostrarMensaje("Error al procesar la hoja: " . $e->getMessage(), "error", true);
    }
}

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Importar desde Excel con Fórmulas (V2)</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .card { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
            .note { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #4CAF50; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <h1>Importar desde Excel con Fórmulas (V2)</h1>";
    
    // Procesar formulario
    $archivoSubido = false;
    $mensaje = "";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar si se ha subido un archivo
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $tempFile = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            
            // Verificar extensión
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($fileExt !== 'xlsx') {
                $mensaje = mostrarMensaje("El archivo debe ser un archivo Excel (.xlsx)", "error", false);
            } else {
                $archivoSubido = true;
                
                // Verificar que existan las tablas necesarias para adicionales
                $tablasNecesarias = [
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
                
                foreach ($tablasNecesarias as $tabla => $sql) {
                    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
                    if ($result->num_rows == 0) {
                        mostrarMensaje("Creando tabla $tabla...", "info", false);
                        $conn->query($sql);
                    }
                }
                
                // Limpiar la base de datos si se solicita
                if (isset($_POST['limpiar_antes']) && $_POST['limpiar_antes'] == 'si') {
                    mostrarMensaje("Limpiando base de datos antes de importar...", "info", false);
                    
                    // Eliminar datos existentes
                    $conn->query("DELETE FROM xls_precios");
                    $conn->query("DELETE FROM xls_opciones");
                    $conn->query("DELETE FROM xls_adicionales_precios");
                    $conn->query("DELETE FROM xls_productos_adicionales");
                    $conn->query("DELETE FROM xls_adicionales");
                    $conn->query("DELETE FROM xls_productos");
                    
                    mostrarMensaje("Base de datos limpiada correctamente", "success", false);
                }
                
                // Mostrar mensaje de inicio de importación
                echo "<div id='importacion-progreso' style='background-color: #e9f7ef; border: 1px solid #28a745; border-radius: 5px; padding: 20px; margin: 20px 0;'>";
                echo "<h3 style='margin-top: 0;'>Importación en progreso...</h3>";
                echo "<p>Por favor espere mientras se procesa el archivo. Esto puede tomar unos minutos.</p>";
                echo "<div style='background-color: #ddd; border-radius: 5px; height: 20px;'>";
                echo "<div id='barra-progreso' style='background-color: #28a745; height: 20px; width: 10%; border-radius: 5px;'></div>";
                echo "</div>";
                echo "</div>";

                // Asegurarse de que la salida se envíe al navegador inmediatamente
                flush();
                ob_flush();

                // Cargar el archivo Excel
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(false); // Importante: leer fórmulas

                // Establecer tiempo máximo de ejecución
                set_time_limit(600); // 10 minutos

                $spreadsheet = $reader->load($tempFile);

                // Obtener las hojas disponibles
                $sheetNames = $spreadsheet->getSheetNames();

                // Procesar cada hoja
                foreach ($sheetNames as $index => $sheetName) {
                    $worksheet = $spreadsheet->getSheetByName($sheetName);
                    
                    // Actualizar barra de progreso
                    $progreso = 10 + (($index + 1) / count($sheetNames) * 80);
                    echo "<script>document.getElementById('barra-progreso').style.width = '{$progreso}%';</script>";
                    flush();
                    ob_flush();
                    
                    mostrarMensaje("Procesando hoja: $sheetName", "info", false);
                    
                    // Determinar qué función usar según el nombre de la hoja
                    if (strtolower($sheetName) === 'adicionales' || 
                        strpos(strtolower($sheetName), 'adicional') !== false ||
                        strpos(strtolower($sheetName), 'adicionales') !== false) {
                        mostrarMensaje("Procesando hoja de adicionales: $sheetName", "success", false);
                        procesarHojaAdicionales($worksheet, $conn);
                    } else {
                        procesarHoja($worksheet, $conn);
                    }
                }

                // Ocultar el div de progreso
                echo "<script>document.getElementById('importacion-progreso').style.display = 'none';</script>";
                flush();
                ob_flush();
                
                // Mostrar resumen de la importación
                echo "<div style='background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0;'>";
                echo "<h3 style='margin-top: 0;'>Resumen de la importación</h3>";
                echo "<ul>";
                echo "<li><strong>Productos importados:</strong> {$estadisticas['productos']}</li>";
                echo "<li><strong>Opciones importadas:</strong> {$estadisticas['opciones']}</li>";
                echo "<li><strong>Precios guardados:</strong> {$estadisticas['precios']}</li>";
                echo "</ul>";
                
                // Si hay errores, mostrar solo el resumen
                if ($estadisticas['errores'] > 0) {
                    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 10px; margin-top: 10px;'>";
                    echo "<h4 style='margin-top: 0;'>Errores encontrados: {$estadisticas['errores']}</h4>";
                    echo "<p>Se encontraron algunos errores durante la importación, pero se han manejado adecuadamente.</p>";
                    echo "</div>";
                }
                
                echo "<div style='margin-top: 20px;'>";
                echo "<a href='cotizador_simple.php' class='btn' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Cotizador Simplificado</a>";
                echo "<a href='admin/dashboard.php' class='btn' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Volver al Panel de Administración</a>";
                echo "</div>";
                echo "</div>";
            }
        } else if (isset($_FILES['excel_file'])) {
            $mensaje = mostrarMensaje("Error al subir el archivo: " . $_FILES['excel_file']['error'], "error");
        }
    }
    
    // Definir estilos CSS para toda la página
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Importar datos desde Excel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .note {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        input[type='file'] {
            display: block;
            margin: 10px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Importar datos desde Excel</h1>
";

    // Mostrar el formulario o el resultado
    if (!$archivoSubido) {
        echo "
        <div class='note'>
            <p><strong>Nota:</strong> Este script importará datos directamente desde un archivo Excel (.xlsx) con fórmulas.</p>
            <p>El sistema calculará los valores de las fórmulas y los importará correctamente.</p>
            <p>Esta versión utiliza las tablas xls_ que mantienen correctamente la estructura jerárquica entre productos y opciones.</p>
        </div>
        
        <form method='post' enctype='multipart/form-data'>
            <div class='form-group'>
                <label for='excel_file'><strong>Seleccione el archivo Excel:</strong></label>
                <input type='file' name='excel_file' id='excel_file' accept='.xlsx' required>
            </div>
            
            <div class='form-group'>
                <input type='checkbox' name='limpiar_antes' value='si' id='limpiar_antes' checked>
                <label for='limpiar_antes'>Limpiar base de datos antes de importar (recomendado)</label>
            </div>
            
            <div class='form-group'>
                <button type='submit' class='btn btn-success'>Importar datos</button>
                <a href='admin/dashboard.php' class='btn btn-secondary'>Volver al Panel</a>
            </div>
        </form>
        ";
    }
    
    echo "
        <div class='footer'>
            <p>&copy; " . date('Y') . " - Cotizador de Presupuestos</p>
        </div>
    </div>
</body>
</html>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
}
?>
