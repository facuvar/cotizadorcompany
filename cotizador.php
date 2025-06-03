<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador de Ascensores - Sistema Ordenado</title>
    <link rel="stylesheet" href="assets/css/modern-dark-theme.css">
    <style>
        /* Ajustes específicos para el cotizador */
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: var(--font-primary);
            margin: 0;
            overflow-x: hidden;
        }

        /* Header público */
        .public-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: var(--spacing-lg) var(--spacing-xl);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .logo-icon svg {
            width: 24px;
            height: 24px;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--text-primary);
        }

        .logo-subtitle {
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        /* Main container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-xl);
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: var(--spacing-xl);
            min-height: calc(100vh - 100px);
        }

        /* Content area */
        .content-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            overflow-y: auto;
            max-height: calc(100vh - 140px);
        }

        .content-header {
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }

        .content-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .content-description {
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            font-size: var(--text-base);
        }

        /* Category cards */
        .category-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .category-card.active {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 1px var(--accent-primary);
        }

        .category-header {
            padding: var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .category-header:hover {
            background: var(--bg-hover);
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .category-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-primary);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .category-icon svg {
            width: 20px;
            height: 20px;
        }

        .category-details {
            display: flex;
            flex-direction: column;
        }

        .category-title {
            font-size: var(--text-base);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .category-count {
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .expand-icon {
            color: var(--text-secondary);
            transition: transform 0.3s ease;
        }

        .expand-icon svg {
            width: 16px;
            height: 16px;
        }

        .category-card.active .expand-icon {
            transform: rotate(180deg);
        }

        .category-options {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }

        .category-card.active .category-options {
            max-height: 5000px;
        }

        .option-item {
            padding: var(--spacing-md) var(--spacing-lg);
            display: flex;
            align-items: center;
            border-top: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
            position: relative;
        }

        .option-item:hover {
            background: var(--bg-hover);
        }

        .option-checkbox {
            width: 20px;
            height: 20px;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-right: var(--spacing-md);
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
            z-index: 1;
        }

        .option-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            margin: 0;
            z-index: 2;
        }

        .option-checkbox.checked {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
        }

        .option-checkbox.checked::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .option-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .option-name {
            font-size: var(--text-sm);
            color: var(--text-primary);
            font-weight: 500;
        }

        .option-price {
            font-size: var(--text-xs);
            font-weight: 600;
            color: var(--accent-success);
            font-family: var(--font-mono);
        }

        .price-unavailable {
            color: var(--text-muted);
            font-style: italic;
        }

        /* Summary panel */
        .summary-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            display: flex;
            flex-direction: column;
            height: fit-content;
            max-height: calc(100vh - 140px);
            position: sticky;
            top: 100px;
        }

        .summary-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .summary-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }

        .summary-subtitle {
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .summary-content {
            padding: var(--spacing-lg);
            flex: 1;
            overflow-y: auto;
        }

        /* Delivery options */
        .delivery-section {
            margin-bottom: var(--spacing-xl);
        }

        .section-title {
            font-size: var(--text-xs);
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-md);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .delivery-options {
            display: grid;
            gap: var(--spacing-sm);
        }

        .delivery-option {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .delivery-option:hover {
            border-color: var(--accent-primary);
            background: var(--bg-hover);
        }

        .delivery-option.selected {
            border-color: var(--accent-primary);
            background: rgba(59, 130, 246, 0.1);
        }

        .delivery-option input {
            position: absolute;
            opacity: 0;
        }

        .delivery-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .delivery-days {
            font-size: var(--text-sm);
            font-weight: 600;
            color: var(--text-primary);
        }

        .delivery-label {
            font-size: var(--text-xs);
            color: var(--text-secondary);
        }

        /* Selected items */
        .selected-items-section {
            margin-bottom: var(--spacing-xl);
        }

        .selected-item {
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            padding: var(--spacing-sm) var(--spacing-md);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: var(--text-xs);
        }

        .selected-item-name {
            color: var(--text-primary);
            flex: 1;
            margin-right: var(--spacing-sm);
        }

        .selected-item-price {
            color: var(--accent-success);
            font-weight: 600;
            font-family: var(--font-mono);
            font-size: var(--text-xs);
        }

        .empty-state {
            text-align: center;
            padding: var(--spacing-xl);
            color: var(--text-muted);
        }

        .empty-state-icon {
            font-size: 2rem;
            margin-bottom: var(--spacing-md);
            opacity: 0.3;
        }

        .empty-state-icon svg {
            width: 48px;
            height: 48px;
        }

        .empty-state p {
            font-size: var(--text-sm);
        }

        /* Totals and footer */
        .summary-footer {
            padding: var(--spacing-lg);
            border-top: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }

        .total-section {
            margin-bottom: var(--spacing-lg);
        }

        .total-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: var(--spacing-xs);
            font-size: var(--text-sm);
        }

        .total-label {
            color: var(--text-secondary);
        }

        .total-value {
            color: var(--text-primary);
            font-family: var(--font-mono);
        }

        .total-final {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding-top: var(--spacing-md);
            border-top: 2px solid var(--border-color);
        }

        .total-final .total-label {
            font-size: var(--text-base);
            font-weight: 600;
            color: var(--text-primary);
        }

        .total-final .total-value {
            font-size: var(--text-xl);
            font-weight: 700;
            color: var(--accent-primary);
        }

        .action-buttons {
            display: grid;
            gap: var(--spacing-md);
        }

        /* Customer form modal */
        .customer-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .customer-modal.active {
            display: flex;
        }

        .customer-modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            text-align: center;
            margin-bottom: var(--spacing-lg);
        }

        .modal-title {
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }

        .modal-subtitle {
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            
            .summary-panel {
                position: relative;
                top: 0;
                max-height: none;
            }
        }

        /* Loading skeleton */
        .skeleton {
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            position: relative;
            overflow: hidden;
        }

        .skeleton::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%);
            animation: skeleton-loading 2s ease-in-out infinite;
        }

        @keyframes skeleton-loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body>
    <!-- Header Público -->
    <header class="public-header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-icon">
                    <span id="logo-icon"></span>
                </div>
                <div class="logo-text">
                    <div class="logo-title">Cotizador de Ascensores</div>
                    <div class="logo-subtitle">Presupuestos profesionales en minutos</div>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="admin/gestionar_datos.php" class="btn btn-secondary">
                    <span id="admin-icon"></span>
                    Gestionar Orden
                </a>
                <a href="index_moderno.php" class="btn btn-secondary">
                    <span id="home-icon"></span>
                    Inicio
                </a>
                <button class="btn btn-primary" onclick="showCustomerForm()" id="generate-quote-btn" disabled>
                    <span id="pdf-icon"></span>
                    Generar PDF
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <div class="main-container">
        <!-- Sección de Opciones -->
        <div class="content-section">
            <div class="content-header">
                <h1 class="content-title">Configura tu Ascensor</h1>
                <p class="content-description">
                    Selecciona las características y opciones que necesitas para tu ascensor. 
                    El precio se calculará automáticamente según tus selecciones.
                </p>
            </div>

            <div id="categories-container">
                <!-- Las categorías se cargarán aquí -->
                <div class="skeleton" style="height: 80px; margin-bottom: 1rem;"></div>
                <div class="skeleton" style="height: 80px; margin-bottom: 1rem;"></div>
                <div class="skeleton" style="height: 80px; margin-bottom: 1rem;"></div>
            </div>
        </div>

        <!-- Panel de Resumen -->
        <div class="summary-panel">
            <div class="summary-header">
                <h2 class="summary-title">Resumen del Presupuesto</h2>
                <p class="summary-subtitle">Tu configuración actual</p>
            </div>

            <div class="summary-content">
                <!-- Selector de Plazo -->
                <div class="delivery-section">
                    <h3 class="section-title">Plazo de Entrega</h3>
                    <div class="delivery-options">
                        <label class="delivery-option">
                            <input type="radio" name="plazo" value="160">
                            <div class="delivery-info">
                                <span class="delivery-days">160-180 Días</span>
                                <span class="delivery-label">Extendido</span>
                            </div>
                        </label>
                        <label class="delivery-option selected">
                            <input type="radio" name="plazo" value="90" checked>
                            <div class="delivery-info">
                                <span class="delivery-days">90 Días</span>
                                <span class="delivery-label">Estándar</span>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="plazo" value="270">
                            <div class="delivery-info">
                                <span class="delivery-days">270 Días</span>
                                <span class="delivery-label">Flexible</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Items Seleccionados -->
                <div class="selected-items-section">
                    <h3 class="section-title">Productos Seleccionados</h3>
                    <div id="selected-items">
                        <div class="empty-state">
                            <div class="empty-state-icon" id="empty-icon"></div>
                            <p>No has seleccionado ningún producto</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con Totales -->
            <div class="summary-footer">
                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">Subtotal:</span>
                        <span class="total-value" id="subtotal">AR$0.00</span>
                    </div>
                    <div class="total-row" id="discount-row" style="display: none;">
                        <span class="total-label">Descuento:</span>
                        <span class="total-value" id="discount" style="color: var(--accent-warning);">AR$0.00</span>
                    </div>
                </div>
                
                <div class="total-final">
                    <span class="total-label">Total:</span>
                    <span class="total-value" id="total-amount">AR$0.00</span>
                </div>

                <div class="action-buttons" style="margin-top: var(--spacing-lg);">
                    <button class="btn btn-primary btn-lg" onclick="showCustomerForm()" id="generate-quote-btn-2" disabled>
                        <span id="pdf-icon-2"></span>
                        Generar Presupuesto
                    </button>
                    <button class="btn btn-secondary" onclick="resetQuote()">
                        <span id="reset-icon"></span>
                        Reiniciar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Datos del Cliente -->
    <div id="customer-modal" class="customer-modal">
        <div class="customer-modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Datos del Cliente</h3>
                <p class="modal-subtitle">Complete la información para generar el presupuesto</p>
            </div>

            <form id="customer-form">
                <div class="form-group">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <input type="text" name="empresa" class="form-control">
                </div>

                <div style="display: flex; gap: var(--spacing-md); justify-content: flex-end; margin-top: var(--spacing-xl);">
                    <button type="button" class="btn btn-secondary" onclick="hideCustomerForm()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span id="download-icon"></span>
                        Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/modern-icons.js?v=1"></script>
    <script>
        // Variables globales
        let categorias = [];
        let opciones = [];
        let selectedOptions = [];
        let currentDelivery = '90';

        // Cargar iconos
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar iconos SVG
            document.getElementById('logo-icon').innerHTML = modernUI.getIcon('chart');
            document.getElementById('admin-icon').innerHTML = modernUI.getIcon('settings');
            document.getElementById('home-icon').innerHTML = modernUI.getIcon('home');
            document.getElementById('pdf-icon').innerHTML = modernUI.getIcon('pdf');
            document.getElementById('pdf-icon-2').innerHTML = modernUI.getIcon('pdf');
            document.getElementById('reset-icon').innerHTML = modernUI.getIcon('refresh');
            document.getElementById('download-icon').innerHTML = modernUI.getIcon('download');
            document.getElementById('empty-icon').innerHTML = modernUI.getIcon('package');

            // Cargar datos
            cargarCategorias();
            
            // Event listeners
            setupEventListeners();
        });

        function setupEventListeners() {
            // Selector de plazo
            document.querySelectorAll('input[name="plazo"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const nuevoPlazo = this.value;
                    console.log(`Cambiando plazo de entrega de ${currentDelivery} días a ${nuevoPlazo} días`);
                    
                    currentDelivery = nuevoPlazo;
                    
                    // Actualizar UI del selector de plazo
                    document.querySelectorAll('.delivery-option').forEach(opt => opt.classList.remove('selected'));
                    this.closest('.delivery-option').classList.add('selected');
                    
                    // NUEVA FUNCIONALIDAD: Actualizar todos los precios para el nuevo plazo
                    actualizarPreciosPorPlazo();
                    
                    // Recalcular totales con el nuevo plazo
                    updateTotals();
                    updateSelectedItems();
                    
                    console.log(`Todos los precios actualizados para plazo de ${nuevoPlazo} días`);
                });
            });

            // Form de cliente
            document.getElementById('customer-form').addEventListener('submit', function(e) {
                e.preventDefault();
                generatePDF();
            });

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideCustomerForm();
                }
            });
        }

        // NUEVA FUNCIÓN: Actualizar todos los precios cuando cambia el plazo de entrega
        function actualizarPreciosPorPlazo() {
            console.log(`Actualizando precios para plazo de ${currentDelivery} días`);
            
            // Actualizar precios en todas las opciones mostradas
            opciones.forEach(opcion => {
                const priceElement = document.getElementById(`price-${opcion.id}`);
                if (priceElement) {
                    const nuevoPrecio = getOptionPrice(opcion);
                    priceElement.innerHTML = nuevoPrecio;
                    
                    // Log para adicionales con RESTAR para verificar que funciona correctamente
                    if (opcion.nombre && opcion.nombre.toLowerCase().includes('restar')) {
                        console.log(`Precio actualizado para adicional que resta: ${opcion.nombre} = ${nuevoPrecio}`);
                    }
                }
            });
            
            // Mostrar mensaje informativo al usuario
            if (typeof modernUI !== 'undefined' && modernUI.showToast) {
                modernUI.showToast(`Precios actualizados para entrega en ${currentDelivery} días`, 'info');
            }
        }

        async function cargarCategorias() {
            try {
                // Agregar cache-busting para evitar problemas de cache
                const timestamp = new Date().getTime();
                const response = await fetch(`api/get_categories_ordered.php?t=${timestamp}`);
                const data = await response.json();
                
                if (data.success) {
                    categorias = data.categorias;
                    opciones = data.opciones;
                    
                    // DEPURACIÓN DETALLADA
                    console.log(`🔄 API: ${opciones.length} opciones cargadas`);
                    
                    // Verificar ascensores específicamente
                    const ascensores = categorias.find(cat => cat.nombre.toLowerCase().includes('ascensor'));
                    if (ascensores) {
                        const opcionesAscensores = opciones.filter(op => parseInt(op.categoria_id) === parseInt(ascensores.id));
                        console.log(`🔄 API: ${opcionesAscensores.length} ascensores encontrados`);
                    }
                    
                    renderCategorias();
                } else {
                    throw new Error(data.message || 'Error al cargar categorías');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('categories-container').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">${modernUI.getIcon('alert-circle')}</div>
                        <p>Error al cargar las categorías</p>
                        <button class="btn btn-primary btn-sm" onclick="cargarCategorias()">Reintentar</button>
                    </div>
                `;
            }
        }
        
        function extraerNumeroParadas(nombre) {
            // Caso especial para Gearless - asignarle un número alto para que aparezca al final
            if (nombre.toLowerCase().includes('gearless')) {
                return 1000; // Un número muy alto para que aparezca después de todas las paradas numeradas
            }
            
            // Extracción normal para nombres con formato "X Paradas"
            if (/(\d+)\s+Paradas/.test(nombre)) {
                return parseInt(nombre.match(/(\d+)\s+Paradas/)[1]);
            }
            
            return 999; // Valor por defecto para los que no tienen número de paradas
        }
        
        function ordenarOpciones() {
            opciones.sort((a, b) => {
                // Primero ordenar por categoría
                if (a.categoria_id !== b.categoria_id) {
                    return a.categoria_id - b.categoria_id;
                }
                
                // Si son ascensores, ordenar por número de paradas
                if (a.categoria_id == 1) {
                    const paradasA = extraerNumeroParadas(a.nombre);
                    const paradasB = extraerNumeroParadas(b.nombre);
                    
                    if (paradasA !== paradasB) {
                        return paradasA - paradasB;
                    }
                }
                
                // Si tienen la misma categoría y mismo número de paradas (o no son ascensores), ordenar por nombre
                return a.nombre.localeCompare(b.nombre);
            });
        }

        function renderCategorias() {
            const container = document.getElementById('categories-container');
            
            if (categorias.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">${modernUI.getIcon('folder')}</div>
                        <p>No hay categorías disponibles</p>
                    </div>
                `;
                return;
            }

            // Reordenar categorías para que Ascensores aparezca antes que Adicionales
            const categoriasOrdenadas = [...categorias].sort((a, b) => {
                const nombreA = a.nombre.toLowerCase();
                const nombreB = b.nombre.toLowerCase();
                
                // Si una es ascensores y la otra adicionales, ascensores va primero
                if (nombreA.includes('ascensor') && nombreB.includes('adicional')) {
                    return -1;
                }
                if (nombreA.includes('adicional') && nombreB.includes('ascensor')) {
                    return 1;
                }
                
                // Para el resto, mantener el orden original de la base de datos
                return a.orden - b.orden;
            });

            container.innerHTML = categoriasOrdenadas.map(categoria => {
                const categoryOptions = opciones.filter(op => {
                    // Convertir ambos a números para comparación estricta
                    const opCatId = parseInt(op.categoria_id);
                    const catId = parseInt(categoria.id);
                    return opCatId === catId;
                });
                
                // DEPURACIÓN ESPECÍFICA: Solo para ascensores
                if (categoria.nombre.toLowerCase().includes('ascensor')) {
                    console.log(`🔍 ASCENSORES - RENDERIZADO:`);
                    console.log(`   Filtradas: ${categoryOptions.length} de ${opciones.length} totales`);
                    console.log(`   Categoría ID: ${categoria.id}, Tipo: ${typeof categoria.id}`);
                    
                    // Verificar ascensores en memoria
                    const ascensoresEnMemoria = opciones.filter(op => parseInt(op.categoria_id) === 1);
                    console.log(`   En memoria: ${ascensoresEnMemoria.length} ascensores`);
                    
                    // Mostrar IDs de las opciones filtradas
                    const idsFilterados = categoryOptions.map(op => op.id).sort((a, b) => a - b);
                    console.log(`   IDs filtrados: ${idsFilterados.join(', ')}`);
                    
                    // Verificar si faltan IDs específicos
                    const idsEnMemoria = ascensoresEnMemoria.map(op => op.id).sort((a, b) => a - b);
                    const idsFaltantes = idsEnMemoria.filter(id => !idsFilterados.includes(id));
                    if (idsFaltantes.length > 0) {
                        console.log(`   ⚠️ IDs FALTANTES: ${idsFaltantes.join(', ')}`);
                    }
                    
                    // NUEVO: Verificar antes del mapeo
                    console.log(`🔧 ANTES DEL MAPEO:`);
                    console.log(`   Opciones a mapear: ${categoryOptions.length}`);
                    console.log(`   Primer ID: ${categoryOptions[0]?.id}, Último ID: ${categoryOptions[categoryOptions.length - 1]?.id}`);
                }
                
                // Mapear iconos según el nombre de la categoría
                let iconName = 'folder';
                if (categoria.nombre.toLowerCase().includes('ascensor')) {
                    iconName = 'building';
                } else if (categoria.nombre.toLowerCase().includes('adicional')) {
                    iconName = 'tool';
                } else if (categoria.nombre.toLowerCase().includes('descuento')) {
                    iconName = 'tag';
                }
                
                return `
                    <div class="category-card" id="category-${categoria.id}">
                        <div class="category-header" data-category-id="${categoria.id}" onclick="handleCategoryClick(${categoria.id})">
                            <div class="category-info">
                                <div class="category-icon">
                                    ${modernUI.getIcon(iconName)}
                                </div>
                                <div class="category-details">
                                    <div class="category-title">${categoria.nombre}</div>
                                    <div class="category-count">${categoryOptions.length} opciones disponibles</div>
                                </div>
                            </div>
                            <div class="expand-icon">
                                ${modernUI.getIcon('chevron-down')}
                            </div>
                        </div>
                        <div class="category-options">
                            ${(() => {
                                // DEPURACIÓN: Log antes del mapeo para ascensores
                                if (categoria.nombre.toLowerCase().includes('ascensor')) {
                                    console.log(`🔧 MAPEANDO ${categoryOptions.length} opciones de ascensores...`);
                                }
                                
                                const htmlOptions = categoryOptions.map(opcion => `
                                    <div class="option-item" data-categoria-id="${categoria.id}" onclick="handleOptionClick(${opcion.id})">
                                        <div class="option-checkbox" data-option-id="${opcion.id}">
                                            <input type="checkbox" data-option-id="${opcion.id}" onclick="event.stopPropagation(); handleOptionClick(${opcion.id});">
                                        </div>
                                        <div class="option-details">
                                            <div class="option-name">${opcion.nombre}</div>
                                            <div class="option-price" id="price-${opcion.id}">
                                                ${getOptionPrice(opcion)}
                                            </div>
                                        </div>
                                    </div>
                                `);
                                
                                // DEPURACIÓN: Log después del mapeo para ascensores
                                if (categoria.nombre.toLowerCase().includes('ascensor')) {
                                    console.log(`🔧 HTML generado para ${htmlOptions.length} opciones`);
                                    console.log(`🔧 Longitud del HTML: ${htmlOptions.join('').length} caracteres`);
                                }
                                
                                return htmlOptions.join('');
                            })()}
                        </div>
                    </div>
                `;
            }).join('');

            // Configurar event listeners para categorías y opciones
            setupCategoryEventListeners();
            
            // BACKUP: También agregar listeners directos a cada elemento
            setupDirectListeners();
            
            // NUEVA FUNCIONALIDAD: Aplicar filtrado inicial de adicionales
            setTimeout(() => {
                filtrarAdicionales();
            }, 100);
            
            // VERIFICACIÓN FINAL: Contar elementos en el DOM
            setTimeout(() => {
                const ascensoresCard = document.querySelector('[data-category-id="1"]');
                if (ascensoresCard) {
                    const optionItems = ascensoresCard.querySelectorAll('.option-item');
                    console.log(`🎯 VERIFICACIÓN FINAL - Elementos de ascensores en DOM: ${optionItems.length}`);
                    
                    // Verificar IDs específicos en el DOM
                    const idsEnDOM = Array.from(optionItems).map(item => {
                        const checkbox = item.querySelector('.option-checkbox');
                        return checkbox ? parseInt(checkbox.getAttribute('data-option-id')) : null;
                    }).filter(id => id !== null).sort((a, b) => a - b);
                    
                    console.log(`🎯 IDs en DOM: ${idsEnDOM.join(', ')}`);
                    
                    // Verificar si falta el ID 514 y siguientes
                    const maxIdEnDOM = Math.max(...idsEnDOM);
                    console.log(`🎯 ID máximo en DOM: ${maxIdEnDOM}`);
                    
                    if (maxIdEnDOM < 541) {
                        console.log(`⚠️ PROBLEMA: El ID máximo en DOM (${maxIdEnDOM}) es menor que 541`);
                    }
                }
            }, 50);

            // Expandir automáticamente la primera categoría si existe
            if (categoriasOrdenadas.length > 0) {
                const primeraCategoria = categoriasOrdenadas[0];
                const opcionesPrimera = opciones.filter(op => {
                    const opCatId = parseInt(op.categoria_id);
                    const catId = parseInt(primeraCategoria.id);
                    return opCatId === catId;
                });
                if (opcionesPrimera.length > 0) {
                    setTimeout(() => {
                        toggleCategory(primeraCategoria.id);
                    }, 100);
                }
            }
        }

        function setupCategoryEventListeners() {
            const container = document.getElementById('categories-container');
            
            if (!container) {
                console.error('No se encontró el contenedor de categorías');
                return;
            }
            
            // Usar un listener más simple y directo
            container.onclick = function(e) {
                console.log('CLICK GLOBAL detectado en:', e.target);
                
                // Buscar si es un header de categoría
                let element = e.target;
                while (element && element !== container) {
                    if (element.classList && element.classList.contains('category-header')) {
                        console.log('Header encontrado, categoría:', element.getAttribute('data-category-id'));
                        const categoryId = element.getAttribute('data-category-id');
                        if (categoryId) {
                            toggleCategory(parseInt(categoryId));
                        }
                        return false;
                    }
                    element = element.parentNode;
                }
                
                // Buscar si es un checkbox o input dentro de option-item
                if (e.target.tagName === 'INPUT' && e.target.type === 'checkbox') {
                    const checkboxElement = e.target.closest('.option-checkbox');
                    if (checkboxElement) {
                        const optionId = checkboxElement.getAttribute('data-option-id');
                        console.log('Checkbox click detectado, opción:', optionId);
                        if (optionId) {
                            toggleOption(parseInt(optionId), checkboxElement);
                            e.stopPropagation(); // Evitar propagación
                            return false;
                        }
                    }
                }
                
                // Buscar si es un item de opción
                element = e.target;
                while (element && element !== container) {
                    if (element.classList && element.classList.contains('option-item')) {
                        console.log('Option item encontrado');
                        e.preventDefault();
                        
                        const checkbox = element.querySelector('.option-checkbox');
                        if (checkbox) {
                            const optionId = checkbox.getAttribute('data-option-id');
                            console.log('Opción a procesar:', optionId);
                            if (optionId) {
                                toggleOption(parseInt(optionId), checkbox);
                            }
                        }
                        return false;
                    }
                    element = element.parentNode;
                }
                
                console.log('Click no procesado');
                return true;
            };
            
            console.log('Event listener global configurado');
        }

        function setupDirectListeners() {
            console.log('Configurando listeners directos como backup...');
            
            // Listeners directos para headers de categorías
            document.querySelectorAll('.category-header').forEach(header => {
                header.onclick = function(e) {
                    e.stopPropagation();
                    const categoryId = this.getAttribute('data-category-id');
                    console.log('Header click directo, categoría:', categoryId);
                    if (categoryId) {
                        toggleCategory(parseInt(categoryId));
                    }
                };
            });
            
            // Listeners directos para inputs de checkbox
            document.querySelectorAll('.option-checkbox input').forEach(input => {
                input.onclick = function(e) {
                    e.stopPropagation();
                    const checkbox = this.closest('.option-checkbox');
                    if (checkbox) {
                        const optionId = checkbox.getAttribute('data-option-id');
                        console.log('Checkbox click directo, opción:', optionId);
                        if (optionId) {
                            toggleOption(parseInt(optionId), checkbox);
                        }
                    }
                };
            });
            
            // Listeners directos para items de opciones
            document.querySelectorAll('.option-item').forEach(item => {
                item.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Option item click directo');
                    
                    const checkbox = this.querySelector('.option-checkbox');
                    if (checkbox) {
                        const optionId = checkbox.getAttribute('data-option-id');
                        console.log('Procesando opción directa:', optionId);
                        if (optionId) {
                            toggleOption(parseInt(optionId), checkbox);
                        }
                    }
                };
            });
            
            console.log('Listeners directos configurados');
        }

        function getOptionPrice(opcion) {
            try {
                // Verificar si es descuento
                if (opcion.categoria_id == 3 && opcion.descuento > 0) {
                    return `${opcion.descuento}% descuento`;
                }
                
                // Obtener precio según delivery actual
                const precio = opcion[`precio_${currentDelivery}_dias`];
                
                // Verificar si hay precio válido
                if (precio && precio > 0) {
                    // Formatear precio de forma segura
                    const precioFormateado = typeof modernUI !== 'undefined' && modernUI.formatCurrency 
                        ? modernUI.formatCurrency(precio)
                        : parseFloat(precio).toLocaleString('es-AR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    
                    // NUEVA FUNCIONALIDAD: Si el adicional tiene "RESTAR" en el título, mostrar con signo negativo
                    if (opcion.nombre && opcion.nombre.toLowerCase().includes('restar')) {
                        return `<span style="color: var(--accent-warning);">-AR$${precioFormateado}</span>`;
                    } else {
                        return `AR$${precioFormateado}`;
                    }
                }
                
                return '<span class="price-unavailable">Precio no disponible</span>';
                
            } catch (error) {
                // En caso de error, devolver un precio genérico sin interrumpir el renderizado
                console.warn('Error en getOptionPrice para opción', opcion.id, ':', error);
                return '<span class="price-unavailable">Precio no disponible</span>';
            }
        }

        function toggleCategory(categoryId) {
            const card = document.getElementById(`category-${categoryId}`);
            card.classList.toggle('active');
        }

        function toggleOption(optionId, checkboxElement) {
            console.log('toggleOption llamado para opción:', optionId, 'elemento:', checkboxElement);
            
            // Obtener el input dentro del checkbox
            const checkbox = checkboxElement.querySelector('input');
            if (!checkbox) {
                console.error('No se encontró el input checkbox en:', checkboxElement);
                return;
            }
            
            // Encontrar la opción para obtener su categoría
            const opcion = opciones.find(op => op.id == optionId);
            if (!opcion) {
                console.error('No se encontró la opción con ID:', optionId);
                return;
            }
            
            const categoriaId = opcion.categoria_id;
            
            // Si el evento viene del click directo en el input, usamos su estado actual
            // De lo contrario, invertimos el estado actual
            const isChecked = checkbox.checked;
            console.log('Estado actual del checkbox:', isChecked);
            
            // Lógica de selección única para ASCENSORES (1) y DESCUENTOS (3)
            if (categoriaId == 1 || categoriaId == 3) {
                if (isChecked) {
                    // Deseleccionar todas las otras opciones de la misma categoría
                    selectedOptions.filter(id => {
                        const otherOption = opciones.find(op => op.id == id);
                        return otherOption && otherOption.categoria_id == categoriaId;
                    }).forEach(id => {
                        if (id != optionId) {
                            removeSelectedOption(id);
                            // Actualizar visualmente el checkbox
                            const otherCheckbox = document.querySelector(`[data-option-id="${id}"].option-checkbox`);
                            if (otherCheckbox) {
                                const otherInput = otherCheckbox.querySelector('input');
                                if (otherInput) {
                                    otherInput.checked = false;
                                    otherCheckbox.classList.remove('checked');
                                }
                            }
                        }
                    });
                    
                    // Seleccionar la nueva opción
                    addSelectedOption(optionId);
                    checkboxElement.classList.add('checked');
                } else {
                    // Deseleccionar la opción actual
                    removeSelectedOption(optionId);
                    checkboxElement.classList.remove('checked');
                }
                
                // NUEVA FUNCIONALIDAD: Filtrar adicionales cuando se selecciona un ascensor
                if (categoriaId == 1) {
                    filtrarAdicionales();
                }
            } else {
                // Lógica normal para ADICIONALES (múltiple selección)
                checkboxElement.classList.toggle('checked', isChecked);
                
                if (isChecked) {
                    addSelectedOption(optionId);
                } else {
                    removeSelectedOption(optionId);
                }
            }
            
            updateTotals();
            updateSelectedItems();
            updateGenerateButton();
            
            console.log('Opciones seleccionadas después del cambio:', selectedOptions);
        }

        // NUEVA FUNCIÓN: Filtrar adicionales basado en la selección de ascensores
        function filtrarAdicionales() {
            // Buscar si hay un ascensor seleccionado
            const ascensorSeleccionado = selectedOptions.find(id => {
                const opcion = opciones.find(op => op.id == id);
                return opcion && opcion.categoria_id == 1;
            });
            
            // Encontrar la categoría de adicionales
            const categoriaAdicionales = categorias.find(cat => 
                cat.nombre.toLowerCase().includes('adicional')
            );
            
            if (!categoriaAdicionales) {
                console.log('No se encontró categoría de adicionales');
                return;
            }
            
            // Obtener todas las opciones de adicionales
            const todasLasOpcionesAdicionales = opciones.filter(op => 
                parseInt(op.categoria_id) === parseInt(categoriaAdicionales.id)
            );
            
            console.log('Total adicionales disponibles:', todasLasOpcionesAdicionales.length);
            
            // Si hay un ascensor seleccionado, filtrar adicionales
            let adicionalesFiltrados = todasLasOpcionesAdicionales;
            
            if (ascensorSeleccionado) {
                const opcionAscensor = opciones.find(op => op.id == ascensorSeleccionado);
                console.log('Ascensor seleccionado:', opcionAscensor?.nombre);
                
                // Si se selecciona electromecánico O gearless, mostrar solo adicionales con "electromecanico"
                if (opcionAscensor && 
                    (opcionAscensor.nombre.toLowerCase().includes('electromecanico') || 
                     opcionAscensor.nombre.toLowerCase().includes('gearless'))) {
                    
                    adicionalesFiltrados = todasLasOpcionesAdicionales.filter(adicional =>
                        adicional.nombre.toLowerCase().includes('electromecanico')
                    );
                    
                    console.log('Filtrando adicionales para electromecanico/gearless:', adicionalesFiltrados.length);
                }
                // Si se selecciona hidráulico, mostrar solo adicionales con "hidraulico"
                else if (opcionAscensor && 
                         opcionAscensor.nombre.toLowerCase().includes('hidraulico')) {
                    
                    adicionalesFiltrados = todasLasOpcionesAdicionales.filter(adicional =>
                        adicional.nombre.toLowerCase().includes('hidraulico')
                    );
                    
                    console.log('Filtrando adicionales para hidraulico:', adicionalesFiltrados.length);
                }
                // Si se selecciona montacargas, mostrar solo adicionales con "montacargas"
                else if (opcionAscensor && 
                         opcionAscensor.nombre.toLowerCase().includes('montacargas')) {
                    
                    adicionalesFiltrados = todasLasOpcionesAdicionales.filter(adicional =>
                        adicional.nombre.toLowerCase().includes('montacargas')
                    );
                    
                    console.log('Filtrando adicionales para montacargas:', adicionalesFiltrados.length);
                }
                // Si se selecciona salvaescaleras, mostrar solo adicionales con "salvaescaleras"
                else if (opcionAscensor && 
                         opcionAscensor.nombre.toLowerCase().includes('salvaescaleras')) {
                    
                    adicionalesFiltrados = todasLasOpcionesAdicionales.filter(adicional =>
                        adicional.nombre.toLowerCase().includes('salvaescaleras')
                    );
                    
                    console.log('Filtrando adicionales para salvaescaleras:', adicionalesFiltrados.length);
                }
            }
            
            // Actualizar la visualización de la categoría de adicionales
            actualizarVisualizacionAdicionales(categoriaAdicionales, adicionalesFiltrados);
        }
        
        // NUEVA FUNCIÓN: Actualizar la visualización de adicionales filtrados
        function actualizarVisualizacionAdicionales(categoria, adicionalesFiltrados) {
            const categoryCard = document.getElementById(`category-${categoria.id}`);
            if (!categoryCard) {
                console.log('No se encontró la tarjeta de categoría de adicionales');
                return;
            }
            
            // Actualizar el contador de opciones disponibles
            const categoryCount = categoryCard.querySelector('.category-count');
            if (categoryCount) {
                categoryCount.textContent = `${adicionalesFiltrados.length} opciones disponibles`;
            }
            
            // Actualizar las opciones mostradas
            const categoryOptions = categoryCard.querySelector('.category-options');
            if (categoryOptions) {
                // Mapear iconos según el nombre de la categoría
                let iconName = 'tool'; // Para adicionales
                
                const htmlOptions = adicionalesFiltrados.map(opcion => `
                    <div class="option-item" data-categoria-id="${categoria.id}" onclick="handleOptionClick(${opcion.id})">
                        <div class="option-checkbox" data-option-id="${opcion.id}">
                            <input type="checkbox" data-option-id="${opcion.id}" onclick="event.stopPropagation(); handleOptionClick(${opcion.id});" ${selectedOptions.includes(opcion.id) ? 'checked' : ''}>
                        </div>
                        <div class="option-details">
                            <div class="option-name">${opcion.nombre}</div>
                            <div class="option-price" id="price-${opcion.id}">
                                ${getOptionPrice(opcion)}
                            </div>
                        </div>
                    </div>
                `);
                
                categoryOptions.innerHTML = htmlOptions.join('');
                
                // Actualizar el estado visual de los checkboxes seleccionados
                adicionalesFiltrados.forEach(opcion => {
                    if (selectedOptions.includes(opcion.id)) {
                        const checkbox = categoryOptions.querySelector(`[data-option-id="${opcion.id}"].option-checkbox`);
                        if (checkbox) {
                            checkbox.classList.add('checked');
                        }
                    }
                });
                
                console.log(`Adicionales actualizados: ${adicionalesFiltrados.length} opciones mostradas`);
            }
        }

        function addSelectedOption(optionId) {
            if (!selectedOptions.includes(optionId)) {
                selectedOptions.push(optionId);
            }
        }

        function removeSelectedOption(optionId) {
            selectedOptions = selectedOptions.filter(id => id !== optionId);
        }

        function updateSelectedItems() {
            const container = document.getElementById('selected-items');
            
            if (selectedOptions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">${modernUI.getIcon('package')}</div>
                        <p>No has seleccionado ningún producto</p>
                    </div>
                `;
                return;
            }

            const selectedOpciones = opciones.filter(op => selectedOptions.includes(op.id));
            
            container.innerHTML = selectedOpciones.map(opcion => `
                <div class="selected-item">
                    <span class="selected-item-name">${opcion.nombre}</span>
                    <span class="selected-item-price">${getOptionPrice(opcion)}</span>
                </div>
            `).join('');
        }

        function updateTotals() {
            const selectedOpciones = opciones.filter(op => selectedOptions.includes(op.id));
            let subtotal = 0;
            let descuentoPorcentaje = 0;

            console.log(`Calculando totales con plazo unificado de ${currentDelivery} días para todos los productos`);

            selectedOpciones.forEach(opcion => {
                if (opcion.categoria_id == 3 && opcion.descuento > 0) {
                    descuentoPorcentaje = Math.max(descuentoPorcentaje, opcion.descuento);
                } else {
                    // IMPORTANTE: Todos los productos (ascensores y adicionales) usan el mismo plazo de entrega
                    const precio = opcion[`precio_${currentDelivery}_dias`] || 0;
                    
                    // NUEVA FUNCIONALIDAD: Si el adicional tiene "RESTAR" en el título, restar el precio
                    if (opcion.nombre && opcion.nombre.toLowerCase().includes('restar')) {
                        subtotal -= parseFloat(precio);
                        console.log(`Restando ${precio} por adicional (${currentDelivery} días): ${opcion.nombre}`);
                    } else {
                        subtotal += parseFloat(precio);
                        console.log(`Sumando ${precio} por producto (${currentDelivery} días): ${opcion.nombre}`);
                    }
                }
            });

            const descuento = subtotal * (descuentoPorcentaje / 100);
            const total = subtotal - descuento;

            console.log(`Subtotal (${currentDelivery} días):`, subtotal, 'Formato:', modernUI.formatCurrency(subtotal));
            console.log(`Total final (${currentDelivery} días):`, total, 'Formato:', modernUI.formatCurrency(total));

            // Actualizar UI
            document.getElementById('subtotal').textContent = `AR$${modernUI.formatCurrency(subtotal)}`;
            document.getElementById('total-amount').textContent = `AR$${modernUI.formatCurrency(total)}`;
            
            const discountRow = document.getElementById('discount-row');
            const discountElement = document.getElementById('discount');
            
            if (descuentoPorcentaje > 0) {
                discountRow.style.display = 'flex';
                discountElement.textContent = `-AR$${modernUI.formatCurrency(descuento)} (${descuentoPorcentaje}%)`;
            } else {
                discountRow.style.display = 'none';
            }

            // Actualizar precios en opciones para asegurar consistencia
            opciones.forEach(opcion => {
                const priceElement = document.getElementById(`price-${opcion.id}`);
                if (priceElement) {
                    priceElement.innerHTML = getOptionPrice(opcion);
                }
            });
        }

        function updateGenerateButton() {
            const hasSelection = selectedOptions.length > 0;
            document.getElementById('generate-quote-btn').disabled = !hasSelection;
            document.getElementById('generate-quote-btn-2').disabled = !hasSelection;
        }

        function showCustomerForm() {
            if (selectedOptions.length === 0) {
                modernUI.showToast('Selecciona al menos un producto', 'warning');
                return;
            }
            
            document.getElementById('customer-modal').classList.add('active');
        }

        function hideCustomerForm() {
            document.getElementById('customer-modal').classList.remove('active');
        }

        function resetQuote() {
            if (confirm('¿Estás seguro de que deseas reiniciar la cotización?')) {
                selectedOptions = [];
                
                // Desmarcar checkboxes
                document.querySelectorAll('.option-checkbox').forEach(cb => {
                    cb.classList.remove('checked');
                    cb.querySelector('input').checked = false;
                });
                
                // Cerrar categorías
                document.querySelectorAll('.category-card').forEach(card => {
                    card.classList.remove('active');
                });
                
                updateTotals();
                updateSelectedItems();
                updateGenerateButton();
                
                // NUEVA FUNCIONALIDAD: Resetear filtrado de adicionales
                filtrarAdicionales();
                
                modernUI.showToast('Cotización reiniciada', 'success');
            }
        }

        async function generatePDF() {
            const formData = new FormData(document.getElementById('customer-form'));
            
            // Agregar datos de la cotización
            formData.append('opciones', JSON.stringify(selectedOptions));
            formData.append('plazo', currentDelivery);
            
            // Depuración: mostrar datos enviados
            console.log('Datos enviados al servidor:');
            console.log('- Nombre:', formData.get('nombre'));
            console.log('- Email:', formData.get('email'));
            console.log('- Teléfono:', formData.get('telefono'));
            console.log('- Empresa:', formData.get('empresa'));
            console.log('- Opciones:', formData.get('opciones'));
            console.log('- Plazo:', formData.get('plazo'));
            console.log('- Opciones seleccionadas:', selectedOptions);
            
            try {
                // Mostrar indicador de carga
                document.querySelector('.customer-modal-content').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 24px; margin-bottom: 20px;">Generando presupuesto...</div>
                        <div style="width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #3b82f6; border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
                        <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
                    </div>
                `;
                
                const response = await fetch('api/generate_quote.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('Respuesta del servidor:', result);
                
                if (result.success) {
                    // Abrir el presupuesto en una nueva pestaña
                    window.open(`api/download_pdf.php?id=${result.quote_id}`, '_blank');
                    
                    hideCustomerForm();
                    modernUI.showToast('Presupuesto generado exitosamente', 'success');
                    
                    // Opcional: reiniciar después de generar
                    setTimeout(() => {
                        if (confirm('¿Deseas crear otro presupuesto?')) {
                            resetQuote();
                        }
                    }, 2000);
                } else {
                    let errorMsg = result.message || 'Error al generar presupuesto';
                    
                    // Si hay detalles adicionales del error, mostrarlos
                    if (result.line) {
                        errorMsg += ` (línea ${result.line})`;
                        console.error('Error detallado:', result);
                    }
                    
                    throw new Error(errorMsg);
                }
            } catch (error) {
                console.error('Error completo:', error);
                
                // Mostrar el mensaje de error en la modal
                document.querySelector('.customer-modal-content').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 24px; margin-bottom: 20px; color: #e11d48;">Error</div>
                        <div style="margin-bottom: 20px;">${error.message}</div>
                        <button class="btn btn-primary" onclick="hideCustomerForm()">Cerrar</button>
                    </div>
                `;
                
                modernUI.showToast('Error al generar el presupuesto: ' + error.message, 'error');
            }
        }

        // Función simple y directa para manejar clicks en opciones
        function handleOptionClick(optionId) {
            console.log('handleOptionClick ejecutado para opción:', optionId);
            
            const checkbox = document.querySelector(`[data-option-id="${optionId}"].option-checkbox`);
            if (checkbox) {
                toggleOption(optionId, checkbox);
            } else {
                console.error('No se encontró checkbox para opción:', optionId);
            }
        }

        // Función simple y directa para manejar clicks en headers de categorías
        function handleCategoryClick(categoryId) {
            console.log('handleCategoryClick ejecutado para categoría:', categoryId);
            toggleCategory(categoryId);
        }

        // Inicializar
        updateGenerateButton();

        // Asegurar que los checkbox sean clickeables
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado, configurando checkboxes...');
            
            // Hacer que los inputs de checkbox sean clickeables
            document.querySelectorAll('.option-checkbox input').forEach(input => {
                input.style.pointerEvents = 'auto';
                input.style.opacity = '0';
                input.style.cursor = 'pointer';
                input.style.zIndex = '10';
                input.style.width = '20px';
                input.style.height = '20px';
                input.style.position = 'absolute';
                input.style.top = '0';
                input.style.left = '0';
                
                // Agregar listener de click directo
                input.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const checkbox = this.closest('.option-checkbox');
                    if (checkbox) {
                        const optionId = checkbox.getAttribute('data-option-id');
                        console.log('Click directo en checkbox:', optionId);
                        if (optionId) {
                            toggleOption(parseInt(optionId), checkbox);
                        }
                    }
                });
            });
            
            // Configurar nuevamente los listeners directos
            setupDirectListeners();
        });
    </script>
</body>
</html> 