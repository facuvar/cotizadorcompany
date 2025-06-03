<?php
// Configuración de la base de datos
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'presupuestos_ascensores';

// Conectar a MySQL
$conn = new mysqli($db_host, $db_user, $db_pass);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada o ya existente.<br>";
} else {
    die("Error al crear la base de datos: " . $conn->error);
}

// Seleccionar la base de datos
$conn->select_db($db_name);

// Leer y ejecutar el archivo SQL
$sql_content = file_get_contents('sistema/db_schema.sql');
$sql_statements = explode(';', $sql_content);

$success = true;
foreach ($sql_statements as $sql) {
    $sql = trim($sql);
    if (empty($sql)) continue;
    
    if ($conn->query($sql) !== TRUE) {
        echo "Error al ejecutar SQL: " . $conn->error . "<br>";
        echo "SQL: " . $sql . "<br><br>";
        $success = false;
    }
}

if ($success) {
    echo "Base de datos inicializada correctamente.<br>";
    
    // Crear datos de ejemplo
    $query = "SELECT COUNT(*) as total FROM fuente_datos";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        // No hay datos, crear algunos de ejemplo
        echo "Creando datos de ejemplo...<br>";
        
        // Crear categorías y opciones de ejemplo
        $categorias = [
            ['nombre' => 'Tipo de Ascensor', 'descripcion' => 'Selecciona el tipo de ascensor que necesitas', 'orden' => 1],
            ['nombre' => 'Capacidad', 'descripcion' => 'Selecciona la capacidad del ascensor', 'orden' => 2],
            ['nombre' => 'Número de Paradas', 'descripcion' => 'Selecciona el número de pisos', 'orden' => 3],
            ['nombre' => 'Acabados', 'descripcion' => 'Selecciona los acabados del ascensor', 'orden' => 4],
            ['nombre' => 'Opciones Adicionales', 'descripcion' => 'Selecciona características adicionales', 'orden' => 5]
        ];
        
        foreach ($categorias as $categoria) {
            $sql = "INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $categoria['nombre'], $categoria['descripcion'], $categoria['orden']);
            $stmt->execute();
            $categoriaId = $conn->insert_id;
            
            // Crear opciones según la categoría
            $opciones = [];
            
            switch ($categoriaId) {
                case 1: // Tipo de Ascensor
                    $opciones = [
                        ['nombre' => 'Ascensor Eléctrico', 'descripcion' => 'Ascensor con motor eléctrico, ideal para edificios de media y alta altura', 'precio' => 15000.00, 'es_obligatorio' => 1, 'orden' => 1],
                        ['nombre' => 'Ascensor Hidráulico', 'descripcion' => 'Ascensor con sistema hidráulico, ideal para edificios de baja altura', 'precio' => 12000.00, 'es_obligatorio' => 1, 'orden' => 2],
                        ['nombre' => 'Ascensor Sin Cuarto de Máquinas', 'descripcion' => 'Ascensor eléctrico sin necesidad de cuarto de máquinas', 'precio' => 18000.00, 'es_obligatorio' => 1, 'orden' => 3]
                    ];
                    break;
                case 2: // Capacidad
                    $opciones = [
                        ['nombre' => '4 Personas (320 kg)', 'descripcion' => 'Capacidad para 4 personas o 320 kg', 'precio' => 0.00, 'es_obligatorio' => 1, 'orden' => 1],
                        ['nombre' => '6 Personas (480 kg)', 'descripcion' => 'Capacidad para 6 personas o 480 kg', 'precio' => 1500.00, 'es_obligatorio' => 1, 'orden' => 2],
                        ['nombre' => '8 Personas (640 kg)', 'descripcion' => 'Capacidad para 8 personas o 640 kg', 'precio' => 3000.00, 'es_obligatorio' => 1, 'orden' => 3]
                    ];
                    break;
                case 3: // Número de Paradas
                    $opciones = [
                        ['nombre' => '2 Paradas', 'descripcion' => 'Ascensor con 2 paradas', 'precio' => 0.00, 'es_obligatorio' => 1, 'orden' => 1],
                        ['nombre' => '3 Paradas', 'descripcion' => 'Ascensor con 3 paradas', 'precio' => 2000.00, 'es_obligatorio' => 1, 'orden' => 2],
                        ['nombre' => '4 Paradas', 'descripcion' => 'Ascensor con 4 paradas', 'precio' => 4000.00, 'es_obligatorio' => 1, 'orden' => 3],
                        ['nombre' => '5 Paradas', 'descripcion' => 'Ascensor con 5 paradas', 'precio' => 6000.00, 'es_obligatorio' => 1, 'orden' => 4]
                    ];
                    break;
                case 4: // Acabados
                    $opciones = [
                        ['nombre' => 'Acabado Estándar', 'descripcion' => 'Acabado básico con materiales estándar', 'precio' => 0.00, 'es_obligatorio' => 1, 'orden' => 1],
                        ['nombre' => 'Acabado Premium', 'descripcion' => 'Acabado con materiales de alta calidad', 'precio' => 2500.00, 'es_obligatorio' => 1, 'orden' => 2],
                        ['nombre' => 'Acabado Lujo', 'descripcion' => 'Acabado con materiales de lujo y diseño personalizado', 'precio' => 5000.00, 'es_obligatorio' => 1, 'orden' => 3]
                    ];
                    break;
                case 5: // Opciones Adicionales
                    $opciones = [
                        ['nombre' => 'Sistema de Monitoreo Remoto', 'descripcion' => 'Sistema para monitorear el estado del ascensor de forma remota', 'precio' => 1200.00, 'es_obligatorio' => 0, 'orden' => 1],
                        ['nombre' => 'Sistema de Ahorro de Energía', 'descripcion' => 'Sistema que reduce el consumo energético del ascensor', 'precio' => 1500.00, 'es_obligatorio' => 0, 'orden' => 2],
                        ['nombre' => 'Sistema de Control de Acceso', 'descripcion' => 'Sistema para controlar el acceso al ascensor mediante tarjetas o códigos', 'precio' => 1800.00, 'es_obligatorio' => 0, 'orden' => 3]
                    ];
                    break;
            }
            
            foreach ($opciones as $opcion) {
                $sql = "INSERT INTO opciones (categoria_id, nombre, descripcion, precio, es_obligatorio, orden) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('issdii', $categoriaId, $opcion['nombre'], $opcion['descripcion'], $opcion['precio'], $opcion['es_obligatorio'], $opcion['orden']);
                $stmt->execute();
            }
        }
        
        // Registrar la fuente de datos como importada desde setup
        $sql = "INSERT INTO fuente_datos (tipo, archivo) VALUES ('excel', 'datos_ejemplo.xlsx')";
        $conn->query($sql);
        
        echo "Datos de ejemplo creados correctamente.<br>";
    }
}

$conn->close();

echo "<br>Inicialización completada. <a href='index.html'>Ir a la página principal</a>";
?> 