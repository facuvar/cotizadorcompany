<?php
// Incluir configuración de la base de datos
include 'db_config.php';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "<h2>Lista de Productos en la Base de Datos</h2>";

// Consultar todos los productos
$sql = "SELECT id, nombre FROM xls_productos ORDER BY nombre";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th></tr>";
    
    // Mostrar datos de cada fila
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nombre"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

// Buscar específicamente ASCENSORES HIDRAULICOS
echo "<h2>Búsqueda de 'ASCENSORES HIDRAULICOS'</h2>";
$sql = "SELECT id, nombre FROM xls_productos WHERE nombre LIKE '%HIDRAULIC%' OR nombre LIKE '%ASCENSOR%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th></tr>";
    
    // Mostrar datos de cada fila
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["nombre"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No se encontró ningún producto con 'HIDRAULIC' o 'ASCENSOR' en el nombre";
}

$conn->close();
?>
