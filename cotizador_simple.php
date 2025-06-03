<?php
// Cotizador simplificado basado en la estructura original
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener productos (todos los productos)
    $query = "SELECT p.* FROM xls_productos p ORDER BY p.id ASC";
    $productos = $conn->query($query);
    
    // Obtener plazos de entrega
    $query = "SELECT * FROM xls_plazos ORDER BY id ASC";
    $plazos = $conn->query($query);
    
    // Plazo por defecto
    $plazoSeleccionado = "160-180 dias"; // Plazo estándar
    
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador de Ascensores</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background-color: #333; color: white; padding: 20px 0; }
        h1, h2, h3 { margin-top: 0; }
        .card { background-color: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .steps { display: flex; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 15px; position: relative; }
        .step.active { font-weight: bold; color: #4CAF50; }
        .step:not(:last-child):after { content: ''; position: absolute; top: 50%; right: 0; width: 100%; height: 2px; background-color: #ddd; z-index: 1; }
        .step-content { display: none; }
        .step-content.active { display: block; }
        .product-grid, .options-grid, .adicionales-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .product-item, .option-item, .adicional-item { border: 1px solid #ddd; border-radius: 5px; padding: 15px; cursor: pointer; transition: all 0.3s; }
        .product-item:hover, .option-item:hover, .adicional-item:hover { border-color: #4CAF50; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-item.selected, .option-item.selected, .adicional-item.selected { border-color: #4CAF50; background-color: #e8f5e9; }
        .product-title, .option-title, .adicional-title { font-weight: 500; margin-bottom: 10px; }
        .product-description, .option-description, .adicional-description { color: #666; font-size: 14px; }
        .option-price, .adicional-price { color: #e74c3c; font-weight: bold; text-align: right; margin-top: 10px; }
        .plazo-selector { margin-bottom: 20px; }
        .plazo-selector select { padding: 8px; border-radius: 4px; border: 1px solid #ddd; width: 100%; }
        .navigation { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-secondary { background-color: #f5f5f5; color: #333; border: 1px solid #ddd; }
        .summary-section { margin-top: 30px; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .total-section { font-size: 18px; font-weight: bold; margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Cotizador de Ascensores</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="steps">
            <div class="step active" data-step="1">1. Seleccionar Producto</div>
            <div class="step" data-step="2">2. Seleccionar Opción</div>
            <div class="step" data-step="3">3. Adicionales</div>
            <div class="step" data-step="4">4. Resumen</div>
        </div>
        
        <!-- Paso 1: Seleccionar Producto -->
        <div class="step-content active" id="step-1">
            <div class="card">
                <h2>Seleccione un Producto</h2>
                <div class="product-grid">
                    <?php if ($productos && $productos->num_rows > 0): ?>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <div class="product-item" data-id="<?php echo $producto['id']; ?>">
                                <div class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                <?php if (!empty($producto['descripcion'])): ?>
                                    <div class="product-description"><?php echo htmlspecialchars($producto['descripcion']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay productos disponibles.</p>
                    <?php endif; ?>
                </div>
                <div class="navigation">
                    <button class="btn btn-secondary" disabled>Anterior</button>
                    <button class="btn next-step" data-next="2">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 2: Seleccionar Opción -->
        <div class="step-content" id="step-2">
            <div class="card">
                <h2>Seleccione una Opción</h2>
                
                <!-- Selector de plazo en el paso 2 -->
                <div class="plazo-selector" style="margin-bottom: 20px;">
                    <label for="plazoSelectPaso2">Plazo de entrega:</label>
                    <select id="plazoSelectPaso2">
                        <?php if ($plazos && $plazos->num_rows > 0): ?>
                            <?php while ($plazo = $plazos->fetch_assoc()): ?>
                                <option value="<?php echo $plazo['id']; ?>" <?php echo ($plazo['nombre'] == $plazoSeleccionado) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($plazo['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="">No hay plazos disponibles</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div id="options-container" class="options-grid">
                    <p>Primero seleccione un producto.</p>
                </div>
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="1">Anterior</button>
                    <button class="btn next-step" data-next="3">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 3: Adicionales -->
        <div class="step-content" id="step-3">
            <div class="card">
                <h2>Seleccione Adicionales (Opcional)</h2>
                <div id="adicionales-container" class="adicionales-grid">
                    <p>Primero seleccione un producto y una opción.</p>
                </div>
                <div class="adicionales-grid" style="display:none;"></div>
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="2">Anterior</button>
                    <button class="btn next-step" data-next="4">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 4: Resumen -->
        <div class="step-content" id="step-4">
            <div class="card">
                <h2>Resumen del Presupuesto</h2>
                <div class="summary-section">
                    <h3>Producto y Opción Seleccionados</h3>
                    <div id="plazo-resumen" class="summary-item">
                        <div class="item-name">Plazo de entrega</div>
                        <div class="item-value">-</div>
                    </div>
                    <div id="producto-resumen" class="summary-item">
                        <div class="item-name">Seleccione un producto</div>
                        <div class="item-price">-</div>
                    </div>
                    <div id="opcion-resumen" class="summary-item">
                        <div class="item-name">Seleccione una opción</div>
                        <div class="item-price">$0,00</div>
                    </div>
                    
                    <h3>Adicionales Seleccionados</h3>
                    <div id="adicionales-resumen">
                        <div class="summary-item">
                            <div class="item-name">No hay adicionales seleccionados</div>
                            <div class="item-price">$0,00</div>
                        </div>
                    </div>
                    
                    <div class="total-section">
                        <div class="summary-item">
                            <div class="item-name">Total:</div>
                            <div id="total-precio" class="item-price">$0,00</div>
                        </div>
                    </div>
                </div>
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="3">Anterior</button>
                    <button class="btn" id="generar-pdf">Generar PDF</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables globales
            let selectedProduct = null;
            let selectedOption = null;
            let selectedPlazo = document.getElementById('plazoSelectPaso2').value;
            let selectedAdicionales = [];
            
            // Elementos DOM
            const steps = document.querySelectorAll('.step');
            const stepContents = document.querySelectorAll('.step-content');
            const productItems = document.querySelectorAll('.product-item');
            const optionsContainer = document.getElementById('options-container');
            const adicionalesContainer = document.getElementById('adicionales-container');
            const plazoSelectPaso2 = document.getElementById('plazoSelectPaso2');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            
            // Elementos del resumen
            const plazoResumen = document.getElementById('plazo-resumen');
            const productoResumen = document.getElementById('producto-resumen');
            const opcionResumen = document.getElementById('opcion-resumen');
            const adicionalesResumen = document.getElementById('adicionales-resumen');
            const totalPrecio = document.getElementById('total-precio');
            
            // Selección de producto
            productItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Quitar selección anterior
                    productItems.forEach(p => p.classList.remove('selected'));
                    
                    // Seleccionar este producto
                    this.classList.add('selected');
                    
                    // Guardar producto seleccionado
                    const productoId = parseInt(this.dataset.id);
                    selectedProduct = {
                        id: productoId,
                        nombre: this.querySelector('.product-title').textContent
                    };
                    
                    // Guardar en variable global para el resumen
                    window.globalSelectedProduct = selectedProduct;
                    
                    console.log('Producto seleccionado:', selectedProduct);
                });
            });
            
            // Función para cargar opciones
            function cargarOpciones(productoId, plazoId) {
                optionsContainer.innerHTML = '<p>Cargando opciones...</p>';
                
                // Realizar petición AJAX para obtener opciones
                fetch(`get_opciones_xls.php?producto_id=${productoId}&plazo_id=${plazoId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Opciones recibidas:', data);
                        
                        if (data.length > 0) {
                            optionsContainer.innerHTML = '';
                            
                            data.forEach(opcion => {
                                const optionItem = document.createElement('div');
                                optionItem.className = 'option-item';
                                optionItem.dataset.id = opcion.id;
                                optionItem.dataset.precio = opcion.precio;
                                
                                optionItem.innerHTML = `
                                    <div class="option-title">${opcion.nombre}</div>
                                    <div class="option-price">$${formatNumber(opcion.precio)}</div>
                                `;
                                
                                optionItem.addEventListener('click', function() {
                                    // Quitar selección anterior
                                    document.querySelectorAll('.option-item').forEach(o => o.classList.remove('selected'));
                                    
                                    // Seleccionar esta opción
                                    this.classList.add('selected');
                                    
                                    // Guardar opción seleccionada
                                    const opcionId = parseInt(this.dataset.id);
                                    selectedOption = {
                                        id: opcionId,
                                        nombre: this.querySelector('.option-title').textContent,
                                        precio: this.dataset.precio
                                    };
                                    
                                    // Guardar en variable global para el resumen
                                    window.globalSelectedOption = selectedOption;
                                    
                                    console.log('Opción seleccionada:', selectedOption);
                                });
                                
                                optionsContainer.appendChild(optionItem);
                            });
                        } else {
                            optionsContainer.innerHTML = '<p>No hay opciones disponibles para este producto.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        optionsContainer.innerHTML = '<p>Error al cargar las opciones. Por favor, inténtelo de nuevo.</p>';
                    });
            }
            
            // Función para cargar adicionales
            function cargarAdicionales(productoId) {
                adicionalesContainer.innerHTML = '<p>Cargando adicionales...</p>';
                
                // Obtener el ID del plazo seleccionado
                const plazoSelect = document.getElementById('plazoSelectPaso2');
                const plazoId = plazoSelect.options[plazoSelect.selectedIndex].value;
                
                console.log('Cargando adicionales para producto ID:', productoId, 'y plazo ID:', plazoId);
                
                // Realizar petición AJAX para obtener adicionales
                fetch(`get_adicionales_xls.php?producto_id=${productoId}&plazo_id=${plazoId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Adicionales recibidos:', data);
                        
                        if (data.length > 0) {
                            adicionalesContainer.innerHTML = '';
                            
                            data.forEach(adicional => {
                                const adicionalItem = document.createElement('div');
                                adicionalItem.className = 'adicional-item';
                                adicionalItem.dataset.id = adicional.id;
                                adicionalItem.dataset.precio = adicional.precio;
                                
                                adicionalItem.innerHTML = `
                                    <div class="adicional-title">${adicional.nombre}</div>
                                    <div class="adicional-price">$${formatNumber(adicional.precio)}</div>
                                `;
                                
                                adicionalItem.addEventListener('click', function() {
                                    // Toggle selección
                                    this.classList.toggle('selected');
                                    
                                    // Guardar adicional seleccionado/deseleccionado
                                    const adicionalId = parseInt(this.dataset.id);
                                    const adicionalNombre = this.querySelector('.adicional-title').textContent;
                                    const adicionalPrecio = this.dataset.precio;
                                    
                                    if (this.classList.contains('selected')) {
                                        // Agregar a seleccionados
                                        selectedAdicionales.push({
                                            id: adicionalId,
                                            nombre: adicionalNombre,
                                            precio: adicionalPrecio
                                        });
                                    } else {
                                        // Quitar de seleccionados
                                        selectedAdicionales = selectedAdicionales.filter(a => a.id !== adicionalId);
                                    }
                                    
                                    // Guardar en variable global para el resumen
                                    window.globalSelectedAdicionales = selectedAdicionales;
                                    
                                    console.log('Adicionales seleccionados:', selectedAdicionales);
                                });
                                
                                adicionalesContainer.appendChild(adicionalItem);
                            });
                        } else {
                            adicionalesContainer.innerHTML = '<p>No hay adicionales disponibles para este producto.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        adicionalesContainer.innerHTML = '<p>Error al cargar los adicionales. Por favor, inténtelo de nuevo.</p>';
                    });
            }
            
            // Cambio de plazo
            plazoSelectPaso2.addEventListener('change', function() {
                const plazoId = parseInt(this.value);
                selectedPlazo = plazoId;
                
                // Guardar en variable global para el resumen
                window.globalSelectedPlazoId = plazoId;
                
                // Si hay un producto seleccionado, cargar sus opciones con el nuevo plazo
                if (selectedProduct) {
                    cargarOpciones(selectedProduct.id, plazoId);
                }
                
                console.log('Plazo seleccionado:', plazoId);
            });
            
            // Navegación entre pasos
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    console.log("Botón siguiente clickeado", this);
                    const currentStep = parseInt(this.closest('.step-content').id.split('-')[1]);
                    const nextStep = parseInt(this.dataset.next);
                    
                    console.log('Navegación: paso actual', currentStep, 'siguiente paso', nextStep);
                    
                    // Validar que se pueda avanzar
                    if (currentStep === 1 && !selectedProduct) {
                        alert('Por favor, seleccione un producto.');
                        return;
                    }
                    
                    if (currentStep === 2 && !selectedOption) {
                        alert('Por favor, seleccione una opción.');
                        return;
                    }
                    
                    // Si vamos al paso 2, cargar opciones
                    if (currentStep === 1 && nextStep === 2) {
                        cargarOpciones(selectedProduct.id, selectedPlazo);
                    }
                    
                    // Verificar si el producto seleccionado puede tener adicionales
                    const productosConAdicionales = [
                        'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                        'ASCENSORES HIDRAULICOS',
                        'MONTACARGAS',
                        'SALVAESCALERAS'
                    ];
                    
                    // Si vamos al paso 3 (adicionales)
                    if (nextStep === 3) {
                        // Verificar si el producto seleccionado puede tener adicionales
                        if (productosConAdicionales.includes(selectedProduct.nombre)) {
                            // Cargar adicionales normalmente
                            cargarAdicionales(selectedProduct.id);
                        } else {
                            // Saltar directamente al paso 4 (resumen)
                            console.log('Este producto no tiene adicionales, saltando al resumen');
                            nextStep = 4;
                            actualizarResumen();
                        }
                    }
                    
                    // Si vamos al paso de resumen, actualizarlo
                    if (nextStep === 4) {
                        actualizarResumen();
                    }
                    
                    // Cambiar al siguiente paso
                    cambiarPaso(nextStep);
                });
            });
            
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.dataset.prev);
                    cambiarPaso(prevStep);
                });
            });
            
            // Función para cambiar de paso
            function cambiarPaso(stepNumber) {
                console.log("Cambiando al paso", stepNumber);
                // Ocultar todos los pasos
                stepContents.forEach(content => content.classList.remove('active'));
                steps.forEach(step => step.classList.remove('active'));
                
                // Mostrar el paso seleccionado
                document.getElementById(`step-${stepNumber}`).classList.add('active');
                document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');
            }
            
            // Función para actualizar el resumen
            function actualizarResumen() {
                // Actualizar el plazo seleccionado
                const plazoSelect = document.getElementById('plazo-select');
                const plazoText = plazoSelect.options[plazoSelect.selectedIndex].text;
                plazoResumen.innerHTML = `
                    <div class="item-name">Plazo de entrega</div>
                    <div class="item-value">${plazoText}</div>
                `;
                
                if (selectedProduct && selectedOption) {
                    productoResumen.innerHTML = `
                        <div class="item-name">${selectedProduct.nombre}</div>
                        <div class="item-price">-</div>
                    `;
                    
                    opcionResumen.innerHTML = `
                        <div class="item-name">${selectedOption.nombre}</div>
                        <div class="item-price">$${formatNumber(selectedOption.precio)}</div>
                    `;
                }
                
                // Actualizar adicionales
                if (selectedAdicionales.length > 0) {
                    adicionalesResumen.innerHTML = '';
                    selectedAdicionales.forEach(adicional => {
                        const adicionalItem = document.createElement('div');
                        adicionalItem.className = 'summary-item';
                        adicionalItem.innerHTML = `
                            <div class="item-name">${adicional.nombre}</div>
                            <div class="item-price">$${formatNumber(parseFloat(adicional.precio))}</div>
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
                let total = selectedOption ? selectedOption.precio : 0;
                selectedAdicionales.forEach(adicional => {
                    total += adicional.precio;
                });
                
                totalPrecio.textContent = `$${formatNumber(total)}`;
            }
            
            // Función para formatear números
            function formatNumber(number) {
                return number.toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace('.', ',');
            }
            
            // Generar PDF
            document.getElementById('generar-pdf').addEventListener('click', function() {
                alert('Generando PDF del presupuesto...');
                // Aquí iría la lógica para generar el PDF
            });
        });
    </script>

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
<script>
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
                
                // Si estamos en el paso 4 (resumen), actualizar el resumen
                if (document.getElementById("step-4").classList.contains("active")) {
                   function actualizarResumenFix() {
                console.log("Actualizando resumen con variables globales:", {
                    globalSelectedProduct,
                    globalSelectedOption,
                    globalSelectedAdicionales,
                    globalSelectedPlazoId
                });
                
                // Elementos del resumen
                const plazoResumen = document.getElementById('plazo-resumen');
                const productoResumen = document.getElementById('producto-resumen');
                const opcionResumen = document.getElementById('opcion-resumen');
                const adicionalesResumen = document.getElementById('adicionales-resumen');
                const totalPrecio = document.getElementById('total-precio');
                
                if (!plazoResumen || !productoResumen || !opcionResumen || !adicionalesResumen || !totalPrecio) {
                    console.error("No se encontraron todos los elementos del resumen");
                    return;
                }
                
                // Actualizar plazo
                if (document.getElementById('plazoSelectPaso2')) {
                    const plazoSelect = document.getElementById('plazoSelectPaso2');
                    const plazoText = plazoSelect.options[plazoSelect.selectedIndex].text;
                    plazoResumen.innerHTML = `
                        <div class="item-name">Plazo de entrega</div>
                        <div class="item-value">${plazoText}</div>
                    `;
                
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
                    
                    // Actualizar el resumen
                    actualizarResumen();
                });
            }
            
            // Función para actualizar el resumen
            function actualizarResumen() {
                console.log("Actualizando resumen...");
                
                // Elementos del resumen
                const plazoResumen = document.getElementById('plazo-resumen');
                const productoResumen = document.getElementById('producto-resumen');
                const opcionResumen = document.getElementById('opcion-resumen');
                const adicionalesResumen = document.getElementById('adicionales-resumen');
                const totalPrecio = document.getElementById('total-precio');
                
                // Actualizar plazo
                if (plazoSelectPaso2) {
                    const plazoText = plazoSelectPaso2.options[plazoSelectPaso2.selectedIndex].text;
                    plazoResumen.innerHTML = `
                        <div class="item-name">Plazo de entrega</div>
                        <div class="item-value">${plazoText}</div>
                    `;
                }
                
                // Actualizar producto
                if (selectedProduct) {
                    productoResumen.innerHTML = `
                        <div class="item-name">${selectedProduct.nombre}</div>
                        <div class="item-price">-</div>
                    `;
                }
                
                // Actualizar opción
                if (selectedOption) {
                    opcionResumen.innerHTML = `
                        <div class="item-name">${selectedOption.nombre}</div>
                        <div class="item-price">$${formatNumber(selectedOption.precio)}</div>
                    `;
                }
                
                // Actualizar adicionales
                if (selectedAdicionales && selectedAdicionales.length > 0) {
                    adicionalesResumen.innerHTML = "";
                    selectedAdicionales.forEach(adicional => {
                        const adicionalItem = document.createElement("div");
                        adicionalItem.className = "summary-item";
                        adicionalItem.innerHTML = `
                            <div class="item-name">${adicional.nombre}</div>
                            <div class="item-price">$${formatNumber(adicional.precio)}</div>
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
                let total = selectedOption ? parseFloat(selectedOption.precio) : 0;
                if (selectedAdicionales) {
                    selectedAdicionales.forEach(adicional => {
                        total += parseFloat(adicional.precio);
                    });
                }
                
                totalPrecio.textContent = `$${formatNumber(total)}`;
            }
            
            // Función para formatear números
            function formatNumber(number) {
                return parseFloat(number).toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace('.', ',');
            }
            
            // También corregir el botón de opciones a adicionales
            const btnOpcionesToAdicionales = document.querySelector("#step-2 .next-step");
            if (btnOpcionesToAdicionales) {
                btnOpcionesToAdicionales.addEventListener("click", function() {
                    console.log("Botón de opciones a adicionales clickeado");
                    
                    // Cambiar al paso 3
                    const stepContents = document.querySelectorAll(".step-content");
                    const steps = document.querySelectorAll(".step");
                    
                    // Ocultar todos los pasos
                    stepContents.forEach(content => content.classList.remove("active"));
                    steps.forEach(step => step.classList.remove("active"));
                    
                    // Mostrar el paso 3
                    document.getElementById("step-3").classList.add("active");
                    document.querySelector(".step[data-step=\"3\"]").classList.add("active");
                });
            }
            
            // Agregar funcionalidad al botón "Generar PDF"
            const btnGenerarPDF = document.getElementById("generar-pdf");
            if (btnGenerarPDF) {
                btnGenerarPDF.addEventListener("click", function() {
                    console.log("Botón generar PDF clickeado");
                    
                    // Verificar que tengamos los datos necesarios
                    if (!globalSelectedProduct || !globalSelectedOption) {
                        alert("Por favor, seleccione un producto y una opción antes de generar el PDF");
                        return;
                    }
                    
                    // Mostrar mensaje de carga
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    this.disabled = true;
                    
                    // Preparar los datos para el presupuesto
                    const presupuestoData = {
                        nombre: "Cliente", // Valores por defecto
                        email: "cliente@ejemplo.com",
                        telefono: "123456789",
                        producto: globalSelectedProduct,
                        opcion: globalSelectedOption,
                        plazo: {
                            id: globalSelectedPlazoId || 1,
                            nombre: document.getElementById('plazoSelectPaso2').options[document.getElementById('plazoSelectPaso2').selectedIndex].text
                        },
                        formaPago: {
                            nombre: "Efectivo",
                            descuento: 0,
                            descuentoMonto: 0
                        },
                        adicionales: globalSelectedAdicionales || [],
                        subtotal: parseFloat(globalSelectedOption.precio),
                        total: parseFloat(globalSelectedOption.precio)
                    };
                    
                    // Calcular total con adicionales
                    if (globalSelectedAdicionales && globalSelectedAdicionales.length > 0) {
                        globalSelectedAdicionales.forEach(adicional => {
                            presupuestoData.total += parseFloat(adicional.precio);
                        });
                    }
                    
                    // Generar PDF directamente
                    window.location.href = 'pdf_basico.php';
                    
                    // Restaurar botón después de un tiempo
                    setTimeout(() => {
                        this.innerHTML = 'Generar PDF';
                        this.disabled = false;
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>
