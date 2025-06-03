<?php
session_start();
require_once '../sistema/includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $html = "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>";
    $html .= $mensaje;
    $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    $html .= "</div>";
    
    return $html;
}

// Función para obtener el ID de un plazo o crearlo si no existe
function obtenerPlazoId($conn, $nombrePlazo) {
    $stmt = $conn->prepare("SELECT id FROM xls_plazos WHERE nombre LIKE ?");
    $stmt->bind_param("s", $nombrePlazo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        // Crear el plazo si no existe
        $stmt = $conn->prepare("INSERT INTO xls_plazos (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombrePlazo);
        $stmt->execute();
        return $conn->insert_id;
    }
}

// Función para limpiar valores monetarios
function limpiarValorMonetario($valor) {
    // Si es un array, convertirlo a string o usar 0
    if (is_array($valor)) {
        error_log("Array encontrado en valor monetario: " . print_r($valor, true));
        return 0;
    }
    
    // Si es null o vacío, devolver 0
    if ($valor === null || $valor === '') {
        return 0;
    }
    
    // Convertir a string para manipularlo
    $valor = (string)$valor;
    
    // Eliminar caracteres no numéricos excepto punto y coma
    $valor = preg_replace('/[^0-9.,]/', '', $valor);
    
    // Reemplazar coma por punto
    $valor = str_replace(',', '.', $valor);
    
    // Convertir a float
    return floatval($valor);
}

// Función para obtener el valor de una celda de forma segura
function getCellValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!preg_match('/^[A-Z]+[0-9]+$/', $coordinate)) {
            error_log("Coordenada de celda inválida: $coordinate");
            return "";
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return "";
        }
        
        // Obtener el valor de la celda
        $value = $worksheet->getCell($coordinate)->getValue();
        
        // Manejar el valor si es un array
        if (is_array($value)) {
            error_log("Array encontrado en celda: " . print_r($value, true));
            return "0";
        }
        
        return $value;
    } catch (Exception $e) {
        error_log("Error al obtener valor de celda $coordinate: " . $e->getMessage());
        return "";
    }
}

// Función para obtener el valor calculado de una celda de forma segura
function getCalculatedValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!preg_match('/^[A-Z]+[0-9]+$/', $coordinate)) {
            error_log("Coordenada de celda inválida para cálculo: $coordinate");
            return 0;
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return 0;
        }
        
        // Obtener el valor calculado de la celda
        $value = $worksheet->getCell($coordinate)->getCalculatedValue();
        
        // Manejar el valor si es un array
        if (is_array($value)) {
            error_log("Array encontrado en valor calculado: " . print_r($value, true));
            return 0;
        }
        
        return $value;
    } catch (Exception $e) {
        error_log("Error al obtener valor calculado de celda $coordinate: " . $e->getMessage());
        return 0;
    }
}

// Función para obtener el valor de una celda de forma segura
function getCellValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!preg_match('/^[A-Z]+[0-9]+$/', $coordinate)) {
            error_log("Coordenada de celda inválida: $coordinate");
            return "";
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return "";
        }
        
        // Obtener el valor de la celda
        $value = $worksheet->getCell($coordinate)->getValue();
        
        // Manejar el valor si es un array
        if (is_array($value)) {
            error_log("Array encontrado en celda: " . print_r($value, true));
            return "0";
        }
        
        return $value;
    } catch (Exception $e) {
        error_log("Error al obtener valor de celda $coordinate: " . $e->getMessage());
        return "";
    }
}

// Función para obtener el valor calculado de una celda de forma segura
function getCalculatedValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!preg_match('/^[A-Z]+[0-9]+$/', $coordinate)) {
            error_log("Coordenada de celda inválida para cálculo: $coordinate");
            return 0;
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return 0;
        }
        
        // Obtener el valor calculado de la celda
        $value = $worksheet->getCell($coordinate)->getCalculatedValue();
        
        // Manejar el valor si es un array
        if (is_array($value)) {
            error_log("Array encontrado en valor calculado: " . print_r($value, true));
            return 0;
        }
        
        return $value;
    } catch (Exception $e) {
        error_log("Error al obtener valor calculado de celda $coordinate: " . $e->getMessage());
        return 0;
    }
}

