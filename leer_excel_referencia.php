<?php
// Script para leer el archivo Excel de referencia
require_once 'vendor/autoload.php';
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo == 'error' ? 'red' : ($tipo == 'success' ? 'green' : 'blue');
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid $color; border-radius: 5px; color: $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Cargar el archivo Excel
$excelFile = 'xls/cotizador-xls.xlsx';

if (!file_exists($excelFile)) {
    die("El archivo $excelFile no existe");
}

try {
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(false); // Importante: leer fórmulas
    $spreadsheet = $reader->load($excelFile);
    
    // Obtener las hojas disponibles
    $sheetNames = $spreadsheet->getSheetNames();
    
    echo "<h2>Hojas disponibles en el archivo:</h2>";
    echo "<ul>";
    foreach ($sheetNames as $sheetName) {
        echo "<li>$sheetName</li>";
    }
    echo "</ul>";
    
    // Verificar si existe la hoja ADICIONALES
    $hojaAdicionales = null;
    foreach ($sheetNames as $sheetName) {
        if (strtoupper($sheetName) === 'ADICIONALES') {
            $hojaAdicionales = $sheetName;
            break;
        }
    }
    
    if ($hojaAdicionales) {
        echo "<h2>Contenido de la hoja ADICIONALES:</h2>";
        
        $worksheet = $spreadsheet->getSheetByName($hojaAdicionales);
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        
        // Mostrar encabezados
        echo "<tr style='background-color: #f2f2f2;'>";
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $header = $worksheet->getCell($col . '1')->getValue();
            echo "<th>$header</th>";
        }
        echo "</tr>";
        
        // Mostrar datos
        for ($row = 2; $row <= min($highestRow, 20); $row++) { // Limitar a 20 filas para no sobrecargar la página
            echo "<tr>";
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $worksheet->getCell($col . $row)->getValue();
                echo "<td>" . (is_null($value) ? "" : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        
        if ($highestRow > 20) {
            echo "<p>Se muestran solo las primeras 20 filas de un total de $highestRow.</p>";
        }
        
        // Analizar la estructura de la hoja
        echo "<h3>Análisis de la estructura:</h3>";
        
        // Verificar columnas esperadas
        $columnaAdicional = false;
        $columnaDescripcion = false;
        $columnasPrecio = [];
        
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $header = strtolower($worksheet->getCell($col . '1')->getValue());
            
            if (strpos($header, 'adicional') !== false) {
                $columnaAdicional = $col;
            } else if (strpos($header, 'descripcion') !== false) {
                $columnaDescripcion = $col;
            } else if (strpos($header, 'precio') !== false) {
                $columnasPrecio[] = $col;
            }
        }
        
        echo "<ul>";
        echo "<li>Columna de adicionales: " . ($columnaAdicional ? "Sí (columna $columnaAdicional)" : "No encontrada") . "</li>";
        echo "<li>Columna de descripción: " . ($columnaDescripcion ? "Sí (columna $columnaDescripcion)" : "No encontrada") . "</li>";
        echo "<li>Columnas de precios: " . (count($columnasPrecio) > 0 ? "Sí (" . implode(", ", $columnasPrecio) . ")" : "No encontradas") . "</li>";
        echo "</ul>";
        
        // Verificar si la estructura es compatible con el importador
        if ($columnaAdicional && count($columnasPrecio) > 0) {
            mostrarMensaje("La estructura de la hoja ADICIONALES es compatible con el importador", "success");
        } else {
            mostrarMensaje("La estructura de la hoja ADICIONALES no es completamente compatible con el importador", "error");
        }
    } else {
        mostrarMensaje("No se encontró la hoja ADICIONALES en el archivo", "error");
    }
} catch (Exception $e) {
    mostrarMensaje("Error al leer el archivo Excel: " . $e->getMessage(), "error");
}
?>
