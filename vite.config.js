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

                'resources/css/app.css',
                'resources/css/reportes.css',

                'resources/css/theme/themes/aurora.css',
                'resources/css/theme/themes/esmeralda.css',
                'resources/css/theme/themes/coral.css',

                'resources/js/app.js',

                'resources/css/dashboard.css',

                'resources/css/inventario.css',

                'resources/css/historial.css',
                'resources/js/historial.js',

                'resources/css/clientes.css',

                'resources/css/pos.css',

                'resources/css/proveedores.css',
                'resources/js/proveedores.js',

                'resources/css/categorias.css',
                'resources/js/categorias.js',

                'resources/js/usuarios.js',

                'resources/css/auth.css',

                'resources/css/public.css',
                'resources/css/public-temas/fresco.css',
                'resources/css/public-temas/feria.css',
                'resources/css/public-temas/hielo.css',
            ],

            refresh: true,

            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Baloo 2', {
                    weights: [500, 600, 700],
                }),
                bunny('Fraunces', {
                    weights: [500, 600, 700],
                }),
                bunny('Anton', {
                    weights: [400],
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