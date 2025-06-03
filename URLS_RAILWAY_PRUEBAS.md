# 🌐 URLs PARA PROBAR EN RAILWAY

## 🎯 URL BASE DEL PROYECTO
**https://cotizadorcompany-production.up.railway.app**

## 🔧 SCRIPTS DE CONFIGURACIÓN

### 1. Setup Completo (NUEVO - Con datos reales)
```
https://cotizadorcompany-production.up.railway.app/setup_railway_completo.php
```
**Descripción**: Script autónomo con todos los datos reales del cotizador. No depende de archivos SQL externos.

### 2. Setup Railway Direct (Existente)
```
https://cotizadorcompany-production.up.railway.app/setup_railway_direct.php
```
**Descripción**: Script de configuración que ya estaba en Railway.

### 3. Setup Railway Auto (Existente)
```
https://cotizadorcompany-production.up.railway.app/setup_railway_auto.php
```
**Descripción**: Configuración automática existente.

## 🧪 SCRIPTS DE DIAGNÓSTICO

### 1. Railway Debug
```
https://cotizadorcompany-production.up.railway.app/railway_debug.php
```
**Descripción**: Diagnóstico completo del entorno Railway.

### 2. Configure Railway
```
https://cotizadorcompany-production.up.railway.app/configure_railway.php
```
**Descripción**: Configuración específica para Railway.

### 3. Railway Diagnóstico
```
https://cotizadorcompany-production.up.railway.app/railway_diagnostico.php
```
**Descripción**: Diagnóstico adicional del sistema.

## 🎯 APLICACIÓN PRINCIPAL

### 1. Cotizador Inteligente
```
https://cotizadorcompany-production.up.railway.app/cotizador.php
```
**Descripción**: Cotizador principal con todas las funcionalidades inteligentes.

### 2. Página Principal
```
https://cotizadorcompany-production.up.railway.app/
```
**Descripción**: Página de inicio del proyecto.

### 3. Página de Pruebas
```
https://cotizadorcompany-production.up.railway.app/test_simple.html
```
**Descripción**: Página con instrucciones de prueba de las funcionalidades.

## 📊 SCRIPTS DE IMPORTACIÓN (Si necesarios)

### 1. Actualizar DB Railway
```
https://cotizadorcompany-production.up.railway.app/actualizar_db_railway.php
```
**Descripción**: Script para actualizar la base de datos.

### 2. Import Railway Final
```
https://cotizadorcompany-production.up.railway.app/import_railway_final.php
```
**Descripción**: Importación final de datos.

## 🚀 PLAN DE PRUEBAS

### Paso 1: Configurar Base de Datos
1. Acceder a: `setup_railway_completo.php` (RECOMENDADO)
2. Si no funciona, probar: `setup_railway_direct.php`
3. Verificar que se configuren las tablas y datos

### Paso 2: Verificar Configuración
1. Acceder a: `railway_debug.php`
2. Confirmar conexión a base de datos
3. Verificar variables de entorno

### Paso 3: Probar Cotizador
1. Acceder a: `cotizador.php`
2. Probar filtrado inteligente:
   - Seleccionar ascensor electromecánico → verificar adicionales filtrados
   - Seleccionar ascensor hidráulico → verificar adicionales filtrados
3. Probar adicionales que restan:
   - Buscar opciones con "RESTAR" → verificar color naranja
4. Probar plazo unificado:
   - Cambiar plazo → verificar sincronización automática

### Paso 4: Verificar Funcionalidades
1. Acceder a: `test_simple.html`
2. Seguir las instrucciones de prueba
3. Confirmar que todas las funcionalidades funcionan

## ✅ FUNCIONALIDADES ESPERADAS

### 🔧 Filtrado Inteligente
- **Electromecánico/Gearless**: Solo adicionales con "electromecánico"
- **Hidráulico**: Solo adicionales con "hidráulico"
- **Montacargas**: Solo adicionales con "montacargas"
- **Salvaescaleras**: Solo adicionales con "salvaescaleras"

### 💰 Adicionales que Restan
- Opciones con "RESTAR" en el nombre
- Precios en color naranja
- Descuentan del total

### ⏰ Plazo Unificado
- Cambiar plazo en cualquier producto
- Todos los productos se actualizan automáticamente
- Recálculo automático de precios

### 💱 Moneda y Cálculos
- Precios en ARS (Pesos Argentinos)
- Cálculos en tiempo real
- Interface moderna con checkboxes

## 🚨 SOLUCIÓN DE PROBLEMAS

### Si no funciona setup_railway_completo.php:
1. Probar `setup_railway_direct.php`
2. Verificar variables de entorno con `railway_debug.php`
3. Revisar logs en Railway dashboard

### Si el cotizador no carga:
1. Verificar que la base de datos esté configurada
2. Comprobar conexión con `railway_debug.php`
3. Revisar errores en logs

### Si las funcionalidades no funcionan:
1. Verificar que los datos se insertaron correctamente
2. Comprobar JavaScript en el navegador
3. Revisar consola del navegador para errores

## 📞 PRÓXIMOS PASOS

1. **Probar setup_railway_completo.php** ← EMPEZAR AQUÍ
2. **Verificar configuración** con railway_debug.php
3. **Probar cotizador** con todas las funcionalidades
4. **Documentar resultados** y reportar cualquier problema

¡El cotizador inteligente está listo para Railway! 🚀 