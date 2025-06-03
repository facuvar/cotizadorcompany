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
    
    // Obtener todas las opciones organizadas por categoría (sin filtro activo)
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
            c.nombre as categoria_nombre
        FROM opciones o
        JOIN categorias c ON o.categoria_id = c.id
        ORDER BY c.orden ASC, o.orden ASC, o.id ASC
    ');
    
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar por categoría
    $resultado = [];
    
    foreach ($opciones as $opcion) {
        $categoriaId = $opcion['categoria_id'];
        
        if (!isset($resultado[$categoriaId])) {
            $resultado[$categoriaId] = [];
        }
        
        $resultado[$categoriaId][] = [
            'id' => $opcion['id'],
            'nombre' => $opcion['nombre'],
            'descripcion' => $opcion['descripcion'],
            'precio' => floatval($opcion['precio']),
            'precio_90_dias' => floatval($opcion['precio_90_dias']),
            'precio_160_dias' => floatval($opcion['precio_160_dias']),
            'precio_270_dias' => floatval($opcion['precio_270_dias']),
            'descuento' => floatval($opcion['descuento']),
            'orden' => intval($opcion['orden']),
            'categoria_nombre' => $opcion['categoria_nombre']
        ];
    }
    
    // Agregar información de debug
    $debug = [
        'total_opciones' => count($opciones),
        'categorias_con_datos' => array_keys($resultado),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $response = [
        'success' => true,
        'data' => $resultado,
        'debug' => $debug
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
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