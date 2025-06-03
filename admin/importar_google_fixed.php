<?php
session_start();
require_once '../sistema/config.php';
require_once '../sistema/includes/db.php';

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
    
    return "<div class='alert $class'>$mensaje</div>";
}

// Función para obtener el ID de la hoja desde la URL
function getSheetId($url) {
    $pattern = '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return false;
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

// Función para obtener los nombres de las hojas de un archivo de Google Sheets
function getSheetNames($sheetId) {
    // Intentaremos obtener los nombres de las hojas desde la API pública
    // Esto es una aproximación, ya que la API pública no proporciona directamente esta información
    
    // Nombres de hojas comunes en el sistema
    return [
        'ASCENSORES' => 0,  // Primera hoja (gid=0)
        'ADICIONALES' => 1,  // Segunda hoja (gid=1)
        'DESCUENTOS' => 2    // Tercera hoja (gid=2)
    ];
}

// Función para obtener datos de una hoja específica de Google Sheets
function getSheetData($sheetId, $gid = 0) {
    // Construir URL para exportar como CSV con el gid específico
    $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
    
    // Obtener datos
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP'
            ]
        ]
    ]);
    
    $data = @file_get_contents($url, false, $context);
    
    if ($data === false) {
        return false;
    }
    
    // Convertir CSV a array
    $rows = array_map('str_getcsv', explode("\n", $data));
    
    return $rows;
}

