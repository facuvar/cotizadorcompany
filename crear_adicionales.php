<?php
// Script para crear adicionales de prueba
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

// Función para mostrar mensajes
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo == 'error' ? 'red' : ($tipo == 'success' ? 'green' : 'blue');
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid $color; border-radius: 5px; color: $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Verificar estructura de la tabla xls_adicionales
$result = $conn->query("DESCRIBE xls_adicionales");
$tieneDescripcion = false;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'descripcion') {
            $tieneDescripcion = true;
            break;
        }
    }
}

// Si no existe la columna descripcion, la agregamos
if (!$tieneDescripcion) {
    $conn->query("ALTER TABLE xls_adicionales ADD COLUMN descripcion TEXT AFTER nombre");
    mostrarMensaje("Se ha agregado la columna 'descripcion' a la tabla xls_adicionales", "success");
}

// Verificar si ya existen adicionales
$result = $conn->query("SELECT COUNT(*) as total FROM xls_adicionales");
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    mostrarMensaje("Ya existen {$row['total']} adicionales en la base de datos", "info");
    
    // Verificar si están asociados a los productos
    $query = "SELECT p.id, p.nombre, COUNT(pa.id) as total_adicionales 
              FROM xls_productos p 
              LEFT JOIN xls_productos_adicionales pa ON p.id = pa.producto_id 
              WHERE p.nombre LIKE '%EQUIPO ELECTROMECANICO%' OR 
                    p.nombre LIKE '%ASCENSORES HIDRAULICOS%' OR 
                    p.nombre LIKE '%MONTACARGAS%' OR 
                    p.nombre LIKE '%SALVAESCALERAS%' 
              GROUP BY p.id";
    $result = $conn->query($query);
    
    echo "<h3>Productos que pueden tener adicionales:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Nombre</th><th>Adicionales</th></tr>";
    
    $necesitaAsociaciones = false;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['total_adicionales']}</td>";
        echo "</tr>";
        
        if ($row['total_adicionales'] == 0) {
            $necesitaAsociaciones = true;
        }
    }
    
    echo "</table>";
    
    if ($necesitaAsociaciones) {
        echo "<form method='post'>";
        echo "<input type='hidden' name='accion' value='asociar'>";
        echo "<button type='submit' style='background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;'>Asociar adicionales a productos</button>";
        echo "</form>";
    }
} else {
    // No hay adicionales, crear nuevos
    echo "<form method='post'>";
    echo "<input type='hidden' name='accion' value='crear'>";
    echo "<button type='submit' style='background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Crear adicionales de prueba</button>";
    echo "</form>";
}

