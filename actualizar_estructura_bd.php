<?php
// Script para actualizar la estructura de la base de datos
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
        <title>Actualizar Estructura de Base de Datos</title>
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
        <h1>Actualizar Estructura de Base de Datos</h1>";
    
    // Verificar si se solicitó la actualización
    if (isset($_POST['actualizar'])) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Verificar si existe la tabla plazos_entrega
            $result = $conn->query("SHOW TABLES LIKE 'plazos_entrega'");
            if ($result->num_rows == 0) {
                // Crear tabla plazos_entrega
                $sql = "CREATE TABLE plazos_entrega (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(100) NOT NULL,
                    multiplicador DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                    orden INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                $conn->query($sql);
                mostrarMensaje("Tabla plazos_entrega creada correctamente", "success");
                
                // Insertar plazos predeterminados
                $plazos = [
                    ['90 dias', 1.30, 1],
                    ['160-180 dias', 1.00, 2],
                    ['270 dias', 0.90, 3]
                ];
                
                $stmt = $conn->prepare("INSERT INTO plazos_entrega (nombre, multiplicador, orden) VALUES (?, ?, ?)");
                foreach ($plazos as $plazo) {
                    $stmt->bind_param("sdi", $plazo[0], $plazo[1], $plazo[2]);
                    $stmt->execute();
                }
                
                mostrarMensaje("Plazos de entrega predeterminados agregados", "success");
            } else {
                mostrarMensaje("La tabla plazos_entrega ya existe", "info");
            }
            
            // 2. Verificar si existe la columna plazo_entrega en xls_precios
            $result = $conn->query("SHOW COLUMNS FROM xls_precios LIKE 'plazo_entrega'");
            if ($result->num_rows == 0) {
                // Agregar columna plazo_entrega
                $sql = "ALTER TABLE xls_precios ADD COLUMN plazo_entrega VARCHAR(100) NOT NULL AFTER opcion_id";
                $conn->query($sql);
                mostrarMensaje("Columna plazo_entrega agregada a la tabla xls_precios", "success");
            } else {
                mostrarMensaje("La columna plazo_entrega ya existe en la tabla xls_precios", "info");
            }
            
            // Confirmar cambios
            $conn->commit();
            echo "<p class='success'><strong>Estructura de base de datos actualizada correctamente</strong></p>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al actualizar la estructura de la base de datos: " . $e->getMessage(), "error");
        }
    }
    
    // Mostrar formulario de confirmación
    echo "
        <div class='card'>
            <h2>Actualizar Estructura de Base de Datos</h2>
            <p>Esta acción actualizará la estructura de la base de datos para soportar plazos de entrega y precios variables.</p>
            
            <form method='post'>
                <button type='submit' name='actualizar' class='btn'>Actualizar Estructura</button>
                <a href='importar_xls_formulas.php' class='btn' style='background-color: #2196F3;'>Volver a Importación</a>
            </form>
        </div>
    ";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error de conexión: " . $e->getMessage(), "error");
}
?>
