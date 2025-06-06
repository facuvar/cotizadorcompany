# 🚪 Funcionalidad de Puertas Exclusivas

## 📋 Descripción

Se ha implementado una nueva funcionalidad en el cotizador que permite la **selección exclusiva** de puertas para ascensores. Esta funcionalidad garantiza que solo se pueda seleccionar un tipo de puerta por cada tipo de ascensor, ya que son opciones mutuamente excluyentes.

## 🎯 Objetivo

Evitar que los usuarios seleccionen múltiples tipos de puertas para el mismo ascensor, lo cual no es técnicamente posible ni comercialmente correcto.

## 🔧 Implementación Técnica

### Grupos de Exclusión Mutua

La funcionalidad se basa en dos grupos principales:

#### Grupo 1: Ascensores Electromecánicos
- `Ascensores Electromecanicos Adicional Puertas de 900`
- `Ascensores Electromecanicos Adicional Puertas de 1000`
- `Ascensores Electromecanicos Adicional Puertas de 1300`
- `Ascensores Electromecanicos Adicional Puertas de 1800`

#### Grupo 2: Ascensores Hidráulicos
- `Ascensores Hidraulicos adicional puertas de 900`
- `Ascensores Hidraulicos adicional puertas de 1000`
- `Ascensores Hidraulicos adicional puertas de 1200`
- `Ascensores Hidraulicos adicional puertas de 1800`

### Lógica de Funcionamiento

```javascript
// Definir grupos de puertas mutuamente excluyentes
const gruposPuertasExcluyentes = [
    // Grupo 1: Puertas de Ascensores Electromecanicos
    [
        'ascensores electromecanicos adicional puertas de 900',
        'ascensores electromecanicos adicional puertas de 1000', 
        'ascensores electromecanicos adicional puertas de 1300',
        'ascensores electromecanicos adicional puertas de 1800'
    ],
    // Grupo 2: Puertas de Ascensores Hidraulicos
    [
        'ascensores hidraulicos adicional puertas de 900',
        'ascensores hidraulicos adicional puertas de 1000',
        'ascensores hidraulicos adicional puertas de 1200', 
        'ascensores hidraulicos adicional puertas de 1800'
    ]
];
```

### Comportamiento del Sistema

1. **Selección Normal**: Cuando el usuario selecciona una puerta, se marca como seleccionada.

2. **Deselección Automática**: Si ya hay otra puerta del mismo grupo seleccionada, se deselecciona automáticamente.

3. **Feedback Visual**: Los checkboxes se actualizan visualmente para reflejar el estado actual.

4. **Otros Adicionales**: Los adicionales que no son puertas siguen funcionando con selección múltiple normal.

## 🎮 Experiencia de Usuario

### Flujo de Selección

1. Usuario selecciona un ascensor (electromecánico o hidráulico)
2. Se muestran los adicionales correspondientes
3. Usuario selecciona una puerta (ej: 900mm)
4. **Automáticamente**: Si después selecciona otra puerta (ej: 1000mm), la anterior se deselecciona
5. Solo queda seleccionada la última puerta elegida

### Indicadores Visuales

- ✅ **Puerta Seleccionada**: Checkbox marcado, fondo azul claro
- ❌ **Puerta Deseleccionada**: Checkbox desmarcado automáticamente
- 🔧 **Otros Adicionales**: Funcionan normalmente (selección múltiple)

## 📝 Casos de Uso

### Caso 1: Selección Inicial
```
Usuario selecciona: "Puertas de 900mm" (Electromecánico)
Resultado: ✅ Puertas 900mm seleccionadas
```

### Caso 2: Cambio de Selección
```
Estado inicial: ✅ Puertas 900mm seleccionadas
Usuario selecciona: "Puertas de 1300mm" (Electromecánico)
Resultado: 
- ❌ Puertas 900mm deseleccionadas automáticamente
- ✅ Puertas 1300mm seleccionadas
```

### Caso 3: Diferentes Tipos de Ascensor
```
Usuario puede tener:
- ✅ Puertas 900mm (Electromecánico)
- ✅ Puertas 1200mm (Hidráulico)
- ✅ Otros adicionales múltiples

Porque son grupos independientes.
```

## 🧪 Testing

### Archivo de Prueba
Se ha creado `test_puertas_exclusivas.html` para verificar la funcionalidad:

```bash
# Abrir en navegador
open test_puertas_exclusivas.html
```

### Casos de Prueba

1. **Test Básico**: Seleccionar una puerta y verificar que se marca
2. **Test Exclusión**: Seleccionar otra puerta del mismo grupo y verificar deselección automática
3. **Test Grupos Independientes**: Verificar que grupos diferentes no se afectan
4. **Test Otros Adicionales**: Verificar que otros adicionales funcionan normalmente

## 🔍 Debugging

### Logs en Consola
La funcionalidad incluye logs detallados:

```javascript
console.log('Opción de puerta detectada, aplicando exclusión mutua para grupo:', grupoExcluyente);
console.log('Deseleccionada opción de puerta:', otherOption.nombre);
```

### Verificación Manual
1. Abrir DevTools (F12)
2. Ir a la pestaña Console
3. Seleccionar puertas y observar los logs
4. Verificar que `selectedOptions` se actualiza correctamente

## 📊 Impacto en el Sistema

### Archivos Modificados
- `cotizador.php`: Función `toggleOption()` actualizada
- `README.md`: Documentación actualizada
- `test_puertas_exclusivas.html`: Archivo de prueba creado

### Compatibilidad
- ✅ **Backward Compatible**: No afecta funcionalidades existentes
- ✅ **Otros Adicionales**: Siguen funcionando normalmente
- ✅ **Filtrado por Tipo**: Se mantiene el filtrado inteligente existente

## 🚀 Beneficios

1. **Prevención de Errores**: Evita selecciones técnicamente imposibles
2. **Mejor UX**: Guía al usuario hacia selecciones válidas
3. **Presupuestos Precisos**: Elimina ambigüedades en las cotizaciones
4. **Mantenimiento**: Código organizado y fácil de extender

## 🔮 Futuras Mejoras

### Posibles Extensiones
1. **Más Grupos Excluyentes**: Agregar otros tipos de adicionales mutuamente excluyentes
2. **Validación Backend**: Validar exclusiones también en el servidor
3. **Mensajes de Usuario**: Mostrar notificaciones cuando se deselecciona automáticamente
4. **Configuración Dinámica**: Permitir configurar grupos desde el panel de administración

### Código para Nuevos Grupos
```javascript
// Ejemplo para agregar nuevos grupos excluyentes
const gruposPuertasExcluyentes = [
    // Grupos existentes...
    
    // Nuevo grupo: Sistemas de Control
    [
        'sistema control basico',
        'sistema control avanzado',
        'sistema control premium'
    ]
];
```

## 📞 Soporte

Para dudas o problemas con esta funcionalidad:
1. Revisar los logs en la consola del navegador
2. Verificar que los nombres de las opciones coincidan exactamente
3. Probar con el archivo `test_puertas_exclusivas.html`
4. Consultar este documento para casos de uso específicos

---

**Fecha de Implementación**: Enero 2025  
**Versión**: 1.0  
**Estado**: ✅ Implementado y Probado 