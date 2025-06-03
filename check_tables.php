<?php
require_once 'sistema/includes/db.php';

try {
    ob_start();

    // Conexión a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Consultar la estructura de la tabla
    $query = "SHOW COLUMNS FROM opcion_precios";
    $result = $conn->query($query);
    
    echo "<h1>Estructura de la tabla opcion_precios</h1>";

    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
    }
        
        echo "</table>";
} else {
        echo "<p>No se pudo obtener la estructura de la tabla opcion_precios o la tabla no existe.</p>";
        echo "<p>Error: " . $conn->error . "</p>";
}

    $output = ob_get_clean();
    file_put_contents('tabla_opcion_precios.html', $output);
    echo "La estructura se ha guardado en el archivo tabla_opcion_precios.html";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 