<?php
/**
 * Versión de debug para el manejo AJAX de ordenamiento
 */

header('Content-Type: application/json');

// Log de debug
error_log("AJAX Debug - Inicio");
error_log("POST data: " . print_r($_POST, true));

// Cargar configuración
$configPath = __DIR__ . '/../sistema/config.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Archivo de configuración no encontrado', 'debug' => 'config not found']);
    exit;
}
require_once $configPath;

// Cargar DB
$dbPath = __DIR__ . '/../sistema/includes/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    echo json_encode(['success' => false, 'message' => 'Archivo de DB no encontrado', 'debug' => 'db not found']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

error_log("Action: $action, ID: $id");

if (!$action || !$id) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos', 'debug' => "action=$action, id=$id"]);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
        exit;
    }
    
    error_log("Conexión DB exitosa");
    
    switch ($action) {
        case 'move_categoria_up':
            error_log("Procesando move_categoria_up para ID: $id");
            
            // Obtener el orden actual
            $stmt = $conn->prepare("SELECT orden FROM categorias WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $categoria_actual = $result->fetch_assoc();
            
            error_log("Categoría actual: " . print_r($categoria_actual, true));
            
            if (!$categoria_actual) {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada', 'debug' => "ID $id no existe"]);
                exit;
            }
            
            $orden_actual = $categoria_actual['orden'] ?? 0;
            error_log("Orden actual: $orden_actual");
            
            // Buscar la categoría anterior
            $stmt = $conn->prepare("SELECT id, orden FROM categorias WHERE orden < ? ORDER BY orden DESC LIMIT 1");
            $stmt->bind_param("i", $orden_actual);
            $stmt->execute();
            $result = $stmt->get_result();
            $categoria_anterior = $result->fetch_assoc();
            
            error_log("Categoría anterior: " . print_r($categoria_anterior, true));
            
            if (!$categoria_anterior) {
                echo json_encode(['success' => false, 'message' => 'La categoría ya está en la primera posición', 'debug' => "No hay categoría con orden < $orden_actual"]);
                exit;
            }
            
            // Intercambiar órdenes
            $orden_anterior = $categoria_anterior['orden'];
            $id_anterior = $categoria_anterior['id'];
            
            error_log("Intercambiando: ID $id (orden $orden_actual) <-> ID $id_anterior (orden $orden_anterior)");
            
            $conn->begin_transaction();
            
            $stmt1 = $conn->prepare("UPDATE categorias SET orden = ? WHERE id = ?");
            $stmt1->bind_param("ii", $orden_anterior, $id);
            $result1 = $stmt1->execute();
            
            $stmt2 = $conn->prepare("UPDATE categorias SET orden = ? WHERE id = ?");
            $stmt2->bind_param("ii", $orden_actual, $id_anterior);
            $result2 = $stmt2->execute();
            
            if ($result1 && $result2) {
                $conn->commit();
                error_log("Transacción exitosa");
                echo json_encode(['success' => true, 'message' => 'Categoría movida hacia arriba', 'debug' => "Intercambio exitoso"]);
            } else {
                $conn->rollback();
                error_log("Error en transacción");
                echo json_encode(['success' => false, 'message' => 'Error en la transacción', 'debug' => "result1=$result1, result2=$result2"]);
            }
            break;
            
        case 'move_categoria_down':
            // Similar lógica para down...
            echo json_encode(['success' => true, 'message' => 'Move down no implementado en debug']);
            break;
            
        case 'move_opcion_up':
            error_log("Procesando move_opcion_up para ID: $id");
            
            // Obtener la opción actual
            $stmt = $conn->prepare("SELECT categoria_id, orden FROM opciones WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $opcion_actual = $result->fetch_assoc();
            
            error_log("Opción actual: " . print_r($opcion_actual, true));
            
            if (!$opcion_actual) {
                echo json_encode(['success' => false, 'message' => 'Opción no encontrada', 'debug' => "ID $id no existe"]);
                exit;
            }
            
            $categoria_id = $opcion_actual['categoria_id'];
            $orden_actual = $opcion_actual['orden'] ?? 0;
            
            // Buscar la opción anterior en la misma categoría
            $stmt = $conn->prepare("SELECT id, orden FROM opciones WHERE categoria_id = ? AND orden < ? ORDER BY orden DESC LIMIT 1");
            $stmt->bind_param("ii", $categoria_id, $orden_actual);
            $stmt->execute();
            $result = $stmt->get_result();
            $opcion_anterior = $result->fetch_assoc();
            
            error_log("Opción anterior: " . print_r($opcion_anterior, true));
            
            if (!$opcion_anterior) {
                echo json_encode(['success' => false, 'message' => 'La opción ya está en la primera posición de su categoría', 'debug' => "No hay opción anterior en categoría $categoria_id"]);
                exit;
            }
            
            // Intercambiar órdenes
            $orden_anterior = $opcion_anterior['orden'];
            $id_anterior = $opcion_anterior['id'];
            
            $conn->begin_transaction();
            
            $stmt1 = $conn->prepare("UPDATE opciones SET orden = ? WHERE id = ?");
            $stmt1->bind_param("ii", $orden_anterior, $id);
            $result1 = $stmt1->execute();
            
            $stmt2 = $conn->prepare("UPDATE opciones SET orden = ? WHERE id = ?");
            $stmt2->bind_param("ii", $orden_actual, $id_anterior);
            $result2 = $stmt2->execute();
            
            if ($result1 && $result2) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Opción movida hacia arriba']);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error en la transacción']);
            }
            break;
            
        case 'move_opcion_down':
            echo json_encode(['success' => true, 'message' => 'Move down no implementado en debug']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida', 'debug' => "action=$action"]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    // Para mysqli no existe inTransaction(), así que simplemente intentamos rollback
    if (isset($conn)) {
        try {
            $conn->rollback();
        } catch (Exception $rollbackException) {
            // Si falla el rollback, lo registramos pero continuamos
            error_log("Error en rollback: " . $rollbackException->getMessage());
        }
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
}
?> 