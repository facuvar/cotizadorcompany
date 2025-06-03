<?php
// Script para verificar el estado actual de la base de datos
// y mostrar información de diagnóstico

// Configuración básica
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'presupuestos_ascensores';

// Conectar a MySQL
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Función para mostrar resultados de manera limpia
function mostrarResultados($result) {
    if (!$result) {
        echo "Error en la consulta: " . $GLOBALS['conn']->error . "<br>";
        return;
    }
    
    if ($result->num_rows === 0) {
        echo "No se encontraron resultados.<br>";
        return;
    }
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    
    // Cabecera
    echo "<tr>";
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    // Datos
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . (is_null($value) ? "NULL" : htmlspecialchars($value)) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
}

// Función para verificar si una tabla existe
function tablaExiste($tableName) {
    $result = $GLOBALS['conn']->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Mostrar cabecera
echo "<!DOCTYPE html>
<html>
<head>
    <title>Verificación del Estado de la Base de Datos</title>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2, h3 { color: #333; }
        table { border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f2f2f2; }
        td, th { padding: 8px; text-align: left; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Verificación del Estado de la Base de Datos</h1>
";

// Verificar tablas principales
$tablas = ['categorias', 'opciones', 'fuente_datos', 'presupuestos', 'presupuesto_detalle', 'configuraciones'];

echo "<div class='section'>";
echo "<h2>Verificación de Tablas</h2>";
echo "<ul>";

foreach ($tablas as $tabla) {
    if (tablaExiste($tabla)) {
        $count = $conn->query("SELECT COUNT(*) as total FROM $tabla")->fetch_assoc()['total'];
        echo "<li><span class='success'>✓</span> Tabla <strong>$tabla</strong> existe - $count registros</li>";
    } else {
        echo "<li><span class='error'>✗</span> Tabla <strong>$tabla</strong> no existe</li>";
    }
}

echo "</ul>";
echo "</div>";

// Verificar estructura de la tabla opciones
if (tablaExiste('opciones')) {
    echo "<div class='section'>";
    echo "<h2>Estructura de la Tabla 'opciones'</h2>";
    
    $result = $conn->query("DESCRIBE opciones");
    mostrarResultados($result);
    
    echo "</div>";
    
    // Mostrar algunos registros de opciones
    echo "<div class='section'>";
    echo "<h2>Muestra de Registros de 'opciones'</h2>";
    
    $result = $conn->query("SELECT * FROM opciones LIMIT 10");
    mostrarResultados($result);
    
    echo "</div>";
}

// Verificar fuente de datos
if (tablaExiste('fuente_datos')) {
    echo "<div class='section'>";
    echo "<h2>Fuentes de Datos</h2>";
    
    $result = $conn->query("SELECT * FROM fuente_datos");
    mostrarResultados($result);
    
    echo "</div>";
}

// Verificar categorías
if (tablaExiste('categorias')) {
    echo "<div class='section'>";
    echo "<h2>Categorías</h2>";
    
    $result = $conn->query("SELECT * FROM categorias ORDER BY orden");
    mostrarResultados($result);
    
    echo "</div>";
}

// Verificar configuraciones
if (tablaExiste('configuraciones')) {
    echo "<div class='section'>";
    echo "<h2>Configuraciones</h2>";
    
    $result = $conn->query("SELECT * FROM configuraciones");
    mostrarResultados($result);
    
    echo "</div>";
}

// Cerrar conexión
$conn->close();

echo "<div class='section'>
    <h2>Acciones</h2>
    <p><a href='setup_database.php'>Reinicializar Base de Datos</a> (Cuidado: esto puede eliminar datos existentes)</p>
    <p><a href='sistema/index.php'>Ir al Sistema</a></p>
    <p><a href='index.html'>Volver a la Página Principal</a></p>
</div>";

echo "</body></html>";
?>
