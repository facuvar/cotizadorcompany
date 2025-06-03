<?php
// Configuración de la conexión
$servername = "localhost";
$username = "root";
$password = "";

// Crear conexión sin seleccionar base de datos
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "Conexión exitosa al servidor MySQL\n\n";

// Listar todas las bases de datos
$result = $conn->query("SHOW DATABASES");

if ($result->num_rows > 0) {
    echo "Bases de datos disponibles:\n";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "No se encontraron bases de datos\n";
}

// Cerrar conexión
$conn->close();
?> 