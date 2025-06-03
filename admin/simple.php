<?php
// Admin simplificado para pruebas
echo "<!DOCTYPE html>";
echo "<html><head><title>Admin Test</title></head><body>";
echo "<h1>🧪 ADMIN DE PRUEBA FUNCIONANDO</h1>";
echo "<p>Si ves esto, PHP está funcionando correctamente.</p>";
echo "<p>Fecha: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Directorio: " . __DIR__ . "</p>";

// Verificar config
if (file_exists(__DIR__ . "/../sistema/config.php")) {
    echo "<p>✅ Config existe</p>";
    require_once __DIR__ . "/../sistema/config.php";
    echo "<p>✅ Config cargado</p>";
    echo "<p>Usuario admin: " . (defined("ADMIN_USER") ? ADMIN_USER : "No definido") . "</p>";
} else {
    echo "<p>❌ Config no existe</p>";
}

echo "<hr>";
echo "<a href=\"../\">🏠 Volver al inicio</a> | ";
echo "<a href=\"../cotizador.php\">🚀 Cotizador</a> | ";
echo "<a href=\"../debug_admin.php\">🔍 Debug Admin</a>";
echo "</body></html>";
?>