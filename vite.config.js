import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/venta.js',
                'resources/js/producto.js',
                'resources/js/app.js',
                'resources/js/proveedores.js',
                'resources/js/categorias.js',

                'resources/css/app.css',
                'resources/css/reportes.css',
                'resources/css/historial.css',
                'resources/css/clientes.css',
                'resources/css/pos.css',
                'resources/css/proveedores.css',
                'resources/css/categorias.css',
            ],

            refresh: true,

            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),

        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: [
                '**/storage/framework/views/**'
            ],
        },
    },
});