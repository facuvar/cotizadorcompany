<?php
require_once 'sistema/config.php';
require_once 'sistema/includes/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Crear un presupuesto de prueba con ubicación de obra
    $numero_presupuesto = 'TEST-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $insert_query = "INSERT INTO presupuestos (
        numero_presupuesto,
        cliente_nombre, 
        cliente_email, 
        cliente_telefono, 
        cliente_empresa,
        ubicacion_obra,
        observaciones,
        subtotal,
        descuento_porcentaje,
        descuento_monto,
        total,
        plazo_entrega,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($insert_query);
    
    $datos = [
        $numero_presupuesto,
        'Juan Carlos Pérez',
        'juan.perez@ejemplo.com',
        '+54 11 4567-8900',
        'Constructora ABC S.A.',
        'Av. Corrientes 1234, CABA, Buenos Aires',
        'Edificio de 8 plantas. Acceso por calle principal. Horario de trabajo: 8:00 a 17:00 hs.',
        150000.00,
        10.0,
        15000.00,
        135000.00,
        '90'
    ];
    
    $stmt->bind_param(
        'sssssssdddds',
        ...$datos
    );
    
    if ($stmt->execute()) {
        $presupuesto_id = $conn->insert_id;
        echo "✅ Presupuesto de prueba creado exitosamente!\n";
        echo "ID: $presupuesto_id\n";
        echo "Número: $numero_presupuesto\n";
        echo "Cliente: Juan Carlos Pérez\n";
        echo "Ubicación: Av. Corrientes 1234, CABA, Buenos Aires\n";
        echo "Observaciones: Edificio de 8 plantas. Acceso por calle principal. Horario de trabajo: 8:00 a 17:00 hs.\n";
        echo "\n";
        echo "🔗 Enlaces para probar:\n";
        echo "- Ver detalle: http://localhost/company-presupuestos-online-2/admin/ver_presupuesto.php?id=$presupuesto_id\n";
        echo "- Ver PDF: http://localhost/company-presupuestos-online-2/sistema/api/download_pdf.php?id=$presupuesto_id\n";
        echo "\n";
        echo "📋 Verificar que se muestren:\n";
        echo "✓ Ubicación de la obra en el detalle del presupuesto\n";
        echo "✓ Observaciones del cliente en el detalle del presupuesto\n";
        echo "✓ Ubicación de la obra en el PDF generado\n";
        echo "✓ Observaciones del cliente en el PDF generado\n";
    } else {
        throw new Exception('Error al crear presupuesto de prueba: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 