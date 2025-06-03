<?php
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h1>Gestión de Fuentes de Datos</h1>";
    
    // Verificar si existe la tabla fuente_datos
    $result = $conn->query("SHOW TABLES LIKE 'fuente_datos'");
    if ($result->num_rows == 0) {
        echo "<p style='color: red;'>La tabla fuente_datos no existe en la base de datos.</p>";
        
        // Crear la tabla si no existe
        echo "<h3>Creando tabla fuente_datos...</h3>";
        
        $sql = "CREATE TABLE fuente_datos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            tipo VARCHAR(50) NOT NULL,
            url TEXT NOT NULL,
            fecha_actualizacion DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Tabla fuente_datos creada correctamente.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al crear la tabla: " . $conn->error . "</p>";
            exit;
        }
    }
    
    // Mostrar fuentes de datos actuales
    $result = $conn->query("SELECT * FROM fuente_datos ORDER BY fecha_actualizacion DESC");
    
    echo "<h2>Fuentes de datos existentes</h2>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>URL</th><th>Fecha Actualización</th><th>Acciones</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['tipo']}</td>";
            echo "<td style='word-break: break-all;'>{$row['url']}</td>";
            echo "<td>{$row['fecha_actualizacion']}</td>";
            echo "<td><a href='?editar={$row['id']}'>Editar</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay fuentes de datos registradas.</p>";
    }
    
    // Formulario para añadir/editar
    if (isset($_GET['editar'])) {
        $id = intval($_GET['editar']);
        $stmt = $conn->prepare("SELECT * FROM fuente_datos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo "<p style='color: red;'>Fuente de datos no encontrada.</p>";
        } else {
            $fuente = $result->fetch_assoc();
            
            echo "<h2>Editar Fuente de Datos</h2>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='id' value='{$fuente['id']}'>";
            
            echo "<div style='margin-bottom: 15px;'>";
            echo "<label for='tipo' style='display: block; margin-bottom: 5px;'>Tipo:</label>";
            echo "<input type='text' id='tipo' name='tipo' value='{$fuente['tipo']}' style='width: 100%; padding: 8px;' required>";
            echo "</div>";
            
            echo "<div style='margin-bottom: 15px;'>";
            echo "<label for='url' style='display: block; margin-bottom: 5px;'>URL:</label>";
            echo "<input type='text' id='url' name='url' value='{$fuente['url']}' style='width: 100%; padding: 8px;' required>";
            echo "</div>";
            
            echo "<button type='submit' name='actualizar' style='padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Guardar Cambios</button>";
            echo " <a href='{$_SERVER['PHP_SELF']}' style='padding: 8px 16px; background-color: #f44336; color: white; text-decoration: none; display: inline-block;'>Cancelar</a>";
            echo "</form>";
        }
    } else {
        echo "<h2>Añadir Nueva Fuente de Datos</h2>";
        echo "<form method='post'>";
        
        echo "<div style='margin-bottom: 15px;'>";
        echo "<label for='tipo' style='display: block; margin-bottom: 5px;'>Tipo:</label>";
        echo "<input type='text' id='tipo' name='tipo' value='google_sheets' style='width: 100%; padding: 8px;' required>";
        echo "</div>";
        
        echo "<div style='margin-bottom: 15px;'>";
        echo "<label for='url' style='display: block; margin-bottom: 5px;'>URL:</label>";
        echo "<input type='text' id='url' name='url' placeholder='https://docs.google.com/spreadsheets/d/XXXX/edit#gid=0' style='width: 100%; padding: 8px;' required>";
        echo "</div>";
        
        echo "<button type='submit' name='agregar' style='padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Añadir Fuente</button>";
        echo "</form>";
    }
    
    // Procesar formulario de actualización
    if (isset($_POST['actualizar']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $tipo = trim($_POST['tipo']);
        $url = trim($_POST['url']);
        
        if (empty($tipo) || empty($url)) {
            echo "<p style='color: red;'>Todos los campos son obligatorios.</p>";
        } else {
            $stmt = $conn->prepare("UPDATE fuente_datos SET tipo = ?, url = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->bind_param("ssi", $tipo, $url, $id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Fuente de datos actualizada correctamente.</p>";
                echo "<script>window.location.href = '{$_SERVER['PHP_SELF']}';</script>";
            } else {
                echo "<p style='color: red;'>❌ Error al actualizar: " . $stmt->error . "</p>";
            }
        }
    }
    
    // Procesar formulario de nueva fuente
    if (isset($_POST['agregar'])) {
        $tipo = trim($_POST['tipo']);
        $url = trim($_POST['url']);
        
        if (empty($tipo) || empty($url)) {
            echo "<p style='color: red;'>Todos los campos son obligatorios.</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO fuente_datos (tipo, url, fecha_actualizacion) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $tipo, $url);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Nueva fuente de datos añadida correctamente.</p>";
                echo "<script>window.location.href = '{$_SERVER['PHP_SELF']}';</script>";
            } else {
                echo "<p style='color: red;'>❌ Error al agregar: " . $stmt->error . "</p>";
            }
        }
    }
    
    // Link para probar la conexión
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='check_google_sheets.php' style='padding: 8px 16px; background-color: #2196F3; color: white; text-decoration: none;'>Verificar Conexión a Google Sheets</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 