<?php
// Script para corregir el problema del resumen vacío
$cotizadorPath = __DIR__ . '/cotizador_simple.php';

// Leer el contenido actual del archivo
$content = file_get_contents($cotizadorPath);

// Crear copia de seguridad
file_put_contents($cotizadorPath . '.bak2', $content);

// Reemplazar el script de corrección anterior con uno mejorado
$oldScriptPattern = '<script>
        // Script de corrección para el botón de navegación
        document.addEventListener\("DOMContentLoaded", function\(\) {
            // Corregir el botón de adicionales a resumen
            const btnAdicionalesToResumen = document.querySelector\("#step-3 .next-step"\);
            if \(btnAdicionalesToResumen\) {
                btnAdicionalesToResumen.addEventListener\("click", function\(\) {
                    // Cambiar directamente al paso 4
                    const stepContents = document.querySelectorAll\(".step-content"\);
                    const steps = document.querySelectorAll\(".step"\);
                    
                    // Ocultar todos los pasos
                    stepContents.forEach\(content => content.classList.remove\("active"\)\);
                    steps.forEach\(step => step.classList.remove\("active"\)\);
                    
                    // Mostrar el paso 4
                    document.getElementById\("step-4"\).classList.add\("active"\);
                    document.querySelector\(".step\[data-step=\"4\"\]"\).classList.add\("active"\);
                    
                    // Actualizar el resumen
                    const event = new CustomEvent\("actualizarResumen"\);
                    document.dispatchEvent\(event\);
                }\);
            }
        }\);
    </script>
</body>';

// Nuevo script mejorado que también actualiza el resumen
$newScript = '<script>
        // Script de corrección para el botón de navegación y actualización del resumen
        document.addEventListener("DOMContentLoaded", function() {
            // Variables globales para acceder desde nuestro script de corrección
            let globalSelectedProduct = null;
            let globalSelectedOption = null;
            let globalSelectedAdicionales = [];
            let globalSelectedPlazoId = null;
            
            // Monitorear las variables importantes
            const originalSetInterval = window.setInterval;
            window.setInterval(function() {
                // Intentar obtener las variables del scope original
                if (typeof selectedProduct !== "undefined") globalSelectedProduct = selectedProduct;
                if (typeof selectedOption !== "undefined") globalSelectedOption = selectedOption;
                if (typeof selectedAdicionales !== "undefined") globalSelectedAdicionales = selectedAdicionales;
                if (typeof selectedPlazoId !== "undefined") globalSelectedPlazoId = selectedPlazoId;
                
                // Verificar si tenemos datos para depuración
                console.log("Monitor de variables:", {
                    globalSelectedProduct,
                    globalSelectedOption,
                    globalSelectedAdicionales,
                    globalSelectedPlazoId
                });
            }, 2000);
            
            // Función para actualizar el resumen
            function actualizarResumenFix() {
                console.log("Actualizando resumen con fix...");
                
                // Elementos del resumen
                const plazoResumen = document.getElementById("plazo-resumen");
                const productoResumen = document.getElementById("producto-resumen");
                const opcionResumen = document.getElementById("opcion-resumen");
                const adicionalesResumen = document.getElementById("adicionales-resumen");
                const totalPrecio = document.getElementById("total-precio");
                
                // Actualizar plazo
                const plazoSelect = document.getElementById("plazo-select");
                if (plazoSelect) {
                    const plazoText = plazoSelect.options[plazoSelect.selectedIndex].text;
                    plazoResumen.innerHTML = `
                        <div class="item-name">Plazo de entrega</div>
                        <div class="item-value">${plazoText}</div>
                    `;
                }
                
                // Actualizar producto y opción
                if (globalSelectedProduct) {
                    productoResumen.innerHTML = `
                        <div class="item-name">${globalSelectedProduct.nombre}</div>
                        <div class="item-price">-</div>
                    `;
                    
                    if (globalSelectedOption) {
                        opcionResumen.innerHTML = `
                            <div class="item-name">${globalSelectedOption.nombre}</div>
                            <div class="item-price">$${formatNumberFix(parseFloat(globalSelectedOption.precio))}</div>
                        `;
                    }
                }
                
                // Actualizar adicionales
                if (globalSelectedAdicionales && globalSelectedAdicionales.length > 0) {
                    adicionalesResumen.innerHTML = "";
                    globalSelectedAdicionales.forEach(adicional => {
                        const adicionalItem = document.createElement("div");
                        adicionalItem.className = "summary-item";
                        adicionalItem.innerHTML = `
                            <div class="item-name">${adicional.nombre}</div>
                            <div class="item-price">$${formatNumberFix(parseFloat(adicional.precio))}</div>
                        `;
                        adicionalesResumen.appendChild(adicionalItem);
                    });
                } else {
                    adicionalesResumen.innerHTML = `
                        <div class="summary-item">
                            <div class="item-name">No hay adicionales seleccionados</div>
                            <div class="item-price">$0,00</div>
                        </div>
                    `;
                }
                
                // Calcular total
                let total = globalSelectedOption ? parseFloat(globalSelectedOption.precio) : 0;
                if (globalSelectedAdicionales) {
                    globalSelectedAdicionales.forEach(adicional => {
                        total += parseFloat(adicional.precio);
                    });
                }
                
                totalPrecio.textContent = `$${formatNumberFix(total)}`;
            }
            
            // Función para formatear números
            function formatNumberFix(number) {
                return number.toLocaleString("es-AR", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace(".", ",");
            }
            
            // Corregir el botón de adicionales a resumen
            const btnAdicionalesToResumen = document.querySelector("#step-3 .next-step");
            if (btnAdicionalesToResumen) {
                btnAdicionalesToResumen.addEventListener("click", function() {
                    console.log("Botón de adicionales a resumen clickeado");
                    
                    // Cambiar directamente al paso 4
                    const stepContents = document.querySelectorAll(".step-content");
                    const steps = document.querySelectorAll(".step");
                    
                    // Ocultar todos los pasos
                    stepContents.forEach(content => content.classList.remove("active"));
                    steps.forEach(step => step.classList.remove("active"));
                    
                    // Mostrar el paso 4
                    document.getElementById("step-4").classList.add("active");
                    document.querySelector(".step[data-step=\"4\"]").classList.add("active");
                    
                    // Actualizar el resumen con nuestra función mejorada
                    setTimeout(actualizarResumenFix, 100);
                });
            }
            
            // También corregir el botón de opciones a adicionales
            const btnOpcionesToAdicionales = document.querySelector("#step-2 .next-step");
            if (btnOpcionesToAdicionales) {
                const originalClickHandler = btnOpcionesToAdicionales.onclick;
                btnOpcionesToAdicionales.addEventListener("click", function() {
                    console.log("Botón de opciones a adicionales clickeado");
                });
            }
        });
    </script>
</body>';

// Reemplazar el script anterior con el nuevo
if (strpos($content, $oldScriptPattern) !== false) {
    $newContent = str_replace($oldScriptPattern, $newScript, $content);
} else {
    // Si no encuentra el patrón exacto, intentar reemplazar solo el final
    $newContent = str_replace('</body>', $newScript, $content);
}

// Guardar el archivo modificado
file_put_contents($cotizadorPath, $newContent);

echo "<h1>Corrección del resumen aplicada</h1>";
echo "<p>Se ha aplicado la corrección al archivo cotizador_simple.php</p>";
echo "<p>Se ha creado una copia de seguridad en cotizador_simple.php.bak2</p>";
echo "<p><a href='cotizador_simple.php'>Ir al cotizador corregido</a></p>";
?>
