<?php
// Script para guardar presupuestos en la base de datos
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Configurar cabecera para respuesta JSON
header('Content-Type: application/json');

// Función para registrar información de depuración
function logDebug($message) {
    $logFile = __DIR__ . '/presupuestos_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }
    
    // Obtener datos del formulario
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception("Datos no válidos");
    }
    
    logDebug("Datos recibidos: " . json_encode($data));
    
    // Validar campos requeridos
    $requiredFields = ['nombre', 'email', 'telefono', 'producto', 'opcion', 'plazo', 'total'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si la tabla existe y tiene la estructura correcta
    $tableExists = $conn->query("SHOW TABLES LIKE 'presupuestos'")->num_rows > 0;
    
    if (!$tableExists) {
        logDebug("La tabla presupuestos no existe. Creando tabla...");
        
        // Crear la tabla de presupuestos
        $createTableSQL = "CREATE TABLE presupuestos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_cliente VARCHAR(100) NOT NULL,
            email_cliente VARCHAR(100) NOT NULL,
            telefono_cliente VARCHAR(20) NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total DECIMAL(10,2) NOT NULL,
            estado ENUM('pendiente','enviado','aprobado','rechazado') DEFAULT 'pendiente',
            codigo VARCHAR(20),
            ip_cliente VARCHAR(45),
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            producto_id INT NOT NULL,
            producto_nombre VARCHAR(100) NOT NULL,
            opcion_id INT NOT NULL,
            opcion_nombre VARCHAR(100) NOT NULL,
            plazo_id INT NOT NULL,
            plazo_nombre VARCHAR(50) NOT NULL,
            forma_pago VARCHAR(100) NOT NULL,
            descuento DECIMAL(10,2) NOT NULL,
            adicionales TEXT,
            subtotal DECIMAL(10,2) NOT NULL
        )";
        
        if (!$conn->query($createTableSQL)) {
            throw new Exception("Error al crear la tabla de presupuestos: " . $conn->error);
        }
    }
    
    // Preparar los datos para inserción
    $nombre = $data['nombre'];
    $email = $data['email'];
    $telefono = $data['telefono'];
    $productoId = $data['producto']['id'];
    $productoNombre = $data['producto']['nombre'];
    $opcionId = $data['opcion']['id'];
    $opcionNombre = $data['opcion']['nombre'];
    $plazoId = $data['plazo']['id'];
    $plazoNombre = $data['plazo']['nombre'];
    $formaPago = $data['formaPago']['nombre'];
    $descuento = $data['formaPago']['descuentoMonto'];
    $adicionales = json_encode($data['adicionales']);
    $subtotal = $data['subtotal'];
    $total = $data['total'];
    $codigo = 'PR-' . date('Ymd') . '-' . rand(1000, 9999);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Insertar en la base de datos
    $sql = "INSERT INTO presupuestos (nombre_cliente, email_cliente, telefono_cliente, total, estado, codigo, ip_cliente, 
                                     nombre, email, telefono, producto_id, producto_nombre, opcion_id, opcion_nombre, 
                                     plazo_id, plazo_nombre, forma_pago, descuento, adicionales, subtotal) 
            VALUES (?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsssssisisssssds", $nombre, $email, $telefono, $total, $codigo, $ip, 
                                           $nombre, $email, $telefono, $productoId, $productoNombre, $opcionId, $opcionNombre, 
                                           $plazoId, $plazoNombre, $formaPago, $descuento, $adicionales, $subtotal);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar el presupuesto: " . $stmt->error);
    }
    
    $presupuestoId = $conn->insert_id;
    logDebug("Presupuesto guardado con ID: $presupuestoId");
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Presupuesto guardado correctamente',
        'presupuesto_id' => $presupuestoId
    ]);
    
} catch (Exception $e) {
    logDebug("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
