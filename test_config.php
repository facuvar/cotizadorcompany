<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Test de Configuración - <?php echo ENVIRONMENT; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: <?php echo ENVIRONMENT === 'railway' ? 'linear-gradient(135deg, #2c3e50, #3498db)' : 'linear-gradient(135deg, #27ae60, #2ecc71)'; ?>;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: <?php echo ENVIRONMENT === 'railway' ? '#3498db' : '#27ae60'; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            font-weight: 600;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Test de Configuración</h1>
            <p>Entorno: <?php echo ENVIRONMENT === 'railway' ? '🚂 Railway (Producción)' : '🏠 Local (Desarrollo)'; ?></p>
        </div>
        
        <div class="content">
            <!-- Información del Entorno -->
            <div class="info-box">
                <h3>📋 Información del Entorno</h3>
                <?php $envInfo = getEnvironmentInfo(); ?>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Entorno</td><td><strong><?php echo $envInfo['environment']; ?></strong></td></tr>
                    <tr><td>Host de BD</td><td><?php echo $envInfo['host']; ?></td></tr>
                    <tr><td>Base de datos</td><td><?php echo $envInfo['database']; ?></td></tr>
                    <tr><td>Puerto</td><td><?php echo $envInfo['port']; ?></td></tr>
                    <tr><td>Debug activo</td><td><?php echo $envInfo['debug'] ? '✅ Sí' : '❌ No'; ?></td></tr>
                    <tr><td>URL base</td><td><?php echo $envInfo['base_url']; ?></td></tr>
                </table>
            </div>
            
            <!-- Test de Conexión -->
            <div class="info-box">
                <h3>🔌 Test de Conexión</h3>
                <?php if (testConnection()): ?>
                    <div class="success">
                        ✅ <strong>Conexión exitosa</strong> - La base de datos está funcionando correctamente
                    </div>
                <?php else: ?>
                    <div class="error">
                        ❌ <strong>Error de conexión</strong> - No se pudo conectar a la base de datos
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Estadísticas de la Base de Datos -->
            <div class="info-box">
                <h3>📊 Estadísticas de la Base de Datos</h3>
                <?php 
                $stats = getDatabaseStats();
                if (isset($stats['error'])): ?>
                    <div class="error">
                        ❌ Error obteniendo estadísticas: <?php echo $stats['error']; ?>
                    </div>
                <?php else: ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['categorias']; ?></div>
                            <div>Categorías</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['opciones']; ?></div>
                            <div>Opciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['presupuestos']; ?></div>
                            <div>Presupuestos</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Información Técnica -->
            <div class="info-box">
                <h3>⚙️ Información Técnica</h3>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Versión PHP</td><td><?php echo PHP_VERSION; ?></td></tr>
                    <tr><td>Aplicación</td><td><?php echo APP_NAME . ' v' . APP_VERSION; ?></td></tr>
                    <tr><td>Zona horaria</td><td><?php echo date_default_timezone_get(); ?></td></tr>
                    <tr><td>Moneda</td><td><?php echo CURRENCY . ' (' . CURRENCY_SYMBOL . ')'; ?></td></tr>
                    <tr><td>Tamaño máximo upload</td><td><?php echo UPLOAD_MAX_SIZE; ?></td></tr>
                    <tr><td>Duración sesión</td><td><?php echo SESSION_LIFETIME; ?> segundos</td></tr>
                </table>
            </div>
            
            <!-- Variables de Entorno (solo Railway) -->
            <?php if (ENVIRONMENT === 'railway'): ?>
            <div class="info-box">
                <h3>🌐 Variables de Entorno Railway</h3>
                <table>
                    <tr><th>Variable</th><th>Valor</th></tr>
                    <tr><td>DB_HOST</td><td><?php echo $_ENV['DB_HOST'] ?? 'No definida'; ?></td></tr>
                    <tr><td>DB_USER</td><td><?php echo $_ENV['DB_USER'] ?? 'No definida'; ?></td></tr>
                    <tr><td>DB_NAME</td><td><?php echo $_ENV['DB_NAME'] ?? 'No definida'; ?></td></tr>
                    <tr><td>DB_PORT</td><td><?php echo $_ENV['DB_PORT'] ?? 'No definida'; ?></td></tr>
                    <tr><td>RAILWAY_ENVIRONMENT</td><td><?php echo $_ENV['RAILWAY_ENVIRONMENT'] ?? 'No definida'; ?></td></tr>
                </table>
            </div>
            <?php endif; ?>
            
            <!-- Enlaces Rápidos -->
            <div class="info-box">
                <h3>🔗 Enlaces Rápidos</h3>
                <a href="cotizador.php" class="btn">💼 Cotizador</a>
                <a href="admin/" class="btn">⚙️ Admin</a>
                <a href="upload_sql_railway.php" class="btn">📁 Upload SQL</a>
                <a href="diagnostico_conexion.php" class="btn">🔍 Diagnóstico</a>
                <?php if (ENVIRONMENT === 'local'): ?>
                <a href="http://localhost/phpmyadmin" class="btn" target="_blank">🗄️ phpMyAdmin</a>
                <?php endif; ?>
            </div>
            
            <!-- Información de Configuración -->
            <div class="info-box">
                <h3>📝 Notas de Configuración</h3>
                <p><strong>✅ Configuración automática:</strong> Este archivo detecta automáticamente si está ejecutándose en Railway o en local.</p>
                <p><strong>🔧 Railway:</strong> Usa variables de entorno para la conexión a la base de datos.</p>
                <p><strong>🏠 Local:</strong> Usa configuración estándar de XAMPP (localhost, root, sin contraseña).</p>
                <p><strong>🔄 Sincronización:</strong> El mismo código funciona en ambos entornos sin modificaciones.</p>
            </div>
        </div>
    </div>
</body>
</html> 