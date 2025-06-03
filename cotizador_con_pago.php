<?php
// Cotizador simplificado - versión nueva y corregida
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
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
        .alert { padding: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .product-item, .option-item, .adicional-item, .forma-pago-item { border: 1px solid #ddd; border-radius: 5px; padding: 15px; cursor: pointer; transition: all 0.3s; }
        .product-item:hover, .option-item:hover, .adicional-item:hover, .forma-pago-item:hover { border-color: #4CAF50; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-item.selected, .option-item.selected, .adicional-item.selected, .forma-pago-item.selected { border-color: #4CAF50; background-color: #e8f5e9; }
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
        #debug-info { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin-top: 20px; font-family: monospace; display: none; }
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
            <div class="step" data-step="4">4. Forma de Pago</div>
            <div class="step" data-step="5">5. Datos del Usuario</div>
            <div class="step" data-step="6">6. Resumen</div>
        </div>
        
        <!-- Paso 1: Seleccionar Producto -->
        <div class="step-content active" id="step-1">
            <div class="card">
                <h2>Seleccione un Producto</h2>
                <div class="product-grid">
                    <?php if ($productos && $productos->num_rows > 0): ?>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <div class="product-item" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <div class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                <div class="product-description"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay productos disponibles.</p>
                    <?php endif; ?>
                </div>
                <div class="navigation">
                    <div></div> <!-- Espacio vacío para alinear el botón a la derecha -->
                    <button class="btn next-step" data-next="2" id="btn-paso1">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 2: Seleccionar Opción -->
        <div class="step-content" id="step-2">
            <div class="card">
                <h2>Seleccione una Opción</h2>
                
                <!-- Selector de plazo en el paso 2 -->
                <div class="plazo-selector" style="margin-bottom: 20px;">
                    <label for="plazo-select">Plazo de entrega:</label>
                    <select id="plazo-select" class="plazo-select">
                        <?php if ($plazos && $plazos->num_rows > 0): ?>
                            <?php while ($plazo = $plazos->fetch_assoc()): ?>
                                <option value="<?php echo $plazo['id']; ?>">
                                    <?php echo htmlspecialchars($plazo['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div id="options-container" class="options-grid">
                    <p>Primero seleccione un producto.</p>
                </div>
                
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="1">Anterior</button>
                    <button class="btn next-step" data-next="3" id="btn-paso2">Siguiente</button>
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
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="2">Anterior</button>
                    <button class="btn next-step" data-next="4" id="btn-paso3">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 4: Forma de Pago -->
        <div class="step-content" id="step-4">
            <div class="card">
                <h2>Seleccione Forma de Pago</h2>
                <div id="formas-pago-container" class="options-grid">
                    <div class="forma-pago-item" data-descuento="8" data-nombre="Efectivo X">
                        <div class="option-title">Efectivo X</div>
                        <div class="option-description">Descuento del 8%</div>
                        <div class="option-price">8% de descuento</div>
                    </div>
                    <div class="forma-pago-item" data-descuento="5" data-nombre="Transferencia">
                        <div class="option-title">Transferencia</div>
                        <div class="option-description">Descuento del 5%</div>
                        <div class="option-price">5% de descuento</div>
                    </div>
                    <div class="forma-pago-item" data-descuento="2" data-nombre="Cheques Electrónicos(30-45)">
                        <div class="option-title">Cheques Electrónicos(30-45)</div>
                        <div class="option-description">Financiación en 6 cheques electrónicos 0-15-30-45-60-90</div>
                        <div class="option-price">2% de descuento</div>
                    </div>
                    <div class="forma-pago-item" data-descuento="5" data-nombre="Mejora de presupuesto">
                        <div class="option-title">Mejora de presupuesto</div>
                        <div class="option-description">Descuento del 5%</div>
                        <div class="option-price">5% de descuento</div>
                    </div>
                </div>
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="3">Anterior</button>
                    <button class="btn next-step" data-next="5" id="btn-paso4">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 5: Datos del Usuario -->
        <div class="step-content" id="step-5">
            <div class="card">
                <h2>Complete sus Datos</h2>
                <p>Por favor, complete sus datos para generar el presupuesto.</p>
                
                <div class="form-group">
                    <label for="nombre">Nombre completo:</label>
                    <input type="text" id="nombre" class="form-control" placeholder="Ingrese su nombre completo" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" class="form-control" placeholder="Ingrese su email" required>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" class="form-control" placeholder="Ingrese su teléfono" required>
                </div>
                
                <div class="navigation">
                    <button class="btn btn-secondary prev-step" data-prev="4">Anterior</button>
                    <button class="btn next-step" data-next="6" id="btn-paso5">Siguiente</button>
                </div>
            </div>
        </div>
        
        <!-- Paso 6: Resumen -->
        <div class="step-content" id="step-6">
            <div class="card">
                <h2>Resumen del Presupuesto</h2>
                <div class="summary-section">
                    <div id="datos-usuario-resumen" class="summary-item">
                        <div class="item-name">Datos del cliente</div>
                        <div class="item-value">-</div>
                    </div>
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
                    <div id="forma-pago-resumen" class="summary-item">
                        <div class="item-name">Forma de pago</div>
                        <div class="item-value">-</div>
                    </div>
                    <div id="descuento-resumen" class="summary-item">
                        <div class="item-name">Descuento</div>
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
                    <button class="btn btn-secondary prev-step" data-prev="5">Anterior</button>
                    <button class="btn" id="generar-pdf">Generar PDF</button>
                </div>
                
                <div id="mensaje-final" style="display:none; margin-top: 20px;" class="alert alert-success">
                    <p>Su presupuesto ha sido guardado correctamente.</p>
                    <p>El PDF se está descargando automáticamente.</p>
                </div>
            </div>
        </div>
        
        <!-- Área de depuración (oculta por defecto) -->
        <div id="debug-info"></div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Habilitar modo depuración
            const debugMode = false;
            const debugInfo = document.getElementById('debug-info');
            
            if (debugMode) {
                debugInfo.style.display = 'block';
            }
            
            function log(message, data = null) {
                console.log(message, data);
                if (debugMode) {
                    const logItem = document.createElement('div');
                    logItem.textContent = `${new Date().toLocaleTimeString()}: ${message} ${data ? JSON.stringify(data) : ''}`;
                    debugInfo.appendChild(logItem);
                }
            }
            
            // Referencias a elementos del DOM
            const steps = document.querySelectorAll('.step');
            const stepContents = document.querySelectorAll('.step-content');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            
            // Elementos del resumen
            const plazoResumen = document.getElementById('plazo-resumen');
            const productoResumen = document.getElementById('producto-resumen');
            const opcionResumen = document.getElementById('opcion-resumen');
            const adicionalesResumen = document.getElementById('adicionales-resumen');
            const totalPrecio = document.getElementById('total-precio');
            
            // Elementos de selección
            const plazoSelect = document.getElementById('plazo-select');
            const optionsContainer = document.getElementById('options-container');
            const adicionalesContainer = document.getElementById('adicionales-container');
            
            // Variables para almacenar selecciones
            let selectedProduct = null;
            let selectedOption = null;
            let selectedAdicionales = [];
            let selectedPlazoId = plazoSelect ? plazoSelect.value : '1';
            let selectedFormaPago = null;
            
            log('Inicialización completada');
            
            // Selección de producto
            document.querySelectorAll('.product-item').forEach(item => {
                item.addEventListener('click', function() {
                    // Quitar selección anterior
                    document.querySelectorAll('.product-item').forEach(p => p.classList.remove('selected'));
                    
                    // Seleccionar este producto
                    this.classList.add('selected');
                    
                    // Guardar producto seleccionado
                    selectedProduct = {
                        id: this.dataset.id,
                        nombre: this.dataset.nombre
                    };
                    
                    log('Producto seleccionado:', selectedProduct);
                    
                    // Limpiar selecciones previas
                    selectedOption = null;
                    selectedAdicionales = [];
                });
            });
            
            // Función para cargar opciones
            function cargarOpciones(productoId, plazoId) {
                log('Cargando opciones para producto ID:', { productoId, plazoId });
                optionsContainer.innerHTML = '<p>Cargando opciones...</p>';
                
                // Realizar petición AJAX para obtener opciones
                fetch(`get_opciones_xls.php?producto_id=${productoId}&plazo_id=${plazoId}`)
                    .then(response => response.json())
                    .then(data => {
                        log('Opciones recibidas:', data);
                        
                        if (data.length > 0) {
                            optionsContainer.innerHTML = '';
                            
                            data.forEach(opcion => {
                                const optionItem = document.createElement('div');
                                optionItem.className = 'option-item';
                                optionItem.dataset.id = opcion.id;
                                optionItem.dataset.nombre = opcion.nombre;
                                optionItem.dataset.precio = opcion.precio;
                                
                                // Formatear el precio correctamente
                                const precioFormateado = formatNumber(parseFloat(opcion.precio));
                                
                                optionItem.innerHTML = `
                                    <div class="option-title">${opcion.nombre}</div>
                                    <div class="option-price">$${precioFormateado}</div>
                                `;
                                
                                optionItem.addEventListener('click', function() {
                                    // Quitar selección anterior
                                    document.querySelectorAll('.option-item').forEach(o => o.classList.remove('selected'));
                                    
                                    // Seleccionar esta opción
                                    this.classList.add('selected');
                                    
                                    // Guardar opción seleccionada
                                    selectedOption = {
                                        id: opcion.id,
                                        nombre: opcion.nombre,
                                        precio: parseFloat(opcion.precio)
                                    };
                                    
                                    log('Opción seleccionada:', selectedOption);
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
                log('Cargando adicionales para producto ID:', { productoId, plazoId: selectedPlazoId });
                adicionalesContainer.innerHTML = '<p>Cargando adicionales...</p>';
                
                // Limpiar selección previa
                selectedAdicionales = [];
                
                // Realizar petición AJAX para obtener adicionales
                fetch(`get_adicionales_xls.php?producto_id=${productoId}&plazo_id=${selectedPlazoId}`)
                    .then(response => response.json())
                    .then(data => {
                        log('Adicionales recibidos:', data);
                        adicionalesContainer.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(adicional => {
                                const adicionalItem = document.createElement('div');
                                adicionalItem.className = 'adicional-item';
                                adicionalItem.dataset.id = adicional.id;
                                adicionalItem.dataset.nombre = adicional.nombre;
                                adicionalItem.dataset.precio = adicional.precio;
                                
                                // Formatear el precio correctamente
                                const precioFormateado = formatNumber(parseFloat(adicional.precio));
                                
                                adicionalItem.innerHTML = `
                                    <div class="adicional-title">${adicional.nombre}</div>
                                    <div class="adicional-description">${adicional.descripcion || ''}</div>
                                    <div class="adicional-price">$${precioFormateado}</div>
                                `;
                                
                                adicionalItem.addEventListener('click', function() {
                                    // Toggle selección
                                    this.classList.toggle('selected');
                                    
                                    if (this.classList.contains('selected')) {
                                        // Agregar a seleccionados
                                        selectedAdicionales.push({
                                            id: adicional.id,
                                            nombre: adicional.nombre,
                                            precio: parseFloat(adicional.precio)
                                        });
                                    } else {
                                        // Quitar de seleccionados
                                        selectedAdicionales = selectedAdicionales.filter(a => a.id != adicional.id);
                                    }
                                    
                                    log('Adicionales seleccionados:', selectedAdicionales);
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
            
            // Cambio de plazo en el paso 2
            if (plazoSelect) {
                plazoSelect.addEventListener('change', function() {
                    selectedPlazoId = this.value;
                    log('Plazo seleccionado:', selectedPlazoId);
                    
                    if (selectedProduct) {
                        cargarOpciones(selectedProduct.id, selectedPlazoId);
                    }
                });
            }
            
            // Navegación entre pasos - Botones específicos
            const btnPaso1 = document.getElementById('btn-paso1');
            const btnPaso2 = document.getElementById('btn-paso2');
            const btnPaso3 = document.getElementById('btn-paso3');
            const btnPaso4 = document.getElementById('btn-paso4');
            
            if (btnPaso1) {
                btnPaso1.addEventListener('click', function() {
                    log('Botón paso 1 clickeado');
                    
                    if (!selectedProduct) {
                        alert('Por favor, seleccione un producto.');
                        return;
                    }
                    
                    cargarOpciones(selectedProduct.id, selectedPlazoId);
                    cambiarPaso(2);
                });
            }
            
            if (btnPaso2) {
                btnPaso2.addEventListener('click', function() {
                    log('Botón paso 2 clickeado');
                    
                    if (!selectedOption) {
                        alert('Por favor, seleccione una opción.');
                        return;
                    }
                    
                    // Verificar si el producto seleccionado puede tener adicionales
                    const productosConAdicionales = [
                        'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                        'ASCENSORES HIDRAULICOS',
                        'ADICIONALES ASCENSORES HIDRAULICOS',
                        'MONTACARGAS',
                        'MONTACARGAS - MAQUINA TAMBOR',
                        'SALVAESCALERAS'
                    ];
                    
                    // Depuración: Mostrar el nombre del producto seleccionado
                    log('Nombre del producto seleccionado:', selectedProduct.nombre);
                    
                    // Forzar que ASCENSORES HIDRAULICOS siempre tenga adicionales
                    let tieneAdicionales = false;
                    
                    // FORZAR que todos los productos de ASCENSORES HIDRAULICOS siempre muestren adicionales
                    if (selectedProduct.nombre.toUpperCase().indexOf('HIDRAULIC') !== -1) {
                        // Forzar adicionales para productos hidráulicos
                        tieneAdicionales = true;
                        log('Producto HIDRAULICO detectado. Forzando mostrar adicionales.');
                        
                        // Ir directamente a cargar adicionales
                        cargarAdicionales(selectedProduct.id);
                        cambiarPaso(3);
                        return; // Importante: salir de la función aquí
                    } else {
                        // Verificación normal para otros productos
                        for (const productoPermitido of productosConAdicionales) {
                            log('Verificando si "' + selectedProduct.nombre + '" incluye "' + productoPermitido + '"');
                            if (selectedProduct.nombre.toUpperCase().includes(productoPermitido)) {
                                tieneAdicionales = true;
                                log('¡Coincidencia encontrada! Producto tiene adicionales.');
                                break;
                            }
                        }
                    }
                    
                    if (tieneAdicionales) {
                        cargarAdicionales(selectedProduct.id);
                        cambiarPaso(3);
                    } else {
                        log('Este producto no tiene adicionales, saltando al resumen');
                        actualizarResumen();
                        cambiarPaso(4);
                    }
                });
            }
            
            if (btnPaso3) {
                btnPaso3.addEventListener('click', function() {
                    log('Botón paso 3 clickeado');
                    cambiarPaso(4);
                });
            }
            
            // Manejar selección de forma de pago
            const formasPagoItems = document.querySelectorAll('.forma-pago-item');
            if (formasPagoItems && formasPagoItems.length > 0) {
                log('Inicializando eventos para ' + formasPagoItems.length + ' formas de pago');
                
                formasPagoItems.forEach(item => {
                    // Agregar evento click a cada forma de pago
                    item.addEventListener('click', function() {
                        log('Forma de pago clickeada:', this.dataset.nombre);
                        
                        // Quitar selección anterior
                        document.querySelectorAll('.forma-pago-item').forEach(p => p.classList.remove('selected'));
                        
                        // Seleccionar esta forma de pago
                        this.classList.add('selected');
                        
                        // Guardar forma de pago seleccionada
                        selectedFormaPago = {
                            nombre: this.dataset.nombre,
                            descuento: parseFloat(this.dataset.descuento)
                        };
                        
                        log('Forma de pago seleccionada:', selectedFormaPago);
                    });
                });
            } else {
                log('No se encontraron elementos de forma de pago');
            }
            
            // Botón para pasar de forma de pago a datos del usuario
            if (btnPaso4) {
                btnPaso4.addEventListener('click', function() {
                    log('Botón paso 4 clickeado');
                    
                    if (!selectedFormaPago) {
                        alert('Por favor, seleccione una forma de pago.');
                        return;
                    }
                    
                    cambiarPaso(5);
                });
            }
            
            // Botón para pasar de datos del usuario a resumen
            const btnPaso5 = document.getElementById('btn-paso5');
            if (btnPaso5) {
                btnPaso5.addEventListener('click', function() {
                    log('Botón paso 5 clickeado');
                    
                    // Validar formulario
                    const nombre = document.getElementById('nombre').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const telefono = document.getElementById('telefono').value.trim();
                    
                    if (!nombre) {
                        alert('Por favor, ingrese su nombre.');
                        return;
                    }
                    
                    if (!email) {
                        alert('Por favor, ingrese su email.');
                        return;
                    }
                    
                    if (!telefono) {
                        alert('Por favor, ingrese su teléfono.');
                        return;
                    }
                    
                    // Guardar datos del usuario
                    const datosUsuario = {
                        nombre: nombre,
                        email: email,
                        telefono: telefono
                    };
                    
                    log('Datos del usuario:', datosUsuario);
                    
                    // Actualizar resumen con datos del usuario
                    actualizarResumen(datosUsuario);
                    cambiarPaso(6);
                });
            }
            
            // Navegación entre pasos - Botones anteriores
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.dataset.prev);
                    log('Botón anterior clickeado, volviendo al paso', prevStep);
                    cambiarPaso(prevStep);
                });
            });
            
            // Función para cambiar de paso
            function cambiarPaso(stepNumber) {
                log('Cambiando al paso', stepNumber);
                
                // Ocultar todos los pasos
                stepContents.forEach(content => content.classList.remove('active'));
                steps.forEach(step => step.classList.remove('active'));
                
                // Mostrar el paso seleccionado
                document.getElementById(`step-${stepNumber}`).classList.add('active');
                document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');
            }
            
            // Función para actualizar el resumen
            function actualizarResumen(datosUsuario = null) {
                log('Actualizando resumen');
                
                // Actualizar el plazo seleccionado
                if (plazoSelect) {
                    const plazoText = plazoSelect.options[plazoSelect.selectedIndex].text;
                    plazoResumen.innerHTML = `
                        <div class="item-name">Plazo de entrega</div>
                        <div class="item-value">${plazoText}</div>
                    `;
                    log('Plazo en resumen actualizado:', plazoText);
                }
                
                if (selectedProduct) {
                    productoResumen.innerHTML = `
                        <div class="item-name">${selectedProduct.nombre}</div>
                        <div class="item-price">-</div>
                    `;
                    log('Producto en resumen actualizado:', selectedProduct.nombre);
                    
                    if (selectedOption) {
                        opcionResumen.innerHTML = `
                            <div class="item-name">${selectedOption.nombre}</div>
                            <div class="item-price">$${formatNumber(selectedOption.precio)}</div>
                        `;
                        log('Opción en resumen actualizada:', { nombre: selectedOption.nombre, precio: selectedOption.precio });
                    }
                }
                
                // Actualizar datos del usuario
                if (datosUsuario) {
                    const datosUsuarioResumen = document.getElementById('datos-usuario-resumen');
                    datosUsuarioResumen.innerHTML = `
                        <div class="item-name">Datos del cliente</div>
                        <div class="item-value">
                            <strong>Nombre:</strong> ${datosUsuario.nombre}<br>
                            <strong>Email:</strong> ${datosUsuario.email}<br>
                            <strong>Teléfono:</strong> ${datosUsuario.telefono}
                        </div>
                    `;
                    log('Datos del usuario en resumen actualizados:', datosUsuario);
                }
                
                // Actualizar forma de pago y descuento
                if (selectedFormaPago) {
                    const formaPagoResumen = document.getElementById('forma-pago-resumen');
                    const descuentoResumen = document.getElementById('descuento-resumen');
                    
                    formaPagoResumen.innerHTML = `
                        <div class="item-name">Forma de pago</div>
                        <div class="item-value">${selectedFormaPago.nombre} (${selectedFormaPago.descuento}% de descuento)</div>
                    `;
                    
                    // Calcular descuento
                    let subtotal = selectedOption ? selectedOption.precio : 0;
                    selectedAdicionales.forEach(adicional => {
                        subtotal += adicional.precio;
                    });
                    
                    const descuentoMonto = (subtotal * selectedFormaPago.descuento) / 100;
                    
                    descuentoResumen.innerHTML = `
                        <div class="item-name">Descuento (${selectedFormaPago.descuento}%)</div>
                        <div class="item-price">-$${formatNumber(descuentoMonto)}</div>
                    `;
                    
                    log('Forma de pago en resumen actualizada:', selectedFormaPago);
                    log('Descuento calculado:', descuentoMonto);
                }
                
                // Actualizar adicionales
                if (selectedAdicionales.length > 0) {
                    adicionalesResumen.innerHTML = '';
                    selectedAdicionales.forEach(adicional => {
                        const adicionalItem = document.createElement('div');
                        adicionalItem.className = 'summary-item';
                        adicionalItem.innerHTML = `
                            <div class="item-name">${adicional.nombre}</div>
                            <div class="item-price">$${formatNumber(adicional.precio)}</div>
                        `;
                        adicionalesResumen.appendChild(adicionalItem);
                    });
                    log('Adicionales en resumen actualizados:', selectedAdicionales);
                } else {
                    adicionalesResumen.innerHTML = `
                        <div class="summary-item">
                            <div class="item-name">No hay adicionales seleccionados</div>
                            <div class="item-price">$0,00</div>
                        </div>
                    `;
                    log('No hay adicionales seleccionados');
                }
                
                // Calcular total
                let subtotal = selectedOption ? selectedOption.precio : 0;
                selectedAdicionales.forEach(adicional => {
                    subtotal += adicional.precio;
                });
                
                // Aplicar descuento si hay forma de pago seleccionada
                let total = subtotal;
                if (selectedFormaPago) {
                    const descuento = (subtotal * selectedFormaPago.descuento) / 100;
                    total = subtotal - descuento;
                }
                
                totalPrecio.textContent = `$${formatNumber(total)}`;
                log('Total actualizado:', total);
            }
            
            // Función para formatear números
            function formatNumber(number) {
                return number.toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).replace('.', ',');
            }
            
            // Generar PDF detallado y guardar presupuesto
            document.getElementById('generar-pdf').addEventListener('click', function() {
                log('Botón generar PDF clickeado');
                
                // Obtener datos del usuario
                const nombre = document.getElementById('nombre').value.trim();
                const email = document.getElementById('email').value.trim();
                const telefono = document.getElementById('telefono').value.trim();
                
                if (!nombre || !email || !telefono) {
                    alert('Por favor, complete todos los campos en el paso anterior.');
                    cambiarPaso(5);
                    return;
                }
                
                // Calcular subtotal y descuento
                let subtotal = selectedOption ? selectedOption.precio : 0;
                selectedAdicionales.forEach(adicional => {
                    subtotal += adicional.precio;
                });
                
                let descuentoMonto = 0;
                if (selectedFormaPago) {
                    descuentoMonto = (subtotal * selectedFormaPago.descuento) / 100;
                }
                
                const total = subtotal - descuentoMonto;
                
                // Preparar datos para guardar
                const presupuestoData = {
                    nombre: nombre,
                    email: email,
                    telefono: telefono,
                    producto: selectedProduct,
                    opcion: selectedOption,
                    plazo: {
                        id: selectedPlazoId,
                        nombre: document.getElementById('plazo-select').options[document.getElementById('plazo-select').selectedIndex].text
                    },
                    formaPago: {
                        ...selectedFormaPago,
                        descuentoMonto: descuentoMonto
                    },
                    adicionales: selectedAdicionales,
                    subtotal: subtotal,
                    total: total
                };
                
                log('Datos del presupuesto a guardar:', presupuestoData);
                
                // Mostrar mensaje de carga
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                this.disabled = true;
                
                // Guardar presupuesto en la base de datos
                fetch('guardar_presupuesto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(presupuestoData)
                })
                .then(response => response.json())
                .then(data => {
                    log('Respuesta del servidor (guardar):', data);
                    
                    if (data.success) {
                        // Obtener el ID del presupuesto guardado
                        const presupuestoId = data.presupuesto_id;
                        log('ID del presupuesto guardado:', presupuestoId);
                        
                        if (presupuestoId) {
                            // Mostrar mensaje de éxito
                            document.getElementById('mensaje-final').style.display = 'block';
                            
                            // Restaurar botón
                            document.getElementById('generar-pdf').innerHTML = 'Generar PDF';
                            document.getElementById('generar-pdf').disabled = false;
                            
                            // Generar PDF detallado directamente
                            const pdfUrl = 'presupuestos/pdf_detallado.php?id=' + presupuestoId;
                            log('Redirigiendo a:', pdfUrl);
                            
                            // Usar setTimeout para asegurar que el mensaje se muestre antes de la redirección
                            setTimeout(function() {
                                window.location.href = pdfUrl;
                            }, 1000);
                            
                            return { success: true };
                        } else {
                            // Si no hay ID, mostrar mensaje de error
                            alert('No se pudo obtener el ID del presupuesto. Intente nuevamente.');
                            document.getElementById('generar-pdf').innerHTML = 'Generar PDF';
                            document.getElementById('generar-pdf').disabled = false;
                            return { success: false };
                        }
                    } else {
                        throw new Error(data.message || 'Error al guardar el presupuesto');
                    }
                })
                
                .catch(error => {
                    log('Error:', error.message);
                    alert('Error: ' + error.message);
                    
                    // Restaurar botón
                    this.innerHTML = 'Generar PDF';
                    this.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
