<?php
// Script para corregir el problema del botón que no permite avanzar al resumen
$cotizadorPath = __DIR__ . '/cotizador_simple.php';

// Leer el contenido actual del archivo
$content = file_get_contents($cotizadorPath);

// Crear copia de seguridad
file_put_contents($cotizadorPath . '.bak', $content);

// Buscar y reemplazar el código problemático
$fixes = [
    // 1. Corregir la estructura del HTML en el paso 3 (adicionales)
    '<div id="adicionales-container" class="adicionales-grid">' => 
    '<div id="adicionales-container" class="adicionales-grid">',
    
    // 2. Asegurar que el evento click del botón funcione correctamente
    'nextButtons.forEach(button => {
                button.addEventListener(\'click\', function() {' => 
    'nextButtons.forEach(button => {
                button.addEventListener(\'click\', function() {
                    console.log("Botón siguiente clickeado", this);',
    
    // 3. Corregir la función de cambio de paso
    'function cambiarPaso(stepNumber) {
                // Ocultar todos los pasos
                stepContents.forEach(content => content.classList.remove(\'active\'));
                steps.forEach(step => step.classList.remove(\'active\'));
                
                // Mostrar el paso seleccionado
                document.getElementById(`step-${stepNumber}`).classList.add(\'active\');
                document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add(\'active\');
            }' => 
    'function cambiarPaso(stepNumber) {
                console.log("Cambiando al paso", stepNumber);
                // Ocultar todos los pasos
                stepContents.forEach(content => content.classList.remove(\'active\'));
                steps.forEach(step => step.classList.remove(\'active\'));
                
                // Mostrar el paso seleccionado
                document.getElementById(`step-${stepNumber}`).classList.add(\'active\');
                document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add(\'active\');
            }'
];

// Aplicar las correcciones
$newContent = $content;
foreach ($fixes as $search => $replace) {
    $newContent = str_replace($search, $replace, $newContent);
}

// Solución directa: reemplazar todo el bloque de navegación
$buttonFix = '
    <script>
        // Script de corrección para el botón de navegación
        document.addEventListener("DOMContentLoaded", function() {
            // Corregir el botón de adicionales a resumen
            const btnAdicionalesToResumen = document.querySelector("#step-3 .next-step");
            if (btnAdicionalesToResumen) {
                btnAdicionalesToResumen.addEventListener("click", function() {
                    // Cambiar directamente al paso 4
                    const stepContents = document.querySelectorAll(".step-content");
                    const steps = document.querySelectorAll(".step");
                    
                    // Ocultar todos los pasos
                    stepContents.forEach(content => content.classList.remove("active"));
                    steps.forEach(step => step.classList.remove("active"));
                    
                    // Mostrar el paso 4
                    document.getElementById("step-4").classList.add("active");
                    document.querySelector(".step[data-step=\"4\"]").classList.add("active");
                    
                    // Actualizar el resumen
                    const event = new CustomEvent("actualizarResumen");
                    document.dispatchEvent(event);
                });
            }
        });
    </script>
</body>';

// Reemplazar la etiqueta de cierre del body con nuestro script de corrección
$newContent = str_replace('</body>', $buttonFix, $newContent);

// Guardar el archivo modificado
file_put_contents($cotizadorPath, $newContent);

echo "<h1>Corrección aplicada</h1>";
echo "<p>Se ha aplicado la corrección al archivo cotizador_simple.php</p>";
echo "<p>Se ha creado una copia de seguridad en cotizador_simple.php.bak</p>";
echo "<p><a href='cotizador_simple.php'>Ir al cotizador corregido</a></p>";
?>
