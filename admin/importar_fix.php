<?php
// Función para manejar valores de celdas que pueden ser arrays
function handleCellValue($value) {
    if (is_array($value)) {
        // Si es un array, intentamos convertirlo a string o devolvemos un valor por defecto
        error_log("Array encontrado en celda: " . print_r($value, true));
        return "0"; // Valor por defecto
    }
    return $value;
}

// Función para verificar si una coordenada de celda es válida
function isCellCoordinateValid($coordinate) {
    // Verificar que la coordenada tenga el formato correcto (letra(s) seguida(s) de número(s))
    return preg_match('/^[A-Z]+[0-9]+$/', $coordinate);
}

// Función para obtener el valor de una celda de forma segura
function getCellValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!isCellCoordinateValid($coordinate)) {
            error_log("Coordenada de celda inválida: $coordinate");
            return "";
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return "";
        }
        
        // Obtener el valor de la celda
        $value = $worksheet->getCell($coordinate)->getValue();
        
        // Manejar el valor si es un array
        return handleCellValue($value);
    } catch (Exception $e) {
        error_log("Error al obtener valor de celda $coordinate: " . $e->getMessage());
        return "";
    }
}

// Función para obtener el valor calculado de una celda de forma segura
function getCalculatedValueSafely($worksheet, $coordinate) {
    try {
        // Verificar que la coordenada sea válida
        if (!isCellCoordinateValid($coordinate)) {
            error_log("Coordenada de celda inválida para cálculo: $coordinate");
            return 0;
        }
        
        // Verificar que la celda exista
        if (!$worksheet->cellExists($coordinate)) {
            return 0;
        }
        
        // Obtener el valor calculado de la celda
        $value = $worksheet->getCell($coordinate)->getCalculatedValue();
        
        // Manejar el valor si es un array
        return handleCellValue($value);
    } catch (Exception $e) {
        error_log("Error al obtener valor calculado de celda $coordinate: " . $e->getMessage());
        return 0;
    }
}

// Función para limpiar valores monetarios (reemplaza a la función limpiarValorMonetario)
function limpiarValorMonetarioSeguro($valor) {
    // Si es un array, intentar convertirlo a string o usar 0
    if (is_array($valor)) {
        error_log("Array encontrado en valor monetario: " . print_r($valor, true));
        return 0;
    }
    
    // Si es null o vacío, devolver 0
    if ($valor === null || $valor === '') {
        return 0;
    }
    
    // Convertir a string si no lo es
    if (!is_string($valor) && !is_numeric($valor)) {
        error_log("Tipo de dato no esperado en valor monetario: " . gettype($valor));
        return 0;
    }
    
    // Convertir a string para manipularlo
    $valor = (string)$valor;
    
    // Eliminar caracteres no numéricos excepto punto y coma
    $valor = preg_replace('/[^0-9.,]/', '', $valor);
    
    // Reemplazar coma por punto
    $valor = str_replace(',', '.', $valor);
    
    // Convertir a float
    return floatval($valor);
}

echo "Funciones de corrección cargadas correctamente.\n";
echo "Incluye este archivo en importar.php para corregir los errores de 'Array to string conversion' y 'Invalid cell coordinate'.\n";
?>
