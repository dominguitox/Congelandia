<?php

return [

    'contacto' => [
        'telefono' => env('PUBLIC_TELEFONO', '+56 9 1234 5678'),
        'whatsapp' => env('PUBLIC_WHATSAPP', '56912345678'),
        'email' => env('PUBLIC_EMAIL', 'contacto@congelandia.cl'),
        'direccion' => env('PUBLIC_DIRECCION', 'Santiago, Chile'),
        'horario' => env('PUBLIC_HORARIO', 'Lunes a sábado, 10:00 a 20:00'),
        'instagram' => env('PUBLIC_INSTAGRAM', ''),
    ],

    /*
    |------------------------------------------------------------------
    | Estilos del sitio publico
    |------------------------------------------------------------------
    | Permite comparar propuestas visuales sin tocar el panel interno.
    | Cada estilo vive en resources/css/public-temas/<estilo>.css
    */

    'estilo_default' => env('PUBLIC_ESTILO', 'fresco'),

    // Muestra la barra flotante para alternar entre estilos.
    // Dejar en false al publicar el sitio definitivo.
    'selector_estilos' => env('PUBLIC_SELECTOR_ESTILOS', true),

];
