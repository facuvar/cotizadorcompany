# 🚀 GUÍA COMPLETA: DESPLIEGUE DE GITHUB A RAILWAY

## 🎯 PROBLEMA IDENTIFICADO
Railway **NO** se sincroniza automáticamente con GitHub. Necesitas configurar el despliegue manualmente.

## ✅ SOLUCIÓN PASO A PASO

### 1. 📤 SUBIR ARCHIVO FALTANTE A GITHUB

```bash
# Agregar el archivo setup_directo.php
git add setup_directo.php
git commit -m "Agregar setup_directo.php para Railway"
git push origin master
```

### 2. 🚂 CONFIGURAR RAILWAY DESDE CERO

#### A. Crear Nuevo Proyecto en Railway
1. Ve a [railway.app](https://railway.app)
2. Haz clic en **"New Project"**
3. Selecciona **"Deploy from GitHub repo"**
4. Busca y selecciona: `facuvar/cotizadorcompany`
5. Railway detectará automáticamente que es un proyecto PHP

#### B. Agregar Base de Datos MySQL
1. En tu proyecto Railway, haz clic en **"New"**
2. Selecciona **"Database"** → **"MySQL"**
3. Railway creará una instancia MySQL automáticamente

#### C. Configurar Variables de Entorno
1. Ve a tu aplicación (no la base de datos)
2. Haz clic en la pestaña **"Variables"**
3. Agrega estas variables una por una:

```env
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_USER=${{MySQL.MYSQL_USER}}
DB_PASS=${{MySQL.MYSQL_PASSWORD}}
DB_NAME=${{MySQL.MYSQL_DATABASE}}
DB_PORT=${{MySQL.MYSQL_PORT}}
```

**IMPORTANTE**: Usa las referencias `${{MySQL.VARIABLE}}` para que Railway conecte automáticamente con tu base de datos.

### 3. 🔧 CONFIGURAR BASE DE DATOS

#### Opción A: Usando setup_directo.php (RECOMENDADO)
1. Una vez desplegado, ve a: `https://tu-app.railway.app/setup_directo.php`
2. Este script configurará automáticamente:
   - ✅ Tablas necesarias
   - ✅ 28 opciones de ejemplo
   - ✅ Todas las funcionalidades del cotizador inteligente

#### Opción B: Importar datos completos
1. Ve a: `https://tu-app.railway.app/actualizar_db_railway.php`
2. Este script importará todos los datos reales (111 opciones)

### 4. 🧪 VERIFICAR FUNCIONAMIENTO

#### URLs de Prueba:
- **Aplicación**: `https://tu-app.railway.app/`
- **Cotizador**: `https://tu-app.railway.app/cotizador.php`
- **Setup**: `https://tu-app.railway.app/setup_directo.php`
- **Diagnóstico**: `https://tu-app.railway.app/diagnostico_railway.php`

#### Pruebas Funcionales:
1. **Filtrado Inteligente**: Selecciona un ascensor electromecánico → solo aparecen adicionales relevantes
2. **Adicionales que Restan**: Busca opciones con "RESTAR" → aparecen en color naranja
3. **Plazo Unificado**: Cambia el plazo → todos los productos se actualizan automáticamente

### 5. 🔄 CONFIGURAR AUTO-DEPLOY (OPCIONAL)

Para que Railway se actualice automáticamente cuando hagas push a GitHub:

1. En Railway, ve a **Settings** → **Service**
2. En **Source Repo**, confirma que está conectado a `facuvar/cotizadorcompany`
3. En **Deploy Triggers**, asegúrate que esté habilitado **"Auto Deploy"**
4. Selecciona la rama **"master"** como fuente

## 🚨 SOLUCIÓN DE PROBLEMAS COMUNES

### Error: "MySQL server has gone away"
```bash
# En Railway Console, ejecuta:
SET GLOBAL max_allowed_packet=1073741824;
SET GLOBAL wait_timeout=28800;
```

### Error: Variables de entorno no funcionan
1. Verifica que uses la sintaxis: `${{MySQL.MYSQL_HOST}}`
2. Redeploy la aplicación después de cambiar variables
3. Espera 2-3 minutos para que los cambios tomen efecto

### Error: Archivos no se encuentran
1. Confirma que el archivo esté en GitHub
2. Verifica que Railway esté conectado al repositorio correcto
3. Haz un redeploy manual si es necesario

## 📊 ESTADO ACTUAL DEL PROYECTO

### ✅ LISTO EN GITHUB:
- 315 archivos subidos
- Cotizador inteligente completo
- Todas las funcionalidades implementadas
- Documentación completa

### 🔧 FUNCIONALIDADES ACTIVAS:
- **Filtrado automático** por tipo de ascensor
- **Adicionales que restan** dinero (6 opciones)
- **Plazo unificado** para toda la cotización
- **Interface moderna** con checkboxes
- **Cálculos en tiempo real**

### 📈 DATOS DISPONIBLES:
- **3 categorías** principales
- **55 ascensores** diferentes
- **52 adicionales** especializados
- **111 opciones** con precios configurados

## 🎉 RESULTADO ESPERADO

Una vez completados estos pasos, tendrás:

1. ✅ **Aplicación funcionando** en Railway
2. ✅ **Base de datos configurada** con todos los datos
3. ✅ **Cotizador inteligente** operativo
4. ✅ **Auto-deploy** desde GitHub (opcional)
5. ✅ **URLs públicas** para compartir

## 📞 PRÓXIMOS PASOS

1. **Ejecutar comandos Git** para subir archivo faltante
2. **Crear proyecto Railway** siguiendo la guía
3. **Configurar variables** de entorno
4. **Ejecutar setup_directo.php** para configurar BD
5. **Probar funcionalidades** del cotizador

¿Quieres que ejecute los comandos Git ahora para subir el archivo faltante? 