// Procesar acciones
if (isset($_POST['accion'])) {
    if ($_POST['accion'] == 'crear') {
        // Crear adicionales de prueba
        $adicionales = [
            ['nombre' => 'Adicional de prueba 1', 'descripcion' => 'Descripción del adicional 1'],
            ['nombre' => 'Adicional de prueba 2', 'descripcion' => 'Descripción del adicional 2'],
            ['nombre' => 'Adicional de prueba 3', 'descripcion' => 'Descripción del adicional 3']
        ];
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Crear los adicionales
            foreach ($adicionales as $adicional) {
                $query = "INSERT INTO xls_adicionales (nombre, descripcion) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ss", $adicional['nombre'], $adicional['descripcion']);
                $stmt->execute();
                $adicionalId = $conn->insert_id;
                
                mostrarMensaje("Adicional creado: {$adicional['nombre']} (ID: $adicionalId)", "success");
                
                // Crear precios para cada plazo
                $query = "SELECT id FROM xls_plazos";
                $result = $conn->query($query);
                
                while ($row = $result->fetch_assoc()) {
                    $plazoId = $row['id'];
                    $precio = rand(1000, 5000);
                    
                    $query = "INSERT INTO xls_adicionales_precios (adicional_id, plazo_id, precio) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("iid", $adicionalId, $plazoId, $precio);
                    $stmt->execute();
                    
                    mostrarMensaje("Precio creado para adicional ID $adicionalId, plazo ID $plazoId: $precio", "success");
                }
                
                // Asociar con los productos específicos
                $productosConAdicionales = [
                    'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                    'ASCENSORES HIDRAULICOS',
                    'MONTACARGAS',
                    'SALVAESCALERAS'
                ];
                
                foreach ($productosConAdicionales as $productoNombre) {
                    $query = "SELECT id FROM xls_productos WHERE nombre LIKE ?";
                    $stmt = $conn->prepare($query);
                    $productoNombreBusqueda = "%$productoNombre%";
                    $stmt->bind_param("s", $productoNombreBusqueda);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $productoId = $row['id'];
                            
                            // Verificar si ya existe la relación
                            $query = "SELECT * FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $productoId, $adicionalId);
                            $stmt->execute();
                            $result2 = $stmt->get_result();
                            
                            if ($result2->num_rows === 0) {
                                // Crear la relación
                                $query = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $productoId, $adicionalId);
                                $stmt->execute();
                                
                                mostrarMensaje("Adicional ID $adicionalId asociado al producto ID $productoId ($productoNombre)", "success");
                            }
                        }
                    } else {
                        mostrarMensaje("No se encontró el producto: $productoNombre", "error");
                    }
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            mostrarMensaje("Adicionales de prueba creados correctamente", "success");
            
            // Recargar la página para mostrar los resultados
            echo "<script>window.location.href = 'crear_adicionales.php';</script>";
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al crear adicionales de prueba: " . $e->getMessage(), "error");
        }
    } else if ($_POST['accion'] == 'asociar') {
        // Asociar adicionales existentes a productos
        $conn->begin_transaction();
        
        try {
            // Obtener todos los adicionales
            $query = "SELECT id FROM xls_adicionales";
            $result = $conn->query($query);
            
            $adicionales = [];
            while ($row = $result->fetch_assoc()) {
                $adicionales[] = $row['id'];
            }
            
            // Obtener productos que pueden tener adicionales
            $productosConAdicionales = [
                'EQUIPO ELECTROMECANICO 450KG CARGA UTIL',
                'ASCENSORES HIDRAULICOS',
                'MONTACARGAS',
                'SALVAESCALERAS'
            ];
            
            foreach ($productosConAdicionales as $productoNombre) {
                $query = "SELECT id FROM xls_productos WHERE nombre LIKE ?";
                $stmt = $conn->prepare($query);
                $productoNombreBusqueda = "%$productoNombre%";
                $stmt->bind_param("s", $productoNombreBusqueda);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productoId = $row['id'];
                        
                        foreach ($adicionales as $adicionalId) {
                            // Verificar si ya existe la relación
                            $query = "SELECT * FROM xls_productos_adicionales WHERE producto_id = ? AND adicional_id = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $productoId, $adicionalId);
                            $stmt->execute();
                            $result2 = $stmt->get_result();
                            
                            if ($result2->num_rows === 0) {
                                // Crear la relación
                                $query = "INSERT INTO xls_productos_adicionales (producto_id, adicional_id) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $productoId, $adicionalId);
                                $stmt->execute();
                                
                                mostrarMensaje("Adicional ID $adicionalId asociado al producto ID $productoId ($productoNombre)", "success");
                            }
                        }
                    }
                } else {
                    mostrarMensaje("No se encontró el producto: $productoNombre", "error");
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            mostrarMensaje("Adicionales asociados correctamente a los productos", "success");
            
            // Recargar la página para mostrar los resultados
            echo "<script>window.location.href = 'crear_adicionales.php';</script>";
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            mostrarMensaje("Error al asociar adicionales: " . $e->getMessage(), "error");
        }
    }
}

// Enlaces
echo "<div style='margin-top: 20px;'>";
echo "<a href='cotizador_simple.php' style='background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Ir al Cotizador</a>";
echo "<a href='importar_xls_formulas_v2.php' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Ir al Importador</a>";
echo "</div>";
?>
