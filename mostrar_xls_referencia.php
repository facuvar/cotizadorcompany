<?php
// Script para mostrar el contenido del archivo XLS de referencia
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Ruta al archivo XLS
    $inputFileName = 'xls/xls-referencia.xlsx';
    
    // Cargar el archivo
    $spreadsheet = IOFactory::load($inputFileName);
    
    // Obtener todas las hojas
    $sheetNames = $spreadsheet->getSheetNames();
    
    echo "<h1>Contenido del archivo XLS de referencia</h1>";
    
    // Mostrar cada hoja
    foreach ($sheetNames as $sheetName) {
        $worksheet = $spreadsheet->getSheetByName($sheetName);
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        echo "<h2>Hoja: $sheetName</h2>";
        
        echo "<table border='1' cellpadding='3' style='border-collapse: collapse;'>";
        
        // Mostrar encabezados (primera fila)
        echo "<tr style='background-color: #f2f2f2;'>";
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $value = $worksheet->getCell($col . '1')->getValue();
            echo "<th>" . htmlspecialchars($value ?? '') . "</th>";
        }
        echo "</tr>";
        
        // Mostrar datos
        for ($row = 2; $row <= $highestRow; $row++) {
            echo "<tr>";
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $worksheet->getCell($col . $row)->getValue();
                
                // Resaltar filas con MONTAPLATOS o ESTRUCTURA
                $cellStyle = '';
                if ($col == 'A' && $value && (
                    stripos($value, 'MONTAPLATO') !== false || 
                    stripos($value, 'ESTRUCTURA') !== false
                )) {
                    $cellStyle = 'background-color: #ffff99; font-weight: bold;';
                }
                
                echo "<td style='$cellStyle'>" . htmlspecialchars($value ?? '') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "Error al leer el archivo: " . $e->getMessage();
}
?>
