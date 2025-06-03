# 🔧 Variables de Entorno para Railway

Configura estas variables en tu proyecto Railway:

## 📊 Base de Datos MySQL
```
DB_HOST=mysql.railway.internal
DB_USER=root
DB_PASS=DEACLLVQgoBvLmRKkFqUazfcOaDVwMKd
DB_NAME=railway
DB_PORT=3306
```

## 🔑 Administración (Opcional)
```
ADMIN_USER=admin
ADMIN_PASS=$2y$10$szOr0zBbR/0iUpJbHGzVgOyMS3vr7/3DbqFnOJTJRKZOwjyWO/vjm
```
*Nota: La contraseña por defecto es `admin123`*

## 📧 Email (Opcional)
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu_email@gmail.com
SMTP_PASS=tu_contraseña_app
FROM_EMAIL=noreply@tuempresa.com
FROM_NAME=Sistema de Presupuestos
```

## 📋 Google Sheets (Opcional)
```
GOOGLE_SHEETS_API_KEY=tu_api_key
GOOGLE_SHEETS_ID=tu_sheet_id
```

---

## 🚀 Pasos para configurar en Railway:

1. Ve a tu proyecto en Railway
2. Selecciona tu aplicación (no la base de datos)
3. Ve a la pestaña "Variables"
4. Agrega cada variable una por una
5. Haz redeploy del proyecto

## 🔗 URLs una vez desplegado:
- **Página Principal**: `https://tu-proyecto.up.railway.app/`
- **Cotizador**: `https://tu-proyecto.up.railway.app/sistema/cotizador.php`
- **Panel Admin**: `https://tu-proyecto.up.railway.app/admin/`
- **Setup**: `https://tu-proyecto.up.railway.app/setup_railway.php`

## ⚠️ Importante:
- Después de configurar las variables, accede a `/setup_railway.php` para configurar las tablas
- Luego ve al panel admin para importar los datos 