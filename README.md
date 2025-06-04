# 🏢 Sistema de Presupuestos Online - Ascensores

Sistema completo de cotización y presupuestos para ascensores, montacargas y salvaescaleras con funcionalidades avanzadas de filtrado inteligente y cálculo automático.

## 🚀 Características Principales

### ✨ Funcionalidades Avanzadas
- **Filtrado Inteligente**: Los adicionales se muestran automáticamente según el tipo de ascensor seleccionado
- **Cálculos Dinámicos**: Precios que se actualizan en tiempo real con diferentes plazos de entrega
- **Gestión de Adicionales**: Soporte para adicionales que suman o restan del precio total
- **Exportación PDF**: Generación automática de presupuestos en formato PDF
- **Panel de Administración**: Gestión completa de productos, precios y configuraciones

### 💰 Sistema de Precios
- **Múltiples Plazos**: 90, 160 y 270 días de entrega
- **Descuentos Automáticos**: Aplicación de descuentos según configuración
- **Precios Dinámicos**: Actualización automática al cambiar plazos
- **Adicionales Inteligentes**: Algunos adicionales restan dinero del total

### 🎯 Experiencia de Usuario
- **Interface Moderna**: Diseño limpio y responsive
- **Filtrado Automático**: Solo muestra opciones relevantes para cada tipo
- **Feedback Visual**: Colores diferenciados para precios y estados
- **Cálculos Instantáneos**: Totales que se actualizan en tiempo real

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Despliegue**: Railway (Producción), XAMPP (Desarrollo local)
- **PDF**: Generación automática de presupuestos

## 📋 Requisitos

### Para Desarrollo Local
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx
- XAMPP recomendado para Windows

### Para Producción
- Servidor con PHP 7.4+
- Base de datos MySQL
- Soporte para variables de entorno

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone https://github.com/tu-usuario/company-presupuestos-online-2.git
cd company-presupuestos-online-2
```

### 2. Configuración de Base de Datos
1. Crear una base de datos MySQL llamada `company_presupuestos`
2. Importar la estructura desde los archivos SQL incluidos
3. Configurar las credenciales en `sistema/config.php`

### 3. Configuración Local
El sistema detecta automáticamente si está en entorno local o producción:

```php
// Para desarrollo local (XAMPP)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'company_presupuestos';
```

### 4. Configuración para Producción (Railway)
El sistema se configura automáticamente usando variables de entorno:

```bash
DB_HOST=tu_host_mysql
DB_USER=tu_usuario
DB_PASS=tu_password
DB_NAME=tu_base_de_datos
DB_PORT=3306
```

## 📁 Estructura del Proyecto

```
company-presupuestos-online-2/
├── cotizador.php              # Cotizador principal
├── sistema/
│   ├── config.php            # Configuración universal
│   ├── admin/                # Panel de administración
│   └── api/                  # API endpoints
├── presupuestos/             # Generación de PDFs
├── assets/
│   ├── css/                  # Estilos
│   ├── js/                   # JavaScript
│   └── images/               # Imágenes
├── uploads/                  # Archivos subidos
└── README.md
```

## 🔧 Funcionalidades Detalladas

### Filtrado Inteligente de Adicionales
El sistema filtra automáticamente los adicionales según el tipo de ascensor:

- **Electromecánico**: Muestra adicionales específicos para este tipo
- **Gearless**: Adicionales para ascensores sin cuarto de máquinas
- **Hidráulico**: Adicionales específicos para sistemas hidráulicos
- **Montacargas**: Adicionales para transporte de carga
- **Salvaescaleras**: Adicionales para accesibilidad

### Sistema de Precios Dinámicos
```javascript
// Los precios se actualizan automáticamente
function actualizarPreciosPorPlazo(plazo) {
    // Sincroniza todos los selectores de plazo
    // Recalcula totales instantáneamente
    // Mantiene selecciones del usuario
}
```

### Adicionales que Restan
Algunos adicionales están configurados para restar dinero del total:
- Se muestran con precio negativo
- Color diferenciado (naranja)
- Se restan automáticamente del total

## 🧪 Testing y Desarrollo

### Panel de Administración
Accede a `/sistema/admin/` para:
- Gestionar categorías y productos
- Configurar precios por plazo
- Administrar adicionales
- Ver estadísticas de uso

### Verificación de Funcionalidades
El sistema incluye scripts de verificación:
- Conexión a base de datos
- Estructura de tablas
- Integridad de datos
- Funcionalidades de filtrado

## 🚀 Despliegue

### Despliegue Automático
El sistema se configura automáticamente según el entorno:

1. **Desarrollo Local**: Detecta XAMPP y usa configuración local
2. **Railway**: Detecta variables de entorno y se configura automáticamente
3. **Otros Servidores**: Usa variables de entorno estándar

### Variables de Entorno Requeridas
```bash
# Base de datos
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_password
DB_NAME=company_presupuestos
DB_PORT=3306

# Configuración opcional
ADMIN_USER=admin
ADMIN_PASS=tu_password_hash
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu_email
SMTP_PASS=tu_password_email
```

## 📊 Base de Datos

### Tablas Principales
- **categorias**: Tipos de productos y adicionales
- **opciones**: Productos con precios por plazo
- **configuracion**: Parámetros del sistema
- **presupuestos**: Historial de cotizaciones

### Estructura de Precios
```sql
CREATE TABLE opciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT,
    nombre VARCHAR(255),
    precio_90_dias DECIMAL(10,2),
    precio_160_dias DECIMAL(10,2),
    precio_270_dias DECIMAL(10,2),
    descuento DECIMAL(5,2) DEFAULT 0,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1
);
```

## 🎯 Casos de Uso

### 1. Cotización Básica
1. Usuario selecciona tipo de ascensor
2. Sistema filtra adicionales relevantes
3. Usuario selecciona opciones deseadas
4. Cálculo automático del total
5. Generación de PDF del presupuesto

### 2. Gestión de Precios
1. Administrador accede al panel
2. Modifica precios por plazo
3. Configura descuentos
4. Los cambios se reflejan inmediatamente

### 3. Adicionales Especiales
1. Configuración de adicionales que restan
2. Visualización diferenciada
3. Cálculo automático en el total

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

Para soporte técnico o consultas:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo

## 🔄 Actualizaciones

### Versión Actual: 2.0
- ✅ Filtrado inteligente de adicionales
- ✅ Cálculos dinámicos por plazo
- ✅ Adicionales que restan
- ✅ Configuración universal (local/Railway)
- ✅ Panel de administración mejorado
- ✅ Generación de PDFs optimizada

### Próximas Funcionalidades
- 🔄 Integración con Google Sheets
- 🔄 Notificaciones por email
- 🔄 Historial de cotizaciones
- 🔄 Reportes avanzados

---

**Última actualización**: Sistema configurado para Railway con credenciales específicas ✅ 