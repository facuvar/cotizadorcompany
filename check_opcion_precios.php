<?php
require_once './sistema/config.php';
require_once './sistema/includes/db.php';

// Obtener instancia de la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Consultar las columnas de la tabla opcion_precios
$result = $conn->query('SHOW COLUMNS FROM opcion_precios');

if ($result) {
    echo "<h3>Columnas de la tabla opcion_precios:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . (isset($row['Default']) ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Consultar ejemplo de datos
echo "<h3>Muestra de datos:</h3>";
$datos = $conn->query("SELECT * FROM opcion_precios LIMIT 5");

if ($datos) {
    echo "<table border='1'>";
    
    // Cabecera de la tabla
    echo "<tr>";
    $fields = $datos->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    // Datos
    $datos->data_seek(0);
    while ($fila = $datos->fetch_assoc()) {
        echo "<tr>";
        foreach ($fila as $valor) {
            echo "<td>" . (is_null($valor) ? "NULL" : $valor) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}
?> 