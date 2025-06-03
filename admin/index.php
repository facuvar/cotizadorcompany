<?php
// Configuración del admin
require_once 'config.php';

// Manejar logout
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Requerir autenticación
requireAuth();

// Obtener estadísticas
$stats = [
    'presupuestos' => ['total' => 0, 'mes' => 0, 'cambio' => 0],
    'clientes' => ['total' => 0, 'nuevos' => 0, 'cambio' => 0],
    'ingresos' => ['total' => 0, 'mes' => 0, 'cambio' => 0],
    'productos' => ['total' => 0, 'activos' => 0]
];

$ultimosPresupuestos = [];
$chartData = ['labels' => [], 'values' => []];

try {
    $pdo = getDBConnection();
    
    // Total presupuestos
    $result = $pdo->query("SELECT COUNT(*) as total FROM presupuestos");
    $stats['presupuestos']['total'] = $result->fetchColumn();
    
    // Presupuestos del mes
    $result = $pdo->query("SELECT COUNT(*) as total FROM presupuestos WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE())");
    $stats['presupuestos']['mes'] = $result->fetchColumn();
    
    // Total ingresos
    $result = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM presupuestos");
    $stats['ingresos']['total'] = $result->fetchColumn();
    
    // Ingresos del mes
    $result = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM presupuestos WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE())");
    $stats['ingresos']['mes'] = $result->fetchColumn();
    
    // Total opciones
    $result = $pdo->query("SELECT COUNT(*) as total FROM opciones");
    $stats['productos']['total'] = $result->fetchColumn();
    
    // Opciones activas (todas por ahora)
    $stats['productos']['activos'] = $stats['productos']['total'];
    
    // Últimos presupuestos
    $stmt = $pdo->query("SELECT * FROM presupuestos ORDER BY fecha_creacion DESC LIMIT 10");
    $ultimosPresupuestos = $stmt->fetchAll();
    
    // Datos para gráfico (últimos 7 días)
    $stmt = $pdo->query("
        SELECT DATE(fecha_creacion) as fecha, COALESCE(SUM(total), 0) as total 
        FROM presupuestos 
        WHERE fecha_creacion >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        GROUP BY DATE(fecha_creacion)
        ORDER BY fecha
    ");
    
    while ($row = $stmt->fetch()) {
        $chartData['labels'][] = date('d/m', strtotime($row['fecha']));
        $chartData['values'][] = floatval($row['total']);
    }
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        echo "Error: " . $e->getMessage();
    }
    logError("Dashboard error: " . $e->getMessage());
}

