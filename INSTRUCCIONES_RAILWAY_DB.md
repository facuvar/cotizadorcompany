# 🚀 Configurar Base de Datos en Railway

Guía paso a paso para crear y configurar la base de datos MySQL en Railway con todos tus datos.

## 📋 Paso 1: Agregar MySQL a Railway

### 1.1 En tu proyecto Railway:
1. Ve a [Railway Dashboard](https://railway.app/dashboard)
2. Abre tu proyecto existente
3. Haz clic en **"+ New"**
4. Selecciona **"Database"**
5. Elige **"Add MySQL"**

### 1.2 Railway creará automáticamente:
- ✅ Base de datos MySQL
- ✅ Variables de entorno automáticas:
  - `MYSQLHOST`
  - `MYSQLUSER`
  - `MYSQLPASSWORD`
  - `MYSQLDATABASE`
  - `MYSQLPORT`

## 🗄️ Paso 2: Crear las Tablas

### 2.1 Acceder a la base de datos:
1. En Railway, haz clic en tu servicio **MySQL**
2. Ve a la pestaña **"Data"**
3. Haz clic en **"Query"** para abrir el editor SQL

### 2.2 Ejecutar script de estructura:
Copia y ejecuta este SQL:

```sql
-- ==========================================
-- CONFIGURACIÓN COMPLETA PARA RAILWAY
-- ==========================================

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

## 📊 Paso 3: Importar Datos Básicos

### 3.1 Datos mínimos para empezar:
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

## 🔄 Paso 4: Importar Datos Completos (Opcional)

### 4.1 Para importar todos tus datos locales:

1. **En tu entorno local**, abre:
   ```
   http://localhost/company-presupuestos-online-2/export_railway_data.html
   ```

2. **Copia todos los scripts SQL** que aparecen en la página

3. **Ejecuta cada script en orden** en Railway:
   - Script de categorías
   - Scripts de opciones (puede haber varias partes)
   - Script de configuración

### 4.2 Verificar importación:
```sql
-- Verificar que todo se importó correctamente
SELECT COUNT(*) as total_categorias FROM categorias;
SELECT COUNT(*) as total_opciones FROM opciones;
SELECT COUNT(*) as total_configuracion FROM configuracion;
```

## 🔍 Paso 5: Verificar Configuración

### 5.1 Usar el script de verificación:
1. Una vez desplegada tu aplicación en Railway
2. Ve a: `https://tu-app.railway.app/setup_railway_db.php`
3. Verifica que aparezcan todos los ✅ verdes

### 5.2 Probar el cotizador:
1. Ve a: `https://tu-app.railway.app/cotizador.php`
2. Verifica que:
   - Se cargan las categorías
   - Se muestran los productos
   - Los cálculos funcionan
   - El filtrado inteligente opera

## 🎯 Paso 6: Configuración Final

### 6.1 Variables de entorno automáticas:
Railway ya configuró automáticamente:
- ✅ `MYSQLHOST` - Host de la base de datos
- ✅ `MYSQLUSER` - Usuario de la base de datos  
- ✅ `MYSQLPASSWORD` - Contraseña
- ✅ `MYSQLDATABASE` - Nombre de la base de datos
- ✅ `MYSQLPORT` - Puerto (3306)

### 6.2 Tu aplicación se conectará automáticamente:
El archivo `sistema/config.php` ya está configurado para detectar Railway y usar estas variables automáticamente.

## 🛠️ Solución de Problemas

### Error: "Table doesn't exist"
```sql
-- Verificar que las tablas existen
SHOW TABLES;
```

### Error: "Connection refused"
- Verifica que MySQL esté ejecutándose en Railway
- Revisa las variables de entorno en Railway

### Datos no aparecen
```sql
-- Verificar datos en las tablas
SELECT * FROM categorias LIMIT 5;
SELECT * FROM opciones LIMIT 5;
```

### Error de permisos
- Railway maneja automáticamente los permisos
- Si hay problemas, recrea el servicio MySQL

## 📋 Lista de Verificación

- [ ] ✅ MySQL agregado a Railway
- [ ] ✅ Variables de entorno automáticas creadas
- [ ] ✅ Script de estructura ejecutado
- [ ] ✅ Datos básicos insertados
- [ ] ✅ Datos completos importados (opcional)
- [ ] ✅ Aplicación desplegada
- [ ] ✅ Verificación con `setup_railway_db.php`
- [ ] ✅ Cotizador funcionando correctamente

## 🎉 ¡Listo!

Una vez completados estos pasos:

### URLs de tu aplicación:
- **Cotizador:** `https://tu-app.railway.app/cotizador.php`
- **Verificación:** `https://tu-app.railway.app/setup_railway_db.php`
- **Admin:** `https://tu-app.railway.app/admin/`

### Características funcionando:
- ✅ Detección automática de Railway
- ✅ Conexión automática a MySQL
- ✅ Filtrado inteligente de adicionales
- ✅ Cálculos dinámicos por plazo
- ✅ Generación de PDFs
- ✅ Panel de administración

## 📞 Soporte

Si tienes problemas:
1. Revisa los logs en Railway
2. Usa `setup_railway_db.php` para diagnóstico
3. Verifica que MySQL esté ejecutándose
4. Consulta la documentación en GitHub

---

**¡Tu sistema está listo para funcionar perfectamente en Railway! 🚀** 