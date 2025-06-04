# 🚀 Configuración del Sistema en Replit

Esta guía te ayudará a configurar tu sistema de presupuestos en Replit desde cero, replicando exactamente los datos de tu base local.

## 📋 Requisitos Previos

- Cuenta en Replit
- Acceso a una base de datos MySQL (puede ser externa o usar un servicio como PlanetScale, Railway, etc.)
- Los archivos del proyecto ya subidos a GitHub

## 🔧 Paso 1: Configurar el Repl

### 1.1 Crear nuevo Repl
1. Ve a [Replit](https://replit.com)
2. Haz clic en "Create Repl"
3. Selecciona "Import from GitHub"
4. Pega la URL de tu repositorio: `https://github.com/facuvar/cotizadorcompany`
5. Nombra tu Repl (ej: "cotizador-company")
6. Haz clic en "Import from GitHub"

### 1.2 Configurar Variables de Entorno
1. En tu Repl, ve a la pestaña "Secrets" (🔒) en el panel izquierdo
2. Agrega las siguientes variables:

```
DB_HOST = tu_host_mysql
DB_USER = tu_usuario_mysql  
DB_PASS = tu_password_mysql
DB_NAME = company_presupuestos
DB_PORT = 3306
```

**Ejemplo con PlanetScale:**
```
DB_HOST = aws.connect.psdb.cloud
DB_USER = tu_usuario_planetscale
DB_PASS = tu_password_planetscale
DB_NAME = company_presupuestos
DB_PORT = 3306
```

## 🗄️ Paso 2: Configurar la Base de Datos

### 2.1 Ejecutar Script de Configuración
1. En tu Repl, abre el archivo `setup_replit.php`
2. Ejecuta el archivo para verificar la configuración
3. Copia el script SQL que aparece en la página

### 2.2 Crear la Estructura de Base de Datos
Ejecuta este SQL en tu base de datos MySQL:

```sql
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS company_presupuestos;
USE company_presupuestos;

-- Tabla categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla opciones
CREATE TABLE IF NOT EXISTS opciones (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla configuracion
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 📊 Paso 3: Importar Datos

### 3.1 Obtener Datos de tu Base Local
1. En tu entorno local, abre: `http://localhost/company-presupuestos-online-2/export_for_replit.php`
2. Copia todos los scripts SQL que aparecen en la página
3. Ejecuta cada script en orden en tu base de datos MySQL

### 3.2 Scripts de Datos Básicos
Si no tienes acceso a tu base local, puedes usar estos datos básicos:

```sql
-- Categorías básicas
INSERT INTO categorias (nombre, descripcion, orden, activo) VALUES
('ASCENSORES', 'Equipos de ascensores electromecánicos', 1, 1),
('ADICIONALES', 'Opciones adicionales para ascensores', 2, 1),
('DESCUENTOS', 'Formas de pago y descuentos', 3, 1);

-- Configuración básica
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('empresa_nombre', 'Tu Empresa', 'Nombre de la empresa'),
('empresa_telefono', '+54 11 1234-5678', 'Teléfono de contacto'),
('empresa_email', 'info@tuempresa.com', 'Email de contacto'),
('moneda_simbolo', '$', 'Símbolo de la moneda'),
('iva_porcentaje', '21', 'Porcentaje de IVA'),
('descuento_maximo', '15', 'Descuento máximo permitido');
```

## 🔍 Paso 4: Verificar la Configuración

### 4.1 Probar la Conexión
1. En tu Repl, ejecuta `setup_replit.php`
2. Verifica que aparezcan mensajes de éxito:
   - ✅ Entorno Replit detectado
   - ✅ Variables de entorno configuradas
   - ✅ Conexión a MySQL exitosa
   - ✅ Base de datos encontrada
   - ✅ Tablas encontradas

### 4.2 Probar el Cotizador
1. Ejecuta tu Repl (botón "Run")
2. Abre `cotizador.php` en el navegador
3. Verifica que:
   - Se cargan las categorías
   - Se muestran los productos
   - Los cálculos funcionan correctamente
   - El filtrado inteligente funciona

## 🎯 Paso 5: Configuración Final

### 5.1 Configurar el Archivo Principal
Asegúrate de que tu archivo `index.php` o archivo principal redirija al cotizador:

```php
<?php
// Redirigir al cotizador principal
header('Location: cotizador.php');
exit;
?>
```

### 5.2 Configurar el Dominio (Opcional)
1. En Replit, ve a la pestaña "Webview"
2. Copia la URL de tu aplicación
3. Opcionalmente, configura un dominio personalizado

## 🛠️ Solución de Problemas

### Error de Conexión a Base de Datos
- Verifica que las variables de entorno estén correctamente configuradas
- Asegúrate de que tu base de datos MySQL esté accesible desde internet
- Verifica que el usuario tenga permisos suficientes

### Tablas No Encontradas
- Ejecuta nuevamente los scripts de creación de estructura
- Verifica que estés conectado a la base de datos correcta

### Datos No Aparecen
- Ejecuta los scripts de inserción de datos
- Verifica que las tablas tengan datos: `SELECT COUNT(*) FROM categorias;`

### Errores de PHP
- Verifica que todas las dependencias estén instaladas
- Revisa los logs de error en Replit

## 📋 Lista de Verificación Final

- [ ] ✅ Repl creado e importado desde GitHub
- [ ] ✅ Variables de entorno configuradas
- [ ] ✅ Base de datos MySQL accesible
- [ ] ✅ Estructura de tablas creada
- [ ] ✅ Datos importados correctamente
- [ ] ✅ Conexión verificada con `setup_replit.php`
- [ ] ✅ Cotizador funcionando correctamente
- [ ] ✅ Filtrado inteligente operativo
- [ ] ✅ Cálculos de precios correctos

## 🎉 ¡Listo!

Una vez completados todos los pasos, tu sistema de presupuestos estará funcionando perfectamente en Replit con todos los datos de tu base local.

### URLs Importantes:
- **Cotizador Principal:** `https://tu-repl.replit.dev/cotizador.php`
- **Configuración:** `https://tu-repl.replit.dev/setup_replit.php`
- **Panel Admin:** `https://tu-repl.replit.dev/admin/`

## 📞 Soporte

Si encuentras algún problema:
1. Revisa los logs de error en Replit
2. Verifica la configuración de variables de entorno
3. Asegúrate de que la base de datos esté accesible
4. Consulta la documentación del proyecto en GitHub

---

**¡Tu sistema está listo para funcionar en Replit! 🚀** 