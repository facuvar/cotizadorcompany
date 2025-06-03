<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizador de Ascensores - Con Títulos</title>
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
        
        .content {
            display: flex;
            min-height: 600px;
        }
        
        .sidebar {
            width: 350px;
            background: #f8f9fa;
            padding: 30px;
            border-right: 1px solid #e9ecef;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .plazo-selector {
            margin-bottom: 30px;
        }
        
        .plazo-selector h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        .plazo-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .plazo-btn {
            padding: 15px;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-align: center;
        }
        
        .plazo-btn:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }
        
        .plazo-btn.active {
            background: #3498db;
            color: white;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .resumen {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .resumen h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .total {
            font-size: 1.8em;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .categoria {
            margin-bottom: 30px;
        }
        
        .categoria h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
            font-size: 1.4em;
        }
        
        .titulo-seccion {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 12px 20px;
            margin: 15px 0 10px 0;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
            box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
        }
        
        .opcion {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .opcion:hover {
            border-color: #3498db;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .opcion.selected {
            border-color: #27ae60;
            background: #d5f4e6;
        }
        
        .opcion input[type="checkbox"] {
            margin-right: 15px;
            transform: scale(1.3);
            accent-color: #27ae60;
        }
        
        .opcion-info {
            flex: 1;
        }
        
        .opcion-nombre {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .opcion-descripcion {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .opcion-precio {
            font-weight: bold;
            color: #27ae60;
            font-size: 1.1em;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }
        
        .error {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .plazo-buttons {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .plazo-btn {
                flex: 1;
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Cotizador de Ascensores</h1>
            <p>Sistema de presupuestos online con títulos organizados</p>
        </div>
        
        <div class="content">
            <div class="sidebar">
                <div class="plazo-selector">
                    <h3>📅 Seleccionar Plazo de Entrega</h3>
                    <div class="plazo-buttons" id="plazos-container">
                        <div class="loading">Cargando plazos...</div>
                    </div>
                </div>
                
                <div class="resumen">
                    <h3>📋 Resumen del Presupuesto</h3>
                    <div id="items-seleccionados">
                        <p style="color: #7f8c8d; text-align: center;">No hay elementos seleccionados</p>
                    </div>
                    <div class="total" id="total-precio">$0</div>
                </div>
            </div>
            
            <div class="main-content">
                <div id="opciones-container">
                    <div class="loading">Cargando opciones...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let datosCompletos = null;
        let plazoSeleccionado = null;
        let opcionesSeleccionadas = new Set();

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarDatos();
        });

        async function cargarDatos() {
            try {
                const response = await fetch('get_all_options_fixed.php');
                const data = await response.json();
                
                if (data.success) {
                    datosCompletos = data;
                    console.log('📊 Datos cargados:', {
                        opciones: data.opciones.length,
                        titulos: data.titulos.length,
                        plazos: data.plazos_entrega.length
                    });
                    
                    mostrarPlazos(data.plazos_entrega);
                    mostrarOpciones(data.opciones, data.titulos);
                } else {
                    throw new Error(data.error || 'Error al cargar datos');
                }
            } catch (error) {
                console.error('❌ Error:', error);
                document.getElementById('opciones-container').innerHTML = 
                    `<div class="error">Error al cargar datos: ${error.message}</div>`;
            }
        }

        function mostrarPlazos(plazos) {
            const container = document.getElementById('plazos-container');
            
            if (plazos.length === 0) {
                container.innerHTML = '<p style="color: #e74c3c;">No hay plazos disponibles</p>';
                return;
            }

            let html = '';
            plazos.forEach((plazo, index) => {
                const isFirst = index === 0;
                html += `
                    <button class="plazo-btn ${isFirst ? 'active' : ''}" 
                            onclick="seleccionarPlazo(${plazo.dias})"
                            data-dias="${plazo.dias}">
                        ${plazo.nombre}
                        <br><small>${plazo.descripcion || ''}</small>
                    </button>
                `;
            });

            container.innerHTML = html;
            
            // Seleccionar el primer plazo por defecto
            if (plazos.length > 0) {
                plazoSeleccionado = plazos[0].dias;
                actualizarPrecios();
            }
        }

        function mostrarOpciones(opciones, titulos) {
            const container = document.getElementById('opciones-container');
            
            // Agrupar por categoría
            const categorias = {};
            
            // Agregar títulos
            titulos.forEach(titulo => {
                if (!categorias[titulo.categoria_id]) {
                    categorias[titulo.categoria_id] = {
                        nombre: titulo.categoria_nombre,
                        orden: titulo.categoria_orden,
                        titulos: [],
                        opciones: []
                    };
                }
                categorias[titulo.categoria_id].titulos.push(titulo);
            });
            
            // Agregar opciones
            opciones.forEach(opcion => {
                if (!categorias[opcion.categoria_id]) {
                    categorias[opcion.categoria_id] = {
                        nombre: opcion.categoria_nombre,
                        orden: opcion.categoria_orden,
                        titulos: [],
                        opciones: []
                    };
                }
                categorias[opcion.categoria_id].opciones.push(opcion);
            });

            // Ordenar categorías
            const categoriasOrdenadas = Object.values(categorias).sort((a, b) => a.orden - b.orden);

            let html = '';
            categoriasOrdenadas.forEach(categoria => {
                html += `
                    <div class="categoria">
                        <h2>${categoria.nombre}</h2>
                `;
                
                // Crear un array combinado de títulos y opciones ordenado
                const elementosOrdenados = [];
                
                // Agregar títulos
                categoria.titulos.forEach(titulo => {
                    elementosOrdenados.push({...titulo, tipo: 'titulo'});
                });
                
                // Agregar opciones
                categoria.opciones.forEach(opcion => {
                    elementosOrdenados.push({...opcion, tipo: 'opcion'});
                });
                
                // Ordenar por orden
                elementosOrdenados.sort((a, b) => a.orden - b.orden);
                
                // Mostrar elementos ordenados
                elementosOrdenados.forEach(elemento => {
                    if (elemento.tipo === 'titulo') {
                        html += `
                            <div class="titulo-seccion">
                                📂 ${elemento.nombre}
                                ${elemento.descripcion ? `<br><small style="opacity: 0.9;">${elemento.descripcion}</small>` : ''}
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="opcion" onclick="toggleOpcion(${elemento.id})">
                                <input type="checkbox" id="opcion-${elemento.id}" onchange="toggleOpcion(${elemento.id})">
                                <div class="opcion-info">
                                    <div class="opcion-nombre">${elemento.nombre}</div>
                                    ${elemento.descripcion ? `<div class="opcion-descripcion">${elemento.descripcion}</div>` : ''}
                                </div>
                                <div class="opcion-precio" id="precio-${elemento.id}">
                                    $${formatearPrecio(elemento.precio_90_dias)}
                                </div>
                            </div>
                        `;
                    }
                });
                
                html += '</div>';
            });

            container.innerHTML = html;
        }

        function seleccionarPlazo(dias) {
            // Actualizar botones
            document.querySelectorAll('.plazo-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-dias="${dias}"]`).classList.add('active');
            
            plazoSeleccionado = dias;
            actualizarPrecios();
        }

        function actualizarPrecios() {
            if (!datosCompletos || !plazoSeleccionado) return;

            datosCompletos.opciones.forEach(opcion => {
                const elemento = document.getElementById(`precio-${opcion.id}`);
                if (elemento) {
                    let precio = 0;
                    switch (plazoSeleccionado) {
                        case 90:
                            precio = opcion.precio_90_dias;
                            break;
                        case 160:
                        case 180:
                            precio = opcion.precio_160_dias;
                            break;
                        case 270:
                            precio = opcion.precio_270_dias;
                            break;
                    }
                    elemento.textContent = `$${formatearPrecio(precio)}`;
                }
            });

            calcularTotal();
        }

        function toggleOpcion(id) {
            const checkbox = document.getElementById(`opcion-${id}`);
            const opcionDiv = checkbox.closest('.opcion');
            
            if (opcionesSeleccionadas.has(id)) {
                opcionesSeleccionadas.delete(id);
                checkbox.checked = false;
                opcionDiv.classList.remove('selected');
            } else {
                opcionesSeleccionadas.add(id);
                checkbox.checked = true;
                opcionDiv.classList.add('selected');
            }
            
            actualizarResumen();
        }

        function actualizarResumen() {
            const container = document.getElementById('items-seleccionados');
            
            if (opcionesSeleccionadas.size === 0) {
                container.innerHTML = '<p style="color: #7f8c8d; text-align: center;">No hay elementos seleccionados</p>';
                document.getElementById('total-precio').textContent = '$0';
                return;
            }

            let html = '';
            let total = 0;

            opcionesSeleccionadas.forEach(id => {
                const opcion = datosCompletos.opciones.find(o => o.id === id);
                if (opcion) {
                    let precio = 0;
                    switch (plazoSeleccionado) {
                        case 90:
                            precio = opcion.precio_90_dias;
                            break;
                        case 160:
                        case 180:
                            precio = opcion.precio_160_dias;
                            break;
                        case 270:
                            precio = opcion.precio_270_dias;
                            break;
                    }
                    
                    total += precio;
                    html += `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding: 8px; background: white; border-radius: 5px;">
                            <span style="font-size: 0.9em;">${opcion.nombre}</span>
                            <span style="font-weight: bold; color: #27ae60;">$${formatearPrecio(precio)}</span>
                        </div>
                    `;
                }
            });

            container.innerHTML = html;
            document.getElementById('total-precio').textContent = `$${formatearPrecio(total)}`;
        }

        function calcularTotal() {
            actualizarResumen();
        }

        function formatearPrecio(precio) {
            return new Intl.NumberFormat('es-AR').format(precio || 0);
        }
    </script>
</body>
</html> 