# 🚀 Instrucciones para Sincronizar Datos con Railway

## 📋 Proceso Completo de Sincronización

### **Paso 1: Exportar Datos desde Localhost** 📤

1. **Ejecuta el exportador:**
   ```
   http://localhost/company-presupuestos-online-2/exportar_datos.php
   ```

2. **Verifica los datos:**
   - ✅ 3 Categorías
   - ✅ 55 Ascensores  
   - ✅ 52 Adicionales
   - ✅ 111 Total Opciones

3. **Descarga el archivo SQL:**
   - Haz clic en el botón "📥 Descargar cotizador_datos_XXXX.sql"
   - El archivo se guardará en tu carpeta de descargas

### **Paso 2: Subir Archivos a Railway** 🚀

1. **Sube los archivos necesarios a Railway:**
   - `cotizador.php` (tu cotizador principal)
   - `test_simple.html` (página de pruebas)
   - `importar_desde_sql.php` (script importador)
   - `cotizador_datos_XXXX.sql` (archivo de datos)

2. **Métodos de subida:**
   - **GitHub:** Commit y push al repositorio
   - **Railway CLI:** `railway up`
   - **Interface web:** Drag & drop en Railway

### **Paso 3: Ejecutar Importación en Railway** 📥

1. **Accede al importador:**
   ```
   https://tu-app.railway.app/importar_desde_sql.php
   ```

2. **El script automáticamente:**
   - ✅ Detecta el archivo SQL
   - ✅ Se conecta a la base de datos Railway
   - ✅ Ejecuta la importación completa
   - ✅ Verifica todas las funcionalidades

3. **Verifica los resultados:**
   - Debe mostrar exactamente los mismos números que localhost
   - Todas las funcionalidades del cotizador deben estar activas

### **Paso 4: Verificar Funcionamiento** ✅

1. **Accede al cotizador:**
   ```
   https://tu-app.railway.app/cotizador.php
   ```

2. **Prueba las funcionalidades:**
   - **Filtrado automático:** Selecciona un ascensor y verifica que solo aparezcan adicionales relevantes
   - **Adicionales que restan:** Busca opciones con "RESTAR" y verifica precios negativos
   - **Plazo unificado:** Cambia el plazo y verifica que todos los productos se actualicen

3. **Página de pruebas:**
   ```
   https://tu-app.railway.app/test_simple.html
   ```

## 🔧 Funcionalidades Verificadas

### **1. Filtrado Inteligente de Adicionales**
- ✅ **Electromecánico/Gearless** → Solo adicionales con "electromecanico"
- ✅ **Hidráulico** → Solo adicionales con "hidraulico"  
- ✅ **Montacargas** → Solo adicionales con "montacargas"
- ✅ **Salvaescaleras** → Solo adicionales con "salvaescaleras"

### **2. Adicionales que Restan Dinero**
- ✅ **"CABINA EN CHAPA C/DETALLES RESTAR"** → Resta dinero
- ✅ **"PB Y PUERTA DE CABINA EN CHAPA RESTAR"** → Resta dinero
- ✅ **Precios negativos** mostrados en color naranja

### **3. Plazo Unificado**
- ✅ **90 días** → Todos los productos usan este plazo
- ✅ **160 días** → Todos los productos usan este plazo
- ✅ **270 días** → Todos los productos usan este plazo
- ✅ **Cambio automático** → Al cambiar plazo, todos se actualizan

## 📊 Estadísticas Esperadas

```
📈 DATOS COMPLETOS:
├── 3 Categorías
├── 55 Ascensores
├── 52 Adicionales
├── 27 Adicionales Electromecánicos
├── 22 Adicionales Hidráulicos
├── 2 Adicionales Montacargas
├── 1 Adicional Salvaescaleras
└── 6 Adicionales que Restan
```

## 🚨 Solución de Problemas

### **Error de Conexión a Railway:**
```bash
# Verifica las credenciales en Railway
DB_HOST=autorack.proxy.rlwy.net
DB_PORT=47470
DB_NAME=railway
DB_USER=root
DB_PASS=LjEWJGgCJHdBcgfGAhGjfEBEhfJjGGjA
```

### **Archivo SQL no encontrado:**
1. Verifica que el archivo .sql esté en el directorio raíz
2. Recarga la página del importador
3. El script detecta automáticamente archivos .sql

### **Datos no coinciden:**
1. Ejecuta nuevamente el exportador en localhost
2. Descarga un archivo SQL fresco
3. Sube el nuevo archivo a Railway
4. Ejecuta la importación nuevamente

## 🎉 Resultado Final

Una vez completados todos los pasos:

✅ **Tu cotizador inteligente estará 100% operativo en Railway**  
✅ **Todos los datos sincronizados perfectamente**  
✅ **Todas las funcionalidades activas**  
✅ **Interface moderna y responsive**  
✅ **Listo para producción**

---

## 📞 Soporte

Si encuentras algún problema:
1. Verifica que XAMPP esté ejecutándose (para exportación)
2. Confirma que Railway esté activo
3. Revisa los logs en el importador
4. Ejecuta el proceso paso a paso

¡Tu cotizador inteligente estará listo en minutos! 🚀 