# 💰 Funcionalidad de Ajuste Masivo de Precios

## 📋 Descripción

Se ha agregado una nueva funcionalidad al panel de administración que permite **incrementar o disminuir todos los precios** de ascensores y adicionales mediante un porcentaje.

## 🎯 Características

### ✅ Lo que SÍ afecta:
- **Todos los ascensores** (2, 3, 4, 5 paradas, montacargas, salvaescaleras, etc.)
- **Todos los adicionales** (opciones extra, accesorios, etc.)
- **Todos los plazos de entrega**:
  - 90 días
  - 160-180 días  
  - 270 días

### ❌ Lo que NO afecta:
- **Descuentos** (formas de pago)
- **Configuraciones del sistema**

## 🚀 Cómo Usar

### 1. Acceder a la Funcionalidad

Puedes acceder de **dos maneras**:

**Opción A: Desde el Sidebar**
1. Ve al panel de administración
2. En el menú lateral, haz clic en **"Ajustar Precios"**

**Opción B: Desde Acciones Rápidas**
1. En el dashboard principal
2. Haz clic en la tarjeta **"Ajustar Precios"** (icono de dólar)

### 2. Usar la Funcionalidad

1. **Ingresa el porcentaje** en el campo correspondiente:
   - **Valores positivos** = Incrementar precios (ej: `10` = +10%)
   - **Valores negativos** = Disminuir precios (ej: `-5` = -5%)

2. **Revisa las estadísticas** mostradas:
   - Opciones totales que se verán afectadas
   - Precios por plazo que se modificarán
   - Categorías incluidas

3. **Haz clic en "Aplicar Ajuste"**

4. **Confirma la acción** en el diálogo que aparece

## 📊 Ejemplos de Uso

### Ejemplo 1: Incremento por Inflación
```
Porcentaje: +10%
Resultado: Un ascensor de $50,000 pasará a costar $55,000
```

### Ejemplo 2: Descuento Promocional
```
Porcentaje: -5%
Resultado: Un adicional de $1,000 pasará a costar $950
```

### Ejemplo 3: Ajuste por Costos
```
Porcentaje: +15%
Resultado: Incremento general del 15% en todos los productos
```

## ⚠️ Precauciones Importantes

### 🔒 Seguridad
- **Solo administradores** pueden acceder a esta funcionalidad
- Se requiere **confirmación** antes de aplicar cambios
- Los cambios son **inmediatos** y afectan el cotizador en tiempo real

### 💾 Respaldo
- **Recomendación:** Realiza una copia de seguridad de la base de datos antes de usar esta funcionalidad
- Los cambios **NO se pueden deshacer** fácilmente
- Considera hacer una prueba con un porcentaje pequeño primero

### 📈 Impacto
- Los cambios afectan **inmediatamente** a:
  - Nuevos presupuestos
  - Cotizaciones en curso
  - Todos los plazos de entrega

## 🛠️ Detalles Técnicos

### Tablas Afectadas
- `opcion_precios` - Precios por plazo de entrega
- `opciones` - Precios base de las opciones

### Cálculo
```sql
nuevo_precio = precio_actual * (1 + porcentaje/100)
```

### Transacciones
- Usa **transacciones de base de datos** para garantizar consistencia
- Si hay un error, **todos los cambios se revierten**

## 📱 Interfaz de Usuario

### Estadísticas en Tiempo Real
- **Opciones Totales:** Número de productos que se verán afectados
- **Precios por Plazo:** Cantidad total de precios individuales
- **Categorías Afectadas:** Lista de categorías incluidas

### Tabla de Categorías
Muestra para cada categoría:
- Nombre de la categoría
- Número de opciones
- Número de precios por plazo
- Precio promedio actual

### Validaciones
- **Rango permitido:** -50% a +100%
- **Validación en tiempo real** del porcentaje ingresado
- **Confirmación obligatoria** antes de aplicar cambios

## 🔄 Casos de Uso Comunes

### 1. Ajuste por Inflación Anual
```
Escenario: Inflación del 8%
Acción: Incrementar todos los precios +8%
Frecuencia: Una vez al año
```

### 2. Promoción Temporal
```
Escenario: Descuento de temporada
Acción: Disminuir precios -10%
Nota: Recordar revertir después de la promoción
```

### 3. Aumento de Costos de Materiales
```
Escenario: Aumento en costos de acero
Acción: Incrementar precios +12%
Aplicación: Inmediata para nuevos presupuestos
```

### 4. Ajuste Competitivo
```
Escenario: Ajuste para mantener competitividad
Acción: Disminuir precios -3%
Objetivo: Mejorar posición en el mercado
```

## 📞 Soporte

Si tienes problemas con esta funcionalidad:

1. **Verifica** que tengas permisos de administrador
2. **Revisa** que la base de datos esté funcionando correctamente
3. **Consulta** los logs del sistema en caso de errores
4. **Contacta** al equipo de desarrollo si persisten los problemas

---

**Nota:** Esta funcionalidad fue diseñada para ser segura y eficiente, pero siempre es recomendable hacer pruebas en un entorno de desarrollo antes de aplicar cambios en producción. 