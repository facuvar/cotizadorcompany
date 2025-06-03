# 🏢 Cotizador Inteligente de Ascensores

Sistema avanzado de cotización para ascensores, montacargas y salvaescaleras con funcionalidades inteligentes de filtrado y cálculo automático.

## 🚀 Características Principales

### ✨ Filtrado Inteligente de Adicionales
- **Filtrado automático**: Los adicionales se muestran según el tipo de ascensor seleccionado
- **Tipos soportados**: Electromecánico, Gearless, Hidráulico, Montacargas, Salvaescaleras
- **Lógica inteligente**: Solo muestra adicionales relevantes para cada tipo de ascensor

### 💰 Cálculos Avanzados
- **Adicionales que restan**: Algunos adicionales específicos restan dinero del total
- **Precios por plazo**: 90, 160 y 270 días de entrega
- **Plazo unificado**: Al cambiar el plazo, todos los productos se actualizan automáticamente
- **Descuentos automáticos**: Aplicación de descuentos según configuración

### 🎯 Experiencia de Usuario Optimizada
- **Interface limpia**: Solo muestra opciones relevantes
- **Feedback visual**: Colores diferenciados para precios negativos
- **Actualización en tiempo real**: Cálculos instantáneos al seleccionar opciones
- **Preservación de selecciones**: Mantiene las opciones seleccionadas al filtrar

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Despliegue**: Railway (Producción), XAMPP (Desarrollo)

## 📋 Requisitos del Sistema

### Desarrollo Local
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx
- XAMPP recomendado para Windows

### Producción (Railway)
- Cuenta en Railway
- Base de datos MySQL configurada
- Variables de entorno configuradas

## 🚀 Instalación y Configuración

### 1. Clonar el Repositorio
   ```bash
git clone https://github.com/tu-usuario/company-presupuestos-online-2.git
cd company-presupuestos-online-2
```

### 2. Configuración Local
1. Importar la base de datos desde `database/company_presupuestos.sql`
2. Configurar `config.php` con tus credenciales locales:
   ```php
<?php
   define('DB_HOST', 'localhost');
define('DB_NAME', 'company_presupuestos');
define('DB_USER', 'root');
define('DB_PASS', '');
?>
```

### 3. Despliegue en Railway
1. Ejecutar el script de sincronización:
   ```bash
php deploy_railway.php
```
2. Verificar la sincronización de datos
3. Configurar variables de entorno en Railway

## 📁 Estructura del Proyecto

```
company-presupuestos-online-2/
├── cotizador.php              # Cotizador principal con todas las mejoras
├── config.php                 # Configuración de base de datos
├── deploy_railway.php         # Script de sincronización con Railway
├── test_simple.html          # Página de pruebas y documentación
├── database/
│   └── company_presupuestos.sql
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── README.md
```

## 🔧 Funcionalidades Detalladas

### Filtrado Inteligente
```javascript
// Ejemplo de filtrado automático
function filtrarAdicionales() {
    const tipoSeleccionado = detectarTipoAscensor();
    const adicionales = document.querySelectorAll('.adicional-item');
    
    adicionales.forEach(adicional => {
        if (esRelevante(adicional, tipoSeleccionado)) {
            adicional.style.display = 'block';
        } else {
            adicional.style.display = 'none';
        }
    });
}
```

### Cálculos con Resta
```javascript
// Adicionales que restan dinero
if (opcion.nombre.toLowerCase().includes('restar')) {
    total -= precio;
} else {
    total += precio;
}
```

### Plazo Unificado
```javascript
// Sincronización automática de plazos
function actualizarPreciosPorPlazo(nuevoPlazo) {
    document.querySelectorAll('.plazo-selector').forEach(selector => {
        selector.value = nuevoPlazo;
    });
    updateTotals();
}
```

## 🧪 Testing

