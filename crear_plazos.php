<?php
// Script para crear la tabla plazos_entrega desde cero
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
        <title>Crear Plazos de Entrega</title>
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
        <h1>Crear Plazos de Entrega</h1>";
    
    // Verificar si se solicitó la creación
    if (isset($_POST['crear'])) {
        // Desactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Eliminar la tabla si existe
        $conn->query("DROP TABLE IF EXISTS plazos_entrega");
        mostrarMensaje("Tabla plazos_entrega eliminada (si existía)", "info");
        
        // Crear la tabla plazos_entrega
        $sql = "CREATE TABLE plazos_entrega (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion VARCHAR(255),
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($sql)) {
            mostrarMensaje("Tabla plazos_entrega creada correctamente", "success");
            
            // Insertar plazos predeterminados
            $sql = "INSERT INTO plazos_entrega (nombre, descripcion) VALUES 
                ('160-180 dias', 'Plazo estándar (160-180 días)'),
                ('90 dias', 'Plazo rápido (90 días)'),
                ('270 dias', 'Plazo económico (270 días)')";
            
            if ($conn->query($sql)) {
                mostrarMensaje("Plazos predeterminados insertados correctamente", "success");
            } else {
                mostrarMensaje("Error al insertar plazos predeterminados: " . $conn->error, "error");
            }
        } else {
            mostrarMensaje("Error al crear la tabla plazos_entrega: " . $conn->error, "error");
        }
        
        // Crear tabla categorias si no existe
        $conn->query("CREATE TABLE IF NOT EXISTS categorias (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            orden INT(11) DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Insertar categorías predeterminadas
        $conn->query("TRUNCATE TABLE categorias");
        $conn->query("INSERT INTO categorias (id, nombre, descripcion, orden) VALUES 
            (1, 'ASCENSORES', 'Equipos electromecanicos', 1),
            (2, 'ADICIONALES', 'Características adicionales', 2),
            (3, 'DESCUENTOS', 'Descuentos aplicables', 3)");
        mostrarMensaje("Categorías sincronizadas", "success");
        
        // Crear tabla opciones si no existe
        $conn->query("CREATE TABLE IF NOT EXISTS opciones (
            id INT(11) NOT NULL AUTO_INCREMENT,
            categoria_id INT(11) NOT NULL,
            nombre VARCHAR(255) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(15,2) DEFAULT 0.00,
            orden INT(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY categoria_id (categoria_id),
            CONSTRAINT fk_opciones_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Limpiar tabla opciones
        $conn->query("TRUNCATE TABLE opciones");
        
        // Insertar productos de ASCENSORES
        $conn->query("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden) VALUES 
            (1, 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL - 4 PARADAS', 'Ascensor estándar para edificios residenciales', 2500000.00, 1),
            (1, 'EQUIPO ELECTROMECANICO 450KG CARGA UTIL - 6 PARADAS', 'Ascensor estándar para edificios residenciales', 3000000.00, 2),
            (1, 'EQUIPO ELECTROMECANICO 750KG CARGA UTIL - 4 PARADAS', 'Ascensor de mayor capacidad para edificios comerciales', 3500000.00, 3),
            (1, 'EQUIPO ELECTROMECANICO 750KG CARGA UTIL - 6 PARADAS', 'Ascensor de mayor capacidad para edificios comerciales', 4000000.00, 4)");
        
        // Insertar productos de ADICIONALES
        $conn->query("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden) VALUES 
            (2, 'PARADA ADICIONAL', 'Costo por cada parada adicional', 500000.00, 1),
            (2, 'ACABADO EN ACERO INOXIDABLE', 'Acabado premium para la cabina', 750000.00, 2),
            (2, 'SISTEMA DE CONTROL DE ACCESO', 'Control de acceso por tarjeta o código', 350000.00, 3),
            (2, 'SISTEMA DE MONITOREO REMOTO', 'Monitoreo y diagnóstico remoto', 250000.00, 4)");
        
        // Insertar productos de DESCUENTOS
        $conn->query("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden) VALUES 
            (3, 'DESCUENTO POR PAGO ANTICIPADO', 'Descuento del 5% por pago anticipado', -0.05, 1),
            (3, 'DESCUENTO POR VOLUMEN', 'Descuento del 10% para proyectos con múltiples ascensores', -0.10, 2)");
        
        mostrarMensaje("Productos y opciones sincronizados", "success");
        
        // Crear tabla opcion_precios si no existe
        $conn->query("CREATE TABLE IF NOT EXISTS opcion_precios (
            id INT(11) NOT NULL AUTO_INCREMENT,
            opcion_id INT(11) NOT NULL,
            plazo_entrega VARCHAR(100) NOT NULL,
            precio DECIMAL(15,2) NOT NULL,
            PRIMARY KEY (id),
            KEY opcion_id (opcion_id),
            CONSTRAINT fk_opcion_precios_opcion FOREIGN KEY (opcion_id) REFERENCES opciones (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Limpiar tabla opcion_precios
        $conn->query("TRUNCATE TABLE opcion_precios");
        
        // Generar precios para cada opción y plazo
        $result = $conn->query("SELECT id, precio FROM opciones");
        $plazos = [
            '160-180 dias' => 1.00,
            '90 dias' => 1.30,
            '270 dias' => 0.90
        ];
        
        if ($result && $result->num_rows > 0) {
            while ($opcion = $result->fetch_assoc()) {
                $opcionId = $opcion['id'];
                $precioBase = $opcion['precio'];
                
                foreach ($plazos as $plazo => $factor) {
                    $precio = $precioBase * $factor;
                    $stmt = $conn->prepare("INSERT INTO opcion_precios (opcion_id, plazo_entrega, precio) VALUES (?, ?, ?)");
                    $stmt->bind_param("isd", $opcionId, $plazo, $precio);
                    $stmt->execute();
                }
            }
            mostrarMensaje("Precios generados para todas las opciones y plazos", "success");
        }
        
        // Reactivar restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        mostrarMensaje("Configuración completada correctamente", "success");
        echo "<p><a href='sistema/cotizador.php' class='btn'>Ir al Cotizador</a></p>";
        
    } else {
        // Mostrar formulario de creación
        echo "
        <div class='card'>
            <p>Este script creará la tabla plazos_entrega desde cero y configurará el cotizador con datos predefinidos.</p>
            <p>Se realizarán las siguientes acciones:</p>
            <ul>
                <li>Eliminar la tabla plazos_entrega si existe</li>
                <li>Crear una nueva tabla plazos_entrega sin la columna multiplicador</li>
                <li>Insertar plazos predeterminados</li>
                <li>Crear o actualizar las tablas categorias y opciones</li>
                <li>Insertar productos y opciones predefinidos</li>
                <li>Generar precios para cada opción y plazo</li>
            </ul>
            <p><strong>Nota:</strong> Esta acción eliminará todos los datos existentes en las tablas mencionadas.</p>
            
            <form method='post'>
                <button type='submit' name='crear' class='btn'>Crear Plazos y Configurar Cotizador</button>
            </form>
        </div>";
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
