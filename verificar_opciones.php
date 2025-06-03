<?php
// Verificar qué productos tienen opciones
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Consulta para obtener productos y número de opciones
    $query = "SELECT p.id, p.nombre, COUNT(o.id) as num_opciones 
              FROM xls_productos p 
              LEFT JOIN xls_opciones o ON p.id = o.producto_id 
              GROUP BY p.id 
              ORDER BY p.orden ASC";
    
    $result = $conn->query($query);
    
    echo "<h1>PRODUCTOS Y SUS OPCIONES</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Producto</th><th>Número de Opciones</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['num_opciones']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
