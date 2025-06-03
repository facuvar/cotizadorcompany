<?php
// Script para obtener opciones según el producto y plazo seleccionados
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Establecer cabeceras para JSON
header('Content-Type: application/json');

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener parámetros
    $producto_id = isset($_GET['producto_id']) ? intval($_GET['producto_id']) : 0;
    $plazo_nombre = isset($_GET['plazo_nombre']) ? $_GET['plazo_nombre'] : '';
    
    // Validar parámetros
    if ($producto_id <= 0 || empty($plazo_nombre)) {
        throw new Exception("Parámetros inválidos");
    }
    
    // Consultar opciones para el producto seleccionado con precios según el plazo
    // Buscamos opciones relacionadas con el producto a través de la tabla producto_opciones
    $query = "
        SELECT o.id, o.nombre, o.descripcion, o.precio as precio_base, op.precio
        FROM opciones o
        LEFT JOIN opcion_precios op ON op.opcion_id = o.id AND op.plazo_entrega = ?
        INNER JOIN producto_opciones po ON o.id = po.opcion_id
        WHERE po.producto_id = ?
        ORDER BY o.orden ASC, o.id ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $plazo_nombre, $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $opciones = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Si no hay precio específico para el plazo, usar el precio base
            if ($row['precio'] === null) {
                $row['precio'] = $row['precio_base'];
            }
            $opciones[] = $row;
        }
    }
    
    // Devolver opciones en formato JSON
    echo json_encode($opciones);
    
} catch (Exception $e) {
    // Devolver error en formato JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>
