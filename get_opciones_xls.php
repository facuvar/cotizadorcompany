<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

header('Content-Type: application/json');

try {
    // Verificar que se haya enviado el ID del producto
    if (!isset($_GET['producto_id']) || empty($_GET['producto_id'])) {
        throw new Exception("ID de producto no proporcionado");
    }
    
    $productoId = intval($_GET['producto_id']);
    
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener opciones para el producto con sus precios para el plazo seleccionado
    $plazoId = isset($_GET['plazo_id']) ? intval($_GET['plazo_id']) : 2; // 160-180 días por defecto
    
    $query = "SELECT o.id, o.nombre, o.descripcion, p.precio 
              FROM xls_opciones o
              LEFT JOIN xls_precios p ON o.id = p.opcion_id AND p.plazo_id = ?
              WHERE o.producto_id = ?
              ORDER BY o.orden ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $plazoId, $productoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $opciones = [];
    
    if ($result && $result->num_rows > 0) {
        while ($opcion = $result->fetch_assoc()) {
            $opciones[] = $opcion;
        }
    }
    
    echo json_encode($opciones);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
