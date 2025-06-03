<?php
// Script para limpiar la base de datos antes de importar un nuevo XLS
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
        <title>Limpieza de Base de Datos</title>
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
        <h1>Limpieza de Base de Datos</h1>";
    
    // Verificar si se solicitó la limpieza
    if (isset($_POST['limpiar'])) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Eliminar precios de opciones
            $query = "DELETE FROM xls_precios";
            $conn->query($query);
            mostrarMensaje("Precios de opciones eliminados", "success");
            
            // 2. Eliminar opciones
            $query = "DELETE FROM xls_opciones";
            $conn->query($query);
            mostrarMensaje("Opciones eliminadas", "success");
            
            // 3. Eliminar precios de adicionales
            $query = "DELETE FROM xls_adicionales_precios";
            $conn->query($query);
            mostrarMensaje("Precios de adicionales eliminados", "success");
            
            // 4. Eliminar relaciones producto-adicional
            $query = "DELETE FROM xls_productos_adicionales";
            $conn->query($query);
            mostrarMensaje("Relaciones producto-adicional eliminadas", "success");
            
            // 5. Eliminar adicionales
            $query = "DELETE FROM xls_adicionales";
            $conn->query($query);
            mostrarMensaje("Adicionales eliminados", "success");
            
            // 6. Eliminar productos
            $query = "DELETE FROM xls_productos";
            $conn->query($query);
            mostrarMensaje("Productos eliminados", "success");
            
            // Confirmar cambios
            $conn->commit();
            echo "<p class='success'><strong>Base de datos limpiada correctamente</strong></p>";
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al limpiar la base de datos: " . $e->getMessage(), "error");
        }
    }
    
    // Mostrar formulario de confirmación
    echo "
        <div class='card'>
            <h2>Limpiar Base de Datos</h2>
            <p>Esta acción eliminará todos los productos, opciones, adicionales y precios de la base de datos.</p>
            <p><strong>Advertencia:</strong> Esta acción no se puede deshacer. Asegúrate de tener una copia de seguridad de tus datos si los necesitas.</p>
            
            <form method='post'>
                <button type='submit' name='limpiar' class='btn' onclick=\"return confirm('¿Estás seguro de que deseas limpiar la base de datos? Esta acción no se puede deshacer.');\">Limpiar Base de Datos</button>
                <a href='importar_desde_excel.php' class='btn' style='background-color: #2196F3;'>Volver a Importación</a>
            </form>
        </div>
    ";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error de conexión: " . $e->getMessage(), "error");
}
?>
