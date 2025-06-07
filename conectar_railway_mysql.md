# 🔧 Conectar a MySQL en Railway - Guía Alternativa

Si no aparece la opción "Query" en Railway, aquí tienes varias alternativas:

## 🎯 Método 1: Variables de Entorno de Railway

### 1.1 Obtener las credenciales:
1. En tu proyecto Railway, haz clic en **MySQL**
2. Ve a la pestaña **"Variables"** o **"Environment"**
3. Copia estas variables:

```
MYSQLHOST = [tu_host]
MYSQLUSER = [tu_usuario]  
MYSQLPASSWORD = [tu_password]
MYSQLDATABASE = [tu_database]
MYSQLPORT = [tu_puerto]
```

### 1.2 Usar un cliente MySQL externo:
- **MySQL Workbench** (Recomendado)
- **phpMyAdmin** online
- **DBeaver** (Gratuito)
- **HeidiSQL** (Windows)

## 🖥️ Método 2: MySQL Workbench (Recomendado)

### 2.1 Descargar e instalar:
1. Ve a: https://dev.mysql.com/downloads/workbench/
2. Descarga e instala MySQL Workbench

### 2.2 Conectar:
1. Abre MySQL Workbench
2. Haz clic en **"+"** para nueva conexión
3. Configura:
   - **Connection Name**: Railway DB
   - **Hostname**: [MYSQLHOST de Railway]
   - **Port**: [MYSQLPORT de Railway]
   - **Username**: [MYSQLUSER de Railway]
   - **Password**: [MYSQLPASSWORD de Railway]
   - **Default Schema**: [MYSQLDATABASE de Railway]

### 2.3 Ejecutar scripts:
1. Conecta a la base de datos
2. Abre una nueva query tab
3. Copia y pega los scripts SQL
4. Ejecuta con **Ctrl+Enter**

## 🌐 Método 3: phpMyAdmin Online

### 3.1 Usar phpMyAdmin web:
1. Ve a: https://www.phpmyadmin.co/
2. O busca "phpMyAdmin online" en Google

### 3.2 Conectar con credenciales Railway:
- **Server**: [MYSQLHOST]
- **Username**: [MYSQLUSER]
- **Password**: [MYSQLPASSWORD]
- **Database**: [MYSQLDATABASE]

## 🔧 Método 4: Terminal/Línea de Comandos

### 4.1 Si tienes MySQL instalado localmente:
```bash
mysql -h [MYSQLHOST] -P [MYSQLPORT] -u [MYSQLUSER] -p[MYSQLPASSWORD] [MYSQLDATABASE]
```

### 4.2 Ejecutar scripts:
```sql
-- Una vez conectado, ejecuta los scripts
SOURCE /ruta/a/tu/script.sql;
```

## 🚀 Método 5: Script PHP Automático

### 5.1 Crear script de instalación automática:
Crea un archivo `install_db.php` en tu proyecto:

```php
<?php
// Script para instalar automáticamente la base de datos en Railway

// Detectar Railway
$isRailway = isset($_ENV['MYSQLHOST']);

if (!$isRailway) {
    die("Este script solo funciona en Railway");
}

// Credenciales automáticas de Railway
$host = $_ENV['MYSQLHOST'];
$user = $_ENV['MYSQLUSER'];
$pass = $_ENV['MYSQLPASSWORD'];
$name = $_ENV['MYSQLDATABASE'];
$port = $_ENV['MYSQLPORT'] ?? 3306;

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", 
        $user, 
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h1>🚀 Instalación Automática de Base de Datos</h1>";
    
    // Script 1: Crear tabla categorias
    $sql1 = "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        orden INT DEFAULT 0,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql1);
    echo "<p>✅ Tabla 'categorias' creada</p>";
    
    // Script 2: Crear tabla opciones
    $sql2 = "CREATE TABLE IF NOT EXISTS opciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        categoria_id INT NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2) DEFAULT 0.00,
        precio_90_dias DECIMAL(10,2) DEFAULT 0.00,
        precio_160_dias DECIMAL(10,2) DEFAULT 0.00,
        precio_270_dias DECIMAL(10,2) DEFAULT 0.00,
        descuento DECIMAL(5,2) DEFAULT 0.00,
        orden INT DEFAULT 0,
        es_titulo TINYINT(1) DEFAULT 0,
        activo TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "<p>✅ Tabla 'opciones' creada</p>";
    
    // Script 3: Crear tabla configuracion
    $sql3 = "CREATE TABLE IF NOT EXISTS configuracion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(100) NOT NULL UNIQUE,
        valor TEXT,
        descripcion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql3);
    echo "<p>✅ Tabla 'configuracion' creada</p>";
    
    // Script 4: Insertar datos básicos
    $sql4 = "INSERT IGNORE INTO categorias (nombre, descripcion, orden, activo) VALUES
        ('ASCENSORES', 'Equipos de ascensores electromecánicos', 1, 1),
        ('ADICIONALES', 'Opciones adicionales para ascensores', 2, 1),
        ('DESCUENTOS', 'Formas de pago y descuentos', 3, 1)";
    
    $pdo->exec($sql4);
    echo "<p>✅ Categorías básicas insertadas</p>";
    
    // Script 5: Configuración básica
    $sql5 = "INSERT IGNORE INTO configuracion (clave, valor, descripcion) VALUES
        ('empresa_nombre', 'Tu Empresa', 'Nombre de la empresa'),
        ('empresa_telefono', '+54 11 1234-5678', 'Teléfono de contacto'),
        ('empresa_email', 'info@tuempresa.com', 'Email de contacto'),
        ('moneda_simbolo', '$', 'Símbolo de la moneda'),
        ('iva_porcentaje', '21', 'Porcentaje de IVA'),
        ('descuento_maximo', '15', 'Descuento máximo permitido')";
    
    $pdo->exec($sql5);
    echo "<p>✅ Configuración básica insertada</p>";
    
    echo "<h2>🎉 ¡Base de datos instalada correctamente!</h2>";
    echo "<p><a href='cotizador.php'>Ir al Cotizador</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
```

### 5.2 Usar el script automático:
1. Sube este archivo a tu proyecto Railway
2. Ve a: `https://tu-app.railway.app/install_db.php`
3. El script creará automáticamente toda la base de datos

## 📋 Resumen de Opciones

| Método | Dificultad | Recomendado |
|--------|------------|-------------|
| MySQL Workbench | Fácil | ⭐⭐⭐⭐⭐ |
| Script PHP Automático | Muy Fácil | ⭐⭐⭐⭐⭐ |
| phpMyAdmin Online | Fácil | ⭐⭐⭐⭐ |
| Terminal/CLI | Medio | ⭐⭐⭐ |

## 🎯 Recomendación

**Usa el Script PHP Automático** - Es la forma más fácil:
1. Crea el archivo `install_db.php` 
2. Súbelo a Railway
3. Visita la URL del script
4. ¡Listo!

¿Cuál método prefieres que usemos? 