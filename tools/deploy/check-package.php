<?php

// Prueba la distribución real, con vendor de producción y una BD temporal.
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$root = $argv[1];
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$metadata = json_decode(file_get_contents($root.'/release.json'), true, flags: JSON_THROW_ON_ERROR);
if (realpath($app->publicPath()) !== realpath($root.'/'.$metadata['public_directory'])) {
    throw new RuntimeException('Laravel usa una carpeta pública incorrecta.');
}
$kernel = $app->make(Kernel::class);
foreach (['/', '/catalogo', '/contacto', '/login'] as $url) {
    $request = Request::create($url);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() !== 200) {
        throw new RuntimeException("La distribución no puede servir {$url}: ".$response->getStatusCode());
    }
    $kernel->terminate($request, $response);
}
if (\App\Models\Version::actual()?->numero !== $metadata['numero']) {
    throw new RuntimeException('La versión visible no coincide con la entrega.');
}
echo "Distribución comprobada: rutas, estilos y versión.\n";