### Página de Pruebas
Acceder a `test_simple.html` para:
- Probar todas las funcionalidades
- Ver ejemplos de uso
- Verificar el filtrado inteligente
- Comprobar cálculos automáticos

### Casos de Prueba
1. **Filtrado**: Seleccionar diferentes tipos de ascensores
2. **Cálculos**: Verificar adicionales que suman y restan
3. **Plazos**: Cambiar plazos y verificar sincronización
4. **Totales**: Comprobar cálculos finales

## 🚀 Despliegue

### Script de Sincronización
```bash
# Ejecutar sincronización con Railway
php deploy_railway.php
```

El script:
- ✅ Conecta a ambas bases de datos
- ✅ Sincroniza categorías y opciones
- ✅ Verifica funcionalidades inteligentes
- ✅ Genera reporte de estado

### Variables de Entorno (Railway)
```
DB_HOST=autorack.proxy.rlwy.net
DB_PORT=47470
DB_NAME=railway
DB_USER=root
DB_PASS=tu_password_railway
```

## 📊 Base de Datos

### Tablas Principales
- **categorias**: Tipos de productos (Ascensores, Adicionales)
- **opciones**: Productos con precios por plazo
- **configuracion**: Parámetros del sistema

### Estructura de Precios
```sql
CREATE TABLE opciones (
    id INT PRIMARY KEY,
    categoria_id INT,
    nombre VARCHAR(255),
    precio_90_dias DECIMAL(10,2),
    precio_160_dias DECIMAL(10,2),
    precio_270_dias DECIMAL(10,2),
    descuento DECIMAL(5,2),
    orden INT
);
```

## 🎯 Casos de Uso

### 1. Cotización Electromecánica
- Usuario selecciona ascensor electromecánico
- Sistema filtra automáticamente adicionales relevantes
- Solo muestra adicionales con "electromecanico" en el nombre

### 2. Adicionales que Restan
- Adicionales con "RESTAR" en el nombre
- Se muestran con precio negativo en color naranja
- Se restan del total automáticamente

### 3. Cambio de Plazo
- Usuario cambia plazo de entrega
- Todos los productos se actualizan al mismo plazo
- Precios y totales se recalculan instantáneamente

## 🔍 Troubleshooting

### Problemas Comunes
1. **Conexión a Railway**: Verificar credenciales y firewall
2. **Filtrado no funciona**: Comprobar nombres de productos en BD
3. **Cálculos incorrectos**: Verificar precios por plazo en BD

### Logs y Debug
- Activar `DEBUG_MODE` en configuración
- Revisar logs en consola del navegador
- Usar `test_simple.html` para diagnóstico

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama para nueva funcionalidad
3. Commit de cambios
4. Push a la rama
5. Crear Pull Request

## 📝 Changelog

### v2.0.0 - Cotizador Inteligente
- ✅ Filtrado automático de adicionales
- ✅ Adicionales que restan dinero
- ✅ Plazo unificado para todos los productos
- ✅ Interface optimizada
- ✅ Script de despliegue a Railway

### v1.0.0 - Versión Base
- ✅ Cotizador básico
- ✅ Cálculos por plazo
- ✅ Generación de PDF

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## 👥 Equipo

- **Desarrollo**: Equipo de desarrollo
- **Testing**: QA Team
- **Despliegue**: DevOps Team

---

🚀 **¡El cotizador inteligente está listo para revolucionar tus cotizaciones de ascensores!**

## 🌐 Deploy Automático

- **GitHub:** https://github.com/facuvar/cotizadorcompany
- **Railway:** https://cotizadorcompany-production.up.railway.app
- **Auto-deploy:** Configurado desde GitHub ✅

## 📋 Scripts Disponibles

- `setup_railway_completo_v2.php` - Setup con manejo de claves foráneas
- `diagnostico_conexion.php` - Diagnóstico completo de conexión
- `cotizador.php` - Aplicación principal

---
*Última actualización: Deploy automático configurado* 