<?php
// Script para modificar el cotizador y hacerlo funcionar con la estructura actual
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
        <title>Reparación del Cotizador</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1, h2, h3 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
            .btn-blue { background-color: #2196F3; }
        </style>
    </head>
    <body>
    <h1>Reparación del Cotizador</h1>";
    
    echo "<div class='section'>";
    echo "<h2>Modificando el archivo del cotizador</h2>";
    
    // Ruta al archivo del cotizador
    $cotizadorPath = __DIR__ . '/sistema/cotizador.php';
    
    if (!file_exists($cotizadorPath)) {
        mostrarMensaje("El archivo del cotizador no existe: $cotizadorPath", "error");
        exit;
    }
    
    // Leer el contenido del archivo
    $cotizadorContent = file_get_contents($cotizadorPath);
    
    // Crear una copia de seguridad
    $backupPath = __DIR__ . '/sistema/cotizador.php.bak';
    file_put_contents($backupPath, $cotizadorContent);
    mostrarMensaje("Copia de seguridad creada: $backupPath", "success");
    
    // Buscar el fragmento de código que necesitamos modificar
    $searchPattern = '// Obtener precios para todos los plazos
                                            $preciosQuery = "SELECT plazo_entrega, precio FROM opcion_precios WHERE opcion_id = " . $opcion[\'id\'];
                                            $preciosResult = $conn->query($preciosQuery);
                                            $precios = [];
                                            
                                            if ($preciosResult && $preciosResult->num_rows > 0) {
                                                while ($precioRow = $preciosResult->fetch_assoc()) {
                                                    $precios[$precioRow[\'plazo_entrega\']] = $precioRow[\'precio\'];
                                                }
                                            }
                                            
                                            // Si no hay precios, usar el precio base
                                            if (empty($precios)) {
                                                $precios[$plazoSeleccionado] = $opcion[\'precio\'];
                                            }';
    
    $replacement = '// Generar precios para todos los plazos basados en el precio base
                                            $precios = [];
                                            
                                            // Plazos predefinidos y sus multiplicadores
                                            $plazosMultiplicadores = [
                                                \'30-60 días\' => 1.15,     // 15% más caro
                                                \'60-90 días\' => 1.10,     // 10% más caro
                                                \'90-120 días\' => 1.05,    // 5% más caro
                                                \'120-150 días\' => 1.00,   // precio base
                                                \'150-180 días\' => 0.95,   // 5% más barato
                                                \'180-210 días\' => 0.90    // 10% más barato
                                            ];
                                            
                                            // Generar precios para cada plazo
                                            foreach ($plazosMultiplicadores as $plazo => $multiplicador) {
                                                $precios[$plazo] = $opcion[\'precio\'] * $multiplicador;
                                            }
                                            
                                            // Si no hay plazos definidos, usar solo el precio base
                                            if (empty($precios)) {
                                                $precios[$plazoSeleccionado] = $opcion[\'precio\'];
                                            }';
    
    // Verificar si el patrón existe en el archivo
    if (strpos($cotizadorContent, $searchPattern) !== false) {
        // Reemplazar el fragmento de código
        $newContent = str_replace($searchPattern, $replacement, $cotizadorContent);
        
        // Guardar el archivo modificado
        if (file_put_contents($cotizadorPath, $newContent)) {
            mostrarMensaje("Archivo del cotizador modificado correctamente.", "success");
        } else {
            mostrarMensaje("Error al guardar el archivo modificado.", "error");
        }
    } else {
        // Si no encontramos el patrón exacto, intentamos buscar un patrón más simple
        $simplePattern = '$preciosQuery = "SELECT plazo_entrega, precio FROM opcion_precios WHERE opcion_id = " . $opcion[\'id\'];';
        
        if (strpos($cotizadorContent, $simplePattern) !== false) {
            // Crear un archivo con el código modificado
            $modifiedCotizadorPath = __DIR__ . '/sistema/cotizador_modified.php';
            
            // Reemplazar el fragmento de código
            $newContent = str_replace($simplePattern, '// Código modificado para generar precios sin usar la tabla opcion_precios
            $precios = [];
            
            // Plazos predefinidos y sus multiplicadores
            $plazosMultiplicadores = [
                \'30-60 días\' => 1.15,     // 15% más caro
                \'60-90 días\' => 1.10,     // 10% más caro
                \'90-120 días\' => 1.05,    // 5% más caro
                \'120-150 días\' => 1.00,   // precio base
                \'150-180 días\' => 0.95,   // 5% más barato
                \'180-210 días\' => 0.90    // 10% más barato
            ];', $cotizadorContent);
            
            file_put_contents($modifiedCotizadorPath, $newContent);
            mostrarMensaje("No se pudo modificar el archivo original. Se ha creado una versión modificada en: $modifiedCotizadorPath", "warning");
            mostrarMensaje("Por favor, revisa el archivo modificado y realiza los cambios manualmente si es necesario.", "warning");
        } else {
            mostrarMensaje("No se pudo encontrar el patrón de código a modificar. Es posible que el cotizador tenga una estructura diferente.", "error");
            
            // Mostrar las primeras líneas del archivo para diagnóstico
            $lines = explode("\n", $cotizadorContent);
            $firstLines = array_slice($lines, 0, 20);
            echo "<h3>Primeras líneas del archivo:</h3>";
            echo "<pre>" . htmlspecialchars(implode("\n", $firstLines)) . "...</pre>";
        }
    }
    
    echo "</div>";
    
    // Solución alternativa: crear un archivo de cotizador simplificado
    echo "<div class='section'>";
    echo "<h2>Solución alternativa</h2>";
    
    echo "<p>Si la modificación del archivo original no funciona, puedes probar esta solución alternativa:</p>";
    
    // Crear un archivo de cotizador simplificado
    $simpleCotizadorPath = __DIR__ . '/sistema/cotizador_simple.php';
    $simpleCotizadorContent = '<?php
require_once \'config.php\';
require_once \'includes/db.php\';
require_once \'includes/functions.php\';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener categorías
    $query = "SELECT * FROM categorias ORDER BY orden ASC";
    $categorias = $conn->query($query);
    
    // Plazo por defecto
    $plazoSeleccionado = "120-150 días";
    
    // Obtener plazos de entrega
    $plazos = [
        ["nombre" => "30-60 días", "descripcion" => "Entrega rápida (30-60 días)", "factor" => 1.15],
        ["nombre" => "60-90 días", "descripcion" => "Entrega estándar (60-90 días)", "factor" => 1.10],
        ["nombre" => "90-120 días", "descripcion" => "Entrega normal (90-120 días)", "factor" => 1.05],
        ["nombre" => "120-150 días", "descripcion" => "Entrega programada (120-150 días)", "factor" => 1.00],
        ["nombre" => "150-180 días", "descripcion" => "Entrega extendida (150-180 días)", "factor" => 0.95],
        ["nombre" => "180-210 días", "descripcion" => "Entrega económica (180-210 días)", "factor" => 0.90]
    ];
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador Simple</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: \'Roboto\', sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background-color: #333; color: white; padding: 20px 0; }
        h1, h2, h3 { margin-top: 0; }
        .card { background-color: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .options { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .option-item { border: 1px solid #ddd; border-radius: 5px; padding: 15px; transition: all 0.3s; }
        .option-item:hover { border-color: #4CAF50; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .option-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .option-title { font-weight: 500; }
        .option-price { color: #e74c3c; font-weight: bold; }
        .option-description { color: #666; font-size: 14px; }
        .plazo-selector { margin-bottom: 20px; }
        .plazo-selector select { padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Cotizador Simple de Ascensores</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="card">
            <h2>Seleccione un plazo de entrega</h2>
            <div class="plazo-selector">
                <select id="plazoSelect">
                    <?php foreach ($plazos as $plazo): ?>
                        <option value="<?php echo $plazo[\'nombre\']; ?>" data-factor="<?php echo $plazo[\'factor\']; ?>"
                            <?php echo ($plazo[\'nombre\'] === $plazoSeleccionado) ? \'selected\' : \'\'; ?>>
                            <?php echo $plazo[\'nombre\']; ?> (<?php echo ($plazo[\'factor\'] > 1 ? \'+\' : \'\') . (($plazo[\'factor\'] - 1) * 100) . \'%\'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if ($categorias && $categorias->num_rows > 0): ?>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <div class="card">
                    <h2><?php echo htmlspecialchars($categoria[\'nombre\']); ?></h2>
                    <?php if (!empty($categoria[\'descripcion\'])): ?>
                        <p><?php echo htmlspecialchars($categoria[\'descripcion\']); ?></p>
                    <?php endif; ?>
                    
                    <div class="options">
                        <?php
                        // Obtener opciones para esta categoría
                        $query = "SELECT * FROM opciones WHERE categoria_id = " . $categoria[\'id\'] . " ORDER BY orden ASC";
                        $opciones = $conn->query($query);
                        
                        if ($opciones && $opciones->num_rows > 0):
                            while ($opcion = $opciones->fetch_assoc()):
                                $precioBase = $opcion[\'precio\'];
                        ?>
                            <div class="option-item" data-precio-base="<?php echo $precioBase; ?>">
                                <div class="option-header">
                                    <div class="option-title"><?php echo htmlspecialchars($opcion[\'nombre\']); ?></div>
                                    <div class="option-price">$<?php echo number_format($precioBase, 2, \',\', \'.\'); ?></div>
                                </div>
                                <?php if (!empty($opcion[\'descripcion\'])): ?>
                                    <div class="option-description"><?php echo htmlspecialchars($opcion[\'descripcion\']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <p>No hay opciones disponibles para esta categoría.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                <h2>No hay categorías disponibles</h2>
                <p>No se encontraron categorías en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener(\'DOMContentLoaded\', function() {
            const plazoSelect = document.getElementById(\'plazoSelect\');
            const optionItems = document.querySelectorAll(\'.option-item\');
            
            // Función para actualizar precios según el plazo seleccionado
            function actualizarPrecios() {
                const selectedOption = plazoSelect.options[plazoSelect.selectedIndex];
                const factor = parseFloat(selectedOption.dataset.factor);
                
                optionItems.forEach(item => {
                    const precioBase = parseFloat(item.dataset.precioBase);
                    const precioAjustado = precioBase * factor;
                    const precioElement = item.querySelector(\'.option-price\');
                    
                    precioElement.textContent = \'$\' + precioAjustado.toLocaleString(\'es-AR\', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).replace(\'.\', \',\');
                });
            }
            
            // Actualizar precios al cambiar el plazo
            plazoSelect.addEventListener(\'change\', actualizarPrecios);
            
            // Actualizar precios iniciales
            actualizarPrecios();
        });
    </script>
</body>
</html>';
    
    file_put_contents($simpleCotizadorPath, $simpleCotizadorContent);
    mostrarMensaje("Se ha creado un cotizador simplificado en: $simpleCotizadorPath", "success");
    
    echo "<p><a href='sistema/cotizador_simple.php' class='btn'>Probar Cotizador Simplificado</a></p>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>Próximos pasos</h2>";
    echo "<p>Intenta acceder al cotizador original para ver si las modificaciones han solucionado el problema:</p>";
    echo "<p><a href='sistema/cotizador.php' class='btn'>Ir al Cotizador Original</a></p>";
    echo "<p>Si el cotizador original sigue sin funcionar, utiliza el cotizador simplificado que hemos creado.</p>";
    echo "</div>";
    
    echo "</body></html>";
    
} catch (Exception $e) {
    mostrarMensaje("Error: " . $e->getMessage(), "error");
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
