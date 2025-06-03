<?php
// Contraseña original: admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Generador de contraseña\n";
echo "Contraseña: $password\n";
echo "Hash generado: $hash\n";

// Obtener el contenido actual del archivo config.php
$configFile = 'sistema/config.php';
$content = file_get_contents($configFile);

// Buscar la línea con la contraseña actual
$pattern = "/define\('ADMIN_PASS',\s*'[^']+'\);/";
$replacement = "define('ADMIN_PASS', '$hash'); // admin123";

// Reemplazar la línea
$newContent = preg_replace($pattern, $replacement, $content);

// Escribir el nuevo contenido al archivo (comentado para seguridad)
if (file_put_contents($configFile, $newContent)) {
    echo "¡Archivo config.php actualizado correctamente!\n";
} else {
    echo "Error al actualizar el archivo config.php\n";
}

echo "Visita sistema/admin/login.php para iniciar sesión\n";
?> 