// Inicializar mensaje
$mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    // Verificar si se ha subido un archivo
    if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        // Verificar extensión del archivo
        $fileExtension = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);
        
        if ($fileExtension === 'xlsx') {
            // Mover archivo temporal
            $tempFile = $_FILES['excel_file']['tmp_name'];
            
            // Verificar si se debe limpiar la base de datos
            $limpiarDB = isset($_POST['limpiar_db']) && $_POST['limpiar_db'] === 'on';
            
            // Conectar a la base de datos
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Limpiar la base de datos si se ha solicitado
            if ($limpiarDB) {
                $mensaje .= mostrarMensaje("Limpiando base de datos...", "info");
                
                // Iniciar transacción
                $conn->begin_transaction();
                
                try {
                    // Eliminar datos existentes
                    $conn->query("DELETE FROM xls_precios");
                    $conn->query("DELETE FROM xls_opciones");
                    $conn->query("DELETE FROM xls_productos");
                    $conn->query("DELETE FROM xls_plazos");
                    
                    // Reiniciar auto-incrementos
                    $conn->query("ALTER TABLE xls_precios AUTO_INCREMENT = 1");
                    $conn->query("ALTER TABLE xls_opciones AUTO_INCREMENT = 1");
                    $conn->query("ALTER TABLE xls_productos AUTO_INCREMENT = 1");
                    $conn->query("ALTER TABLE xls_plazos AUTO_INCREMENT = 1");
                    
                    // Confirmar transacción
                    $conn->commit();
                    
                    $mensaje .= mostrarMensaje("Base de datos limpiada correctamente", "success");
                } catch (Exception $e) {
                    // Revertir cambios en caso de error
                    $conn->rollback();
                    $mensaje .= mostrarMensaje("Error al limpiar la base de datos: " . $e->getMessage(), "error");
                }
            }
            
            // Cargar librería PhpSpreadsheet
            require '../vendor/autoload.php';
            
            try {
                // Cargar archivo Excel
                $mensaje .= mostrarMensaje("Cargando archivo Excel...", "info");
                
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(false); // Importante: leer fórmulas
                
                // Establecer tiempo máximo de ejecución
                set_time_limit(600); // 10 minutos
                
                $spreadsheet = $reader->load($tempFile);
                
                // Obtener las hojas disponibles
                $sheetNames = $spreadsheet->getSheetNames();
                
                // Iniciar transacción para la importación
                $conn->begin_transaction();
                
                // Procesar cada hoja
                foreach ($sheetNames as $sheetName) {
                    $mensaje .= mostrarMensaje("Procesando hoja: $sheetName", "info");
                    
                    try {
                        // Obtener la hoja de trabajo actual
                        $worksheet = $spreadsheet->getSheetByName($sheetName);
                        
                        if (!$worksheet) {
                            $mensaje .= mostrarMensaje("La hoja '$sheetName' no existe en el archivo", "error");
                            continue;
                        }
                        
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
                            try {
                                $cellCoordinate = $col . '1';
                                
                                // Usar nuestra función segura para obtener el valor
                                $header = getCellValueSafely($worksheet, $cellCoordinate);
                                
                                if (is_string($header) && strpos(strtolower($header), 'precio') !== false) {
                                    $plazos[$col] = trim(str_replace('precio', '', strtolower($header)));
                                }
                            } catch (Exception $e) {
                                error_log("Error al procesar encabezado en columna $col: " . $e->getMessage());
                                continue;
                            }
                        }
                        
                        if (empty($plazos)) {
                            $mensaje .= mostrarMensaje("No se encontraron columnas de precios en la hoja", "warning");
                            continue;
                        }
                        
                        // Procesar filas de datos (desde la fila 2)
                        $lastProductName = '';
                        
                        for ($row = 2; $row <= $highestRow; $row++) {
                            try {
                                // Usar funciones seguras para obtener valores
                                $productName = getCellValueSafely($worksheet, 'A' . $row);
                                $opcionName = getCellValueSafely($worksheet, 'B' . $row);
                                
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
                                            $mensaje .= mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
                                        } else {
                                            $row_data = $result->fetch_assoc();
                                            $productoId = $row_data['id'];
                                            $mensaje .= mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info");
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
                                    
                                    $mensaje .= mostrarMensaje("Opción creada: $opcionName para producto $currentProduct", "success");
                                    
                                    // Procesar precios para cada plazo
                                    foreach ($plazos as $col => $plazoNombre) {
                                        // Verificar que $col sea una cadena válida
                                        if (!is_string($col)) {
                                            error_log("Error: Columna no válida: " . print_r($col, true));
                                            continue; // Saltar esta iteración si la columna no es válida
                                        }
                                        
                                        // Obtener el valor calculado de forma segura
                                        $cellCoordinate = $col . $row;
                                        $precioCalculado = getCalculatedValueSafely($worksheet, $cellCoordinate);
                                        
                                        // Limpiar y convertir el valor
                                        $precio = limpiarValorMonetario($precioCalculado);
                                        
                                        // Obtener el ID del plazo
                                        $plazoId = obtenerPlazoId($conn, $plazoNombre);
                                        
                                        // Guardar el precio
                                        $query = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("iid", $opcionId, $plazoId, $precio);
                                        $stmt->execute();
                                        
                                        $mensaje .= mostrarMensaje("Precio guardado para opción '$opcionName', plazo ID '$plazoId': $precio", "success");
                                    }
                                }
                            } catch (Exception $e) {
                                error_log("Error al procesar fila $row: " . $e->getMessage());
                                $mensaje .= mostrarMensaje("Error al procesar fila $row: " . $e->getMessage(), "error");
                                continue;
                            }
                        }
                    } catch (Exception $e) {
                        $mensaje .= mostrarMensaje("Error al procesar la hoja: " . $e->getMessage(), "error");
                    }
                }
                
                // Confirmar transacción
                $conn->commit();
                $mensaje .= mostrarMensaje("Importación finalizada", "success");
                
            } catch (Exception $e) {
                // Revertir cambios en caso de error
                if ($conn->inTransaction()) {
                    $conn->rollback();
                }
                $mensaje .= mostrarMensaje("Error al importar el archivo: " . $e->getMessage(), "error");
            }
        } else {
            $mensaje = mostrarMensaje('El archivo debe ser un archivo Excel (.xlsx)', 'error');
        }
    } else {
        $mensaje = mostrarMensaje('Error al subir el archivo: ' . $_FILES['excel_file']['error'], 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Datos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 20px;
        }
        .container {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Importar Datos desde Excel</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Instrucciones</h5>
            </div>
            <div class="card-body">
                <p>Suba un archivo Excel (.xlsx) con los datos a importar. El archivo debe tener el siguiente formato:</p>
                <ul>
                    <li>Columna A: Nombre del producto (solo en la primera fila de cada producto)</li>
                    <li>Columna B: Nombre de la opción</li>
                    <li>Columnas C en adelante: Precios para diferentes plazos (encabezados deben contener la palabra "precio")</li>
                </ul>
                <p class="text-danger">¡Atención! Si marca la opción "Limpiar base de datos", se eliminarán todos los datos existentes antes de importar.</p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Subir Archivo</h5>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Archivo Excel (.xlsx)</label>
                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="limpiar_db" name="limpiar_db">
                        <label class="form-check-label" for="limpiar_db">Limpiar base de datos antes de importar</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Importar Datos
                    </button>
                    
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Panel
                    </a>
                </form>
            </div>
        </div>
        
        <?php if (!empty($mensaje)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Resultado de la Importación</h5>
            </div>
            <div class="card-body">
                <?php echo $mensaje; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
