<?php
// Leer el archivo XLS de referencia para obtener datos de MONTAPLATOS y ESTRUCTURA
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

try {
    $reader = new Xlsx();
    $spreadsheet = $reader->load('xls/xls-referencia.xlsx');
    
    echo "<h1>Contenido del archivo XLS de referencia</h1>";
    
    // Obtener todas las hojas disponibles
    $sheetNames = $spreadsheet->getSheetNames();
    echo "<h2>Hojas disponibles:</h2>";
    echo "<ul>";
    foreach ($sheetNames as $sheetName) {
        echo "<li>$sheetName</li>";
    }
    echo "</ul>";
    
    // Leer la hoja activa
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "<h2>Buscando MONTAPLATOS y ESTRUCTURA</h2>";
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Fila</th><th>Contenido</th></tr>";
    
    $encontradoMontaplatos = false;
    $encontradoEstructura = false;
    
    for ($row = 1; $row <= $highestRow; $row++) {
        $cellValue = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
        
        if ($cellValue && (
            stripos($cellValue, 'MONTAPLATO') !== false || 
            stripos($cellValue, 'ESTRUCTURA') !== false
        )) {
            echo "<tr><td>$row</td><td><strong>$cellValue</strong></td></tr>";
            
            // Mostrar las siguientes filas para ver las opciones
            for ($subrow = $row + 1; $subrow <= min($row + 10, $highestRow); $subrow++) {
                $subCellValue = $worksheet->getCellByColumnAndRow(1, $subrow)->getValue();
                if (!$subCellValue || stripos($subCellValue, 'MONTAPLATO') !== false || stripos($subCellValue, 'ESTRUCTURA') !== false) {
                    break;
                }
                
                echo "<tr><td>$subrow</td><td style='padding-left: 20px;'>";
                
                // Mostrar todas las columnas para esta fila
                for ($col = 1; $col <= 10; $col++) {
                    $colValue = $worksheet->getCellByColumnAndRow($col, $subrow)->getValue();
                    if ($colValue) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        echo "<div><strong>Columna $colLetter:</strong> $colValue</div>";
                    }
                }
                
                echo "</td></tr>";
            }
            
            if (stripos($cellValue, 'MONTAPLATO') !== false) {
                $encontradoMontaplatos = true;
            }
            if (stripos($cellValue, 'ESTRUCTURA') !== false) {
                $encontradoEstructura = true;
            }
        }
    }
    
    echo "</table>";
    
    if (!$encontradoMontaplatos) {
        echo "<p>No se encontró información sobre MONTAPLATOS.</p>";
    }
    
    if (!$encontradoEstructura) {
        echo "<p>No se encontró información sobre ESTRUCTURA.</p>";
    }
    
    // Intentar buscar en otras hojas si no se encontró en la hoja activa
    if (!$encontradoMontaplatos || !$encontradoEstructura) {
        echo "<h2>Buscando en otras hojas</h2>";
        
        foreach ($sheetNames as $sheetName) {
            if ($sheetName !== $spreadsheet->getActiveSheet()->getTitle()) {
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                $highestRow = $worksheet->getHighestRow();
                
                echo "<h3>Hoja: $sheetName</h3>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Fila</th><th>Contenido</th></tr>";
                
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                    
                    if ($cellValue && (
                        (!$encontradoMontaplatos && stripos($cellValue, 'MONTAPLATO') !== false) || 
                        (!$encontradoEstructura && stripos($cellValue, 'ESTRUCTURA') !== false)
                    )) {
                        echo "<tr><td>$row</td><td><strong>$cellValue</strong></td></tr>";
                        
                        // Mostrar las siguientes filas para ver las opciones
                        for ($subrow = $row + 1; $subrow <= min($row + 10, $highestRow); $subrow++) {
                            $subCellValue = $worksheet->getCellByColumnAndRow(1, $subrow)->getValue();
                            if (!$subCellValue || stripos($subCellValue, 'MONTAPLATO') !== false || stripos($subCellValue, 'ESTRUCTURA') !== false) {
                                break;
                            }
                            
                            echo "<tr><td>$subrow</td><td style='padding-left: 20px;'>";
                            
                            // Mostrar todas las columnas para esta fila
                            for ($col = 1; $col <= 10; $col++) {
                                $colValue = $worksheet->getCellByColumnAndRow($col, $subrow)->getValue();
                                if ($colValue) {
                                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                                    echo "<div><strong>Columna $colLetter:</strong> $colValue</div>";
                                }
                            }
                            
                            echo "</td></tr>";
                        }
                        
                        if (stripos($cellValue, 'MONTAPLATO') !== false) {
                            $encontradoMontaplatos = true;
                        }
                        if (stripos($cellValue, 'ESTRUCTURA') !== false) {
                            $encontradoEstructura = true;
                        }
                    }
                }
                
                echo "</table>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
