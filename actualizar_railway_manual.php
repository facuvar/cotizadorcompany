<?php
/**
 * Script para actualizar manualmente archivos críticos en Railway
 * Esto permite superar problemas de despliegue automático desde GitHub
 */

// Configuración de seguridad - cambia esta clave
$clave_acceso = 'company2024';
$clave_proporcionada = $_POST['clave'] ?? $_GET['clave'] ?? '';
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// Verificación básica de seguridad
$acceso_permitido = ($clave_proporcionada === $clave_acceso);

// Header y estilos
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Actualización Manual Railway</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; white-space: pre-wrap; overflow-x: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type='text'], input[type='password'] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .btn-warning { background: #ff9800; }
        .btn-warning:hover { background: #e68a00; }
        textarea { width: 100%; height: 300px; font-family: monospace; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 1px solid #ddd; }
        .tab { padding: 10px 15px; cursor: pointer; }
        .tab.active { border-bottom: 2px solid #4CAF50; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Actualización Manual de Archivos en Railway</h1>";

// Formulario de acceso si no está autenticado
if (!$acceso_permitido) {
    echo "
        <div class='info'>Este script permite actualizar manualmente archivos críticos cuando el despliegue automático desde GitHub no funciona correctamente.</div>
        
        <form method='post' action=''>
            <div class='form-group'>
                <label for='clave'>Clave de acceso:</label>
                <input type='password' id='clave' name='clave' required>
            </div>
            <button type='submit'>Acceder</button>
        </form>";
} else {
    // Panel principal cuando está autenticado
    echo "
        <div class='success'>✅ Acceso verificado</div>
        
        <div class='tabs'>
            <div class='tab active' onclick='cambiarTab(\"tab-cotizador\")'>Cotizador</div>
            <div class='tab' onclick='cambiarTab(\"tab-railway\")'>Base de Datos</div>
            <div class='tab' onclick='cambiarTab(\"tab-sql\")'>Ejecutar SQL</div>
            <div class='tab' onclick='cambiarTab(\"tab-diagnostico\")'>Diagnóstico</div>
        </div>";
    
    // TAB 1: Actualizar Cotizador
    echo "<div id='tab-cotizador' class='tab-content active'>";
    
    if ($accion === 'actualizar_cotizador') {
        // Procesar actualización del cotizador
        $contenido_nuevo = $_POST['contenido_cotizador'] ?? '';
        
        if (!empty($contenido_nuevo)) {
            $resultado = file_put_contents('cotizador.php', $contenido_nuevo);
            
            if ($resultado !== false) {
                echo "<div class='success'>✅ El archivo cotizador.php ha sido actualizado exitosamente (" . $resultado . " bytes escritos)</div>";
            } else {
                echo "<div class='error'>❌ Error al escribir el archivo cotizador.php. Verifica los permisos de escritura.</div>";
            }
        } else {
            echo "<div class='error'>❌ El contenido está vacío. No se realizó ninguna actualización.</div>";
        }
    }
    
    // Obtener contenido actual del cotizador
    $contenido_actual = file_exists('cotizador.php') ? file_get_contents('cotizador.php') : '';
    
    // Mostrar formulario para actualizar cotizador
    echo "
        <h2>📄 Actualizar Cotizador</h2>
        <div class='info'>Aquí puedes revisar y actualizar el archivo cotizador.php manualmente.</div>
        
        <form method='post' action='?clave=$clave_acceso&accion=actualizar_cotizador'>
            <div class='form-group'>
                <label for='contenido_cotizador'>Contenido del archivo cotizador.php:</label>
                <textarea id='contenido_cotizador' name='contenido_cotizador'>$contenido_actual</textarea>
            </div>
            <div class='form-group'>
                <button type='submit'>Actualizar Cotizador</button>
            </div>
        </form>
        
        <div class='warning'>
            ⚠️ Importante: Para implementar los cambios necesarios, asegúrate de que el archivo contenga:
            <ul>
                <li>El símbolo 'AR$' en lugar de '€' para los precios</li>
                <li>Los ajustes de z-index para hacer los checkboxes clickeables</li>
                <li>No modifiques otras partes del código a menos que sepas lo que estás haciendo</li>
            </ul>
        </div>
    ";
    echo "</div>";
    
    // TAB 2: Actualizar Base de Datos
    echo "<div id='tab-railway' class='tab-content'>";
    
    if ($accion === 'actualizar_db') {
        // Incluir y ejecutar el script de actualización de DB
        echo "<div class='info'>Ejecutando actualización de base de datos...</div>";
        ob_start();
        include_once 'actualizar_db_railway.php';
        $resultado_db = ob_get_clean();
        echo "<div class='code'>$resultado_db</div>";
    }
    
    echo "
        <h2>🗄️ Actualizar Base de Datos</h2>
        <div class='info'>Aquí puedes actualizar la configuración de la base de datos para usar Pesos Argentinos (ARS).</div>
        
        <form method='post' action='?clave=$clave_acceso&accion=actualizar_db'>
            <div class='form-group'>
                <button type='submit'>Ejecutar Actualización de Base de Datos</button>
            </div>
        </form>
    ";
    echo "</div>";
    
    // TAB 3: Ejecutar SQL
    echo "<div id='tab-sql' class='tab-content'>";
    
    if ($accion === 'ejecutar_sql') {
        // Procesar ejecución de SQL
        $sql_query = $_POST['sql_query'] ?? '';
        
        if (!empty($sql_query)) {
            echo "<div class='info'>Ejecutando SQL...</div>";
            
            try {
                require_once 'sistema/config.php';
                
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Dividir las consultas por punto y coma
                $queries = explode(';', $sql_query);
                $resultados = [];
                
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (empty($query)) continue;
                    
                    try {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $count = $stmt->rowCount();
                        $resultados[] = "✅ Consulta ejecutada: $count filas afectadas";
                    } catch (PDOException $e) {
                        $resultados[] = "❌ Error en consulta: " . $e->getMessage() . "\nConsulta: $query";
                    }
                }
                
                echo "<div class='success'>SQL ejecutado con los siguientes resultados:</div>";
                echo "<div class='code'>" . implode("\n", $resultados) . "</div>";
                
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='error'>❌ No se proporcionó ninguna consulta SQL.</div>";
        }
    }
    
    // Cargar archivo SQL si se especifica
    $sql_file = $_GET['sql_file'] ?? '';
    $sql_content = '';
    
    if (!empty($sql_file) && file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        echo "<div class='info'>Archivo SQL cargado: $sql_file</div>";
    }
    
    echo "
        <h2>🗄️ Ejecutar Script SQL</h2>
        <div class='info'>Aquí puedes ejecutar consultas SQL directamente en la base de datos de Railway.</div>
        <div class='warning'>⚠️ Ten cuidado con las consultas que ejecutas. Las operaciones son irreversibles.</div>
        
        <form method='post' action='?clave=$clave_acceso&accion=ejecutar_sql'>
            <div class='form-group'>
                <label for='sql_query'>SQL a ejecutar:</label>
                <textarea id='sql_query' name='sql_query' style='height: 400px;'>$sql_content</textarea>
            </div>
            <div class='form-group'>
                <button type='submit' class='btn-warning'>Ejecutar SQL</button>
            </div>
        </form>
    ";
    echo "</div>";
    
    // TAB 4: Diagnóstico
    echo "<div id='tab-diagnostico' class='tab-content'>";
    
    if ($accion === 'diagnostico') {
        // Incluir y ejecutar el script de diagnóstico
        echo "<div class='info'>Ejecutando diagnóstico del sistema...</div>";
        ob_start();
        include_once 'deploy_to_railway.php';
        $resultado_diagnostico = ob_get_clean();
        echo "<div class='code'>$resultado_diagnostico</div>";
    }
    
    echo "
        <h2>🔍 Diagnóstico del Sistema</h2>
        <div class='info'>Ejecuta un diagnóstico completo para verificar el estado actual del sistema.</div>
        
        <form method='post' action='?clave=$clave_acceso&accion=diagnostico'>
            <div class='form-group'>
                <button type='submit'>Ejecutar Diagnóstico</button>
            </div>
        </form>
    ";
    echo "</div>";
}

// Código JavaScript para las pestañas
echo "
    <script>
        function cambiarTab(tabId) {
            // Ocultar todos los contenidos de pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Desactivar todas las pestañas
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activar la pestaña seleccionada
            document.getElementById(tabId).classList.add('active');
            
            // Encontrar y activar el botón de la pestaña
            const tabs = Array.from(document.querySelectorAll('.tab'));
            const index = tabs.findIndex(tab => tab.getAttribute('onclick').includes(tabId));
            if (index >= 0) {
                tabs[index].classList.add('active');
            }
        }
    </script>
";

echo "
    </div>
</body>
</html>";
?> 