# 🚀 Instrucciones de Despliegue

## Despliegue Automático (Recomendado)

### Opción 1: Script PowerShell
```powershell
.\deploy.ps1
```

### Opción 2: Script Batch
```cmd
deploy.bat
```

## Despliegue Manual

### 1. Preparar GitHub

#### Inicializar repositorio (si es necesario)
```bash
git init
```

#### Agregar archivos
```bash
git add .
git commit -m "Cotizador inteligente con todas las mejoras"
```

#### Conectar con GitHub
```bash
git remote add origin https://github.com/tu-usuario/company-presupuestos-online-2.git
git push -u origin main
```

### 2. Sincronizar con Railway

#### Ejecutar script de sincronización
```bash
php deploy_railway.php
```

#### Verificar conexión
- ✅ Conexión a Railway establecida
- ✅ Base de datos local conectada
- ✅ Categorías sincronizadas
- ✅ Opciones sincronizadas
- ✅ Funcionalidades verificadas

## 🔧 Configuración de Railway

### Variables de Entorno Requeridas
```
DB_HOST=autorack.proxy.rlwy.net
DB_PORT=47470
DB_NAME=railway
DB_USER=root
DB_PASS=tu_password_railway
```

### Configuración del Proyecto
1. Crear nuevo proyecto en Railway
2. Conectar repositorio GitHub
3. Configurar variables de entorno
4. Desplegar automáticamente

## 📋 Checklist de Despliegue

### Antes del Despliegue
- [ ] Base de datos local actualizada
- [ ] Todas las funcionalidades probadas
- [ ] Archivos de configuración listos
- [ ] Credenciales de Railway verificadas

### Durante el Despliegue
- [ ] Código subido a GitHub
- [ ] Base de datos sincronizada
- [ ] Variables de entorno configuradas
- [ ] Aplicación desplegada en Railway

### Después del Despliegue
- [ ] Aplicación accesible en Railway
- [ ] Filtrado inteligente funcionando
- [ ] Adicionales que restan operativos
- [ ] Plazo unificado activo
- [ ] Cálculos correctos

## 🧪 Verificación Post-Despliegue

### Pruebas Funcionales
1. **Filtrado Inteligente**
   - Seleccionar ascensor electromecánico
   - Verificar que solo aparezcan adicionales relevantes
   - Probar con otros tipos de ascensores

2. **Adicionales que Restan**
   - Buscar adicionales con "RESTAR" en el nombre
   - Verificar que aparezcan en color naranja
   - Comprobar que resten del total

3. **Plazo Unificado**
   - Cambiar plazo en cualquier producto
   - Verificar que todos se actualicen
   - Comprobar recálculo de totales

### URLs de Prueba
- **Producción**: `https://tu-app.railway.app/cotizador.php`
- **Pruebas**: `https://tu-app.railway.app/test_simple.html`

## 🔍 Troubleshooting

### Error: "No se puede conectar a Railway"
```bash
# Verificar credenciales
php -r "
$pdo = new PDO('mysql:host=autorack.proxy.rlwy.net;port=47470;dbname=railway', 'root', 'tu_password');
echo 'Conexión exitosa';
"
```

### Error: "Filtrado no funciona"
1. Verificar nombres de productos en BD
2. Comprobar que contengan palabras clave
3. Revisar función `filtrarAdicionales()`

### Error: "Cálculos incorrectos"
1. Verificar precios por plazo en BD
2. Comprobar función `updateTotals()`
3. Revisar lógica de adicionales que restan

## 📞 Soporte

### Archivos de Log
- `deploy_railway.php` - Genera log de sincronización
- Consola del navegador - Errores JavaScript
- Railway logs - Errores de servidor

### Comandos Útiles
```bash
# Ver estado de Git
git status

# Ver logs de Railway
railway logs

# Probar conexión a BD
php deploy_railway.php

# Verificar funcionalidades
# Abrir: test_simple.html
```

## 🎉 ¡Listo!

Tu cotizador inteligente está desplegado y funcionando con:
- ✅ Filtrado automático de adicionales
- ✅ Adicionales que restan dinero
- ✅ Plazo unificado para todos los productos
- ✅ Interface optimizada y responsive
- ✅ Cálculos precisos en tiempo real

🌐 **¡Tu aplicación está lista para revolucionar las cotizaciones de ascensores!** 