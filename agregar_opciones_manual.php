<?php
// Script para agregar opciones manualmente para MONTAPLATOS y ESTRUCTURA
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Verificar si se envió el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    try {
        // Conectar a la base de datos
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Obtener datos del formulario
        $producto_id = $_POST['producto_id'];
        $opciones = $_POST['opciones'];
        
        // Eliminar opciones existentes y sus precios
        $conn->begin_transaction();
        
        try {
            // Obtener IDs de opciones existentes
            $query = "SELECT id FROM xls_opciones WHERE producto_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $producto_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $opcion_ids = [];
            while ($row = $result->fetch_assoc()) {
                $opcion_ids[] = $row['id'];
            }
            
            // Eliminar precios de estas opciones
            if (!empty($opcion_ids)) {
                $placeholders = implode(',', array_fill(0, count($opcion_ids), '?'));
                $query = "DELETE FROM xls_precios WHERE opcion_id IN ($placeholders)";
                $stmt = $conn->prepare($query);
                
                $types = str_repeat('i', count($opcion_ids));
                $stmt->bind_param($types, ...$opcion_ids);
                $stmt->execute();
                
                // Eliminar opciones
                $query = "DELETE FROM xls_opciones WHERE producto_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $producto_id);
                $stmt->execute();
            }
            
            // Insertar nuevas opciones con sus precios
            foreach ($opciones as $opcion) {
                if (empty($opcion['nombre'])) continue;
                
                // Insertar opción
                $stmt = $conn->prepare("INSERT INTO xls_opciones (producto_id, nombre, descripcion) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $producto_id, $opcion['nombre'], $opcion['descripcion']);
                $stmt->execute();
                
                $opcion_id = $conn->insert_id;
                
                // Insertar precios para cada plazo
                foreach ([1, 2, 3] as $plazo_id) {
                    $precio = str_replace(['$', '.', ','], ['', '', '.'], $opcion['precios'][$plazo_id]);
                    
                    $stmt = $conn->prepare("INSERT INTO xls_precios (opcion_id, plazo_id, precio) VALUES (?, ?, ?)");
                    $stmt->bind_param("iid", $opcion_id, $plazo_id, $precio);
                    $stmt->execute();
                }
            }
            
            $conn->commit();
            $mensaje = "Opciones guardadas correctamente";
            $tipo_mensaje = "success";
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
        
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Obtener productos
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $query = "SELECT id, nombre FROM xls_productos WHERE nombre LIKE '%MONTAPLATO%' OR nombre LIKE '%ESTRUCTURA%' ORDER BY nombre ASC";
    $productos = $conn->query($query);
    
    // Obtener plazos
    $query = "SELECT id, nombre FROM xls_plazos ORDER BY orden ASC";
    $plazos = $conn->query($query);
    $plazos_array = [];
    while ($plazo = $plazos->fetch_assoc()) {
        $plazos_array[$plazo['id']] = $plazo['nombre'];
    }
    
} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Opciones Manualmente</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1, h2 { margin-top: 0; }
        .card { background-color: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-secondary { background-color: #f5f5f5; color: #333; border: 1px solid #ddd; }
        .opciones-container { margin-top: 20px; }
        .opcion-item { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
        .precios-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
        .btn-add { background-color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Agregar Opciones Manualmente</h1>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="producto_id">Seleccione Producto:</label>
                    <select name="producto_id" id="producto_id" required>
                        <option value="">-- Seleccione un producto --</option>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="opciones-container" id="opciones-container">
                    <h2>Opciones</h2>
                    
                    <div class="opcion-item">
                        <div class="form-group">
                            <label>Nombre de la Opción:</label>
                            <input type="text" name="opciones[0][nombre]" placeholder="Ej: MONTAPLATOS 50KG" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <textarea name="opciones[0][descripcion]" placeholder="Descripción de la opción"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Precios:</label>
                            <div class="precios-grid">
                                <?php foreach ($plazos_array as $plazo_id => $plazo_nombre): ?>
                                    <div>
                                        <label><?php echo htmlspecialchars($plazo_nombre); ?>:</label>
                                        <input type="text" name="opciones[0][precios][<?php echo $plazo_id; ?>]" placeholder="Ej: $1,000,000.00" required>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn-add" id="agregar-opcion">Agregar Otra Opción</button>
                <hr style="margin: 20px 0;">
                
                <div style="text-align: right;">
                    <button type="submit" name="guardar">Guardar Opciones</button>
                </div>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="cotizador_xls_fixed.php" style="padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Ir al Cotizador</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let opcionCount = 1;
            
            document.getElementById('agregar-opcion').addEventListener('click', function() {
                const container = document.getElementById('opciones-container');
                const opcionItem = document.createElement('div');
                opcionItem.className = 'opcion-item';
                
                let preciosHtml = '';
                <?php foreach ($plazos_array as $plazo_id => $plazo_nombre): ?>
                    preciosHtml += `
                        <div>
                            <label><?php echo htmlspecialchars($plazo_nombre); ?>:</label>
                            <input type="text" name="opciones[${opcionCount}][precios][<?php echo $plazo_id; ?>]" placeholder="Ej: $1,000,000.00" required>
                        </div>
                    `;
                <?php endforeach; ?>
                
                opcionItem.innerHTML = `
                    <div class="form-group">
                        <label>Nombre de la Opción:</label>
                        <input type="text" name="opciones[${opcionCount}][nombre]" placeholder="Ej: MONTAPLATOS 50KG" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción:</label>
                        <textarea name="opciones[${opcionCount}][descripcion]" placeholder="Descripción de la opción"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Precios:</label>
                        <div class="precios-grid">
                            ${preciosHtml}
                        </div>
                    </div>
                `;
                
                container.appendChild(opcionItem);
                opcionCount++;
            });
        });
    </script>
</body>
</html>
