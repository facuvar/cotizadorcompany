# Corrección: Giracoches y Montaplatos sin Adicionales

## Problema Identificado

Los giracoches y montaplatos estaban mostrando opciones de adicionales cuando no deberían tenerlos, ya que estos productos no requieren ni permiten adicionales.

## Solución Implementada

### 1. Modificación de la Función `filtrarAdicionales()`

**Ubicación:** `cotizador.php` - Líneas ~1350-1360

**Cambios realizados:**
- Agregada nueva regla de filtrado para giracoches y montaplatos
- Cuando se selecciona un giracoches o montaplatos, se establece `adicionalesFiltrados = []`
- Deselección automática de adicionales previamente seleccionados

### 2. Lógica de Deselección Automática

**Funcionalidad:**
- Al seleccionar giracoches o montaplatos, se deseleccionan automáticamente todos los adicionales
- Se actualiza la interfaz visual removiendo los checkboxes marcados
- Se recalculan los totales automáticamente

### 3. Interfaz de Usuario Mejorada

**Cambios en `actualizarVisualizacionAdicionales()`:**
- Mensaje informativo cuando no hay adicionales disponibles
- Contador actualizado: "No disponible para este producto"
- Icono informativo con texto explicativo

## Código Implementado

### Nueva Regla de Filtrado

```javascript
// NUEVA REGLA: Si se selecciona giracoches o montaplatos, NO mostrar adicionales
if (opcionAscensor && 
    (opcionAscensor.nombre.toLowerCase().includes('giracoches') || 
     opcionAscensor.nombre.toLowerCase().includes('montaplatos'))) {
    
    adicionalesFiltrados = [];
    console.log('Giracoches o Montaplatos seleccionado: NO se muestran adicionales');
    
    // Deseleccionar automáticamente cualquier adicional que esté seleccionado
    const adicionalesSeleccionados = selectedOptions.filter(id => {
        const opcion = opciones.find(op => op.id == id);
        return opcion && parseInt(opcion.categoria_id) === parseInt(categoriaAdicionales.id);
    });
    
    adicionalesSeleccionados.forEach(id => {
        removeSelectedOption(id);
        // Actualizar visualmente el checkbox
        const checkbox = document.querySelector(`[data-option-id="${id}"].option-checkbox`);
        if (checkbox) {
            const input = checkbox.querySelector('input');
            if (input) {
                input.checked = false;
                checkbox.classList.remove('checked');
            }
        }
    });
    
    if (adicionalesSeleccionados.length > 0) {
        console.log(`Deseleccionados ${adicionalesSeleccionados.length} adicionales automáticamente`);
        updateTotals();
        updateSelectedItems();
    }
}
```

### Mensaje Informativo

```javascript
if (adicionalesFiltrados.length === 0) {
    // Mostrar mensaje cuando no hay adicionales disponibles
    categoryOptions.innerHTML = `
        <div style="padding: var(--spacing-lg); text-align: center; color: var(--text-muted);">
            <div style="margin-bottom: var(--spacing-sm);">
                ${modernUI.getIcon('info-circle')}
            </div>
            <p>Los giracoches y montaplatos no requieren adicionales.</p>
        </div>
    `;
}
```

## Flujo de Funcionamiento

1. **Usuario selecciona un ascensor normal:** Se muestran adicionales filtrados según el tipo
2. **Usuario selecciona giracoches o montaplatos:** 
   - Se ocultan todos los adicionales
   - Se deseleccionan automáticamente adicionales previamente seleccionados
   - Se muestra mensaje informativo
   - Se recalculan los totales
3. **Usuario cambia a otro tipo de ascensor:** Se restaura el filtrado normal de adicionales

## Productos Afectados

### Giracoches
- Cualquier producto que contenga "giracoches" en el nombre (case-insensitive)
- Categoría: Generalmente en la categoría de ascensores (categoria_id = 1)

### Montaplatos
- Cualquier producto que contenga "montaplatos" en el nombre (case-insensitive)
- Categoría: Generalmente en la categoría de ascensores (categoria_id = 1)

## Beneficios de la Corrección

1. **Precisión técnica:** Los productos que no requieren adicionales no los muestran
2. **Experiencia de usuario:** Interfaz más clara y sin opciones irrelevantes
3. **Cálculos correctos:** Evita adicionales innecesarios en el presupuesto
4. **Información clara:** Mensaje explicativo para el usuario

## Compatibilidad

- ✅ Funciona con la lógica existente de filtrado de adicionales
- ✅ Compatible con otros tipos de ascensores (hidráulicos, electromecánicos, etc.)
- ✅ Mantiene la funcionalidad de selección múltiple para otros productos
- ✅ No afecta el funcionamiento de descuentos u otras categorías

## Archivos Modificados

- `cotizador.php` - Función `filtrarAdicionales()` y `actualizarVisualizacionAdicionales()`

## Testing Recomendado

1. Seleccionar un ascensor normal → Verificar que se muestran adicionales
2. Seleccionar giracoches → Verificar que no se muestran adicionales
3. Seleccionar montaplatos → Verificar que no se muestran adicionales
4. Seleccionar adicionales, luego giracoches → Verificar deselección automática
5. Cambiar de giracoches a ascensor normal → Verificar que se restauran adicionales 