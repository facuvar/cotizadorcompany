<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Redirigir al nuevo importador mejorado
header('Location: importar_excel_mejorado.php');
exit;

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $class = 'alert-info';
    if ($tipo == 'success') $class = 'alert-success';
    if ($tipo == 'error') $class = 'alert-danger';
    if ($tipo == 'warning') $class = 'alert-warning';
    
    return "<div class='alert $class'>$mensaje</div>";
}

// Función para registrar mensajes en un log sin mostrarlos
function registrarLog($mensaje) {
    global $logMensajes;
    $logMensajes[] = $mensaje;
    return "";
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

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Procesar la importación
$mensaje = '';
$importacionExitosa = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha subido un archivo
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        
        // Verificar extensión
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExt !== 'xlsx') {
            $mensaje = mostrarMensaje("El archivo debe ser un archivo Excel (.xlsx)", "error");
        } else {
            // Limpiar la base de datos si se solicita
            if (isset($_POST['limpiar_antes']) && $_POST['limpiar_antes'] == 'si') {
                $mensaje .= mostrarMensaje("Limpiando base de datos antes de importar...", "info");
                
                // Eliminar datos existentes
                $conn->query("DELETE FROM xls_precios");
                $conn->query("DELETE FROM xls_opciones");
                $conn->query("DELETE FROM xls_adicionales_precios");
                $conn->query("DELETE FROM xls_productos_adicionales");
                $conn->query("DELETE FROM xls_adicionales");
                $conn->query("DELETE FROM xls_productos");
                
                $mensaje .= mostrarMensaje("Base de datos limpiada correctamente", "success");
            }
            
            try {
                // Cargar el archivo Excel
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(false); // Importante: leer fórmulas
                
                // Establecer tiempo máximo de ejecución
                set_time_limit(600); // 10 minutos
                
                $spreadsheet = $reader->load($tempFile);
                
                // Obtener las hojas disponibles
                $sheetNames = $spreadsheet->getSheetNames();
                
                // Procesar cada hoja
                foreach ($sheetNames as $sheetName) {
                    $worksheet = $spreadsheet->getSheetByName($sheetName);
                    
                    $mensaje .= mostrarMensaje("Procesando hoja: $sheetName", "info");
                    
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
                        $mensaje .= mostrarMensaje("No se encontraron columnas de precios en la hoja", "error");
                        continue;
                    }
                    
                    $mensaje .= mostrarMensaje("Plazos de entrega detectados: " . implode(", ", $plazos), "info");
                    
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
                                        $mensaje .= mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
                                    } else {
                                        $row = $result->fetch_assoc();
                                        $productoId = $row['id'];
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
                                    // Obtener el valor calculado (no la fórmula)
                                    $precioCalculado = $worksheet->getCell($col . $row)->getCalculatedValue();
                                    
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
                        }
                        
                        // Confirmar transacción
                        $conn->commit();
                        $mensaje .= mostrarMensaje("Importación de la hoja $sheetName completada con éxito", "success");
                        
                    } catch (Exception $e) {
                        // Revertir cambios en caso de error
                        $conn->rollback();
                        $mensaje .= mostrarMensaje("Error al procesar la hoja: " . $e->getMessage(), "error");
                    }
                }
                
                $mensaje .= mostrarMensaje("Importación finalizada", "success");
                $importacionExitosa = true;
                
            } catch (Exception $e) {
                $mensaje .= mostrarMensaje("Error al importar el archivo: " . $e->getMessage(), "error");
            }
        }
    } else if (isset($_FILES['excel_file'])) {
        $mensaje .= mostrarMensaje("Error al subir el archivo: " . $_FILES['excel_file']['error'], "error");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar desde Excel - Panel de Administración</title>
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
                        <h1 class="h2">Importar desde Excel con Fórmulas</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="importar.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="import-results">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$importacionExitosa): ?>
                    <!-- Import form -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="import-card">
                                <h4><i class="bi bi-file-earmark-excel text-success me-2"></i> Importar desde Excel con Fórmulas</h4>
                                <p>Sube un archivo Excel (.xlsx) con los datos de productos, opciones y precios. El sistema calculará automáticamente las fórmulas.</p>
                                
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
                        
                        <div class="col-md-4">
                            <div class="import-card">
                                <h4><i class="bi bi-info-circle text-info me-2"></i> Instrucciones</h4>
                                
                                <div class="alert alert-info">
                                    <h5>Formato del archivo Excel</h5>
                                    <p>El archivo Excel debe tener el siguiente formato:</p>
                                    <ul>
                                        <li>Columna A: Nombre del producto</li>
                                        <li>Columna B: Nombre de la opción</li>
                                        <li>Columnas C en adelante: Precios por plazo</li>
                                    </ul>
                                    <p>El sistema calculará automáticamente las fórmulas en el Excel.</p>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5>Importante</h5>
                                    <p>La importación reemplazará todos los datos existentes si selecciona "Limpiar base de datos".</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="import-card">
                                <h4><i class="bi bi-check-circle-fill text-success me-2"></i> Importación Completada</h4>
                                <p>La importación se ha completado correctamente. Ahora puede ver los productos y opciones en el cotizador.</p>
                                
                                <div class="mt-4">
                                    <a href="../cotizador_xls_fixed.php" target="_blank" class="btn btn-primary me-2">
                                        <i class="bi bi-calculator me-2"></i> Ver Cotizador
                                    </a>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="bi bi-speedometer2 me-2"></i> Ir al Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
