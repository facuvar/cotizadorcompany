<?php
/**
 * Script para importar y estructurar datos desde xls-referencia.xlsx
 * Reorganiza la base de datos según la estructura correcta del negocio
 */

echo "<h1>📊 IMPORTAR ESTRUCTURA DESDE EXCEL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; }
    .btn { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; }
    .btn:hover { background: #45a049; }
    .btn-danger { background: #f44336; }
    .btn-danger:hover { background: #da190b; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .category-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

echo "<div class='container'>";

// Cargar configuración
echo "<h2>📋 Paso 1: Cargar configuración</h2>";

try {
    require_once 'sistema/config.php';
    echo "<div class='success'>✅ Configuración cargada correctamente</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error cargando configuración: " . $e->getMessage() . "</div>";
    exit;
}

// Conectar a la base de datos
echo "<h2>🔌 Paso 2: Conectar a la base de datos</h2>";

try {
    require_once 'sistema/includes/db.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($conn && !$conn->connect_error) {
        echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
        echo "<div class='info'>Versión del servidor: " . $conn->server_info . "</div>";
    } else {
        echo "<div class='error'>❌ Error de conexión: " . ($conn ? $conn->connect_error : 'Conexión nula') . "</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error conectando: " . $e->getMessage() . "</div>";
    exit;
}

// Verificar archivo Excel
echo "<h2>📄 Paso 3: Verificar archivo Excel</h2>";

$excelFile = 'uploads/xls-referencia.xlsx';
if (file_exists($excelFile)) {
    $fileSize = filesize($excelFile);
    echo "<div class='success'>✅ Archivo encontrado: " . basename($excelFile) . " (" . number_format($fileSize) . " bytes)</div>";
} else {
    echo "<div class='error'>❌ Archivo no encontrado: " . $excelFile . "</div>";
    echo "<div class='warning'>Por favor, asegúrate de que el archivo xls-referencia.xlsx esté en la carpeta uploads/</div>";
    exit;
}

// Mostrar estructura actual
echo "<h2>📊 Paso 4: Estructura actual de la base de datos</h2>";

$tables = ['categorias', 'opciones', 'plazos_entrega'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<div class='info'>• Tabla '$table': $count registros</div>";
    }
}

// Mostrar categorías actuales
$result = $conn->query("SELECT * FROM categorias ORDER BY orden ASC");
if ($result && $result->num_rows > 0) {
    echo "<div class='category-section'>";
    echo "<h3>Categorías actuales:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion'] ?? '') . "</td>";
        echo "<td>" . ($row['orden'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// Botones de acción
echo "<h2>🔧 Paso 5: Acciones disponibles</h2>";

echo "<div class='info'>";
echo "<strong>Estructura objetivo según Excel:</strong><br>";
echo "1. <strong>ASCENSORES ELECTROMECÁNICOS</strong> - Modelos principales + Adicionales 1A-30A<br>";
echo "2. <strong>ASCENSORES OPCIÓN GEARLESS</strong> - Modelos principales + Adicionales 32A-55A<br>";
echo "3. <strong>MONTACARGAS</strong> - Modelos principales + Adicionales 57A-59A<br>";
echo "4. <strong>ADICIONALES</strong> - Todos a precio 270 días<br>";
echo "5. <strong>FORMAS DE PAGO</strong> - Con descuentos específicos<br>";
echo "</div>";

echo "<form method='post' style='margin: 20px 0;'>";
echo "<button type='submit' name='restructure_db' class='btn'>🏗️ Reestructurar Base de Datos</button>";
echo "<button type='submit' name='import_excel' class='btn'>📊 Importar desde Excel</button>";
echo "<button type='submit' name='reset_db' class='btn btn-danger'>🗑️ Limpiar y Empezar de Nuevo</button>";
echo "</form>";

// Procesar reestructuración
if (isset($_POST['restructure_db'])) {
    echo "<h2>🏗️ Reestructurando base de datos</h2>";
    
    try {
        // Limpiar datos existentes
        $conn->query("DELETE FROM opciones");
        $conn->query("DELETE FROM categorias");
        echo "<div class='success'>✅ Datos existentes limpiados</div>";
        
        // Crear categorías según estructura del Excel
        $categorias = [
            [
                'nombre' => 'ASCENSORES ELECTROMECÁNICOS',
                'descripcion' => 'Ascensores electromecánicos con adicionales 1A-30A',
                'orden' => 1
            ],
            [
                'nombre' => 'ASCENSORES OPCIÓN GEARLESS',
                'descripcion' => 'Ascensores opción gearless con adicionales 32A-55A',
                'orden' => 2
            ],
            [
                'nombre' => 'MONTACARGAS',
                'descripcion' => 'Montacargas con adicionales 57A-59A',
                'orden' => 3
            ],
            [
                'nombre' => 'ADICIONALES',
                'descripcion' => 'Opciones adicionales (todos a precio 270 días)',
                'orden' => 4
            ],
            [
                'nombre' => 'FORMAS DE PAGO',
                'descripcion' => 'Descuentos según forma de pago',
                'orden' => 5
            ]
        ];
        
        $categoriaIds = [];
        foreach ($categorias as $cat) {
            $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $cat['nombre'], $cat['descripcion'], $cat['orden']);
            
            if ($stmt->execute()) {
                $categoriaId = $conn->insert_id;
                $categoriaIds[$cat['nombre']] = $categoriaId;
                echo "<div class='success'>✅ Categoría creada: " . $cat['nombre'] . " (ID: $categoriaId)</div>";
            } else {
                echo "<div class='error'>❌ Error creando categoría " . $cat['nombre'] . ": " . $stmt->error . "</div>";
            }
        }
        
        // Crear formas de pago
        if (isset($categoriaIds['FORMAS DE PAGO'])) {
            $formasPago = [
                ['nombre' => 'Efectivo X', 'descripcion' => 'Pago en efectivo', 'precio' => -8.00, 'orden' => 1],
                ['nombre' => 'Transferencia', 'descripcion' => 'Transferencia bancaria', 'precio' => -5.00, 'orden' => 2],
                ['nombre' => 'Cheques electrónicos (30-45)', 'descripcion' => 'Cheques electrónicos a 30-45 días', 'precio' => -2.00, 'orden' => 3],
                ['nombre' => 'Financiación 6 cheques (0-15-30-45-60-90)', 'descripcion' => 'Financiación en 6 cheques', 'precio' => 0.00, 'orden' => 4],
                ['nombre' => 'Mejora de presupuesto', 'descripcion' => 'Mejora de presupuesto', 'precio' => -5.00, 'orden' => 5]
            ];
            
            foreach ($formasPago as $forma) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['FORMAS DE PAGO'], $forma['nombre'], $forma['descripcion'], $forma['precio'], $forma['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Forma de pago creada: " . $forma['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando forma de pago: " . $stmt->error . "</div>";
                }
            }
        }
        
        // Crear opciones de ejemplo para ascensores
        if (isset($categoriaIds['ASCENSORES ELECTROMECÁNICOS'])) {
            $ascensores = [
                ['nombre' => 'Ascensor 4 personas - 4 paradas', 'descripcion' => 'Ascensor electromecánico 4 personas', 'precio' => 2500000.00, 'orden' => 1],
                ['nombre' => 'Ascensor 6 personas - 4 paradas', 'descripcion' => 'Ascensor electromecánico 6 personas', 'precio' => 3000000.00, 'orden' => 2],
                ['nombre' => 'Ascensor 4 personas - 6 paradas', 'descripcion' => 'Ascensor electromecánico 4 personas, 6 paradas', 'precio' => 3200000.00, 'orden' => 3]
            ];
            
            foreach ($ascensores as $ascensor) {
                $stmt = $conn->prepare("INSERT INTO opciones (categoria_id, nombre, descripcion, precio, orden, es_obligatorio) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->bind_param('issdi', $categoriaIds['ASCENSORES ELECTROMECÁNICOS'], $ascensor['nombre'], $ascensor['descripcion'], $ascensor['precio'], $ascensor['orden']);
                
                if ($stmt->execute()) {
                    echo "<div class='success'>✅ Ascensor creado: " . $ascensor['nombre'] . "</div>";
                } else {
                    echo "<div class='error'>❌ Error creando ascensor: " . $stmt->error . "</div>";
                }
            }
        }
        
        echo "<div class='success'>🎉 Reestructuración completada exitosamente</div>";
        echo "<div class='info'>🔄 Recargando página para mostrar cambios...</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 3000);</script>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en la reestructuración: " . $e->getMessage() . "</div>";
    }
}

// Procesar reset
if (isset($_POST['reset_db'])) {
    echo "<h2>🗑️ Limpiando base de datos</h2>";
    
    try {
        $conn->query("DELETE FROM opciones");
        $conn->query("DELETE FROM categorias");
        $conn->query("ALTER TABLE categorias AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE opciones AUTO_INCREMENT = 1");
        
        echo "<div class='success'>✅ Base de datos limpiada completamente</div>";
        echo "<div class='info'>🔄 Recargando página...</div>";
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error limpiando base de datos: " . $e->getMessage() . "</div>";
    }
}

// Enlaces de prueba
echo "<h2>🔗 Enlaces útiles</h2>";
echo "<div class='info'>";
echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Probar Cotizador</a><br>";
echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>🔐 Panel Admin</a><br>";
echo "<a href='railway_debug.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico Railway</a><br>";
echo "</div>";

echo "</div>";
?> 