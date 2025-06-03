<?php
require_once 'sistema/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener categorías
    $stmt = $pdo->query('SELECT * FROM categorias ORDER BY orden ASC');
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener plazos
    $stmt = $pdo->query('SELECT * FROM plazos_entrega ORDER BY orden ASC');
    $plazos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener todas las opciones organizadas por categoría
    $stmt = $pdo->query('
        SELECT 
            o.id,
            o.categoria_id,
            o.nombre,
            o.descripcion,
            o.precio,
            o.precio_90_dias,
            o.precio_160_dias,
            o.precio_270_dias,
            o.descuento,
            o.orden,
            c.nombre as categoria_nombre
        FROM opciones o
        JOIN categorias c ON o.categoria_id = c.id
        ORDER BY c.orden ASC, o.orden ASC, o.id ASC
    ');
    
    $todasOpciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar por categoría
    $opcionesPorCategoria = [];
    foreach ($todasOpciones as $opcion) {
        $categoriaId = $opcion['categoria_id'];
        if (!isset($opcionesPorCategoria[$categoriaId])) {
            $opcionesPorCategoria[$categoriaId] = [];
        }
        $opcionesPorCategoria[$categoriaId][] = $opcion;
    }
    
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador Simple - <?php echo count($todasOpciones); ?> opciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
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
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .content {
            padding: 30px;
        }
        
        .stats {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .plazo-selector {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .plazo-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .plazo-btn {
            padding: 12px 24px;
            border: 2px solid #4CAF50;
            background: white;
            color: #4CAF50;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .plazo-btn.active {
            background: #4CAF50;
            color: white;
        }
        
        .accordion {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .accordion-header {
            padding: 20px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .accordion-header:hover {
            background: #e9ecef;
        }
        
        .accordion-header.active {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .accordion-title {
            font-size: 1.2rem;
            font-weight: 500;
        }
        
        .accordion-icon {
            transition: transform 0.3s;
            font-size: 1.2rem;
        }
        
        .accordion-header.active .accordion-icon {
            transform: rotate(180deg);
        }
        
        .accordion-content {
            display: none;
            padding: 20px;
            background: white;
        }
        
        .accordion-content.active {
            display: block;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .option-item {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .option-item:hover {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .option-item.selected {
            border-color: #4CAF50;
            background: #e8f5e9;
        }
        
        .option-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .option-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        
        .option-price {
            font-weight: 700;
            color: #4CAF50;
            font-size: 1.1rem;
        }
        
        .option-description {
            color: #666;
            font-size: 0.9rem;
            margin-top: 8px;
        }
        
        .resumen {
            position: sticky;
            top: 20px;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            border: 1px solid #e0e0e0;
        }
        
        .resumen h3 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .resumen-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .resumen-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4CAF50;
            border-top: 2px solid #4CAF50;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .btn-generar {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-generar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-building"></i> Cotizador Simple</h1>
            <p>Versión sin AJAX - Datos cargados directamente desde PHP</p>
        </div>
        
        <div class="content">
            <div class="stats">
                <strong>📊 Estadísticas:</strong>
                Total opciones: <?php echo count($todasOpciones); ?> |
                Categorías: <?php echo count($categorias); ?> |
                Plazos: <?php echo count($plazos); ?>
            </div>
            
            <!-- Selector de Plazo -->
            <div class="plazo-selector">
                <h3><i class="fas fa-clock"></i> Selecciona el plazo de entrega</h3>
                <div class="plazo-buttons">
                    <?php foreach ($plazos as $index => $plazo): ?>
                        <button class="plazo-btn <?php echo $index === 1 ? 'active' : ''; ?>" 
                                data-plazo="<?php echo $plazo['nombre']; ?>">
                            <?php echo htmlspecialchars($plazo['nombre']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Categorías y Opciones -->
            <div class="categorias-container">
                <?php foreach ($categorias as $categoria): ?>
                    <?php $categoriaId = $categoria['id']; ?>
                    <?php $opciones = isset($opcionesPorCategoria[$categoriaId]) ? $opcionesPorCategoria[$categoriaId] : []; ?>
                    
                    <div class="accordion">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <div class="accordion-title">
                                <i class="fas fa-<?php echo $categoria['nombre'] === 'ASCENSORES' ? 'building' : ($categoria['nombre'] === 'ADICIONALES' ? 'plus-circle' : 'percentage'); ?>"></i>
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                <span class="option-count">(<?php echo count($opciones); ?> opciones disponibles)</span>
                            </div>
                            <div class="accordion-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <?php if (!empty($categoria['descripcion'])): ?>
                                <p style="margin-bottom: 20px; color: #666;">
                                    <?php echo htmlspecialchars($categoria['descripcion']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="options-grid">
                                <?php if (empty($opciones)): ?>
                                    <div style="grid-column: 1 / -1; text-align: center; color: #666; font-style: italic;">
                                        No hay opciones en esta categoría
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($opciones as $opcion): ?>
                                        <div class="option-item" 
                                             data-id="<?php echo $opcion['id']; ?>"
                                             data-categoria="<?php echo $categoriaId; ?>"
                                             data-precio-90="<?php echo $opcion['precio_90_dias']; ?>"
                                             data-precio-160="<?php echo $opcion['precio_160_dias']; ?>"
                                             data-precio-270="<?php echo $opcion['precio_270_dias']; ?>"
                                             data-descuento="<?php echo $opcion['descuento']; ?>"
                                             data-nombre="<?php echo htmlspecialchars($opcion['nombre']); ?>">
                                            
                                            <div class="option-header">
                                                <div class="option-name"><?php echo htmlspecialchars($opcion['nombre']); ?></div>
                                                <div class="option-price">$<?php echo number_format($opcion['precio_160_dias'], 0, ',', '.'); ?></div>
                                            </div>
                                            
                                            <?php if (!empty($opcion['descripcion'])): ?>
                                                <div class="option-description">
                                                    <?php echo htmlspecialchars($opcion['descripcion']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumen -->
            <div class="resumen">
                <h3><i class="fas fa-calculator"></i> Resumen del Presupuesto</h3>
                <div id="resumen-items">
                    <p style="text-align: center; color: #666;">Selecciona opciones para ver el resumen</p>
                </div>
                <div class="resumen-total" id="total-presupuesto" style="display: none;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>TOTAL:</span>
                        <span id="total-amount">$0</span>
                    </div>
                </div>
                <button class="btn-generar" id="btn-generar" disabled>
                    <i class="fas fa-file-pdf"></i> Generar Presupuesto PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        let plazoSeleccionado = '160-180 dias';
        let opcionesSeleccionadas = {};
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners para plazos
            document.querySelectorAll('.plazo-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.plazo-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    plazoSeleccionado = this.dataset.plazo;
                    actualizarPrecios();
                });
            });
            
            // Event listeners para opciones
            document.querySelectorAll('.option-item').forEach(item => {
                item.addEventListener('click', function() {
                    toggleOpcion(this);
                });
            });
        });
        
        function obtenerPrecioPorPlazo(element, plazo) {
            switch(plazo) {
                case '90 dias':
                    return parseFloat(element.dataset.precio90) || 0;
                case '160-180 dias':
                    return parseFloat(element.dataset.precio160) || 0;
                case '270 dias':
                    return parseFloat(element.dataset.precio270) || 0;
                default:
                    return parseFloat(element.dataset.precio160) || 0;
            }
        }
        
        function toggleOpcion(element) {
            const opcionId = element.dataset.id;
            const categoriaId = element.dataset.categoria;
            const nombre = element.dataset.nombre;
            const descuento = parseFloat(element.dataset.descuento) || 0;
            
            // Para ASCENSORES y DESCUENTOS solo una opción por categoría
            if (categoriaId == 1 || categoriaId == 3) {
                // Deseleccionar otras opciones de la misma categoría
                document.querySelectorAll(`[data-categoria="${categoriaId}"] .option-item`).forEach(item => {
                    item.classList.remove('selected');
                });
                
                // Limpiar selecciones previas de esta categoría
                Object.keys(opcionesSeleccionadas).forEach(key => {
                    if (opcionesSeleccionadas[key].categoria_id == categoriaId) {
                        delete opcionesSeleccionadas[key];
                    }
                });
            }
            
            // Toggle selección
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                delete opcionesSeleccionadas[opcionId];
            } else {
                element.classList.add('selected');
                opcionesSeleccionadas[opcionId] = {
                    id: opcionId,
                    nombre: nombre,
                    categoria_id: categoriaId,
                    descuento: descuento,
                    precio_actual: obtenerPrecioPorPlazo(element, plazoSeleccionado),
                    element: element
                };
            }
            
            actualizarContadores();
            actualizarResumen();
        }
        
        function actualizarPrecios() {
            document.querySelectorAll('.option-item').forEach(item => {
                const precio = obtenerPrecioPorPlazo(item, plazoSeleccionado);
                const priceElement = item.querySelector('.option-price');
                if (priceElement) {
                    priceElement.textContent = `$${formatNumber(precio)}`;
                }
                
                // Actualizar precio en seleccionadas
                const opcionId = item.dataset.id;
                if (opcionesSeleccionadas[opcionId]) {
                    opcionesSeleccionadas[opcionId].precio_actual = precio;
                }
            });
            
            actualizarResumen();
        }
        
        function actualizarContadores() {
            document.querySelectorAll('.accordion').forEach((accordion, index) => {
                const categoriaId = index + 1;
                const contador = accordion.querySelector('.option-count');
                const seleccionadas = Object.values(opcionesSeleccionadas).filter(o => o.categoria_id == categoriaId).length;
                const total = accordion.querySelectorAll('.option-item').length;
                contador.textContent = `(${seleccionadas}/${total} seleccionadas)`;
            });
        }
        
        function actualizarResumen() {
            const resumenContainer = document.getElementById('resumen-items');
            const totalElement = document.getElementById('total-presupuesto');
            const totalAmount = document.getElementById('total-amount');
            const btnGenerar = document.getElementById('btn-generar');
            
            if (Object.keys(opcionesSeleccionadas).length === 0) {
                resumenContainer.innerHTML = '<p style="text-align: center; color: #666;">Selecciona opciones para ver el resumen</p>';
                totalElement.style.display = 'none';
                btnGenerar.disabled = true;
                return;
            }
            
            let html = '';
            let subtotal = 0;
            let descuentoPorcentaje = 0;
            
            Object.values(opcionesSeleccionadas).forEach(opcion => {
                const precio = opcion.precio_actual || 0;
                
                if (opcion.categoria_id == 3) { // DESCUENTOS
                    descuentoPorcentaje = opcion.descuento;
                    html += `
                        <div class="resumen-item">
                            <span>${opcion.nombre}</span>
                            <span style="color: #f44336;">-${descuentoPorcentaje}%</span>
                        </div>
                    `;
                } else {
                    subtotal += precio;
                    html += `
                        <div class="resumen-item">
                            <span>${opcion.nombre}</span>
                            <span>$${formatNumber(precio)}</span>
                        </div>
                    `;
                }
            });
            
            // Calcular descuento
            const montoDescuento = subtotal * (descuentoPorcentaje / 100);
            const total = subtotal - montoDescuento;
            
            if (subtotal > 0) {
                html += `
                    <div class="resumen-item">
                        <span><strong>Subtotal:</strong></span>
                        <span><strong>$${formatNumber(subtotal)}</strong></span>
                    </div>
                `;
                
                if (montoDescuento > 0) {
                    html += `
                        <div class="resumen-item">
                            <span>Descuento (${descuentoPorcentaje}%):</span>
                            <span style="color: #f44336;">-$${formatNumber(montoDescuento)}</span>
                        </div>
                    `;
                }
            }
            
            resumenContainer.innerHTML = html;
            
            if (total > 0) {
                totalAmount.textContent = `$${formatNumber(total)}`;
                totalElement.style.display = 'block';
                btnGenerar.disabled = false;
            } else {
                totalElement.style.display = 'none';
                btnGenerar.disabled = true;
            }
        }
        
        function toggleAccordion(header) {
            const content = header.nextElementSibling;
            
            // Cerrar otros acordeones
            document.querySelectorAll('.accordion-header').forEach(h => {
                if (h !== header) {
                    h.classList.remove('active');
                    h.nextElementSibling.classList.remove('active');
                }
            });
            
            // Toggle actual
            header.classList.toggle('active');
            content.classList.toggle('active');
        }
        
        function formatNumber(num) {
            return new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(num);
        }
        
        // Event listener para generar PDF
        document.getElementById('btn-generar').addEventListener('click', function() {
            if (Object.keys(opcionesSeleccionadas).length === 0) {
                alert('Selecciona al menos una opción para generar el presupuesto');
                return;
            }
            
            alert('Función de generación de PDF en desarrollo');
        });
    </script>
</body>
</html> 