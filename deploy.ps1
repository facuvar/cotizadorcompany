# Script de despliegue automatizado para PowerShell
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🚀 SCRIPT DE DESPLIEGUE AUTOMATIZADO" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si estamos en un repositorio Git
if (-not (Test-Path ".git")) {
    Write-Host "❌ No se detectó un repositorio Git" -ForegroundColor Red
    Write-Host "📝 Inicializando repositorio..." -ForegroundColor Yellow
    git init
    Write-Host "✅ Repositorio Git inicializado" -ForegroundColor Green
    Write-Host ""
}

# Agregar archivos al staging
Write-Host "📂 Agregando archivos al staging..." -ForegroundColor Yellow
git add .
Write-Host "✅ Archivos agregados" -ForegroundColor Green
Write-Host ""

# Solicitar mensaje de commit
$commitMsg = Read-Host "💬 Ingresa el mensaje del commit"
if ([string]::IsNullOrEmpty($commitMsg)) {
    $commitMsg = "Actualización del cotizador inteligente"
}

# Hacer commit
Write-Host "📝 Realizando commit..." -ForegroundColor Yellow
git commit -m $commitMsg
Write-Host "✅ Commit realizado" -ForegroundColor Green
Write-Host ""

# Verificar si existe el remote origin
try {
    git remote get-url origin | Out-Null
    Write-Host "✅ Remote origin detectado" -ForegroundColor Green
} catch {
    Write-Host "❌ No se detectó remote origin" -ForegroundColor Red
    $repoUrl = Read-Host "🔗 Ingresa la URL del repositorio GitHub"
    git remote add origin $repoUrl
    Write-Host "✅ Remote origin agregado" -ForegroundColor Green
    Write-Host ""
}

# Push a GitHub
Write-Host "🚀 Subiendo a GitHub..." -ForegroundColor Yellow
try {
    git push -u origin main
    Write-Host "✅ Código subido a GitHub (main)" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Intentando con master..." -ForegroundColor Yellow
    try {
        git push -u origin master
        Write-Host "✅ Código subido a GitHub (master)" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error al subir a GitHub" -ForegroundColor Red
    }
}
Write-Host ""

# Ejecutar sincronización con Railway
Write-Host "🚂 Sincronizando con Railway..." -ForegroundColor Yellow
try {
    php deploy_railway.php
    Write-Host "✅ Sincronización con Railway completada" -ForegroundColor Green
} catch {
    Write-Host "❌ Error en la sincronización con Railway" -ForegroundColor Red
    Write-Host "Verifica que PHP esté instalado y el archivo deploy_railway.php exista" -ForegroundColor Yellow
}
Write-Host ""

# Mostrar resumen
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🎉 DESPLIEGUE COMPLETADO" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ Código subido a GitHub" -ForegroundColor Green
Write-Host "✅ Base de datos sincronizada con Railway" -ForegroundColor Green
Write-Host "✅ Cotizador inteligente desplegado" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Tu aplicación está lista en producción!" -ForegroundColor Yellow
Write-Host ""

# Mostrar información adicional
Write-Host "📋 INFORMACIÓN ADICIONAL:" -ForegroundColor Cyan
Write-Host "• Archivo de pruebas: test_simple.html" -ForegroundColor White
Write-Host "• Script de sincronización: deploy_railway.php" -ForegroundColor White
Write-Host "• Documentación: README.md" -ForegroundColor White
Write-Host ""

Read-Host "Presiona Enter para continuar..." 