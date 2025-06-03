<?php
// Iniciar sesión antes de cualquier output
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Cargar configuración
require_once '../sistema/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Obtener estadísticas
$stats = [];
$stats['categorias'] = $pdo->query('SELECT COUNT(*) FROM categorias WHERE activo = 1')->fetchColumn();
$stats['opciones'] = $pdo->query('SELECT COUNT(*) FROM opciones WHERE activo = 1')->fetchColumn();
$stats['ascensores'] = $pdo->query('SELECT COUNT(*) FROM opciones WHERE categoria_id = 1 AND activo = 1')->fetchColumn();
$stats['adicionales'] = $pdo->query('SELECT COUNT(*) FROM opciones WHERE categoria_id = 2 AND activo = 1')->fetchColumn();
$stats['descuentos'] = $pdo->query('SELECT COUNT(*) FROM opciones WHERE categoria_id = 3 AND activo = 1')->fetchColumn();

// Obtener categorías
$categorias = $pdo->query('SELECT * FROM categorias WHERE activo = 1 ORDER BY orden')->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Datos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background-color: white;
            border-left: 4px solid #007bff;
        }
        .railway-badge {
            background: #0066ff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .nav-pills .nav-link {
            border-radius: 10px;
            margin-bottom: 5px;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .section-content {
            display: none;
        }
        .section-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <?php if (defined('IS_RAILWAY') && IS_RAILWAY): ?>
                        <div class="text-center">
                            <span class="railway-badge">🚂 Railway</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <h5>Panel Admin</h5>
                        <p class="small">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'admin'); ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gestionar_datos.php" class="active">
                                <i class="bi bi-database-gear"></i> Gestionar Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="presupuestos.php">
                                <i class="bi bi-file-earmark-text"></i> Presupuestos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../cotizador.php" target="_blank">
                                <i class="bi bi-calculator"></i> Cotizador
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../" target="_blank">
                                <i class="bi bi-house"></i> Sitio Web
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a href="?logout=1" class="text-danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-database-gear"></i> Gestionar Datos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <h3 class="text-primary"><?= $stats['categorias'] ?></h3>
                            <p class="mb-0">Categorías</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <h3 class="text-success"><?= $stats['ascensores'] ?></h3>
                            <p class="mb-0">Ascensores</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <h3 class="text-warning"><?= $stats['adicionales'] ?></h3>
                            <p class="mb-0">Adicionales</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <h3 class="text-info"><?= $stats['descuentos'] ?></h3>
                            <p class="mb-0">Descuentos</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card text-center">
                            <h3 class="text-dark"><?= $stats['opciones'] ?></h3>
                            <p class="mb-0">Total Opciones</p>
                        </div>
                    </div>
                </div>

                <!-- Navegación por pestañas -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-ascensores-tab" data-bs-toggle="pill" data-bs-target="#v-pills-ascensores" type="button" role="tab">
                                <i class="bi bi-building"></i> Ascensores
                            </button>
                            <button class="nav-link" id="v-pills-adicionales-tab" data-bs-toggle="pill" data-bs-target="#v-pills-adicionales" type="button" role="tab">
                                <i class="bi bi-gear"></i> Adicionales
                            </button>
                            <button class="nav-link" id="v-pills-descuentos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-descuentos" type="button" role="tab">
                                <i class="bi bi-percent"></i> Descuentos
                            </button>
                            <button class="nav-link" id="v-pills-categorias-tab" data-bs-toggle="pill" data-bs-target="#v-pills-categorias" type="button" role="tab">
                                <i class="bi bi-folder"></i> Categorías
                            </button>
                            <button class="nav-link" id="v-pills-importar-tab" data-bs-toggle="pill" data-bs-target="#v-pills-importar" type="button" role="tab">
                                <i class="bi bi-upload"></i> Importar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="tab-content" id="v-pills-tabContent">
                            <!-- Sección Ascensores -->
                            <div class="tab-pane fade show active" id="v-pills-ascensores" role="tabpanel">
                                <div class="form-container">
                                    <h4><i class="bi bi-building"></i> Gestión de Ascensores</h4>
                                    <div id="message-ascensores"></div>
                                    
                                    <form id="form-ascensor">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre del Ascensor</label>
                                                    <input type="text" class="form-control" name="nombre" required placeholder="Ej: EQUIPO ELECTROMECANICO 450KG">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Orden</label>
                                                    <input type="number" class="form-control" name="orden" required placeholder="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="es_titulo" id="es_titulo_ascensor">
                                                <label class="form-check-label" for="es_titulo_ascensor">
                                                    Es un título (no seleccionable)
                                                </label>
                                                <div class="form-text">Marcar si es un encabezado que agrupa opciones</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Descripción (Opcional)</label>
                                            <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción detallada del ascensor"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 90 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_90_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 160-180 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_160_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 270 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_270_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-save"></i> Guardar Ascensor
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario('form-ascensor')">
                                            <i class="bi bi-arrow-clockwise"></i> Limpiar
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="table-container">
                                    <h5>Lista de Ascensores</h5>
                                    <div id="lista-ascensores">
                                        <!-- Se cargará dinámicamente -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Adicionales -->
                            <div class="tab-pane fade" id="v-pills-adicionales" role="tabpanel">
                                <div class="form-container">
                                    <h4><i class="bi bi-gear"></i> Gestión de Adicionales</h4>
                                    <div id="message-adicionales"></div>
                                    
                                    <form id="form-adicional">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre del Adicional</label>
                                                    <input type="text" class="form-control" name="nombre" required placeholder="Ej: Parada adicional">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Orden</label>
                                                    <input type="number" class="form-control" name="orden" required placeholder="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="es_titulo" id="es_titulo_adicional">
                                                <label class="form-check-label" for="es_titulo_adicional">
                                                    Es un título (no seleccionable)
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Descripción (Opcional)</label>
                                            <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción del adicional"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 90 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_90_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 160-180 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_160_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Precio 270 días</label>
                                                    <input type="number" step="0.01" class="form-control" name="precio_270_dias" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-save"></i> Guardar Adicional
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario('form-adicional')">
                                            <i class="bi bi-arrow-clockwise"></i> Limpiar
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="table-container">
                                    <h5>Lista de Adicionales</h5>
                                    <div id="lista-adicionales">
                                        <!-- Se cargará dinámicamente -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Descuentos -->
                            <div class="tab-pane fade" id="v-pills-descuentos" role="tabpanel">
                                <div class="form-container">
                                    <h4><i class="bi bi-percent"></i> Gestión de Descuentos</h4>
                                    <div id="message-descuentos"></div>
                                    
                                    <form id="form-descuento">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre del Descuento</label>
                                                    <input type="text" class="form-control" name="nombre" required placeholder="Ej: Descuento por volumen">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Orden</label>
                                                    <input type="number" class="form-control" name="orden" required placeholder="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="es_titulo" id="es_titulo_descuento">
                                                <label class="form-check-label" for="es_titulo_descuento">
                                                    Es un título (no seleccionable)
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Descripción (Opcional)</label>
                                            <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción del descuento"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Porcentaje de Descuento (%)</label>
                                                    <input type="number" step="0.01" class="form-control" name="descuento" placeholder="5.00" min="0" max="100">
                                                    <div class="form-text">Ingrese solo el número del porcentaje (ej: 5 para 5%)</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-save"></i> Guardar Descuento
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario('form-descuento')">
                                            <i class="bi bi-arrow-clockwise"></i> Limpiar
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="table-container">
                                    <h5>Lista de Descuentos</h5>
                                    <div id="lista-descuentos">
                                        <!-- Se cargará dinámicamente -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Categorías -->
                            <div class="tab-pane fade" id="v-pills-categorias" role="tabpanel">
                                <div class="form-container">
                                    <h4><i class="bi bi-folder"></i> Gestión de Categorías</h4>
                                    <div id="message-categorias"></div>
                                    
                                    <form id="form-categoria">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Nombre de la Categoría</label>
                                                    <input type="text" class="form-control" name="nombre" required placeholder="Ej: Ascensores">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Orden</label>
                                                    <input type="number" class="form-control" name="orden" required placeholder="1">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Descripción (Opcional)</label>
                                            <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción de la categoría"></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-save"></i> Guardar Categoría
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarFormulario('form-categoria')">
                                            <i class="bi bi-arrow-clockwise"></i> Limpiar
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="table-container">
                                    <h5>Lista de Categorías</h5>
                                    <div id="lista-categorias">
                                        <!-- Se cargará dinámicamente -->
                                    </div>
                                </div>
                            </div>

                            <!-- Sección Importar -->
                            <div class="tab-pane fade" id="v-pills-importar" role="tabpanel">
                                <div class="form-container">
                                    <h4><i class="bi bi-upload"></i> Importar Datos</h4>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Información:</strong> Puedes importar datos desde archivos Excel (.xlsx) o CSV.
                                    </div>
                                    
                                    <form id="form-importar" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label class="form-label">Seleccionar archivo</label>
                                            <input type="file" class="form-control" name="archivo" accept=".xlsx,.xls,.csv" required>
                                            <div class="form-text">Formatos soportados: Excel (.xlsx, .xls) y CSV (.csv)</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de datos</label>
                                            <select class="form-select" name="tipo_datos" required>
                                                <option value="">Seleccionar tipo...</option>
                                                <option value="ascensores">Ascensores</option>
                                                <option value="adicionales">Adicionales</option>
                                                <option value="descuentos">Descuentos</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-upload"></i> Importar Datos
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="table-container">
                                    <h5>Historial de Importaciones</h5>
                                    <div id="historial-importaciones">
                                        <!-- Se cargará dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funciones JavaScript para la gestión de datos
        
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarAscensores();
            cargarAdicionales();
            cargarDescuentos();
            cargarCategorias();
        });

        // Función para limpiar formularios
        function limpiarFormulario(formId) {
            const form = document.getElementById(formId);
            form.reset();
            
            // Resetear variables de edición
            editandoId = null;
            tipoEditando = null;
            
            // Restaurar botón original
            const submitBtn = form.querySelector('button[type="submit"]');
            if (formId === 'form-ascensor') {
                submitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Crear Ascensor';
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-success');
                // Reinicializar checkbox "Es título"
                actualizarEstadoEsTitulo('form-ascensor', false);
            } else if (formId === 'form-adicional') {
                submitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Crear Adicional';
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-success');
                // Reinicializar checkbox "Es título"
                actualizarEstadoEsTitulo('form-adicional', false);
            } else if (formId === 'form-descuento') {
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Guardar Descuento';
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-success');
            } else if (formId === 'form-categoria') {
                submitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Crear Categoría';
                submitBtn.classList.remove('btn-warning');
                submitBtn.classList.add('btn-success');
            }
        }

        // Funciones para cargar datos
        function cargarAscensores() {
            fetch('api_gestionar_datos.php?action=listar_ascensores')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarTablaAscensores(data.data);
                } else {
                    document.getElementById('lista-ascensores').innerHTML = '<div class="alert alert-warning">Error al cargar ascensores: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('lista-ascensores').innerHTML = '<div class="alert alert-danger">Error de conexión al cargar ascensores</div>';
            });
        }

        function cargarAdicionales() {
            fetch('api_gestionar_datos.php?action=listar_adicionales')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarTablaAdicionales(data.data);
                } else {
                    document.getElementById('lista-adicionales').innerHTML = '<div class="alert alert-warning">Error al cargar adicionales: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('lista-adicionales').innerHTML = '<div class="alert alert-danger">Error de conexión al cargar adicionales</div>';
            });
        }

        function cargarDescuentos() {
            fetch('api_gestionar_datos.php?action=listar_descuentos')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarTablaDescuentos(data.data);
                } else {
                    document.getElementById('lista-descuentos').innerHTML = '<div class="alert alert-warning">Error al cargar descuentos: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('lista-descuentos').innerHTML = '<div class="alert alert-danger">Error de conexión al cargar descuentos</div>';
            });
        }

        function cargarCategorias() {
            fetch('api_gestionar_datos.php?action=listar_categorias')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarTablaCategorias(data.data);
                } else {
                    document.getElementById('lista-categorias').innerHTML = '<div class="alert alert-warning">Error al cargar categorías: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('lista-categorias').innerHTML = '<div class="alert alert-danger">Error de conexión al cargar categorías</div>';
            });
        }

        // Funciones para mostrar tablas
        function mostrarTablaAscensores(ascensores) {
            let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
            html += '<thead class="table-dark"><tr>';
            html += '<th>Nombre</th><th>Tipo</th><th>90 días</th><th>160-180 días</th><th>270 días</th><th>Orden</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';
            
            ascensores.forEach(ascensor => {
                const esTitulo = ascensor.es_titulo == 1;
                const rowClass = esTitulo ? 'table-secondary fw-bold' : '';
                const tipo = esTitulo ? '<span class="badge bg-primary">📂 Título</span>' : '<span class="badge bg-success">⚙️ Opción</span>';
                
                html += `<tr class="${rowClass}">`;
                html += `<td>${esTitulo ? '📂 ' : ''}${ascensor.nombre}</td>`;
                html += `<td>${tipo}</td>`;
                
                if (esTitulo) {
                    html += '<td>-</td><td>-</td><td>-</td>';
                } else {
                    html += `<td>$${parseFloat(ascensor.precio_90_dias || 0).toLocaleString()}</td>`;
                    html += `<td>$${parseFloat(ascensor.precio_160_dias || 0).toLocaleString()}</td>`;
                    html += `<td>$${parseFloat(ascensor.precio_270_dias || 0).toLocaleString()}</td>`;
                }
                
                html += `<td>${ascensor.orden}</td>`;
                html += `<td>
                    <button class="btn btn-sm btn-warning" onclick="editarAscensor(${ascensor.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarAscensor(${ascensor.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            
            if (ascensores.length === 0) {
                html = '<div class="alert alert-info">No hay ascensores registrados</div>';
            }
            
            document.getElementById('lista-ascensores').innerHTML = html;
        }

        function mostrarTablaAdicionales(adicionales) {
            let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
            html += '<thead class="table-dark"><tr>';
            html += '<th>Nombre</th><th>Tipo</th><th>90 días</th><th>160-180 días</th><th>270 días</th><th>Orden</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';
            
            adicionales.forEach(adicional => {
                const esTitulo = adicional.es_titulo == 1;
                const rowClass = esTitulo ? 'table-secondary fw-bold' : '';
                const tipo = esTitulo ? '<span class="badge bg-primary">📂 Título</span>' : '<span class="badge bg-info">🔧 Adicional</span>';
                
                html += `<tr class="${rowClass}">`;
                html += `<td>${esTitulo ? '📂 ' : ''}${adicional.nombre}</td>`;
                html += `<td>${tipo}</td>`;
                
                if (esTitulo) {
                    html += '<td>-</td><td>-</td><td>-</td>';
                } else {
                    html += `<td>$${parseFloat(adicional.precio_90_dias || 0).toLocaleString()}</td>`;
                    html += `<td>$${parseFloat(adicional.precio_160_dias || 0).toLocaleString()}</td>`;
                    html += `<td>$${parseFloat(adicional.precio_270_dias || 0).toLocaleString()}</td>`;
                }
                
                html += `<td>${adicional.orden}</td>`;
                html += `<td>
                    <button class="btn btn-sm btn-warning" onclick="editarAdicional(${adicional.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarAdicional(${adicional.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            
            if (adicionales.length === 0) {
                html = '<div class="alert alert-info">No hay adicionales registrados</div>';
            }
            
            document.getElementById('lista-adicionales').innerHTML = html;
        }

        function mostrarTablaDescuentos(descuentos) {
            let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
            html += '<thead class="table-dark"><tr>';
            html += '<th>Nombre</th><th>Tipo</th><th>Porcentaje</th><th>Descripción</th><th>Orden</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';
            
            descuentos.forEach(descuento => {
                const esTitulo = descuento.es_titulo == 1;
                const rowClass = esTitulo ? 'table-secondary fw-bold' : '';
                const tipo = esTitulo ? '<span class="badge bg-primary">📂 Título</span>' : '<span class="badge bg-warning">💰 Descuento</span>';
                
                html += `<tr class="${rowClass}">`;
                html += `<td>${esTitulo ? '📂 ' : ''}${descuento.nombre}</td>`;
                html += `<td>${tipo}</td>`;
                
                if (esTitulo) {
                    html += '<td>-</td>';
                } else {
                    const porcentaje = parseFloat(descuento.descuento || 0);
                    html += `<td><span class="badge bg-success">${porcentaje}%</span></td>`;
                }
                
                html += `<td>${descuento.descripcion || '-'}</td>`;
                html += `<td>${descuento.orden}</td>`;
                html += `<td>
                    <button class="btn btn-sm btn-warning" onclick="editarDescuento(${descuento.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarDescuento(${descuento.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            
            if (descuentos.length === 0) {
                html = '<div class="alert alert-info">No hay descuentos registrados</div>';
            }
            
            document.getElementById('lista-descuentos').innerHTML = html;
        }

        function mostrarTablaCategorias(categorias) {
            let html = '<div class="table-responsive"><table class="table table-striped table-hover">';
            html += '<thead class="table-dark"><tr>';
            html += '<th>Nombre</th><th>Descripción</th><th>Orden</th><th>Estado</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';
            
            categorias.forEach(categoria => {
                const estado = categoria.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                
                html += '<tr>';
                html += `<td><strong>${categoria.nombre}</strong></td>`;
                html += `<td>${categoria.descripcion || '-'}</td>`;
                html += `<td>${categoria.orden}</td>`;
                html += `<td>${estado}</td>`;
                html += `<td>
                    <button class="btn btn-sm btn-warning" onclick="editarCategoria(${categoria.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarCategoria(${categoria.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            
            if (categorias.length === 0) {
                html = '<div class="alert alert-info">No hay categorías registradas</div>';
            }
            
            document.getElementById('lista-categorias').innerHTML = html;
        }

        // Función para mostrar mensajes
        function mostrarMensaje(containerId, mensaje, tipo) {
            const container = document.getElementById(containerId);
            const alertClass = tipo === 'success' ? 'alert-success' : tipo === 'error' ? 'alert-danger' : 'alert-info';
            
            container.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Variables globales para edición
        let editandoId = null;
        let tipoEditando = null;

        // Funciones de edición
        function editarAscensor(id) {
            fetch(`api_gestionar_datos.php?action=obtener_ascensor&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ascensor = data.data;
                    editandoId = id;
                    tipoEditando = 'ascensor';
                    
                    // Llenar el formulario
                    const form = document.getElementById('form-ascensor');
                    form.querySelector('[name="nombre"]').value = ascensor.nombre;
                    form.querySelector('[name="orden"]').value = ascensor.orden;
                    form.querySelector('[name="descripcion"]').value = ascensor.descripcion || '';
                    form.querySelector('[name="precio_90_dias"]').value = ascensor.precio_90_dias || '';
                    form.querySelector('[name="precio_160_dias"]').value = ascensor.precio_160_dias || '';
                    form.querySelector('[name="precio_270_dias"]').value = ascensor.precio_270_dias || '';
                    form.querySelector('[name="es_titulo"]').checked = ascensor.es_titulo == 1;
                    
                    // Cambiar el botón
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="bi bi-save"></i> Actualizar Ascensor';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Activar la pestaña de ascensores
                    document.getElementById('v-pills-ascensores-tab').click();
                    
                    // Actualizar estado del checkbox "Es título"
                    actualizarEstadoEsTitulo('form-ascensor', ascensor.es_titulo == 1);
                    
                    // Scroll al formulario
                    form.scrollIntoView({ behavior: 'smooth' });
                    
                    mostrarMensaje('message-ascensores', 'Datos cargados para edición', 'success');
                } else {
                    mostrarMensaje('message-ascensores', 'Error al cargar datos: ' + data.error, 'error');
                }
            })
            .catch(error => {
                mostrarMensaje('message-ascensores', 'Error al cargar datos', 'error');
            });
        }

        function eliminarAscensor(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este ascensor?')) {
                fetch(`api_gestionar_datos.php?action=eliminar&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-ascensores', 'Ascensor eliminado exitosamente', 'success');
                        cargarAscensores();
                    } else {
                        mostrarMensaje('message-ascensores', 'Error al eliminar: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    mostrarMensaje('message-ascensores', 'Error al eliminar', 'error');
                });
            }
        }

        function editarAdicional(id) {
            fetch(`api_gestionar_datos.php?action=obtener_adicional&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const adicional = data.data;
                    editandoId = id;
                    tipoEditando = 'adicional';
                    
                    // Llenar el formulario
                    const form = document.getElementById('form-adicional');
                    form.querySelector('[name="nombre"]').value = adicional.nombre;
                    form.querySelector('[name="orden"]').value = adicional.orden;
                    form.querySelector('[name="descripcion"]').value = adicional.descripcion || '';
                    form.querySelector('[name="precio_90_dias"]').value = adicional.precio_90_dias || '';
                    form.querySelector('[name="precio_160_dias"]').value = adicional.precio_160_dias || '';
                    form.querySelector('[name="precio_270_dias"]').value = adicional.precio_270_dias || '';
                    form.querySelector('[name="es_titulo"]').checked = adicional.es_titulo == 1;
                    
                    // Cambiar el botón
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="bi bi-save"></i> Actualizar Adicional';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Activar la pestaña de adicionales
                    document.getElementById('v-pills-adicionales-tab').click();
                    
                    // Actualizar estado del checkbox "Es título"
                    actualizarEstadoEsTitulo('form-adicional', adicional.es_titulo == 1);
                    
                    // Scroll al formulario
                    form.scrollIntoView({ behavior: 'smooth' });
                    
                    mostrarMensaje('message-adicionales', 'Datos cargados para edición', 'success');
                } else {
                    mostrarMensaje('message-adicionales', 'Error al cargar datos: ' + data.error, 'error');
                }
            });
        }

        function eliminarAdicional(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este adicional?')) {
                fetch(`api_gestionar_datos.php?action=eliminar&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-adicionales', 'Adicional eliminado exitosamente', 'success');
                        cargarAdicionales();
                    } else {
                        mostrarMensaje('message-adicionales', 'Error al eliminar: ' + data.error, 'error');
                    }
                });
            }
        }

        function editarDescuento(id) {
            fetch(`api_gestionar_datos.php?action=obtener_descuento&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const descuento = data.data;
                    editandoId = id;
                    tipoEditando = 'descuento';
                    
                    // Llenar el formulario
                    const form = document.getElementById('form-descuento');
                    form.querySelector('[name="nombre"]').value = descuento.nombre;
                    form.querySelector('[name="orden"]').value = descuento.orden;
                    form.querySelector('[name="descripcion"]').value = descuento.descripcion || '';
                    form.querySelector('[name="descuento"]').value = descuento.descuento || '';
                    
                    // Cambiar el botón
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="bi bi-save"></i> Actualizar Descuento';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Activar la pestaña de descuentos
                    document.getElementById('v-pills-descuentos-tab').click();
                    
                    // Actualizar estado del checkbox "Es título"
                    actualizarEstadoEsTitulo('form-descuento', descuento.es_titulo == 1);
                    
                    // Scroll al formulario
                    form.scrollIntoView({ behavior: 'smooth' });
                    
                    mostrarMensaje('message-descuentos', 'Datos cargados para edición', 'success');
                } else {
                    mostrarMensaje('message-descuentos', 'Error al cargar datos: ' + data.error, 'error');
                }
            });
        }

        function eliminarDescuento(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este descuento?')) {
                fetch(`api_gestionar_datos.php?action=eliminar&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-descuentos', 'Descuento eliminado exitosamente', 'success');
                        cargarDescuentos();
                    } else {
                        mostrarMensaje('message-descuentos', 'Error al eliminar: ' + data.error, 'error');
                    }
                });
            }
        }

        function editarCategoria(id) {
            fetch(`api_gestionar_datos.php?action=obtener_categoria&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categoria = data.data;
                    editandoId = id;
                    tipoEditando = 'categoria';
                    
                    // Llenar el formulario
                    const form = document.getElementById('form-categoria');
                    form.querySelector('[name="nombre"]').value = categoria.nombre;
                    form.querySelector('[name="orden"]').value = categoria.orden;
                    form.querySelector('[name="descripcion"]').value = categoria.descripcion || '';
                    
                    // Cambiar el botón
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<i class="bi bi-save"></i> Actualizar Categoría';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Activar la pestaña de categorías
                    document.getElementById('v-pills-categorias-tab').click();
                    
                    // Scroll al formulario
                    form.scrollIntoView({ behavior: 'smooth' });
                    
                    mostrarMensaje('message-categorias', 'Datos cargados para edición', 'success');
                } else {
                    mostrarMensaje('message-categorias', 'Error al cargar datos: ' + data.error, 'error');
                }
            });
        }

        function eliminarCategoria(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
                fetch(`api_gestionar_datos.php?action=eliminar_categoria&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-categorias', 'Categoría eliminada exitosamente', 'success');
                        cargarCategorias();
                    } else {
                        mostrarMensaje('message-categorias', 'Error al eliminar: ' + data.error, 'error');
                    }
                });
            }
        }

        // Manejadores de formularios
        document.addEventListener('DOMContentLoaded', function() {
            // Formulario de ascensores
            document.getElementById('form-ascensor').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                if (editandoId && tipoEditando === 'ascensor') {
                    formData.append('action', 'actualizar_ascensor');
                    formData.append('id', editandoId);
                } else {
                    formData.append('action', 'crear_ascensor');
                }
                
                fetch('api_gestionar_datos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-ascensores', data.message, 'success');
                        cargarAscensores();
                        limpiarFormulario('form-ascensor');
                    } else {
                        mostrarMensaje('message-ascensores', 'Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    mostrarMensaje('message-ascensores', 'Error de conexión', 'error');
                });
            });

            // Formulario de adicionales
            document.getElementById('form-adicional').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                if (editandoId && tipoEditando === 'adicional') {
                    formData.append('action', 'actualizar_adicional');
                    formData.append('id', editandoId);
                } else {
                    formData.append('action', 'crear_adicional');
                }
                
                fetch('api_gestionar_datos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-adicionales', data.message, 'success');
                        cargarAdicionales();
                        limpiarFormulario('form-adicional');
                    } else {
                        mostrarMensaje('message-adicionales', 'Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    mostrarMensaje('message-adicionales', 'Error de conexión', 'error');
                });
            });

            // Formulario de descuentos
            document.getElementById('form-descuento').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                if (editandoId && tipoEditando === 'descuento') {
                    formData.append('action', 'actualizar_descuento');
                    formData.append('id', editandoId);
                } else {
                    formData.append('action', 'crear_descuento');
                }
                
                fetch('api_gestionar_datos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-descuentos', data.message, 'success');
                        cargarDescuentos();
                        limpiarFormulario('form-descuento');
                    } else {
                        mostrarMensaje('message-descuentos', 'Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    mostrarMensaje('message-descuentos', 'Error de conexión', 'error');
                });
            });

            // Formulario de categorías
            document.getElementById('form-categoria').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                if (editandoId && tipoEditando === 'categoria') {
                    formData.append('action', 'actualizar_categoria');
                    formData.append('id', editandoId);
                } else {
                    formData.append('action', 'crear_categoria');
                }
                
                fetch('api_gestionar_datos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('message-categorias', data.message, 'success');
                        cargarCategorias();
                        limpiarFormulario('form-categoria');
                    } else {
                        mostrarMensaje('message-categorias', 'Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    mostrarMensaje('message-categorias', 'Error de conexión', 'error');
                });
            });

            // Inicializar manejadores de "Es título"
            manejarEsTitulo('es_titulo_ascensor', 'form-ascensor');
            manejarEsTitulo('es_titulo_adicional', 'form-adicional');
            manejarEsTitulo('es_titulo_descuento', 'form-descuento');
        });

        // Función para manejar el checkbox "Es título"
        function manejarEsTitulo(checkboxId, formId) {
            const checkbox = document.getElementById(checkboxId);
            const form = document.getElementById(formId);
            
            if (!checkbox || !form) {
                console.error('No se encontró checkbox o formulario:', checkboxId, formId);
                return;
            }
            
            const precioInputs = form.querySelectorAll('input[name*="precio"]');
            
            function togglePrecios() {
                const isChecked = checkbox.checked;
                precioInputs.forEach(input => {
                    if (isChecked) {
                        input.value = '';
                        input.disabled = true;
                        input.style.backgroundColor = '#f8f9fa';
                        input.style.color = '#6c757d';
                    } else {
                        input.disabled = false;
                        input.style.backgroundColor = '';
                        input.style.color = '';
                    }
                });
            }
            
            // Remover listener anterior si existe
            checkbox.removeEventListener('change', togglePrecios);
            // Agregar nuevo listener
            checkbox.addEventListener('change', togglePrecios);
            // Ejecutar al cargar
            togglePrecios();
        }

        // Función para actualizar el estado del checkbox al editar
        function actualizarEstadoEsTitulo(formId, esTitulo) {
            let checkboxId;
            if (formId === 'form-ascensor') {
                checkboxId = 'es_titulo_ascensor';
            } else if (formId === 'form-adicional') {
                checkboxId = 'es_titulo_adicional';
            } else if (formId === 'form-descuento') {
                checkboxId = 'es_titulo_descuento';
            }
            
            if (checkboxId) {
                const checkbox = document.getElementById(checkboxId);
                if (checkbox) {
                    checkbox.checked = esTitulo;
                    // Reinicializar el manejador
                    manejarEsTitulo(checkboxId, formId);
                }
            }
        }
    </script>
</body>
</html> 