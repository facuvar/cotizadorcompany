<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

header('Content-Type: application/json');

// Función para registrar información de depuración
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/adicionales_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= " - " . json_encode($data);
    }
    
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
}

try {
    // Verificar que se hayan enviado los parámetros necesarios
    if (!isset($_GET['producto_id']) || empty($_GET['producto_id'])) {
        throw new Exception("ID de producto no proporcionado");
    }
    
    $productoId = intval($_GET['producto_id']);
    $plazoId = isset($_GET['plazo_id']) ? intval($_GET['plazo_id']) : 2; // 160-180 días por defecto
    
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener el nombre del producto
    $query = "SELECT nombre FROM xls_productos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Producto no encontrado");
    }
    
    $row = $result->fetch_assoc();
    $productoNombre = $row['nombre'];
    
    logDebug("Producto encontrado: $productoNombre (ID: $productoId)");
    
    // Verificar si es un producto hidráulico
    if (stripos($productoNombre, 'HIDRAULIC') !== false) {
        // Para productos hidráulicos, usar la lista fija de nombres pero buscar precios en la base de datos
        logDebug("Producto hidráulico detectado: $productoNombre. Buscando adicionales en la base de datos.");
        
        // Lista de nombres de adicionales para hidráulicos
        $nombresAdicionales = [
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
        
        // Buscar estos adicionales en la base de datos para obtener sus precios actualizados
        $adicionales = [];
        
        // Primero intentamos buscar en la base de datos
        $placeholders = str_repeat('?,', count($nombresAdicionales) - 1) . '?';
        $query = "SELECT a.id, a.nombre, a.descripcion, ap.precio 
                  FROM xls_adicionales a
                  LEFT JOIN xls_adicionales_precios ap ON a.id = ap.adicional_id AND ap.plazo_id = ?
                  WHERE a.nombre IN ($placeholders)
                  ORDER BY a.id ASC";
        
        logDebug("Buscando adicionales en la base de datos con la consulta: $query");
        
        // Preparar la consulta
        $stmt = $conn->prepare($query);
        
        // Crear array de parámetros para bind_param
        $types = 'i' . str_repeat('s', count($nombresAdicionales));
        $params = array($types, $plazoId);
        
        // Agregar cada nombre de adicional como parámetro
        foreach ($nombresAdicionales as $nombre) {
            $params[] = $nombre;
        }
        
        // Convertir parámetros a referencias
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $refs);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Verificar si encontramos adicionales en la base de datos
        if ($result && $result->num_rows > 0) {
            logDebug("Se encontraron " . $result->num_rows . " adicionales en la base de datos.");
            
            while ($adicional = $result->fetch_assoc()) {
                $adicionales[] = $adicional;
            }
            
            // Devolver los adicionales encontrados en la base de datos
            echo json_encode($adicionales);
            exit;
        }
        
        // Si no encontramos nada en la base de datos, usar valores predeterminados
        logDebug("No se encontraron adicionales en la base de datos. Usando valores predeterminados.");
        
        // Valores predeterminados (como respaldo)
        $adicionalesPredeterminados = [
            ['id' => 1001, 'nombre' => 'ADICIONAL 2 TRAMOS', 'descripcion' => '', 'precio' => 1168620.00],
            ['id' => 1002, 'nombre' => 'ADICIONAL 750KG CENTRAL Y PISTON', 'descripcion' => '', 'precio' => 716944.00],
            ['id' => 1003, 'nombre' => 'ADICIONAL CABINA 2,25M3', 'descripcion' => '', 'precio' => 279608.00],
            ['id' => 1004, 'nombre' => 'ADICIONAL 1000KG CENTRAL Y PISTON', 'descripcion' => '', 'precio' => 1742176.00],
            ['id' => 1005, 'nombre' => 'ADICIONAL CABINA 2,66', 'descripcion' => '', 'precio' => 494188.00],
            ['id' => 1006, 'nombre' => 'ADICIONAL PISO EN ACERO', 'descripcion' => '', 'precio' => 288211.00],
            ['id' => 1007, 'nombre' => 'ADICIONAL PANORAMICO', 'descripcion' => '', 'precio' => 419411.00],
            ['id' => 1008, 'nombre' => 'RESTAR CABINA EN CHAPA', 'descripcion' => '', 'precio' => 288211.00],
            ['id' => 1009, 'nombre' => 'RESTAR PUERTA CABINA Y PB A CHAPA', 'descripcion' => '', 'precio' => 418695.00],
            ['id' => 1010, 'nombre' => 'RESTAR SIN PUERTAS EXT X4', 'descripcion' => '', 'precio' => 1732139.00],
            ['id' => 1011, 'nombre' => 'RESTAR OPERADOR Y DEJAR PUERTA PLEGADIZA CHAPÀ', 'descripcion' => '', 'precio' => 511898.00],
            ['id' => 1012, 'nombre' => 'PUERTAS DE 900', 'descripcion' => '', 'precio' => 440762.00],
            ['id' => 1013, 'nombre' => 'PUERTAS DE 1000', 'descripcion' => '', 'precio' => 880112.00],
            ['id' => 1014, 'nombre' => 'PUERTAS DE 1200', 'descripcion' => '', 'precio' => 1101906.00],
            ['id' => 1015, 'nombre' => 'PUERTAS DE 1800', 'descripcion' => '', 'precio' => 1248826.00],
            ['id' => 1016, 'nombre' => 'ADICIONAL ACCESO EN CABINA EN ACERO', 'descripcion' => '', 'precio' => 1303405.00],
            ['id' => 1017, 'nombre' => 'PUERTA PANORAMICA CABINA + PB', 'descripcion' => '', 'precio' => 1397913.00],
            ['id' => 1018, 'nombre' => 'PUERTA PANORAMICA PISOS', 'descripcion' => '', 'precio' => 652419.00],
            ['id' => 1019, 'nombre' => 'TARJETA CHIP KEYPASS', 'descripcion' => '', 'precio' => 14338.00],
            ['id' => 1020, 'nombre' => 'SISTEMA KEYPASS COMPLETO (UN COD POR PISO)', 'descripcion' => '', 'precio' => 845995.00],
            ['id' => 1021, 'nombre' => 'SISTEMA KEYPASS SIMPLE (UN COD UNIVERSAL)', 'descripcion' => '', 'precio' => 372811.00],
            ['id' => 1022, 'nombre' => 'SISTEMA UPS', 'descripcion' => '', 'precio' => 195008.00]
        ];
        
        // Ajustar precios según el plazo seleccionado
        if ($plazoId == 1) { // 90 días
            foreach ($adicionalesPredeterminados as &$adicional) {
                $adicional['precio'] = $adicional['precio'] * 1.3; // 30% más
            }
        } else if ($plazoId == 3) { // 270 días
            foreach ($adicionalesPredeterminados as &$adicional) {
                $adicional['precio'] = $adicional['precio'] * 1.17; // 17% más
            }
        }
        
        // Devolver los adicionales predeterminados
        echo json_encode($adicionalesPredeterminados);
        exit;
    }
    
    // Obtener adicionales con sus precios para el plazo seleccionado
    if (!empty($adicionalesEspecificos)) {
        // Si tenemos adicionales específicos para este producto (hidráulicos), usarlos
        logDebug("Usando adicionales específicos para producto hidráulico: $productoNombre");
        
        // Construir la cláusula IN con los nombres de los adicionales específicos
        $placeholders = str_repeat('?,', count($adicionalesEspecificos) - 1) . '?';
        $query = "SELECT a.id, a.nombre, a.descripcion, ap.precio 
                  FROM xls_adicionales a
                  LEFT JOIN xls_adicionales_precios ap ON a.id = ap.adicional_id AND ap.plazo_id = ?
                  WHERE a.nombre IN ($placeholders)
                  ORDER BY a.id ASC";
        
        logDebug("Ejecutando consulta con adicionales específicos: $query");
        
        $stmt = $conn->prepare($query);
        
        // Crear array de parámetros para bind_param
        $types = 'i' . str_repeat('s', count($adicionalesEspecificos));
        $params = array($types, $plazoId);
        
        // Agregar cada adicional específico como referencia
        foreach ($adicionalesEspecificos as $adicional) {
            $params[] = $adicional;
        }
        
        // Convertir parámetros a referencias
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $refs);
    } else {
        // Consulta estándar para otros productos
        $query = "SELECT a.id, a.nombre, a.descripcion, ap.precio 
                  FROM xls_adicionales a
                  LEFT JOIN xls_adicionales_precios ap ON a.id = ap.adicional_id AND ap.plazo_id = ?
                  WHERE a.id IN (SELECT adicional_id FROM xls_productos_adicionales WHERE producto_id = ?)
                  ORDER BY a.id ASC";
        
        logDebug("Ejecutando consulta estándar: $query con plazo_id=$plazoId y producto_id=$productoId");
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $plazoId, $productoId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    logDebug("Resultados obtenidos: " . $result->num_rows);
    
    $adicionales = [];
    
    if ($result && $result->num_rows > 0) {
        while ($adicional = $result->fetch_assoc()) {
            // Si el precio es null, establecerlo a 0
            if ($adicional['precio'] === null) {
                $adicional['precio'] = 0;
            } else {
                // Asegurarse de que el precio sea un número (float)
                $adicional['precio'] = floatval($adicional['precio']);
            }
            
            $adicionales[] = $adicional;
            logDebug("Adicional encontrado: {$adicional['nombre']} con precio {$adicional['precio']}");
        }
    }
    
    logDebug("Devolviendo " . count($adicionales) . " adicionales");
    echo json_encode($adicionales);
    
} catch (Exception $e) {
    logDebug("Error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>
