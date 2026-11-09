@php
    $nombre = mb_strtolower(trim($nombre ?? ''));
@endphp

<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($nombre)
        @case('bebidas')
            <path d="M10 2h4v3l2 3v14H8V8l2-3V2Z" />
            <path d="M8 11h8" />
        @break

        @case('panadería')
        @case('panaderia')
            <path d="M4 10a4 4 0 0 1 4-5h8a4 4 0 0 1 4 5v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-7Z" />
            <path d="M8 9v11M12 9v11M16 9v11" />
        @break

        @case('lácteos')
        @case('lacteos')
            <path d="M8 3h8v4l2 4v10H6V11l2-4V3Z" />
            <path d="M6 11h12" />
        @break

        @case('despensa')
            <path d="m12 3 9 5-9 5-9-5 9-5Z" />
            <path d="M3 8v8l9 5 9-5V8" />
            <path d="M12 13v8" />
        @break

        @case('verduras')
            <path d="M20 4C12 4 5 8 5 15c0 3 2 5 5 5 7 0 10-8 10-16Z" />
            <path d="M5 20c2-5 6-9 11-11" />
        @break

        @case('frutas')
            <path d="M12 7c-4-1-7 2-7 6a5 5 0 0 0 5 5c1 0 1.5-.5 2-.5s1 .5 2 .5a5 5 0 0 0 5-5c0-4-3-7-7-6Z" />
            <path d="M12 7c0-2 1-3 3-4" />
        @break

        @case('pescado')
            <path d="M3 12s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5Z" />
            <path d="M19 12c1.4 1 2 2.4 2 2.4s-.6-1.4-2-2.4Z" />
            <circle cx="8" cy="12" r="1" fill="currentColor" stroke="none" />
        @break

        @default
            <path d="M12 2v20M4.2 6.5l15.6 11M19.8 6.5l-15.6 11" />
            <path d="M12 5.5 9.8 3.8M12 5.5l2.2-1.7M12 18.5l-2.2 1.7M12 18.5l2.2 1.7" />
    @endswitch
</svg>
