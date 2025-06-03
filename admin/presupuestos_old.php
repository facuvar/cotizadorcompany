<?php
session_start();
require_once '../sistema/config.php';
require_once '../sistema/includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtros
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_producto = isset($_GET['producto']) ? $_GET['producto'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir consulta
$query = "SELECT * FROM presupuestos WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM presupuestos WHERE 1=1";

$params = [];
$types = "";

if (!empty($filtro_cliente)) {
    $query .= " AND (nombre_cliente LIKE ? OR nombre LIKE ?)";
    $countQuery .= " AND (nombre_cliente LIKE ? OR nombre LIKE ?)";
    $params[] = "%$filtro_cliente%";
    $params[] = "%$filtro_cliente%";
    $types .= "ss";
}

if (!empty($filtro_producto)) {
    $query .= " AND producto_nombre LIKE ?";
    $countQuery .= " AND producto_nombre LIKE ?";
    $params[] = "%$filtro_producto%";
    $types .= "s";
}

if (!empty($filtro_fecha_desde)) {
    $query .= " AND fecha_creacion >= ?";
    $countQuery .= " AND fecha_creacion >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if (!empty($filtro_fecha_hasta)) {
    $query .= " AND fecha_creacion <= ?";
    $countQuery .= " AND fecha_creacion <= ?";
    $params[] = $filtro_fecha_hasta . " 23:59:59";
    $types .= "s";
}

// Ordenar
$query .= " ORDER BY fecha_creacion DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Ejecutar consulta para contar total
$stmt = $conn->prepare($countQuery);
if (!empty($params) && count($params) > 0 && !empty($types)) {
    // Solo vincular parámetros si hay filtros (excluyendo los parámetros de paginación)
    $countTypes = substr($types, 0, strlen($types) - 2); // Quitar los dos 'i' de la paginación
    if (!empty($countTypes)) {
        $stmt->bind_param($countTypes, ...array_slice($params, 0, count($params) - 2));
    }
}
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total = $row['total'];
$totalPages = ceil($total / $limit);

// Ejecutar consulta principal
$stmt = $conn->prepare($query);
if (!empty($params) && count($params) > 0 && !empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$presupuestos = $stmt->get_result();

// Eliminar presupuesto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM presupuestos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    header('Location: presupuestos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuestos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-item {
            margin-bottom: 5px;
        }
        .sidebar .nav-item i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>Panel de Administración</h5>
                        <p class="small">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrador'); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="importar.php">
                                <i class="bi bi-file-earmark-excel"></i> Importar Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="presupuestos.php" class="active">
                                <i class="bi bi-file-text"></i> Presupuestos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a href="dashboard.php?logout=1" class="text-danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Presupuestos</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="../cotizador_xls_fixed.php" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-circle me-1"></i> Nuevo Presupuesto
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" class="row g-3">
                                <div class="col-md-3">
                                    <label for="cliente" class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="producto" class="form-label">Producto</label>
                                    <input type="text" class="form-control" id="producto" name="producto" value="<?php echo htmlspecialchars($filtro_producto); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-filter me-1"></i> Filtrar
                                    </button>
                                    <a href="presupuestos.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i> Limpiar Filtros
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Presupuestos Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Lista de Presupuestos</h5>
                            <span class="badge bg-primary"><?php echo $total; ?> presupuestos</span>
                        </div>
                        <div class="card-body">
                            <?php if ($presupuestos && $presupuestos->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Cliente</th>
                                                <th>Producto</th>
                                                <th>Total</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($presupuesto = $presupuestos->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $presupuesto['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($presupuesto['nombre_cliente'] ?? $presupuesto['nombre'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($presupuesto['producto_nombre'] ?? 'N/A'); ?></td>
                                                    <td>$<?php echo number_format($presupuesto['total'] ?? 0, 2, ',', '.'); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($presupuesto['fecha_creacion'])); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="ver_presupuesto.php?id=<?php echo $presupuesto['id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="presupuestos.php?eliminar=<?php echo $presupuesto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este presupuesto?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&cliente=<?php echo urlencode($filtro_cliente); ?>&producto=<?php echo urlencode($filtro_producto); ?>&fecha_desde=<?php echo urlencode($filtro_fecha_desde); ?>&fecha_hasta=<?php echo urlencode($filtro_fecha_hasta); ?>">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&cliente=<?php echo urlencode($filtro_cliente); ?>&producto=<?php echo urlencode($filtro_producto); ?>&fecha_desde=<?php echo urlencode($filtro_fecha_desde); ?>&fecha_hasta=<?php echo urlencode($filtro_fecha_hasta); ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&cliente=<?php echo urlencode($filtro_cliente); ?>&producto=<?php echo urlencode($filtro_producto); ?>&fecha_desde=<?php echo urlencode($filtro_fecha_desde); ?>&fecha_hasta=<?php echo urlencode($filtro_fecha_hasta); ?>">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> No se encontraron presupuestos
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
