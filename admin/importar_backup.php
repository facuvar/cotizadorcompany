<?php
session_start();
require_once '../sistema/includes/db.php';
require_once '../sistema/includes/funciones.php';
require_once 'importar_fix.php'; // Incluir funciones de corrección

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $class = 'alert-info';
    if ($tipo == 'success') $class = 'alert-success';
    if ($tipo == 'error') $class = 'alert-danger';
    if ($tipo == 'warning') $class = 'alert-warning';
    
    echo "<div class='alert $class'>$mensaje</div>";
}

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

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

// Procesar la importación de Excel
$mensaje = '';
$archivoSubido = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    // Verificar que se haya subido un archivo
    if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        // Verificar que sea un archivo Excel
        $fileType = mime_content_type($_FILES['excel_file']['tmp_name']);
        $allowedTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream'
        ];
        
        if (in_array($fileType, $allowedTypes) || pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION) === 'xlsx') {
            $tempFile = $_FILES['excel_file']['tmp_name'];
            $archivoSubido = true;
            
            // Limpiar la base de datos si se solicita
            if (isset($_POST['limpiar_antes']) && $_POST['limpiar_antes'] == 'si') {
                mostrarMensaje("Limpiando base de datos antes de importar...", "info");
                
                // Eliminar datos existentes
                $conn->query("DELETE FROM xls_precios");
                $conn->query("DELETE FROM xls_opciones");
                $conn->query("DELETE FROM xls_adicionales_precios");
                $conn->query("DELETE FROM xls_productos_adicionales");
                $conn->query("DELETE FROM xls_adicionales");
                $conn->query("DELETE FROM xls_productos");
                
                mostrarMensaje("Base de datos limpiada correctamente", "success");
            }
            
            // Importar directamente desde el panel de administración
            require_once '../vendor/autoload.php';
            // Importar las clases necesarias
            require_once '../vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/IOFactory.php';
            
            try {
                // Cargar el archivo Excel
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(false); // Importante: leer fórmulas
                
                // Establecer tiempo máximo de ejecución
                set_time_limit(600); // 10 minutos
                
                $spreadsheet = $reader->load($tempFile);
                
                // Obtener las hojas disponibles
                $sheetNames = $spreadsheet->getSheetNames();
                
                // Procesar cada hoja
                foreach ($sheetNames as $sheetName) {
                    mostrarMensaje("Procesando hoja: $sheetName", "info");
                        // Obtener la hoja de trabajo actual
                        $worksheet = $spreadsheet->getSheetByName($sheetName);
                        
                        if (!$worksheet) {
                            mostrarMensaje("La hoja '$sheetName' no existe en el archivo", "error");
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
                    try {
                        for ($col = 'C'; $col <= $highestColumn; $col++) {
                            // Verificar que la coordenada de celda sea válida
                            $cellCoordinate = $col . '1';
                            if (!$worksheet->cellExists($cellCoordinate)) {
                                continue; // Saltar esta columna si la celda no existe
                            }
                            
                            $header = $worksheet->getCell($cellCoordinate)->getValue();
                            
                            // Verificar que el valor del encabezado no sea un array
                            if (is_array($header)) {
                                error_log("Valor de encabezado es un array en $cellCoordinate: " . print_r($header, true));
                                continue;
                            }
                            
                            if (is_string($header) && strpos(strtolower($header), 'precio') !== false) {
                                $plazos[$col] = trim(str_replace('precio', '', strtolower($header)));
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error al procesar encabezados: " . $e->getMessage());
                        mostrarMensaje("Error al procesar encabezados: " . $e->getMessage(), "error");
                    }
                    
                    if (empty($plazos)) {
                        mostrarMensaje("No se encontraron columnas de precios en la hoja", "error");
                        continue;
                    }
                    
                    mostrarMensaje("Plazos de entrega detectados: " . implode(", ", $plazos), "info");
                    
                    // Iniciar transacción
                    $conn->begin_transaction();
                    
                    try {
                        // Procesar cada fila
                        $lastProductName = '';
                        
                        for ($row = 2; $row <= $highestRow; $row++) {
                            try {
                                // Verificar que la celda A exista
                                $cellCoordinate = 'A' . $row;
                                if (!$worksheet->cellExists($cellCoordinate)) {
                                    continue; // Saltar esta fila si la celda no existe
                                }
                                
                                $firstCell = trim($worksheet->getCell($cellCoordinate)->getValue());
                                
                                // Si el valor es un array, convertirlo a string o saltar
                                if (is_array($firstCell)) {
                                    error_log("Valor de celda es un array en $cellCoordinate: " . print_r($firstCell, true));
                                    continue;
                                }
                                
                                // Detectar producto
                                if (!empty($firstCell) && strpos(strtoupper($firstCell), 'EQUIPO') !== false) {
                                    $productName = trim($worksheet->getCell('A' . $row)->getValue());
                                    
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
                                                mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
                                            } else {
                                                $row = $result->fetch_assoc();
                                                $productoId = $row['id'];
                                                mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info");
                                            }
                                            
                                            $lastProductName = $currentProduct;
                                        }
                                    }
                                }
                                
                                // Si hay una opción en columna B y tenemos un producto actual
                                if (!empty($worksheet->getCell('B' . $row)->getValue()) && !empty($productoId)) {
                                    // Crear la opción
                                    $opcionName = trim($worksheet->getCell('B' . $row)->getValue());
                                    $query = "INSERT INTO xls_opciones (producto_id, nombre) VALUES (?, ?)";
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
                                        mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
                                    } else {
                                        $row = $result->fetch_assoc();
                                        $productoId = $row['id'];
                                        mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info");
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
                                
                                mostrarMensaje("Opción creada: $opcionName para producto $currentProduct", "success");
                                
                                // Procesar precios para cada plazo
                                foreach ($plazos as $col => $plazoNombre) {
                                    // Verificar que $col sea una cadena válida
                                    $columna = is_string($col) ? $col : (is_numeric($col) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1) : null);
                                    
                                    if ($columna === null) {
                                        error_log("Error: Columna no válida: " . print_r($col, true));
                                        continue; // Saltar esta iteración si la columna no es válida
                                    }
                                    
                                    // Obtener el valor calculado de forma segura usando nuestras funciones de corrección
                                    $cellCoordinate = $columna . $row;
                                    $precioCalculado = getCalculatedValueSafely($worksheet, $cellCoordinate);
                                    
                                    // Limpiar y convertir el valor de forma segura
                                    $precio = limpiarValorMonetarioSeguro($precioCalculado);
                                    
                                    // Obtener el ID del plazo
                                    $plazoId = obtenerPlazoId($conn, $plazoNombre);
                                    
                                    // Guardar el precio
                                    $query = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("iid", $opcionId, $plazoId, $precio);
                                    $stmt->execute();
                                    
                                    // Mostrar mensaje de éxito
                                    mostrarMensaje("Precio guardado para opción '$opcionName', plazo ID '$plazoId': $precio", "success");
                                }
                            }
                        }
                        
                        // Confirmar transacción
                        $conn->commit();
                        mostrarMensaje("Importación de la hoja $sheetName completada con éxito", "success");
                        
                    } catch (Exception $e) {
                        // Revertir cambios en caso de error
                        $conn->rollback();
                        mostrarMensaje("Error al procesar la hoja: " . $e->getMessage(), "error");
                    }
                }
                
                mostrarMensaje("Importación finalizada", "success");
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
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-item {
            margin-bottom: 5px;
        }
        .sidebar .nav-item i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
        }
        .import-card {
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>Panel de Administración</h5>
                        <p class="small">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrador'); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="importar.php" class="active">
                                <i class="bi bi-file-earmark-excel"></i> Importar Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="presupuestos.php">
                                <i class="bi bi-file-text"></i> Presupuestos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a href="dashboard.php?logout=1" class="text-danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Importar Datos</h1>
                    </div>
                    
                    <?php echo $mensaje; ?>
                    
                    <!-- Import from Excel -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="import-card">
                                <h4><i class="bi bi-file-earmark-excel text-success me-2"></i> Importar desde Excel</h4>
                                <p>Sube un archivo Excel (.xlsx) con los datos de productos, opciones y precios.</p>
                                
                                <div class="alert alert-success mb-4">
                                    <h4 class="alert-heading"><i class="bi bi-stars"></i> ¡IMPORTADOR VERIFICADO!</h4>
                                    <p>Este importador ha sido verificado y maneja correctamente los archivos Excel con fórmulas, manteniendo la estructura de productos y opciones.</p>
                                    <hr>
                                    <div class="d-grid gap-2 col-md-8 mx-auto">
                                        <a href="../importar_xls_formulas_v2.php" class="btn btn-success btn-lg">
                                            <i class="bi bi-file-earmark-excel me-2"></i>
                                            Usar Importador Verificado
                                        </a>
                                    </div>
                                </div>
                                <hr class="my-4">
                                
                                <form method="post" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="excel_file" class="form-label">Archivo Excel</label>
                                        <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx" required>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="limpiar_antes" name="limpiar_antes" value="si" checked>
                                        <label class="form-check-label" for="limpiar_antes">
                                            Limpiar base de datos antes de importar
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-upload me-2"></i> Importar desde Excel
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="import-card">
                                <h4><i class="bi bi-cloud text-primary me-2"></i> Importar desde Google Sheets</h4>
                                <p>Importa datos desde una hoja de cálculo pública de Google Sheets.</p>
                                
                                <form action="importar_google.php" method="post">
                                    <div class="mb-3">
                                        <label for="sheet_url" class="form-label">URL de Google Sheets</label>
                                        <input type="url" class="form-control" id="sheet_url" name="sheet_url" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                        <div class="form-text">La hoja debe estar configurada como "Cualquiera con el enlace puede ver"</div>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="limpiar_antes_google" name="limpiar_antes" value="si" checked>
                                        <label class="form-check-label" for="limpiar_antes_google">
                                            Limpiar base de datos antes de importar
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cloud-download me-2"></i> Importar desde Google Sheets
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="import-card">
                                <h4><i class="bi bi-info-circle text-info me-2"></i> Instrucciones</h4>
                                
                                <div class="alert alert-info">
                                    <h5>Formato del archivo Excel</h5>
                                    <p>El archivo Excel debe tener el siguiente formato:</p>
                                    <ul>
                                        <li>Una hoja para productos principales (ascensores)</li>
                                        <li>Una hoja para productos adicionales</li>
                                        <li>Cada producto debe tener sus opciones y precios por plazo de entrega</li>
                                    </ul>
                                    <p>Descarga la <a href="../xls/plantilla.xlsx" class="alert-link">plantilla de ejemplo</a> para ver el formato correcto.</p>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5>Importante</h5>
                                    <p>La importación reemplazará todos los datos existentes si selecciona "Limpiar base de datos".</p>
                                    <p>Asegúrese de tener una copia de seguridad de sus datos antes de importar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