// Procesar la importación
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sheet_url'])) {
    $sheetUrl = $_POST['sheet_url'];
    $sheetId = getSheetId($sheetUrl);
    
    if ($sheetId) {
        // Limpiar la base de datos si se solicita
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
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
        
        // Obtener nombres de las hojas
        $sheets = getSheetNames($sheetId);
        $mensaje .= mostrarMensaje("Procesando archivo de Google Sheets...", "info");
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Procesar cada hoja
            foreach ($sheets as $sheetName => $gid) {
                $mensaje .= mostrarMensaje("Procesando hoja: $sheetName", "info");
                
                // Obtener datos de la hoja
                $data = getSheetData($sheetId, $gid);
                
                if (!$data || count($data) <= 1) {
                    $mensaje .= mostrarMensaje("No se encontraron datos en la hoja $sheetName o la hoja está vacía", "warning");
                    continue;
                }
                
                // Procesar datos
                $currentProduct = null;
                $productoId = null;
                $headers = [];
                $plazos = [];
                
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
                
                foreach ($data as $index => $row) {
                    // Saltar filas vacías
                    if (empty($row[0])) continue;
                    
                    // La primera fila contiene los encabezados
                    if ($index === 0) {
                        $headers = $row;
                        
                        // Identificar columnas de plazos
                        foreach ($headers as $colIndex => $header) {
                            if (strpos($header, 'día') !== false || strpos($header, 'dias') !== false) {
                                $plazos[$colIndex] = $header;
                            }
                        }
                        
                        $mensaje .= mostrarMensaje("Plazos de entrega detectados: " . implode(", ", $plazos), "info");
                        continue;
                    }
                    
                    // Detectar producto
                    $firstCell = trim($row[0]);
                    
                    if (!empty($firstCell) && (
                        strpos(strtoupper($firstCell), 'EQUIPO') !== false || 
                        strpos(strtoupper($firstCell), 'ESTRUCTURA') !== false ||
                        strpos(strtoupper($firstCell), 'HIDRAULIC') !== false ||
                        strpos(strtoupper($firstCell), 'MONTACARGA') !== false ||
                        strpos(strtoupper($firstCell), 'SALVAESCALERA') !== false ||
                        strpos(strtoupper($firstCell), 'ESCALERA') !== false ||
                        strpos(strtoupper($firstCell), 'MONTAPLATO') !== false ||
                        strpos(strtoupper($firstCell), 'GIRACOCHE') !== false ||
                        strpos(strtoupper($firstCell), 'ADICIONAL') !== false ||
                        strpos(strtoupper($firstCell), 'PUERTA') !== false ||
                        strpos(strtoupper($firstCell), 'SISTEMA') !== false
                    )) {
                        $currentProduct = $firstCell;
                        
                        // Verificar si el producto ya existe
                        $query = "SELECT id FROM xls_productos WHERE nombre = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("s", $currentProduct);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 0) {
                            // Crear el producto
                            $query = "INSERT INTO xls_productos (nombre) VALUES (?)";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s", $currentProduct);
                            $stmt->execute();
                            $productoId = $conn->insert_id;
                            $mensaje .= mostrarMensaje("Producto creado: $currentProduct (ID: $productoId)", "success");
                        } else {
                            $row = $result->fetch_assoc();
                            $productoId = $row['id'];
                            $mensaje .= mostrarMensaje("Producto encontrado: $currentProduct (ID: $productoId)", "info");
                        }
                        
                        continue;
                    }
                    
                    // Si tenemos un producto actual y esta fila tiene datos en la segunda columna, es una opción
                    if ($productoId && !empty($row[1])) {
                        $opcionNombre = trim($row[1]);
                        
                        // Crear la opción
                        $query = "INSERT INTO xls_opciones (producto_id, nombre) VALUES (?, ?)";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $productoId, $opcionNombre);
                        $stmt->execute();
                        $opcionId = $conn->insert_id;
                        
                        // Guardar precios por plazo
                        foreach ($plazos as $colIndex => $plazo) {
                            if (isset($row[$colIndex]) && (
                                is_numeric(str_replace([',', '.', '$', ' '], '', $row[$colIndex])) || 
                                substr(trim($row[$colIndex]), 0, 1) === '='
                            )) {
                                // Limpiar y convertir el precio
                                $precioStr = $row[$colIndex];
                                
                                // Verificar si es una fórmula (comienza con =)
                                if (substr(trim($precioStr), 0, 1) === '=') {
                                    // Extraer los números de la fórmula y calcular
                                    preg_match_all('/\d+/', $precioStr, $matches);
                                    $numeros = $matches[0];
                                    
                                    if (count($numeros) >= 2) {
                                        // Sumar los números (asumiendo que la fórmula es una suma)
                                        $precio = array_sum($numeros);
                                    } else {
                                        // Si no podemos extraer al menos dos números, usar 0
                                        $precio = 0;
                                    }
                                } else {
                                    // No es una fórmula, procesar normalmente
                                    $precio = str_replace([',', '.', '$', ' '], '', $precioStr);
                                    $precio = floatval($precio) / 100; // Convertir a formato decimal
                                }
                                
                                // Obtener el ID del plazo
                                $plazoId = obtenerPlazoId($conn, $plazo);
                                
                                // Guardar el precio
                                $query = "INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("iid", $opcionId, $plazoId, $precio);
                                $stmt->execute();
                                
                                $mensaje .= mostrarMensaje("Precio guardado para opción '$opcionNombre', plazo ID '$plazoId': $precio", "success");
                            }
                        }
                    }
                }
                
                $mensaje .= mostrarMensaje("Importación de la hoja $sheetName completada con éxito", "success");
            }
            
            // Procesar adicionales para productos hidráulicos
            $mensaje .= mostrarMensaje("Procesando adicionales para productos hidráulicos...", "info");
            
            // Buscar todos los productos hidráulicos
            $query = "SELECT id, nombre FROM xls_productos WHERE nombre LIKE '%HIDRAULIC%'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $resultHidraulicos = $stmt->get_result();
            
            $productosHidraulicos = [];
            while ($row = $resultHidraulicos->fetch_assoc()) {
                $productosHidraulicos[] = $row;
            }
            
            if (count($productosHidraulicos) > 0) {
                $mensaje .= mostrarMensaje("Se encontraron " . count($productosHidraulicos) . " productos hidráulicos", "info");
                
                // Procesar cada adicional hidráulico
                foreach ($adicionalesHidraulicos as $adicionalName) {
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
                        $descripcion = $adicionalName; // Usar el nombre como descripción por defecto
                        $stmt->bind_param("ss", $adicionalName, $descripcion);
                        $stmt->execute();
                        $adicionalId = $conn->insert_id;
                        $mensaje .= mostrarMensaje("Adicional hidráulico creado: $adicionalName (ID: $adicionalId)", "success");
                    } else {
                        $rowData = $result->fetch_assoc();
                        $adicionalId = $rowData['id'];
                        $mensaje .= mostrarMensaje("Adicional hidráulico encontrado: $adicionalName (ID: $adicionalId)", "info");
                    }
                    
                    // Asociar el adicional con todos los productos hidráulicos
                    foreach ($productosHidraulicos as $producto) {
                        $productoId = $producto['id'];
                        $productoNombre = $producto['nombre'];
                        
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
                            
                            $mensaje .= mostrarMensaje("Adicional '$adicionalName' asociado al producto hidráulico ID $productoId ($productoNombre)", "success");
                        }
                    }
                }
            } else {
                $mensaje .= mostrarMensaje("No se encontraron productos hidráulicos en la importación", "warning");
            }
            
            // Confirmar cambios
            $conn->commit();
            $mensaje .= mostrarMensaje("Datos importados correctamente desde Google Sheets", "success");
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            $mensaje .= mostrarMensaje("Error al importar datos: " . $e->getMessage(), "error");
        }
    } else {
        $mensaje .= mostrarMensaje("URL de Google Sheets inválida", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar desde Google Sheets - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
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
                        <p class="small">Bienvenido, <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Administrador'; ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="productos.php">
                                <i class="bi bi-box"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="opciones.php">
                                <i class="bi bi-list-check"></i> Opciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="precios.php">
                                <i class="bi bi-cash"></i> Precios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="importar.php" class="active">
                                <i class="bi bi-upload"></i> Importar Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="usuarios.php">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
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
                        <h1 class="h2">Importar desde Google Sheets</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="importar.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                    
                    <?php echo $mensaje; ?>
                    
                    <!-- Import form -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="import-card">
                                <h4><i class="bi bi-cloud text-primary me-2"></i> Importar desde Google Sheets</h4>
                                <p>Importa datos desde una hoja de cálculo pública de Google Sheets.</p>
                                
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="sheet_url" class="form-label">URL de Google Sheets</label>
                                        <input type="url" class="form-control" id="sheet_url" name="sheet_url" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                                        <div class="form-text">La hoja debe estar configurada como "Cualquiera con el enlace puede ver"</div>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="limpiar_antes" name="limpiar_antes" value="si" checked>
                                        <label class="form-check-label" for="limpiar_antes">
                                            Limpiar base de datos antes de importar
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cloud-download me-2"></i> Importar desde Google Sheets
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="import-card">
                                <h4><i class="bi bi-info-circle text-info me-2"></i> Instrucciones</h4>
                                
                                <div class="alert alert-info">
                                    <h5>Cómo compartir tu hoja de Google</h5>
                                    <ol>
                                        <li>Abre tu hoja de Google Sheets</li>
                                        <li>Haz clic en "Compartir" en la esquina superior derecha</li>
                                        <li>Haz clic en "Cualquiera con el enlace"</li>
                                        <li>Asegúrate de que el permiso sea "Lector"</li>
                                        <li>Copia el enlace y pégalo en el formulario</li>
                                    </ol>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5>Formato de la hoja</h5>
                                    <p>La hoja debe tener el mismo formato que el archivo Excel de ejemplo.</p>
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
