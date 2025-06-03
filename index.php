<?php
// Archivo principal para Railway
// Redirige automáticamente al sistema

// Detectar si estamos en Railway
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']);

if ($isRailway) {
    // En Railway, servir directamente el sistema
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Si es la raíz, mostrar la página principal
    if ($requestUri === '/' || $requestUri === '/index.php') {
        include 'index.html';
        exit;
    }
    
    // Si es una ruta del sistema, incluir el archivo correspondiente
    if (strpos($requestUri, '/sistema/') === 0) {
        $file = '.' . $requestUri;
        if (file_exists($file)) {
            include $file;
            exit;
        }
    }
    
    // Si es una ruta del admin, incluir el archivo correspondiente
    if (strpos($requestUri, '/admin/') === 0) {
        $file = '.' . $requestUri;
        if (file_exists($file)) {
            include $file;
            exit;
        }
    }
    
    // Para otros archivos estáticos
    $file = '.' . $requestUri;
    if (file_exists($file)) {
        // Determinar el tipo de contenido
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'html' => 'text/html',
            'pdf' => 'application/pdf'
        ];
        
        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }
        
        readfile($file);
        exit;
    }
    
    // Si no se encuentra el archivo, mostrar 404
    http_response_code(404);
    echo "<h1>404 - Archivo no encontrado</h1>";
    echo "<p>La ruta solicitada no existe: " . htmlspecialchars($requestUri) . "</p>";
    echo "<p><a href='/'>Volver al inicio</a></p>";
    
} else {
    // En desarrollo local, redirigir a index.html
    header('Location: index.html');
    exit;
}
?> 