<?php
session_start();
require_once '../sistema/config.php';
require_once '../sistema/includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: presupuestos.php');
    exit;
}

$presupuestoId = (int)$_GET['id'];

// Conectar a la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();

// Obtener datos del presupuesto
$sql = "SELECT * FROM presupuestos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $presupuestoId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: presupuestos.php');
    exit;
}

$presupuesto = $result->fetch_assoc();

// Obtener historial de estados si existe la tabla
$historial = [];
$tableExists = $conn->query("SHOW TABLES LIKE 'presupuestos_historial'")->num_rows > 0;

if ($tableExists) {
    $sqlHistorial = "SELECT * FROM presupuestos_historial WHERE presupuesto_id = ? ORDER BY fecha_cambio DESC";
    $stmtHistorial = $conn->prepare($sqlHistorial);
    $stmtHistorial->bind_param("i", $presupuestoId);
    $stmtHistorial->execute();
    $resultHistorial = $stmtHistorial->get_result();
    
    while ($row = $resultHistorial->fetch_assoc()) {
        $historial[] = $row;
    }
}

// Formatear adicionales para mostrar
$adicionales = [];
if (!empty($presupuesto['adicionales'])) {
    $adicionalesData = json_decode($presupuesto['adicionales'], true);
    if (is_array($adicionalesData)) {
        $adicionales = $adicionalesData;
    }
}

// Función para formatear precio
function formatearPrecio($precio) {
    return '$ ' . number_format($precio, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Presupuesto - Panel de Administración</title>
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
        .badge-estado {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }
        .estado-pendiente { background-color: #ffc107; color: #212529; }
        .estado-enviado { background-color: #17a2b8; color: white; }
        .estado-aprobado { background-color: #28a745; color: white; }
        .estado-rechazado { background-color: #dc3545; color: white; }
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
                            <a href="presupuestos.php" class="active">
                                <i class="bi bi-file-earmark-text"></i> Presupuestos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="opciones.php">
                                <i class="bi bi-list-check"></i> Opciones
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
                        <h1 class="h2">Detalles del Presupuesto</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="presupuestos.php" class="btn btn-sm btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                            <a href="../presupuestos/pdf_detallado.php?id=<?php echo $presupuestoId; ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-file-pdf me-1"></i> Ver PDF
                            </a>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal">
                                <i class="bi bi-arrow-repeat me-1"></i> Cambiar Estado
                            </button>
                        </div>
                    </div>
                    
                    <!-- Información del presupuesto -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Información General</h5>
                                    <span class="badge badge-estado estado-<?php echo htmlspecialchars($presupuesto['estado'] ?? 'pendiente'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($presupuesto['estado'] ?? 'Pendiente')); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Código</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['codigo'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Fecha de Creación</h6>
                                            <p class="fs-5"><?php echo date('d/m/Y H:i', strtotime($presupuesto['fecha_creacion'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <h5 class="border-bottom pb-2 mb-3">Datos del Cliente</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Nombre</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['nombre']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Email</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['email']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Teléfono</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['telefono']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <h5 class="border-bottom pb-2 mb-3">Producto Seleccionado</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Producto</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['producto_nombre']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Opción</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['opcion_nombre']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <h5 class="border-bottom pb-2 mb-3">Condiciones</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Plazo de Entrega</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['plazo_nombre']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Forma de Pago</h6>
                                            <p class="fs-5"><?php echo htmlspecialchars($presupuesto['forma_pago']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($adicionales)): ?>
                                    <h5 class="border-bottom pb-2 mb-3">Adicionales</h5>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <ul class="list-group">
                                                <?php foreach ($adicionales as $adicional): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <?php echo htmlspecialchars($adicional['nombre'] ?? 'Adicional'); ?>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <?php echo formatearPrecio($adicional['precio'] ?? 0); ?>
                                                    </span>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <h5 class="border-bottom pb-2 mb-3">Resumen Financiero</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Subtotal</h6>
                                            <p class="fs-5"><?php echo formatearPrecio($presupuesto['subtotal']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Descuento</h6>
                                            <p class="fs-5"><?php echo formatearPrecio($presupuesto['descuento']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted">Total</h6>
                                            <p class="fs-5 fw-bold"><?php echo formatearPrecio($presupuesto['total']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Historial de estados -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Historial de Estados</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($historial)): ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i> No hay historial de cambios de estado
                                        </div>
                                    <?php else: ?>
                                        <div class="timeline">
                                            <?php foreach ($historial as $cambio): ?>
                                                <div class="timeline-item mb-3 pb-3 border-bottom">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="badge bg-secondary">
                                                            <?php echo date('d/m/Y H:i', strtotime($cambio['fecha_cambio'])); ?>
                                                        </span>
                                                        <span class="badge estado-<?php echo htmlspecialchars($cambio['estado_nuevo']); ?>">
                                                            <?php echo ucfirst(htmlspecialchars($cambio['estado_nuevo'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Estado anterior: <?php echo ucfirst(htmlspecialchars($cambio['estado_anterior'])); ?></small>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Usuario: <?php echo htmlspecialchars($cambio['usuario']); ?></small>
                                                    </div>
                                                    <?php if (!empty($cambio['comentario'])): ?>
                                                        <div class="mt-2">
                                                            <p class="mb-0"><small><?php echo htmlspecialchars($cambio['comentario']); ?></small></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Acciones</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-danger eliminar-presupuesto" data-id="<?php echo $presupuestoId; ?>" data-bs-toggle="modal" data-bs-target="#eliminarModal">
                                            <i class="bi bi-trash me-1"></i> Eliminar Presupuesto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Cambiar Estado -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar Estado del Presupuesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" name="presupuesto_id" value="<?php echo $presupuestoId; ?>">
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Nuevo Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="">Seleccionar estado...</option>
                                <option value="pendiente" <?php echo ($presupuesto['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="enviado" <?php echo ($presupuesto['estado'] == 'enviado') ? 'selected' : ''; ?>>Enviado</option>
                                <option value="aprobado" <?php echo ($presupuesto['estado'] == 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                                <option value="rechazado" <?php echo ($presupuesto['estado'] == 'rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario (opcional)</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEstado">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Eliminar -->
    <div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este presupuesto? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="presupuestos.php?eliminar=<?php echo $presupuestoId; ?>" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Cambiar estado
            $('#btnGuardarEstado').click(function() {
                var formData = $('#formCambiarEstado').serialize();
                
                $.ajax({
                    url: 'cambiar_estado_presupuesto.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            alert('Estado actualizado correctamente');
                            // Recargar la página para mostrar los cambios
                            location.reload();
                        } else {
                            // Mostrar mensaje de error
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            });
        });
    </script>
</body>
</html>
