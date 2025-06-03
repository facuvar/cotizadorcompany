<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'sistema/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener todas las opciones con información de categoría
    $stmt = $pdo->query('
        SELECT 
            o.id,
            o.categoria_id,
            o.nombre,
            o.descripcion,
            o.precio,
            o.precio_90_dias,
            o.precio_160_dias,
            o.precio_270_dias,
            o.descuento,
            o.orden,
            o.es_titulo,
            c.nombre as categoria_nombre,
            c.orden as categoria_orden
        FROM opciones o
        JOIN categorias c ON o.categoria_id = c.id
        WHERE o.activo = 1 AND c.activo = 1
        ORDER BY c.orden ASC, o.orden ASC, o.id ASC
    ');
    
    $todasLasOpciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Separar títulos de opciones seleccionables
    $opcionesSeleccionables = [];
    $titulos = [];
    
    foreach ($todasLasOpciones as $opcion) {
        if ($opcion['es_titulo'] == 1) {
            $titulos[] = [
                'id' => intval($opcion['id']),
                'categoria_id' => intval($opcion['categoria_id']),
                'nombre' => $opcion['nombre'],
                'descripcion' => $opcion['descripcion'],
                'orden' => intval($opcion['orden']),
                'categoria_nombre' => $opcion['categoria_nombre'],
                'categoria_orden' => intval($opcion['categoria_orden'])
            ];
        } else {
            $opcionesSeleccionables[] = [
                'id' => intval($opcion['id']),
                'categoria_id' => intval($opcion['categoria_id']),
                'nombre' => $opcion['nombre'],
                'descripcion' => $opcion['descripcion'],
                'precio' => floatval($opcion['precio']),
                'precio_90_dias' => floatval($opcion['precio_90_dias']),
                'precio_160_dias' => floatval($opcion['precio_160_dias']),
                'precio_270_dias' => floatval($opcion['precio_270_dias']),
                'descuento' => floatval($opcion['descuento']),
                'orden' => intval($opcion['orden']),
                'categoria_nombre' => $opcion['categoria_nombre'],
                'categoria_orden' => intval($opcion['categoria_orden'])
            ];
        }
    }
    
    // Obtener plazos de entrega
    $stmt_plazos = $pdo->query('
        SELECT 
            id,
            nombre,
            dias
        FROM plazos_entrega
        WHERE activo = 1
        ORDER BY dias ASC
    ');
    
    $plazos = $stmt_plazos->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear opciones para el frontend
    $opcionesFormateadas = [];
    foreach ($opcionesSeleccionables as $opcion) {
        $opcionesFormateadas[] = [
            'id' => intval($opcion['id']),
            'categoria_id' => intval($opcion['categoria_id']),
            'nombre' => $opcion['nombre'],
            'descripcion' => $opcion['descripcion'],
            'precio' => floatval($opcion['precio']),
            'precio_90_dias' => floatval($opcion['precio_90_dias']),
            'precio_160_dias' => floatval($opcion['precio_160_dias']),
            'precio_270_dias' => floatval($opcion['precio_270_dias']),
            'descuento' => floatval($opcion['descuento']),
            'orden' => intval($opcion['orden']),
            'categoria_nombre' => $opcion['categoria_nombre'],
            'categoria_orden' => intval($opcion['categoria_orden'])
        ];
    }
    
    // Formatear plazos para el frontend
    $plazosFormateados = [];
    foreach ($plazos as $plazo) {
        $plazosFormateados[] = [
            'id' => intval($plazo['id']),
            'nombre' => $plazo['nombre'],
            'dias' => intval($plazo['dias']),
            'descripcion' => '' // Campo vacío ya que no existe en la BD
        ];
    }
    
    $response = [
        'success' => true,
        'opciones' => $opcionesFormateadas,
        'titulos' => $titulos,
        'plazos_entrega' => $plazosFormateados,
        'debug' => [
            'total_opciones_seleccionables' => count($opcionesFormateadas),
            'total_titulos' => count($titulos),
            'total_plazos' => count($plazosFormateados),
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'file' => __FILE__,
            'line' => __LINE__
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?> 