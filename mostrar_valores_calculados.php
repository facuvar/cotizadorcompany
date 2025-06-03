<?php
// Script para mostrar los valores calculados del archivo XLS de referencia
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    // Ruta al archivo XLS
    $inputFileName = 'xls/xls-referencia.xlsx';
    
    // Cargar el archivo con valores calculados
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true); // Esto hace que se lean los valores calculados, no las fórmulas
    $spreadsheet = $reader->load($inputFileName);
    
    // Obtener todas las hojas
    $sheetNames = $spreadsheet->getSheetNames();
    
    echo "<h1>Valores Calculados del XLS de Referencia</h1>";
    
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
            
            $rowData = [];
            $isTargetRow = false;
            
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col . $row);
                $value = $cell->getValue();
                $formattedValue = $value;
                
                // Si es un valor numérico, formatearlo como moneda
                if (is_numeric($value)) {
                    $formattedValue = '$' . number_format($value, 2, ',', '.');
                }
                
                // Verificar si es una fila de MONTAPLATOS o ESTRUCTURA
                if ($col == 'A' && $value && (
                    stripos($value, 'MONTAPLATO') !== false || 
                    stripos($value, 'ESTRUCTURA') !== false
                )) {
                    $isTargetRow = true;
                }
                
                $rowData[$col] = [
                    'value' => $value,
                    'formatted' => $formattedValue
                ];
            }
            
            // Resaltar filas con MONTAPLATOS o ESTRUCTURA o sus opciones
            $rowStyle = '';
            if ($isTargetRow) {
                $rowStyle = 'background-color: #ffff99; font-weight: bold;';
            }
            
            // Mostrar la fila
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellStyle = $rowStyle;
                echo "<td style='$cellStyle'>" . htmlspecialchars($rowData[$col]['formatted'] ?? '') . "</td>";
            }
            
            echo "</tr>";
            
            // Si es una fila de MONTAPLATOS o ESTRUCTURA, mostrar las siguientes filas (opciones)
            if ($isTargetRow) {
                $optionRow = $row + 1;
                $hasOptions = true;
                
                while ($hasOptions && $optionRow <= $highestRow) {
                    $optionValue = $worksheet->getCell('A' . $optionRow)->getValue();
                    
                    // Si la celda A está vacía o contiene otro producto principal, salir del bucle
                    if (!$optionValue || (
                        stripos($optionValue, 'MONTAPLATO') !== false || 
                        stripos($optionValue, 'ESTRUCTURA') !== false ||
                        stripos($optionValue, 'GIRACOCHE') !== false ||
                        stripos($optionValue, 'EQUIPO') !== false
                    )) {
                        $hasOptions = false;
                        continue;
                    }
                    
                    echo "<tr style='background-color: #e6f7ff;'>";
                    for ($col = 'A'; $col <= $highestColumn; $col++) {
                        $cell = $worksheet->getCell($col . $optionRow);
                        $value = $cell->getValue();
                        $formattedValue = $value;
                        
                        // Si es un valor numérico, formatearlo como moneda
                        if (is_numeric($value)) {
                            $formattedValue = '$' . number_format($value, 2, ',', '.');
                        }
                        
                        echo "<td>" . htmlspecialchars($formattedValue ?? '') . "</td>";
                    }
                    echo "</tr>";
                    
                    $optionRow++;
                }
            }
        }
        
        echo "</table>";
    }
    
    // Mostrar un formulario para importar los datos
    echo "<div style='margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>Importar Datos de MONTAPLATOS y ESTRUCTURA</h3>";
    echo "<form action='importar_datos_seleccionados.php' method='post'>";
    echo "<button type='submit' style='padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;'>Importar Datos</button>";
    echo "</form>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "Error al leer el archivo: " . $e->getMessage();
}
?>
