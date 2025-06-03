<?php
// Mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Definir directorios
$directorios = [
    __DIR__ . '/sistema/uploads',
    __DIR__ . '/sistema/uploads/xls'
];

// Verificar y crear directorios
echo "<h1>Verificación de directorios</h1>";
echo "<pre>";

foreach ($directorios as $dir) {
    echo "Verificando: $dir\n";
    
    if (file_exists($dir)) {
        echo "  ✓ El directorio ya existe\n";
        echo "  - Permisos: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
    } else {
        echo "  ✗ El directorio no existe, intentando crear...\n";
        
        if (mkdir($dir, 0777, true)) {
            echo "  ✓ Directorio creado correctamente\n";
            echo "  - Permisos: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
        } else {
            echo "  ✗ Error al crear el directorio\n";
            echo "  - Último error: " . error_get_last()['message'] . "\n";
        }
    }
    
    // Intentar crear un archivo de prueba
    $testFile = $dir . '/test.txt';
    if (file_put_contents($testFile, 'Test de escritura: ' . date('Y-m-d H:i:s'))) {
        echo "  ✓ Prueba de escritura exitosa\n";
        unlink($testFile); // Eliminar archivo de prueba
    } else {
        echo "  ✗ Error en prueba de escritura\n";
        echo "  - Último error: " . error_get_last()['message'] . "\n";
    }
    
    echo "\n";
}

// Verificar configuración del PHP para cargas
echo "Configuración PHP para cargas de archivos:\n";
echo "upload_max_filesize = " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size = " . ini_get('post_max_size') . "\n";
echo "max_execution_time = " . ini_get('max_execution_time') . "\n";
echo "max_input_time = " . ini_get('max_input_time') . "\n";
echo "memory_limit = " . ini_get('memory_limit') . "\n";
echo "file_uploads = " . (ini_get('file_uploads') ? 'On' : 'Off') . "\n";
echo "Usuario PHP: " . get_current_user() . "\n";
echo "</pre>";

echo "<p><a href='test_upload.php'>Ir a la página de prueba de carga</a></p>";
echo "<p><a href='sistema/admin/login.php'>Ir al panel de administración</a></p>";
?> 