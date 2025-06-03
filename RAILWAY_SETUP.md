# 🚀 Configuración en Railway

Esta guía te ayudará a desplegar el Sistema de Presupuestos de Ascensores en Railway con base de datos MySQL.

## 📋 Requisitos Previos

1. Cuenta en [Railway](https://railway.app)
2. Repositorio en GitHub (ya tienes: `https://github.com/facuvar/cotizadorcompany.git`)

## 🗄️ Paso 1: Crear Base de Datos MySQL en Railway

1. **Accede a Railway** y crea un nuevo proyecto
2. **Agrega MySQL**:
   - Haz clic en "New" → "Database" → "MySQL"
   - Railway creará automáticamente una instancia de MySQL
3. **Obtén las credenciales**:
   - Ve a la pestaña "Variables" de tu base de datos MySQL
   - Copia los valores de:
     - `MYSQL_HOST`
     - `MYSQL_USER` 
     - `MYSQL_PASSWORD`
     - `MYSQL_DATABASE`
     - `MYSQL_PORT`

## 🌐 Paso 2: Desplegar la Aplicación

1. **Conectar GitHub**:
   - En el mismo proyecto, haz clic en "New" → "GitHub Repo"
   - Selecciona `facuvar/cotizadorcompany`
   - Railway detectará automáticamente que es un proyecto PHP

2. **Configurar Variables de Entorno**:
   Ve a la pestaña "Variables" de tu aplicación y agrega:

   ```
   DB_HOST=valor_de_MYSQL_HOST
   DB_USER=valor_de_MYSQL_USER  
   DB_PASS=valor_de_MYSQL_PASSWORD
   DB_NAME=valor_de_MYSQL_DATABASE
   DB_PORT=valor_de_MYSQL_PORT
   
   # Opcional: Cambiar credenciales de admin
   ADMIN_USER=admin
   ADMIN_PASS=$2y$10$szOr0zBbR/0iUpJbHGzVgOyMS3vr7/3DbqFnOJTJRKZOwjyWO/vjm
   
   # Para emails (opcional)
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=tu_email@gmail.com
   SMTP_PASS=tu_contraseña_app
   FROM_EMAIL=noreply@tuempresa.com
   FROM_NAME=Sistema de Presupuestos
   ```

3. **Desplegar**:
   - Railway desplegará automáticamente
   - Obtendrás una URL como: `https://tu-proyecto.up.railway.app`

## 🔧 Paso 3: Configurar Base de Datos

1. **Accede a tu aplicación** en la URL proporcionada por Railway
2. **Ve a** `/setup_railway.php` (crearemos este archivo)
3. **Ejecuta la configuración** automática de tablas

## 📊 Paso 4: Importar Datos

1. **Accede al panel admin**: `https://tu-proyecto.up.railway.app/admin/`
2. **Inicia sesión** con las credenciales configuradas
3. **Importa datos** desde Excel o Google Sheets

## 🔄 Desarrollo Local vs Railway

Para cambiar entre configuraciones:

```bash
# Usar configuración local (XAMPP)
php switch_config.php local

# Usar configuración Railway
php switch_config.php railway
```

## 🌍 URLs del Sistema

Una vez desplegado en Railway:

- **Página Principal**: `https://tu-proyecto.up.railway.app/`
- **Cotizador**: `https://tu-proyecto.up.railway.app/sistema/cotizador.php`
- **Panel Admin**: `https://tu-proyecto.up.railway.app/admin/`

## 🚨 Solución de Problemas

### Error de Conexión a BD
1. Verifica que las variables de entorno estén correctas
2. Asegúrate de que la base de datos MySQL esté ejecutándose
3. Revisa los logs en Railway

### Error 500
1. Revisa los logs de Railway
2. Verifica que todos los archivos se hayan subido correctamente
3. Comprueba que las extensiones PHP necesarias estén disponibles

### Archivos no se cargan
1. Verifica que los directorios de uploads tengan permisos
2. En Railway, los archivos se almacenan temporalmente

## 📝 Notas Importantes

- **Archivos temporales**: Railway reinicia los contenedores, los archivos subidos se pueden perder
- **Base de datos**: Los datos en MySQL persisten entre reinicios
- **Variables de entorno**: Siempre usa variables de entorno para credenciales sensibles
- **HTTPS**: Railway proporciona HTTPS automáticamente

## 🔐 Seguridad

- Cambia las credenciales de administrador por defecto
- Usa contraseñas seguras para la base de datos
- Configura variables de entorno para datos sensibles
- Considera usar Railway's Private Networking para mayor seguridad 