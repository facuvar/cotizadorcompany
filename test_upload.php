<?php
// Configurar visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Directorio de uploads
$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Procesar la carga de archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Información de depuración:</h2>";
    
    // Verificar si se subió un archivo
    if (isset($_FILES['testFile'])) {
        echo "<pre>";
        echo "Archivo recibido:\n";
        print_r($_FILES['testFile']);
        echo "\n\n";
        
        // Intentar mover el archivo
        $targetFile = $uploadDir . '/' . basename($_FILES['testFile']['name']);
        if (move_uploaded_file($_FILES['testFile']['tmp_name'], $targetFile)) {
            echo "Archivo subido correctamente a: " . $targetFile . "\n";
            
            // Mostrar información del archivo
            echo "Tamaño: " . filesize($targetFile) . " bytes\n";
            echo "Tipo MIME detectado: " . mime_content_type($targetFile) . "\n";
            
            // Listar el contenido del directorio
            echo "\nContenido del directorio de uploads:\n";
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "- $file (" . filesize($uploadDir . '/' . $file) . " bytes)\n";
                }
            }
        } else {
            echo "Error al mover el archivo.\n";
            echo "Estado de permisos del directorio destino: \n";
            echo "Directorio: $uploadDir\n";
            echo "Existe: " . (file_exists($uploadDir) ? 'Sí' : 'No') . "\n";
            echo "Permisos: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
            echo "Usuario actual: " . get_current_user() . "\n";
        }
        echo "</pre>";
    } else {
        echo "No se recibió ningún archivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Carga de Archivos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        pre { background-color: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Test de Carga de Archivos Excel</h1>
    <form method="post" enctype="multipart/form-data">
        <p>Selecciona un archivo para probar la carga:</p>
        <input type="file" name="testFile" accept=".xlsx,.xls">
        <br><br>
        <button type="submit">Subir Archivo</button>
    </form>
    
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Por favor, selecciona un archivo primero.');
        }
    });
    </script>
</body>
</html> 