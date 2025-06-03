# Sistema de Ordenamiento para Cotizador

## Descripción

Se ha implementado un sistema de ordenamiento personalizable para las categorías y opciones del cotizador de ascensores. Esto permite organizar la visualización de los datos según las necesidades del negocio.

## Archivos Creados/Modificados

### Nuevos Archivos:
- `cotizador_ordenado.php` - Versión del cotizador que respeta el orden configurado en el admin
- `api/get_categories_ordered.php` - API que devuelve datos ordenados por el campo `orden`
- `inicializar_orden.php` - Script para configurar los campos de orden inicialmente

### Archivos Modificados:
- `admin/gestionar_datos.php` - Agregado controles de ordenamiento (botones ↑ ↓)
- `cotizador.php` - Agregado enlace a la versión ordenada

## Funcionalidades

### Panel de Administración (`admin/gestionar_datos.php`)

1. **Ordenamiento de Categorías:**
   - Botones para subir/bajar categorías
   - Las categorías se muestran en el orden configurado
   - El orden afecta cómo aparecen en el cotizador

2. **Ordenamiento de Opciones:**
   - Botones para subir/bajar opciones dentro de cada categoría
   - Las opciones se ordenan por categoría y luego por orden personalizado
   - Columna "Orden" muestra los controles de ordenamiento

### Cotizadores

1. **Cotizador Original (`cotizador.php`):**
   - Mantiene la lógica de ordenamiento original
   - Enlace a la versión ordenada

2. **Cotizador Ordenado (`cotizador_ordenado.php`):**
   - Respeta el orden configurado en el admin
   - Enlace directo al panel de gestión de orden
   - Las categorías y opciones aparecen según el orden de la base de datos

## Instalación

1. **Ejecutar el script de inicialización:**
   ```
   http://tu-dominio/inicializar_orden.php
   ```
   
   Este script:
   - Verifica/crea los campos `orden` en las tablas
   - Inicializa el orden basado en el orden alfabético actual
   - Solo necesita ejecutarse una vez

2. **Configurar el orden:**
   - Ir a `admin/gestionar_datos.php`
   - Usar los botones ↑ ↓ para organizar categorías y opciones
   - Los cambios se reflejan inmediatamente en `cotizador_ordenado.php`

## Uso

### Para Administradores:
1. Acceder al panel admin: `admin/gestionar_datos.php`
2. Ir a la pestaña "Categorías" para ordenar categorías
3. Ir a la pestaña "Opciones" para ordenar opciones dentro de cada categoría
4. Usar los botones ↑ ↓ para cambiar el orden
5. Verificar los cambios en `cotizador_ordenado.php`

### Para Usuarios:
- `cotizador.php` - Versión original con ordenamiento automático
- `cotizador_ordenado.php` - Versión que respeta el orden configurado por el admin

## Estructura de Base de Datos

### Tabla `categorias`:
- `orden` (INT) - Campo para el orden personalizado

### Tabla `opciones`:
- `orden` (INT) - Campo para el orden personalizado dentro de cada categoría

## APIs

### `api/get_categories.php` (Original):
- Devuelve datos con ordenamiento automático
- Usado por `cotizador.php`

### `api/get_categories_ordered.php` (Nuevo):
- Devuelve datos ordenados por campo `orden`
- Usado por `cotizador_ordenado.php`
- Incluye información de ordenamiento

## Beneficios

1. **Flexibilidad:** Los administradores pueden organizar los productos según estrategia comercial
2. **Facilidad de uso:** Interfaz intuitiva con botones ↑ ↓
3. **Compatibilidad:** El cotizador original sigue funcionando sin cambios
4. **Escalabilidad:** Fácil agregar más criterios de ordenamiento en el futuro

## Notas Técnicas

- El ordenamiento se basa en intercambio de posiciones entre elementos adyacentes
- Las transacciones de base de datos aseguran consistencia
- Los campos `orden` se inicializan automáticamente para nuevos elementos
- El sistema es compatible con la estructura existente 