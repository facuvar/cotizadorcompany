<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Verificación de Categorías</h1>";
    
    // Verificar si existe la categoría GIRACOCHES
    $result = $conn->query("SELECT * FROM categorias WHERE nombre = 'GIRACOCHES'");
    
    if ($result && $result->num_rows > 0) {
        $categoria = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Categoría GIRACOCHES encontrada con ID: {$categoria['id']}</p>";
    } else {
        echo "<p style='color: red;'>❌ No existe la categoría GIRACOCHES</p>";
        
        // Crear la categoría
        echo "<form method='post'>";
        echo "<button type='submit' name='crear_categoria' style='padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer;'>Crear Categoría GIRACOCHES</button>";
        echo "</form>";
        
        if (isset($_POST['crear_categoria'])) {
            $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)");
            $nombre = 'GIRACOCHES';
            $descripcion = 'Plataformas giratorias para vehículos';
            $orden = 10;
            $stmt->bind_param("ssi", $nombre, $descripcion, $orden);
            
            if ($stmt->execute()) {
                $categoriaId = $conn->insert_id;
                echo "<p style='color: green;'>✅ Categoría GIRACOCHES creada con ID: {$categoriaId}</p>";
                echo "<script>window.location.href = '{$_SERVER['PHP_SELF']}';</script>";
            } else {
                echo "<p style='color: red;'>❌ Error al crear la categoría: {$stmt->error}</p>";
            }
        }
    }
    
    // Listar todas las categorías
    echo "<h2>Todas las categorías</h2>";
    $result = $conn->query("SELECT * FROM categorias ORDER BY orden");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Orden</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['nombre']}</td>";
            echo "<td>{$row['descripcion']}</td>";
            echo "<td>{$row['orden']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay categorías en la base de datos.</p>";
    }
    
    // Listar opciones de GIRACOCHES si existe
    if (isset($categoria)) {
        echo "<h2>Opciones de GIRACOCHES</h2>";
        $result = $conn->query("SELECT * FROM opciones WHERE categoria_id = {$categoria['id']}");
        
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Precio Base</th><th>Obligatorio</th><th>Orden</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['nombre']}</td>";
                echo "<td>{$row['descripcion']}</td>";
                echo "<td>{$row['precio']}</td>";
                echo "<td>" . ($row['es_obligatorio'] ? 'Sí' : 'No') . "</td>";
                echo "<td>{$row['orden']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No hay opciones para la categoría GIRACOCHES.</p>";
            
            // Botón para ejecutar importación
            echo "<form method='post' action='sistema/admin/test_import_giracoches.php'>";
            echo "<button type='submit' style='padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; margin-top: 10px;'>Ejecutar Importación de Prueba</button>";
            echo "</form>";
        }
    }
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 