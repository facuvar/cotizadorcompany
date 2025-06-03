<?php
require_once '../sistema/config.php';
require_once '../sistema/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Variables para el resultado
$mensaje = "";
$importacionExitosa = false;
$logMensajes = []; // Para almacenar mensajes detallados
$resumenImportacion = [
    'productos' => 0,
    'opciones' => 0,
    'precios' => 0
];

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $class = 'alert-info';
    if ($tipo == 'success') $class = 'alert-success';
    if ($tipo == 'error') $class = 'alert-danger';
    if ($tipo == 'warning') $class = 'alert-warning';
    
    return "<div class='alert $class'>$mensaje</div>";
}

// Función para registrar mensajes en el log sin mostrarlos
function registrarLog($mensaje) {
    global $logMensajes;
    $logMensajes[] = $mensaje;
}

// Función para limpiar y convertir valores monetarios
function limpiarValorMonetario($valor) {
    // Si es una cadena, limpiar caracteres no numéricos
    if (is_string($valor)) {
        return (float) preg_replace('/[^0-9.]/', '', $valor);
    }
    
    // Si es un número, devolverlo tal cual
    return (float) $valor;
}

// Función para limpiar la base de datos
function limpiarBaseDatos($conn) {
    // Desactivar restricciones de clave foránea
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Truncar tablas
    $conn->query("TRUNCATE TABLE xls_precios");
    $conn->query("TRUNCATE TABLE xls_opciones");
    $conn->query("TRUNCATE TABLE xls_productos");
    $conn->query("TRUNCATE TABLE xls_plazos");
    
    // Reactivar restricciones de clave foránea
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    registrarLog("Base de datos limpiada correctamente");
}

// Función para obtener el ID de un plazo
function obtenerPlazoId($conn, $nombrePlazo) {
    // Verificar si el plazo ya existe
    $query = "SELECT id FROM xls_plazos WHERE nombre = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombrePlazo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    
    // Si no existe, crear el plazo
    $query = "INSERT INTO xls_plazos (nombre) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombrePlazo);
    $stmt->execute();
    
    return $conn->insert_id;
}

