<?php
/**
 * Script de ANÁLISIS del archivo SQL
 * Para entender exactamente qué contiene y por qué no se procesan las consultas
 */

echo "<h1>🔍 ANÁLISIS DEL ARCHIVO SQL</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; border-radius: 5px; }
    .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 11px; max-height: 500px; overflow-y: auto; white-space: pre-wrap; }
    .hex { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 10px; }
</style>";

echo "<div class='container'>";

// Leer archivo SQL
echo "<div class='step'>";
echo "<h2>📄 Análisis del Archivo SQL</h2>";

$sqlFile = 'railway_import.sql';
if (!file_exists($sqlFile)) {
    echo "<div class='error'>❌ No se encontró el archivo $sqlFile</div>";
    exit;
}

$sqlContent = file_get_contents($sqlFile);
$fileSize = filesize($sqlFile);
echo "<div class='info'>📄 Archivo: " . number_format($fileSize) . " bytes</div>";

// Mostrar primeros 500 caracteres en hexadecimal
echo "<div class='info'>🔢 Primeros 100 bytes en hexadecimal:</div>";
echo "<div class='hex'>";
$hexDump = '';
for ($i = 0; $i < min(100, strlen($sqlContent)); $i++) {
    $hexDump .= sprintf('%02X ', ord($sqlContent[$i]));
    if (($i + 1) % 16 == 0) $hexDump .= "\n";
}
echo $hexDump;
echo "</div>";

// Mostrar primeras 50 líneas tal como están
$lines = explode("\n", $sqlContent);
echo "<div class='info'>📄 Total de líneas: " . count($lines) . "</div>";
echo "<div class='info'>📝 Primeras 50 líneas del archivo (tal como están):</div>";
echo "<div class='debug'>";
for ($i = 0; $i < min(50, count($lines)); $i++) {
    $lineNum = $i + 1;
    $line = $lines[$i];
    $displayLine = htmlspecialchars($line);
    $length = strlen($line);
    echo sprintf("%3d: [%3d chars] %s\n", $lineNum, $length, $displayLine);
}
echo "</div>";

// Buscar líneas que contengan palabras clave
echo "<div class='info'>🔍 Buscando líneas con palabras clave:</div>";
echo "<div class='debug'>";

$keywords = ['CREATE TABLE', 'INSERT INTO', 'DROP TABLE', 'VALUES'];
$foundLines = [];

foreach ($lines as $lineNum => $line) {
    foreach ($keywords as $keyword) {
        if (stripos($line, $keyword) !== false) {
            $foundLines[] = [
                'line' => $lineNum + 1,
                'keyword' => $keyword,
                'content' => trim($line)
            ];
        }
    }
}

if (!empty($foundLines)) {
    foreach ($foundLines as $found) {
        echo "Línea {$found['line']} [{$found['keyword']}]: " . htmlspecialchars(substr($found['content'], 0, 200)) . "\n";
    }
} else {
    echo "❌ No se encontraron líneas con palabras clave SQL\n";
}
echo "</div>";

// Analizar caracteres especiales
echo "<div class='info'>🔤 Análisis de caracteres especiales en las primeras 10 líneas:</div>";
echo "<div class='debug'>";
for ($i = 0; $i < min(10, count($lines)); $i++) {
    $line = $lines[$i];
    $lineNum = $i + 1;
    echo "Línea $lineNum:\n";
    echo "  Contenido: " . htmlspecialchars($line) . "\n";
    echo "  Longitud: " . strlen($line) . " caracteres\n";
    echo "  Primer carácter: " . (strlen($line) > 0 ? sprintf("'%s' (ASCII %d)", $line[0], ord($line[0])) : "vacía") . "\n";
    echo "  Último carácter: " . (strlen($line) > 0 ? sprintf("'%s' (ASCII %d)", $line[strlen($line)-1], ord($line[strlen($line)-1])) : "vacía") . "\n";
    echo "\n";
}
echo "</div>";

// Buscar patrones específicos de MariaDB
echo "<div class='info'>🔍 Buscando patrones específicos de MariaDB:</div>";
echo "<div class='debug'>";

$patterns = [
    '/CREATE TABLE.*`.*`/i' => 'CREATE TABLE con backticks',
    '/INSERT INTO.*`.*`/i' => 'INSERT INTO con backticks',
    '/DROP TABLE.*`.*`/i' => 'DROP TABLE con backticks',
    '/ENGINE=\w+/i' => 'Definición de ENGINE',
    '/CHARSET=\w+/i' => 'Definición de CHARSET',
    '/AUTO_INCREMENT=\d+/i' => 'AUTO_INCREMENT',
    '/\/\*!\d+.*\*\//i' => 'Comentarios MySQL específicos'
];

foreach ($patterns as $pattern => $description) {
    preg_match_all($pattern, $sqlContent, $matches);
    echo "$description: " . count($matches[0]) . " coincidencias\n";
    if (count($matches[0]) > 0) {
        echo "  Ejemplo: " . htmlspecialchars(substr($matches[0][0], 0, 100)) . "...\n";
    }
    echo "\n";
}
echo "</div>";

// Intentar dividir por punto y coma y analizar
echo "<div class='info'>📊 Análisis dividiendo por punto y coma:</div>";
echo "<div class='debug'>";

$statements = explode(';', $sqlContent);
echo "Total de segmentos divididos por ';': " . count($statements) . "\n\n";

$validStatements = 0;
foreach ($statements as $index => $statement) {
    $statement = trim($statement);
    if (!empty($statement) && !preg_match('/^--/', $statement) && !preg_match('/^\/\*/', $statement)) {
        $validStatements++;
        if ($validStatements <= 5) {
            echo "Segmento válido $validStatements:\n";
            echo htmlspecialchars(substr($statement, 0, 300)) . "...\n\n";
        }
    }
}
echo "Total de segmentos válidos: $validStatements\n";
echo "</div>";

echo "</div>";

echo "</div>"; // container
?> 