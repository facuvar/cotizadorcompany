<?php
// Script para corregir la estructura de plazos en la base de datos
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = 'black';
    if ($tipo == 'success') $color = 'green';
    if ($tipo == 'error') $color = 'red';
    if ($tipo == 'warning') $color = 'orange';
    
    echo "<p style='color: $color;'>$mensaje</p>";
}

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Corregir Estructura de Plazos</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .card { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Corregir Estructura de Plazos</h1>";
    
    // Verificar si se solicitó la corrección
    if (isset($_POST['corregir'])) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Verificar si existe la tabla xls_plazos
            $result = $conn->query("SHOW TABLES LIKE 'xls_plazos'");
            if ($result->num_rows == 0) {
                // Crear tabla xls_plazos
                $sql = "CREATE TABLE xls_plazos (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    multiplicador DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                    orden INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                $conn->query($sql);
                mostrarMensaje("Tabla xls_plazos creada correctamente", "success");
                
                // Insertar plazos predeterminados
                $plazos = [
                    ['90 dias', 1.30, 1],
                    ['160-180 dias', 1.00, 2],
                    ['270 dias', 0.90, 3]
                ];
                
                $stmt = $conn->prepare("INSERT INTO xls_plazos (nombre, multiplicador, orden) VALUES (?, ?, ?)");
                foreach ($plazos as $plazo) {
                    $stmt->bind_param("sdi", $plazo[0], $plazo[1], $plazo[2]);
                    $stmt->execute();
                }
                
                mostrarMensaje("Plazos predeterminados agregados a xls_plazos", "success");
            } else {
                mostrarMensaje("La tabla xls_plazos ya existe", "info");
            }
            
            // 2. Verificar la estructura de la tabla xls_precios
            $result = $conn->query("SHOW CREATE TABLE xls_precios");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $createTable = $row['Create Table'];
                
                // Verificar si hay restricciones de clave foránea
                if (strpos($createTable, 'CONSTRAINT') !== false) {
                    // Eliminar la tabla y recrearla
                    $conn->query("DROP TABLE xls_precios");
                    mostrarMensaje("Tabla xls_precios eliminada para reconstrucción", "warning");
                    
                    // Crear la tabla xls_precios con la estructura correcta
                    $sql = "CREATE TABLE xls_precios (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        opcion_id INT(11) NOT NULL,
                        plazo_id INT(11) NOT NULL,
                        precio DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                        PRIMARY KEY (id),
                        KEY opcion_id (opcion_id),
                        KEY plazo_id (plazo_id),
                        CONSTRAINT xls_precios_ibfk_1 FOREIGN KEY (opcion_id) REFERENCES xls_opciones (id) ON DELETE CASCADE,
                        CONSTRAINT xls_precios_ibfk_2 FOREIGN KEY (plazo_id) REFERENCES xls_plazos (id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                    
                    $conn->query($sql);
                    mostrarMensaje("Tabla xls_precios reconstruida correctamente", "success");
                } else {
                    mostrarMensaje("La tabla xls_precios no tiene restricciones de clave foránea", "info");
                }
            } else {
                // Si la tabla no existe, crearla
                $sql = "CREATE TABLE xls_precios (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    opcion_id INT(11) NOT NULL,
                    plazo_id INT(11) NOT NULL,
                    precio DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    PRIMARY KEY (id),
                    KEY opcion_id (opcion_id),
                    KEY plazo_id (plazo_id),
                    CONSTRAINT xls_precios_ibfk_1 FOREIGN KEY (opcion_id) REFERENCES xls_opciones (id) ON DELETE CASCADE,
                    CONSTRAINT xls_precios_ibfk_2 FOREIGN KEY (plazo_id) REFERENCES xls_plazos (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                $conn->query($sql);
                mostrarMensaje("Tabla xls_precios creada correctamente", "success");
            }
            
            // Confirmar cambios
            $conn->commit();
            echo "<p class='success'><strong>Estructura de plazos corregida correctamente</strong></p>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al corregir la estructura: " . $e->getMessage(), "error");
        }
    }
    
    // Mostrar formulario de confirmación
    echo "
        <div class='card'>
            <h2>Corregir Estructura de Plazos</h2>
            <p>Esta acción corregirá la estructura de las tablas relacionadas con los plazos de entrega.</p>
            <p><strong>Advertencia:</strong> Se eliminarán y recrearán algunas tablas. Asegúrate de tener una copia de seguridad de tus datos si los necesitas.</p>
            
            <form method='post'>
                <button type='submit' name='corregir' class='btn' onclick=\"return confirm('¿Estás seguro de que deseas corregir la estructura? Esta acción puede eliminar datos existentes.');\">Corregir Estructura</button>
                <a href='importar_xls_formulas.php' class='btn' style='background-color: #2196F3;'>Volver a Importación</a>
            </form>
        </div>
    ";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error de conexión: " . $e->getMessage(), "error");
}
?>
