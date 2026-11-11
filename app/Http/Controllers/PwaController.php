<?php

namespace App\Http\Controllers;

class PwaController extends Controller
{
    public function index()
    {
        // Esta vista es pública y genérica: no contiene usuario, CSRF ni datos
        // privados. Es el único HTML que puede almacenar el Service Worker.
        return response()->view('offline.index')->header('Cache-Control', 'no-cache');
    }

    public function worker()
    {
        $manifestPath = public_path('build/manifest.json');
        abort_unless(is_file($manifestPath), 503, 'Compila los recursos antes de preparar el modo offline.');
        $manifestText = file_get_contents($manifestPath);
        $manifest = json_decode($manifestText, true, flags: JSON_THROW_ON_ERROR);
        $assets = ['/offline', '/offline.webmanifest', '/offline-icon.svg', '/offline-icon-192.png', '/offline-icon-512.png'];
        $visitados = [];
        $visitar = function (string $key) use (&$visitar, &$visitados, &$assets, $manifest) {
            if (isset($visitados[$key])) {
                return;
            }
            $visitados[$key] = true;
            $entry = $manifest[$key];
            $assets[] = '/build/'.$entry['file'];
            foreach ($entry['css'] ?? [] as $css) {
                $assets[] = '/build/'.$css;
            }
            foreach ($entry['imports'] ?? [] as $import) {
                $visitar($import);
            }
        };
        $visitar('resources/js/offline/index.js');
        $source = file_get_contents(resource_path('js/offline/worker.js'));
        $version = substr(hash('sha256', $manifestText.$source.file_get_contents(resource_path('views/offline/index.blade.php')).file_get_contents(public_path('offline.webmanifest'))), 0, 20);
        $script = str_replace(['__CACHE_NAME__', '__ASSETS__'], [json_encode('congelandia-shell-'.$version), json_encode(array_values(array_unique($assets)))], $source);

        return response($script)->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/')->header('Cache-Control', 'no-cache');
    }
}
