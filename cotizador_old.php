<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador de Ascensores</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 0;
            min-height: 600px;
        }

        .options-panel {
            padding: 30px;
            background: #f8f9fa;
        }

        .category {
            margin-bottom: 25px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .category-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .category-header:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        }

        .category-header h3 {
            font-size: 1.3em;
        }

        .toggle-icon {
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }

        .category.active .toggle-icon {
            transform: rotate(180deg);
        }

        .category-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: white;
        }

        .category.active .category-content {
            max-height: 1000px;
        }

        .option-item {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .option-item:hover {
            background-color: #f8f9fa;
        }

        .option-item:last-child {
            border-bottom: none;
        }

        .option-item input[type="checkbox"] {
            margin-right: 12px;
            transform: scale(1.2);
        }

        .option-label {
            flex: 1;
            font-weight: 500;
            color: #2c3e50;
        }

        .option-price {
            font-weight: bold;
            color: #27ae60;
            margin-left: 10px;
        }

        .price-unavailable {
            color: #e74c3c;
            font-style: italic;
        }

        .summary-panel {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            position: sticky;
            top: 0;
        }

        .delivery-selector {
            margin-bottom: 25px;
        }

        .delivery-selector h3 {
            margin-bottom: 15px;
            color: #ecf0f1;
        }

        .delivery-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .delivery-option {
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .delivery-option:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.4);
        }

        .delivery-option.selected {
            background: rgba(52, 152, 219, 0.3);
            border-color: #3498db;
        }

        .delivery-option input[type="radio"] {
            display: none;
        }

        .summary {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary h3 {
            margin-bottom: 15px;
            color: #ecf0f1;
        }

        .selected-items {
            margin-bottom: 15px;
        }

        .selected-item {
            background: rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total {
            border-top: 2px solid rgba(255,255,255,0.3);
            padding-top: 15px;
            margin-top: 15px;
        }

        .total-amount {
            font-size: 1.8em;
            font-weight: bold;
            color: #f39c12;
        }

        .btn-generate {
            width: 100%;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-generate:hover {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .warning {
            background: #f39c12;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .summary-panel {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Cotizador de Ascensores</h1>
            <p>Selecciona las opciones y obtén tu presupuesto personalizado</p>
        </div>

        <div class="content">
            <div class="options-panel">
                <div id="categorias-container">
                    <!-- Las categorías se cargarán aquí -->
                </div>
            </div>

            <div class="summary-panel">
                <div class="delivery-selector">
                    <h3>📅 Plazo de Entrega</h3>
                    <div class="delivery-options">
                        <label class="delivery-option">
                            <input type="radio" name="plazo" value="90" checked>
                            <div>
                                <strong>90 días</strong><br>
                                <small>Entrega rápida</small>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="plazo" value="160">
                            <div>
                                <strong>160-180 días</strong><br>
                                <small>Entrega estándar</small>
                            </div>
                        </label>
                        <label class="delivery-option">
                            <input type="radio" name="plazo" value="270">
                            <div>
                                <strong>270 días</strong><br>
                                <small>Entrega extendida</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="summary">
                    <h3>📋 Resumen del Presupuesto</h3>
                    <div class="selected-items" id="selected-items">
                        <div style="text-align: center; color: #bdc3c7; font-style: italic;">
                            Selecciona opciones para ver el resumen
                        </div>
                    </div>
                    <div class="total">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Total:</span>
                            <span class="total-amount" id="total-amount">$0</span>
                        </div>
                    </div>
                </div>

                <button class="btn-generate" onclick="generarPresupuesto()">
                    📄 Generar Presupuesto PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        let opciones = [];
        let plazosEntrega = {};
        let selectedOptions = new Set();
        let currentPlazo = '90';

        // Cargar datos iniciales
        async function cargarDatos() {
            try {
                const response = await fetch('get_all_options_fixed.php');
                const data = await response.json();
                
                console.log('Datos recibidos:', data); // Debug
                
                if (data.success) {
                    opciones = data.opciones;
                    plazosEntrega = data.plazos_entrega;
                    renderizarCategorias();
                } else {
                    console.error('Error al cargar datos:', data.error);
                    mostrarError('Error al cargar datos: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión: ' + error.message);
            }
        }

        function mostrarError(mensaje) {
            const container = document.getElementById('categorias-container');
            container.innerHTML = `
                <div style="background: #e74c3c; color: white; padding: 20px; border-radius: 10px; text-align: center;">
                    <h3>⚠️ Error</h3>
                    <p>${mensaje}</p>
                    <button onclick="cargarDatos()" style="background: white; color: #e74c3c; border: none; padding: 10px 20px; border-radius: 5px; margin-top: 10px; cursor: pointer;">
                        🔄 Reintentar
                    </button>
                </div>
            `;
        }

        function renderizarCategorias() {
            const container = document.getElementById('categorias-container');
            const categorias = {};

            // Agrupar opciones por categoría
            opciones.forEach(opcion => {
                if (!categorias[opcion.categoria_id]) {
                    categorias[opcion.categoria_id] = {
                        nombre: opcion.categoria_nombre,
                        opciones: []
                    };
                }
                categorias[opcion.categoria_id].opciones.push(opcion);
            });

            // Renderizar cada categoría
            container.innerHTML = '';
            Object.keys(categorias).forEach(categoriaId => {
                const categoria = categorias[categoriaId];
                const categoryDiv = document.createElement('div');
                categoryDiv.className = 'category';
                categoryDiv.innerHTML = `
                    <div class="category-header" onclick="toggleCategory(${categoriaId})">
                        <h3>${categoria.nombre}</h3>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="category-content">
                        ${categoria.opciones.map(opcion => `
                            <div class="option-item">
                                <input type="checkbox" 
                                       id="option-${opcion.id}" 
                                       onchange="toggleOption(${opcion.id})"
                                       ${opcion.categoria_id == 1 ? 'name="ascensor"' : ''}>
                                <label for="option-${opcion.id}" class="option-label">
                                    ${opcion.nombre}
                                </label>
                                <span class="option-price" id="price-${opcion.id}">
                                    ${formatearPrecio(opcion, currentPlazo)}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                `;
                container.appendChild(categoryDiv);
            });

            // Activar primera categoría por defecto
            if (Object.keys(categorias).length > 0) {
                toggleCategory(Object.keys(categorias)[0]);
            }
        }

        function formatearPrecio(opcion, plazo) {
            let precio = 0;
            let texto = '';

            // Determinar el precio según el plazo
            switch(plazo) {
                case '90':
                    precio = parseFloat(opcion.precio_90_dias || 0);
                    break;
                case '160':
                    precio = parseFloat(opcion.precio_160_dias || 0);
                    break;
                case '270':
                    precio = parseFloat(opcion.precio_270_dias || 0);
                    break;
            }

            // Si es categoría de descuentos, mostrar el descuento
            if (opcion.categoria_id == 3 && opcion.descuento > 0) {
                return `${opcion.descuento}% desc.`;
            }

            // Si no hay precio para este plazo, buscar en otros plazos
            if (precio === 0) {
                const precios = [
                    { plazo: '270', valor: parseFloat(opcion.precio_270_dias || 0) },
                    { plazo: '160', valor: parseFloat(opcion.precio_160_dias || 0) },
                    { plazo: '90', valor: parseFloat(opcion.precio_90_dias || 0) }
                ];

                const precioDisponible = precios.find(p => p.valor > 0);
                
                if (precioDisponible) {
                    return `<span class="price-unavailable">Solo ${precioDisponible.plazo}d: $${precioDisponible.valor.toLocaleString('es-AR')}</span>`;
                } else {
                    return '<span class="price-unavailable">Consultar</span>';
                }
            }

            return `$${precio.toLocaleString('es-AR')}`;
        }

        function toggleCategory(categoriaId) {
            const categories = document.querySelectorAll('.category');
            categories.forEach(cat => {
                const header = cat.querySelector('.category-header');
                const categoryIdFromHeader = header.getAttribute('onclick').match(/\d+/)[0];
                
                if (categoryIdFromHeader == categoriaId) {
                    cat.classList.toggle('active');
                } else {
                    cat.classList.remove('active');
                }
            });
        }

        function toggleOption(opcionId) {
            const checkbox = document.getElementById(`option-${opcionId}`);
            const opcion = opciones.find(o => o.id == opcionId);

            if (checkbox.checked) {
                // Si es categoría ascensores, desmarcar otros
                if (opcion.categoria_id == 1) {
                    document.querySelectorAll('input[name="ascensor"]').forEach(cb => {
                        if (cb.id !== `option-${opcionId}`) {
                            cb.checked = false;
                            selectedOptions.delete(parseInt(cb.id.split('-')[1]));
                        }
                    });
                }
                selectedOptions.add(opcionId);
            } else {
                selectedOptions.delete(opcionId);
            }

            actualizarResumen();
        }

        function actualizarResumen() {
            const selectedItemsContainer = document.getElementById('selected-items');
            const totalAmountElement = document.getElementById('total-amount');

            if (selectedOptions.size === 0) {
                selectedItemsContainer.innerHTML = `
                    <div style="text-align: center; color: #bdc3c7; font-style: italic;">
                        Selecciona opciones para ver el resumen
                    </div>
                `;
                totalAmountElement.textContent = '$0';
                return;
            }

            let total = 0;
            let descuentoTotal = 0;
            let itemsHtml = '';

            selectedOptions.forEach(opcionId => {
                const opcion = opciones.find(o => o.id == opcionId);
                if (!opcion) return;

                let precio = 0;
                let textoItem = '';

                // Calcular precio según plazo
                switch(currentPlazo) {
                    case '90':
                        precio = parseFloat(opcion.precio_90_dias || 0);
                        break;
                    case '160':
                        precio = parseFloat(opcion.precio_160_dias || 0);
                        break;
                    case '270':
                        precio = parseFloat(opcion.precio_270_dias || 0);
                        break;
                }

                // Si es descuento
                if (opcion.categoria_id == 3 && opcion.descuento > 0) {
                    descuentoTotal += parseFloat(opcion.descuento);
                    textoItem = `${opcion.nombre} (${opcion.descuento}%)`;
                } else {
                    total += precio;
                    textoItem = `${opcion.nombre}`;
                }

                itemsHtml += `
                    <div class="selected-item">
                        <span>${textoItem}</span>
                        <span>${precio > 0 ? '$' + precio.toLocaleString('es-AR') : (opcion.descuento > 0 ? opcion.descuento + '%' : 'Consultar')}</span>
                    </div>
                `;
            });

            // Aplicar descuentos
            if (descuentoTotal > 0) {
                const montoDescuento = total * (descuentoTotal / 100);
                total -= montoDescuento;
                
                itemsHtml += `
                    <div class="selected-item" style="color: #e74c3c;">
                        <span>Descuento aplicado (${descuentoTotal}%)</span>
                        <span>-$${montoDescuento.toLocaleString('es-AR')}</span>
                    </div>
                `;
            }

            selectedItemsContainer.innerHTML = itemsHtml;
            totalAmountElement.textContent = '$' + total.toLocaleString('es-AR');
        }

        // Event listeners para cambio de plazo
        document.addEventListener('DOMContentLoaded', function() {
            const plazoInputs = document.querySelectorAll('input[name="plazo"]');
            plazoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.checked) {
                        currentPlazo = this.value;
                        
                        // Actualizar clase selected en delivery options
                        document.querySelectorAll('.delivery-option').forEach(option => {
                            option.classList.remove('selected');
                        });
                        this.closest('.delivery-option').classList.add('selected');
                        
                        // Actualizar precios mostrados
                        opciones.forEach(opcion => {
                            const priceElement = document.getElementById(`price-${opcion.id}`);
                            if (priceElement) {
                                priceElement.innerHTML = formatearPrecio(opcion, currentPlazo);
                            }
                        });
                        
                        // Actualizar resumen
                        actualizarResumen();
                    }
                });
            });

            // Marcar primera opción como seleccionada
            document.querySelector('.delivery-option').classList.add('selected');
        });

        function generarPresupuesto() {
            if (selectedOptions.size === 0) {
                alert('Por favor selecciona al menos una opción para generar el presupuesto.');
                return;
            }

            // Aquí iría la lógica para generar el PDF
            alert('Funcionalidad de PDF en desarrollo. Opciones seleccionadas: ' + Array.from(selectedOptions).join(', '));
        }

        // Cargar datos al iniciar
        cargarDatos();
    </script>
</body>
</html> 