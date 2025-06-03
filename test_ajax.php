<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Ordenamiento</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .btn { padding: 5px 10px; margin: 5px; cursor: pointer; }
        .result { margin: 10px 0; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Test AJAX Ordenamiento</h1>
    
    <h2>Probar Categorías:</h2>
    <button class="btn" onclick="testCategoria(1, 'up')">Mover Categoría 1 ↑</button>
    <button class="btn" onclick="testCategoria(1, 'down')">Mover Categoría 1 ↓</button>
    
    <h2>Probar Opciones:</h2>
    <button class="btn" onclick="testOpcion(1, 'up')">Mover Opción 1 ↑</button>
    <button class="btn" onclick="testOpcion(1, 'down')">Mover Opción 1 ↓</button>
    
    <div id="result" class="result">
        Resultados aparecerán aquí...
    </div>

    <script>
        function testCategoria(id, direccion) {
            const action = direccion === 'up' ? 'move_categoria_up' : 'move_categoria_down';
            
            console.log('Enviando:', { action, id });
            
            fetch('admin/ajax_ordenamiento_debug.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&id=${id}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    document.getElementById('result').innerHTML = 
                        `<strong>Categoría ${id} ${direccion}:</strong><br>
                         Success: ${data.success}<br>
                         Message: ${data.message}`;
                } catch (e) {
                    document.getElementById('result').innerHTML = 
                        `<strong>Error parsing JSON:</strong><br>${text}`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = 
                    `<strong>Error de conexión:</strong><br>${error.message}`;
            });
        }

        function testOpcion(id, direccion) {
            const action = direccion === 'up' ? 'move_opcion_up' : 'move_opcion_down';
            
            console.log('Enviando:', { action, id });
            
            fetch('admin/ajax_ordenamiento_debug.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&id=${id}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    document.getElementById('result').innerHTML = 
                        `<strong>Opción ${id} ${direccion}:</strong><br>
                         Success: ${data.success}<br>
                         Message: ${data.message}`;
                } catch (e) {
                    document.getElementById('result').innerHTML = 
                        `<strong>Error parsing JSON:</strong><br>${text}`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = 
                    `<strong>Error de conexión:</strong><br>${error.message}`;
            });
        }
    </script>
</body>
</html> 