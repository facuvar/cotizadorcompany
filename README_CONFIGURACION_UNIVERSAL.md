# 🔧 Configuración Universal - Railway y Local

## 📋 Descripción

Este sistema permite que el mismo código funcione tanto en **Railway (producción)** como en **Local (desarrollo)** sin necesidad de modificar archivos o configuraciones manualmente.

## ✅ Archivos Creados

- `config.php` - Configuración universal que detecta automáticamente el entorno
- `test_config.php` - Página de prueba para verificar la configuración
- `ejemplo_actualizacion.php` - Ejemplos de cómo actualizar archivos existentes

## 🚀 Cómo Funciona

### Detección Automática de Entorno

El sistema detecta automáticamente si está ejecutándose en Railway o Local mediante:

```php
$isRailway = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
             isset($_SERVER['RAILWAY_ENVIRONMENT']) ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'railway.app') !== false ||
             strpos($_SERVER['HTTP_HOST'] ?? '', 'up.railway.app') !== false;
```

### Configuración por Entorno

**🚂 Railway (Producción):**
- Host: `mysql.railway.internal` (desde variables de entorno)
- Usuario: `root` (desde variables de entorno)
- Contraseña: Desde `$_ENV['DB_PASS']`
- Base de datos: `railway` (desde variables de entorno)
- Debug: Desactivado
- Errores: Ocultos

**🏠 Local (Desarrollo):**
- Host: `localhost`
- Usuario: `root`
- Contraseña: `` (vacía)
- Base de datos: `cotizador_company`
- Debug: Activado
- Errores: Visibles

## 🔌 Uso de la Configuración

### 1. Incluir la Configuración

```php
require_once 'config.php';
```

### 2. Obtener Conexión PDO (Recomendado)

```php
try {
    $pdo = getDBConnection();
    
    // Usar la conexión
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE activo = ?");
    $stmt->execute([1]);
    $categorias = $stmt->fetchAll();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 3. Obtener Conexión MySQLi (Alternativa)

```php
try {
    $mysqli = getMySQLiConnection();
    
    // Usar la conexión
    $result = $mysqli->query("SELECT * FROM opciones");
    $opciones = $result->fetch_all(MYSQLI_ASSOC);
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## 🛠️ Funciones Disponibles

### Conexión
- `getDBConnection()` - Obtiene conexión PDO
- `getMySQLiConnection()` - Obtiene conexión MySQLi
- `testConnection()` - Verifica si la conexión funciona

### Información
- `getEnvironmentInfo()` - Información del entorno actual
- `getDatabaseStats()` - Estadísticas de la base de datos

### Constantes Disponibles
- `ENVIRONMENT` - 'railway' o 'local'
- `DEBUG_MODE` - true/false
- `BASE_URL` - URL base de la aplicación
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT` - Credenciales de BD
- `APP_NAME`, `APP_VERSION` - Información de la aplicación
- `CURRENCY`, `CURRENCY_SYMBOL` - Configuración de moneda

## 📝 Actualizar Archivos Existentes

### Paso 1: Agregar Configuración

```php
// Al inicio del archivo
require_once 'config.php';
```

### Paso 2: Reemplazar Conexiones Directas

**❌ ANTES:**
```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'cotizador_company';

$conexion = new mysqli($host, $user, $pass, $dbname);
```

**✅ DESPUÉS:**
```php
require_once 'config.php';
$pdo = getDBConnection();
```

### Paso 3: Actualizar Consultas

**❌ ANTES:**
```php
$result = $conexion->query("SELECT * FROM tabla");
$data = $result->fetch_all(MYSQLI_ASSOC);
```

**✅ DESPUÉS:**
```php
$stmt = $pdo->query("SELECT * FROM tabla");
$data = $stmt->fetchAll();
```

## 🧪 Probar la Configuración

### Local
1. Abrir: `http://localhost/company-presupuestos-online-2/test_config.php`
2. Verificar que muestre "🏠 Local (Desarrollo)"
3. Confirmar conexión exitosa a la base de datos local

### Railway
1. Hacer push a GitHub
2. Esperar deploy automático en Railway
3. Abrir: `https://cotizadorcompany-production.up.railway.app/test_config.php`
4. Verificar que muestre "🚂 Railway (Producción)"
5. Confirmar conexión exitosa a la base de datos Railway

## 📁 Archivos que Necesitan Actualización

### Archivos Principales
1. `cotizador.php` - Archivo principal del cotizador
2. `admin/index.php` - Panel de administración
3. `api/opciones.php` - API para obtener opciones
4. `api/guardar_presupuesto.php` - API para guardar presupuestos
5. `upload_sql_railway.php` - Gestor de uploads

### Buscar y Reemplazar
Buscar en archivos:
- `mysqli_connect()`
- `new mysqli()`
- `new PDO()`
- Credenciales hardcodeadas
- Detección manual de entorno

## 🔍 Diagnóstico de Problemas

### Error de Conexión Local
1. Verificar que XAMPP esté ejecutándose
2. Verificar que la base de datos `cotizador_company` exista
3. Verificar credenciales en phpMyAdmin

### Error de Conexión Railway
1. Verificar variables de entorno en Railway dashboard
2. Verificar que la base de datos esté configurada
3. Revisar logs de Railway

### Variables de Entorno Railway
```
DB_HOST=mysql.railway.internal
DB_USER=root
DB_PASS=DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd
DB_NAME=railway
DB_PORT=3306
```

## 🚀 Deploy a Railway

### Automático (Recomendado)
1. Hacer commit de los cambios
2. Push a GitHub: `git push origin master`
3. Railway detecta automáticamente y hace deploy

### Manual (CLI)
```bash
railway login
railway link
railway up --detach
```

## 📊 Monitoreo

### URLs de Prueba
- **Local:** `http://localhost/company-presupuestos-online-2/test_config.php`
- **Railway:** `https://cotizadorcompany-production.up.railway.app/test_config.php`

### Verificaciones
- ✅ Detección correcta de entorno
- ✅ Conexión exitosa a base de datos
- ✅ Estadísticas de tablas
- ✅ Variables de entorno (Railway)

## 🔧 Mantenimiento

### Agregar Nueva Configuración
Editar `config.php` y agregar en la sección correspondiente:

```php
// Configuración específica por entorno
if (ENVIRONMENT === 'railway') {
    define('NUEVA_CONFIG', 'valor_railway');
} else {
    define('NUEVA_CONFIG', 'valor_local');
}
```

### Agregar Nueva Función
```php
function nuevaFuncion() {
    $pdo = getDBConnection();
    // Lógica de la función
    return $resultado;
}
```

## ✅ Ventajas del Sistema

1. **🔄 Sincronización automática** - El mismo código funciona en ambos entornos
2. **🛡️ Seguridad** - Credenciales desde variables de entorno en producción
3. **🐛 Debug inteligente** - Errores visibles solo en desarrollo
4. **📊 Monitoreo** - Funciones de diagnóstico integradas
5. **🚀 Deploy simple** - Sin configuración manual en cada deploy
6. **🔧 Mantenimiento fácil** - Un solo archivo de configuración

## 🆘 Soporte

Si tienes problemas:
1. Revisar `test_config.php` para diagnóstico
2. Verificar logs de error (local: XAMPP, Railway: dashboard)
3. Confirmar variables de entorno en Railway
4. Verificar que las tablas existan en ambas bases de datos

---

**🎉 ¡Configuración universal lista para usar!**

El sistema detecta automáticamente el entorno y usa las credenciales correctas. No necesitas modificar código para cambiar entre local y Railway. 