// Calcular cambios porcentuales (simulados por ahora)
$stats['presupuestos']['cambio'] = 12.5;
$stats['clientes']['cambio'] = -2.4;
$stats['ingresos']['cambio'] = 18.2;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Cotizador Company</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #1e293b;
            color: white;
            padding: 2rem 0;
        }
        
        .sidebar-header {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid #334155;
        }
        
        .sidebar-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 2rem 0;
        }
        
        .nav-item {
            display: block;
            padding: 0.75rem 2rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover,
        .nav-item.active {
            background: #334155;
            color: white;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .environment-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .railway {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .local {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #3b82f6;
        }
        
        .stat-card.success::before { background: #10b981; }
        .stat-card.warning::before { background: #f59e0b; }
        .stat-card.info::before { background: #06b6d4; }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.success { background: rgba(16, 185, 129, 0.1); }
        .stat-icon.warning { background: rgba(245, 158, 11, 0.1); }
        .stat-icon.info { background: rgba(6, 182, 212, 0.1); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .stat-change {
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .stat-change.positive { color: #10b981; }
        .stat-change.negative { color: #ef4444; }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }
        
        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table th {
            font-weight: 600;
            color: #374151;
            background: #f8fafc;
        }
        
        .table tbody tr:hover {
            background: #f8fafc;
        }
        
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status.completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .quick-actions {
            display: grid;
            gap: 1rem;
        }
        
        .action-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.3s ease;
        }
        
        .action-item:hover {
            background: #f8fafc;
            border-color: #3b82f6;
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .action-content h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .action-content p {
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🏢 Admin Panel</h2>
                <p style="font-size: 0.875rem; color: #94a3b8; margin-top: 0.5rem;">Cotizador Company</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item active">📊 Dashboard</a>
                <a href="presupuestos.php" class="nav-item">📋 Presupuestos</a>
                <a href="productos.php" class="nav-item">🏗️ Productos</a>
                <a href="clientes.php" class="nav-item">👥 Clientes</a>
                <a href="configuracion.php" class="nav-item">⚙️ Configuración</a>
                <a href="../cotizador.php" class="nav-item" target="_blank">🔗 Ver Cotizador</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <header class="header">
                <h1 class="header-title">Dashboard</h1>
                <div class="header-actions">
                    <div class="environment-badge <?php echo ENVIRONMENT; ?>">
                        <?php echo ENVIRONMENT === 'railway' ? '🚂 Railway' : '🏠 Local'; ?>
                    </div>
                    <a href="?logout=1" class="btn btn-danger">Cerrar Sesión</a>
                </div>
            </header>
            
            <div class="content">
                <!-- Estadísticas -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📋</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['presupuestos']['total']); ?></div>
                        <div class="stat-label">Total Presupuestos</div>
                        <div class="stat-change positive">
                            ↗️ +<?php echo $stats['presupuestos']['cambio']; ?>% este mes
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-header">
                            <div class="stat-icon success">💰</div>
                        </div>
                        <div class="stat-value">$<?php echo number_format($stats['ingresos']['total'], 0, ',', '.'); ?></div>
                        <div class="stat-label">Ingresos Totales</div>
                        <div class="stat-change positive">
                            ↗️ +<?php echo $stats['ingresos']['cambio']; ?>% este mes
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon warning">🏗️</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['productos']['total']); ?></div>
                        <div class="stat-label">Productos Disponibles</div>
                        <div class="stat-change">
                            <?php echo $stats['productos']['activos']; ?> activos
                        </div>
                    </div>
                    
                    <div class="stat-card info">
                        <div class="stat-header">
                            <div class="stat-icon info">📈</div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['presupuestos']['mes']); ?></div>
                        <div class="stat-label">Presupuestos Este Mes</div>
                        <div class="stat-change positive">
                            ↗️ Crecimiento constante
                        </div>
                    </div>
                </div>
                
                <!-- Contenido principal -->
                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Últimos Presupuestos</h3>
                        </div>
                        
                        <?php if (empty($ultimosPresupuestos)): ?>
                            <div style="text-align: center; padding: 2rem; color: #64748b;">
                                <p>📋 No hay presupuestos registrados aún</p>
                                <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                                    Los presupuestos aparecerán aquí cuando los usuarios usen el cotizador.
                                </p>
                            </div>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosPresupuestos as $presupuesto): ?>
                                    <tr>
                                        <td>#<?php echo $presupuesto['id']; ?></td>
                                        <td><?php echo htmlspecialchars($presupuesto['nombre_cliente'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($presupuesto['total'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($presupuesto['fecha_creacion'])); ?></td>
                                        <td><span class="status pending">Pendiente</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Acciones Rápidas</h3>
                        </div>
                        
                        <div class="quick-actions">
                            <a href="../cotizador.php" class="action-item" target="_blank">
                                <div class="action-icon">🔗</div>
                                <div class="action-content">
                                    <h4>Ver Cotizador</h4>
                                    <p>Abrir el cotizador público</p>
                                </div>
                            </a>
                            
                            <a href="../upload_sql_railway.php" class="action-item" target="_blank">
                                <div class="action-icon">📤</div>
                                <div class="action-content">
                                    <h4>Upload SQL</h4>
                                    <p>Subir archivos SQL</p>
                                </div>
                            </a>
                            
                            <a href="productos.php" class="action-item">
                                <div class="action-icon">🏗️</div>
                                <div class="action-content">
                                    <h4>Gestionar Productos</h4>
                                    <p>Editar ascensores y adicionales</p>
                                </div>
                            </a>
                            
                            <a href="configuracion.php" class="action-item">
                                <div class="action-icon">⚙️</div>
                                <div class="action-content">
                                    <h4>Configuración</h4>
                                    <p>Ajustes del sistema</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 