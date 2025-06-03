<?php
/**
 * Script para corregir la tabla fuente_datos
 * Esta tabla debe tener al menos un registro para que el cotizador funcione
 */

echo "<h1>🔧 CORREGIR TABLA FUENTE_DATOS</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

echo "<div class='container'>";

try {
    // Cargar configuración
    require_once 'sistema/config.php';
    require_once 'sistema/includes/db.php';
    
    echo "<div class='success'>✅ Configuración cargada</div>";
    
    // Obtener conexión
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='success'>✅ Conexión establecida</div>";
    
    // Verificar estado actual de fuente_datos
    $query = "SELECT COUNT(*) as total FROM fuente_datos";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $totalActual = $row['total'];
    
    echo "<div class='info'>📊 Registros actuales en fuente_datos: " . $totalActual . "</div>";
    
    if ($totalActual == 0) {
        echo "<div class='warning'>⚠️ La tabla fuente_datos está vacía. Insertando registro inicial...</div>";
        
        // Insertar registro inicial
        $insertQuery = "INSERT INTO fuente_datos (archivo_origen, fecha_actualizacion, descripcion) VALUES (?, NOW(), ?)";
        $stmt = $conn->prepare($insertQuery);
        
        $archivo = 'Datos iniciales del sistema';
        $descripcion = 'Registro inicial creado automáticamente para permitir el funcionamiento del cotizador';
        
        $stmt->bind_param('ss', $archivo, $descripcion);
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Registro inicial insertado correctamente</div>";
            echo "<div class='info'>ID del nuevo registro: " . $conn->insert_id . "</div>";
        } else {
            echo "<div class='error'>❌ Error insertando registro: " . $stmt->error . "</div>";
        }
        
        $stmt->close();
        
    } else {
        echo "<div class='success'>✅ La tabla fuente_datos ya tiene datos</div>";
        
        // Mostrar los registros existentes
        $query = "SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC";
        $result = $conn->query($query);
        
        echo "<div class='info'><strong>Registros existentes:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "• ID: " . $row['id'] . " - " . $row['archivo_origen'] . " (" . $row['fecha_actualizacion'] . ")<br>";
        }
        echo "</div>";
    }
    
    // Verificar otras tablas importantes
    echo "<h2>📋 Verificación de otras tablas</h2>";
    
    $tablasImportantes = ['categorias', 'opciones', 'plazos_entrega'];
    
    foreach ($tablasImportantes as $tabla) {
        $query = "SELECT COUNT(*) as total FROM " . $tabla;
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $total = $row['total'];
        
        if ($total > 0) {
            echo "<div class='success'>✅ Tabla " . $tabla . ": " . $total . " registros</div>";
        } else {
            echo "<div class='error'>❌ Tabla " . $tabla . ": vacía</div>";
        }
    }
    
    echo "<h2>🧪 Probar cotizador</h2>";
    echo "<div class='info'>";
    echo "Ahora puedes probar el cotizador:<br>";
    echo "<a href='sistema/cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🚀 Abrir Cotizador</a><br>";
    echo "<a href='admin/' target='_blank' style='color: blue; text-decoration: underline;'>👤 Abrir Panel Admin</a><br>";
    echo "<a href='debug_cotizador.php' target='_blank' style='color: blue; text-decoration: underline;'>🔍 Diagnóstico Completo</a><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div>";
?> 