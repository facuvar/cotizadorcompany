<?php
// Incluir configuración de la base de datos
include 'db_config.php';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "<h2>Creación de Producto ASCENSORES HIDRAULICOS</h2>";

// Verificar si el producto ya existe
$sql = "SELECT id FROM xls_productos WHERE nombre = 'ASCENSORES HIDRAULICOS'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $productoId = $row['id'];
    echo "El producto ASCENSORES HIDRAULICOS ya existe con ID: " . $productoId . "<br>";
} else {
    // Insertar el producto
    $sql = "INSERT INTO xls_productos (nombre, descripcion) VALUES ('ASCENSORES HIDRAULICOS', 'Ascensores con sistema hidráulico')";
    
    if ($conn->query($sql) === TRUE) {
        $productoId = $conn->insert_id;
        echo "Producto ASCENSORES HIDRAULICOS creado exitosamente con ID: " . $productoId . "<br>";
    } else {
        echo "Error al crear el producto: " . $conn->error . "<br>";
        die();
    }
}

// Lista de IDs de adicionales que deben asociarse con ASCENSORES HIDRAULICOS
$adicionalesIds = [
    52, // ADICIONAL 2 TRAMOS
    53, // ADICIONAL 750KG CENTRAL Y PISTON
    26, // ADICIONAL CABINA 2,25M3
    54, // ADICIONAL 1000KG CENTRAL Y PISTON
    28, // ADICIONAL CABINA 2,66
    55, // ADICIONAL PISO EN ACERO
    56, // ADICIONAL PANORAMICO
    57, // RESTAR CABINA EN CHAPA
    58, // RESTAR PUERTA CABINA Y PB A CHAPA
    59, // RESTAR SIN PUERTAS EXT X4
    60, // RESTAR OPERADOR Y DEJAR PUERTA PLEGADIZA CHAPÀ
    34, // PUERTAS DE 900
    35, // PUERTAS DE 1000
    61, // PUERTAS DE 1200
    37, // PUERTAS DE 1800
    62, // ADICIONAL ACCESO EN CABINA EN ACERO
    38, // PUERTA PANORAMICA CABINA + PB
    39, // PUERTA PANORAMICA PISOS
    40, // TARJETA CHIP KEYPASS
    41, // SISTEMA KEYPASS COMPLETO (UN COD POR PISO)
    42, // SISTEMA KEYPASS SIMPLE (UN COD UNIVERSAL)
    43  // SISTEMA UPS
];

echo "<h3>Asociando adicionales al producto ASCENSORES HIDRAULICOS</h3>";
echo "<ul>";

// Asociar los adicionales al producto
foreach ($adicionalesIds as $adicionalId) {
    // Verificar si ya existe la asociación
    $sql = "SELECT * FROM xls_productos_adicionales WHERE producto_id = $productoId AND adicional_id = $adicionalId";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<li>El adicional ID $adicionalId ya está asociado al producto</li>";
    } else {
        // Insertar la asociación
        $sql = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES ($productoId, $adicionalId)";
        
        if ($conn->query($sql) === TRUE) {
            // Obtener el nombre del adicional
            $sqlNombre = "SELECT nombre FROM xls_adicionales WHERE id = $adicionalId";
            $resultNombre = $conn->query($sqlNombre);
            $nombreAdicional = "ID: " . $adicionalId;
            
            if ($resultNombre->num_rows > 0) {
                $rowNombre = $resultNombre->fetch_assoc();
                $nombreAdicional = $rowNombre['nombre'] . " (ID: " . $adicionalId . ")";
            }
            
            echo "<li>Adicional '$nombreAdicional' asociado correctamente al producto</li>";
        } else {
            echo "<li>Error al asociar el adicional ID $adicionalId: " . $conn->error . "</li>";
        }
    }
}

echo "</ul>";

echo "<p>Proceso completado. <a href='verificar_productos.php'>Verificar productos</a> | <a href='cotizador_simple.php'>Ir al cotizador</a></p>";

$conn->close();
?>
