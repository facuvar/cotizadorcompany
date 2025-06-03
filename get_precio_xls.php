<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

header('Content-Type: application/json');

try {
    // Verificar que se haya enviado el ID de la opción y del plazo
    if (!isset($_GET['opcion_id']) || empty($_GET['opcion_id'])) {
        throw new Exception("ID de opción no proporcionado");
    }
    
    if (!isset($_GET['plazo_id']) || empty($_GET['plazo_id'])) {
        throw new Exception("ID de plazo no proporcionado");
    }
    
    $opcionId = intval($_GET['opcion_id']);
    $plazoId = intval($_GET['plazo_id']);
    
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener el nombre del plazo a partir del ID
    $queryPlazo = "SELECT nombre FROM plazos_entrega WHERE id = ?";
    $stmtPlazo = $conn->prepare($queryPlazo);
    $stmtPlazo->bind_param("i", $plazoId);
    $stmtPlazo->execute();
    $resultPlazo = $stmtPlazo->get_result();
    
    if (!$resultPlazo || $resultPlazo->num_rows == 0) {
        throw new Exception("Plazo de entrega no encontrado");
    }
    
    $plazoRow = $resultPlazo->fetch_assoc();
    $plazoNombre = $plazoRow['nombre'];
    
    // Obtener precio para la opción y plazo seleccionados
    $query = "SELECT precio FROM opcion_precios WHERE opcion_id = ? AND plazo_entrega = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $opcionId, $plazoNombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $precio = $result->fetch_assoc();
        echo json_encode($precio);
    } else {
        // Si no se encuentra el precio específico, obtener el precio base de la opción
        $query = "SELECT precio FROM opciones WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $opcionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $precioBase = $row['precio'];
            
            // Aplicar multiplicador según el plazo (si existe)
            $multiplicador = 1.0;
            
            // Calcular precio final
            $precio = $precioBase * $multiplicador;
            echo json_encode(['precio' => $precio]);
        } else {
            throw new Exception("No se encontró la opción o el plazo");
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