// Procesar el formulario
if (isset($_POST['importar'])) {
    $limpiarDB = isset($_POST['limpiar_db']) && $_POST['limpiar_db'] == '1';
    
    // Verificar si se ha subido un archivo
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $tempFile = $_FILES['excel_file']['tmp_name'];
        
        // Limpiar la base de datos si se solicitó
        if ($limpiarDB) {
            registrarLog("Limpiando base de datos antes de importar...");
            limpiarBaseDatos($conn);
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
                
                registrarLog("Procesando hoja: $sheetName");
                
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
                    registrarLog("No se encontraron columnas de precios en la hoja");
                    continue;
                }
                
                registrarLog("Plazos de entrega detectados: " . implode(", ", $plazos));
                
                // Iniciar transacción
                $conn->begin_transaction();
                
                try {
                    // Procesar cada fila
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $productName = trim($worksheet->getCell('A' . $row)->getValue());
                        $opcionName = trim($worksheet->getCell('B' . $row)->getValue());
                        
                        // Si hay un nombre de producto, crear o actualizar el producto actual
                        if (!empty($productName)) {
                            // Verificar si el producto ya existe
                            $query = "SELECT id FROM xls_productos WHERE nombre = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s", $productName);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $row_product = $result->fetch_assoc();
                                $productoId = $row_product['id'];
                            } else {
                                // Crear nuevo producto
                                $query = "INSERT INTO xls_productos (nombre) VALUES (?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("s", $productName);
                                $stmt->execute();
                                $productoId = $conn->insert_id;
                                $resumenImportacion['productos']++;
                                registrarLog("Producto creado: $productName (ID: $productoId)");
                            }
                            
                            $currentProduct = $productName;
                        }
                        
                        // Si hay un nombre de opción y un producto actual, crear la opción
                        if (!empty($opcionName) && !empty($currentProduct) && !empty($productoId)) {
                            // Verificar si la opción ya existe para este producto
                            $query = "SELECT id FROM xls_opciones WHERE producto_id = ? AND nombre = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("is", $productoId, $opcionName);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $row_opcion = $result->fetch_assoc();
                                $opcionId = $row_opcion['id'];
                            } else {
                                // Crear nueva opción
                                $query = "INSERT INTO xls_opciones (producto_id, nombre) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("is", $productoId, $opcionName);
                                $stmt->execute();
                                $opcionId = $conn->insert_id;
                                $resumenImportacion['opciones']++;
                                registrarLog("Opción creada: $opcionName para producto $currentProduct");
                            }
                            
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
                                $resumenImportacion['precios']++;
                                
                                registrarLog("Precio guardado para opción '$opcionName', plazo ID '$plazoId': $precio");
                            }
                        }
                    }
                    
                    // Confirmar transacción
                    $conn->commit();
                    registrarLog("Importación de la hoja $sheetName completada con éxito");
                    
                } catch (Exception $e) {
                    // Revertir cambios en caso de error
                    $conn->rollback();
                    registrarLog("Error al procesar la hoja: " . $e->getMessage());
                    $mensaje .= mostrarMensaje("Error al procesar la hoja $sheetName: " . $e->getMessage(), "error");
                }
            }
            
            // Si no hay errores, mostrar mensaje de éxito
            if (empty($mensaje)) {
                $importacionExitosa = true;
                $mensaje = mostrarMensaje("
                    <h4>¡Importación completada con éxito!</h4>
                    <p>Se han importado:</p>
                    <ul>
                        <li><strong>{$resumenImportacion['productos']}</strong> productos</li>
                        <li><strong>{$resumenImportacion['opciones']}</strong> opciones</li>
                        <li><strong>{$resumenImportacion['precios']}</strong> precios</li>
                    </ul>
                ", "success");
            }
            
        } catch (Exception $e) {
            $mensaje .= mostrarMensaje("Error al importar el archivo: " . $e->getMessage(), "error");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            min-height: 100vh;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
        }
        .nav-link:hover, .nav-link.active {
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            margin-bottom: 20px;
        }
        .log-container {
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.85rem;
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
        }
        .log-entry {
            margin-bottom: 3px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="importar.php">
                                <i class="fas fa-file-import me-2"></i>
                                Importar Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="presupuestos.php">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Presupuestos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Importar desde Excel con Fórmulas</h1>
                </div>
                
                <?php if (!$importacionExitosa): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Importar archivo Excel</h5>
                        <p class="card-text">
                            Seleccione un archivo Excel (.xlsx) que contenga los datos de productos, opciones y precios.
                            El archivo debe tener la siguiente estructura:
                        </p>
                        <ul>
                            <li>Columna A: Nombre del producto</li>
                            <li>Columna B: Nombre de la opción</li>
                            <li>Columnas C en adelante: Precios para diferentes plazos de entrega</li>
                        </ul>
                        
                        <form method="post" enctype="multipart/form-data" class="mt-4">
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Archivo Excel</label>
                                <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="limpiar_db" name="limpiar_db" value="1" checked>
                                <label class="form-check-label" for="limpiar_db">Limpiar base de datos antes de importar</label>
                            </div>
                            
                            <button type="submit" name="importar" class="btn btn-primary">
                                <i class="fas fa-file-import me-2"></i>
                                Importar desde Excel
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($mensaje)): ?>
                <div class="mt-4">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($importacionExitosa && !empty($logMensajes)): ?>
                <div class="mt-4">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#logDetails">
                        <i class="fas fa-terminal me-1"></i> Mostrar detalles técnicos
                    </button>
                    
                    <div class="collapse mt-2" id="logDetails">
                        <div class="card">
                            <div class="card-header">
                                Detalles de la importación
                            </div>
                            <div class="card-body">
                                <div class="log-container">
                                    <?php foreach ($logMensajes as $log): ?>
                                    <div class="log-entry"><?php echo htmlspecialchars($log); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($importacionExitosa): ?>
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Volver al Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
