<?php
/**
 * Script para instalar dependencias de Composer en cPanel
 * Accede a: https://newmoon.posmoon.com.ar/install-composer.php
 * 
 * IMPORTANTE: Eliminar este archivo después de usarlo por seguridad
 */

set_time_limit(300); // 5 minutos

echo "<!DOCTYPE html>
<html>
<head>
    <title>Instalación de Composer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 5px; overflow-x: auto; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #667eea; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 Instalación de Dependencias de Composer</h1>";

// Verificar ruta de extensiones
$extensionesPath = __DIR__ . '/extensiones';

echo "<div class='step'>";
echo "<h3>Paso 1: Verificando directorios...</h3>";
if (!is_dir($extensionesPath)) {
    echo "<p class='error'>❌ Error: No se encuentra el directorio 'extensiones'</p>";
    die("</div></div></body></html>");
}
echo "<p class='success'>✅ Directorio 'extensiones' encontrado: $extensionesPath</p>";
echo "</div>";

// Verificar composer.json
$composerJsonPath = $extensionesPath . '/composer.json';
echo "<div class='step'>";
echo "<h3>Paso 2: Verificando composer.json...</h3>";
if (!file_exists($composerJsonPath)) {
    echo "<p class='error'>❌ Error: No se encuentra composer.json</p>";
    die("</div></div></body></html>");
}
echo "<p class='success'>✅ composer.json encontrado</p>";
echo "</div>";

// Intentar encontrar el ejecutable de Composer
$composerPaths = [
    '/usr/local/bin/ea-php81 /opt/cpanel/composer/bin/composer',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
    'composer'
];

$composerCmd = null;
echo "<div class='step'>";
echo "<h3>Paso 3: Buscando Composer...</h3>";

foreach ($composerPaths as $path) {
    $testCmd = "$path --version 2>&1";
    $output = shell_exec($testCmd);
    if ($output && strpos($output, 'Composer') !== false) {
        $composerCmd = $path;
        echo "<p class='success'>✅ Composer encontrado: $path</p>";
        echo "<pre>$output</pre>";
        break;
    }
}

if (!$composerCmd) {
    echo "<p class='error'>❌ No se pudo encontrar Composer en el servidor</p>";
    echo "<p class='warning'>⚠️ Debes ejecutar manualmente desde Terminal de cPanel:</p>";
    echo "<pre>cd /home/newmoon/public_html/extensiones\n/usr/local/bin/ea-php81 /opt/cpanel/composer/bin/composer install --no-dev</pre>";
    die("</div></div></body></html>");
}
echo "</div>";

// Ejecutar composer install
echo "<div class='step'>";
echo "<h3>Paso 4: Instalando dependencias...</h3>";
echo "<p>Ejecutando: <code>$composerCmd install --no-dev</code></p>";
echo "<pre>";

$output = [];
$return_var = 0;
chdir($extensionesPath);
exec("$composerCmd install --no-dev 2>&1", $output, $return_var);

foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

echo "</pre>";

if ($return_var === 0) {
    echo "<p class='success'>✅ ¡Dependencias instaladas exitosamente!</p>";
} else {
    echo "<p class='error'>❌ Error al instalar dependencias (código: $return_var)</p>";
}
echo "</div>";

// Verificar instalación
echo "<div class='step'>";
echo "<h3>Paso 5: Verificando instalación...</h3>";

$vendorPath = $extensionesPath . '/vendor';
$autoloadPath = $vendorPath . '/autoload.php';

if (file_exists($autoloadPath)) {
    echo "<p class='success'>✅ autoload.php encontrado</p>";
    
    // Verificar MercadoPago
    $mpPath = $vendorPath . '/mercadopago';
    if (is_dir($mpPath)) {
        echo "<p class='success'>✅ SDK de MercadoPago instalado</p>";
    } else {
        echo "<p class='error'>❌ SDK de MercadoPago NO encontrado</p>";
    }
} else {
    echo "<p class='error'>❌ autoload.php NO encontrado</p>";
}
echo "</div>";

echo "<div class='step'>";
echo "<h3>✅ Proceso completado</h3>";
echo "<p><strong>IMPORTANTE:</strong> Por seguridad, <span class='error'>ELIMINA este archivo (install-composer.php)</span> después de usarlo.</p>";
echo "<p>Ahora recarga tu aplicación: <a href='index.php'>Ir a la aplicación</a></p>";
echo "</div>";

echo "</div></body></html>";
?>

