<?php
// Script para verificar y sugerir la instalación de mPDF

$mpdfInstallPath = __DIR__ . '/vendor/mpdf/mpdf';

if (file_exists($mpdfInstallPath)) {
    echo "<h1>✅ mPDF ya está instalado</h1>";
    echo "<p>La biblioteca mPDF está instalada en: $mpdfInstallPath</p>";
} else {
    echo "<h1>❌ mPDF no está instalado</h1>";
    echo "<p>La biblioteca mPDF es necesaria para generar PDFs. Siga estos pasos para instalarla:</p>";
    
    echo "<h2>Opción 1: Instalar con Composer (recomendado)</h2>";
    echo "<ol>";
    echo "<li>Asegúrese de tener <a href='https://getcomposer.org/download/' target='_blank'>Composer</a> instalado</li>";
    echo "<li>Abra una terminal y navegue al directorio raíz del proyecto</li>";
    echo "<li>Ejecute el comando: <code>composer require mpdf/mpdf</code></li>";
    echo "<li>Espere a que la instalación se complete</li>";
    echo "</ol>";
    
    echo "<h2>Opción 2: Instalación manual</h2>";
    echo "<ol>";
    echo "<li>Descargue mPDF desde <a href='https://github.com/mpdf/mpdf/releases' target='_blank'>GitHub</a></li>";
    echo "<li>Cree la carpeta <code>vendor/mpdf</code> en la raíz del proyecto</li>";
    echo "<li>Extraiga los archivos descargados en esta carpeta</li>";
    echo "</ol>";
    
    echo "<h2>Después de instalar</h2>";
    echo "<p>Una vez instalado, actualice el archivo api/download_pdf.php para utilizarlo.</p>";
    
    echo "<p><a href='?check'>Verificar de nuevo</a></p>";
}
?> 