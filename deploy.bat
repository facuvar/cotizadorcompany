@echo off
echo.
echo ========================================
echo 🚀 SCRIPT DE DESPLIEGUE AUTOMATIZADO
echo ========================================
echo.

REM Verificar si estamos en un repositorio Git
if not exist ".git" (
    echo ❌ No se detectó un repositorio Git
    echo 📝 Inicializando repositorio...
    git init
    echo ✅ Repositorio Git inicializado
    echo.
)

REM Agregar archivos al staging
echo 📂 Agregando archivos al staging...
git add .
echo ✅ Archivos agregados
echo.

REM Solicitar mensaje de commit
set /p commit_msg="💬 Ingresa el mensaje del commit: "
if "%commit_msg%"=="" set commit_msg="Actualización del cotizador inteligente"

REM Hacer commit
echo 📝 Realizando commit...
git commit -m "%commit_msg%"
echo ✅ Commit realizado
echo.

REM Verificar si existe el remote origin
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo ❌ No se detectó remote origin
    set /p repo_url="🔗 Ingresa la URL del repositorio GitHub: "
    git remote add origin !repo_url!
    echo ✅ Remote origin agregado
    echo.
)

REM Push a GitHub
echo 🚀 Subiendo a GitHub...
git push -u origin main
if errorlevel 1 (
    echo ⚠️  Intentando con master...
    git push -u origin master
)
echo ✅ Código subido a GitHub
echo.

REM Ejecutar sincronización con Railway
echo 🚂 Sincronizando con Railway...
php deploy_railway.php
echo.

REM Mostrar resumen
echo ========================================
echo 🎉 DESPLIEGUE COMPLETADO
echo ========================================
echo ✅ Código subido a GitHub
echo ✅ Base de datos sincronizada con Railway
echo ✅ Cotizador inteligente desplegado
echo.
echo 🌐 Tu aplicación está lista en producción!
echo.

pause 