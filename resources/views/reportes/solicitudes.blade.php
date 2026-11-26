@extends('layouts.app')
@section('title', 'Solicitudes a proveedores')
@push('css')
    @vite(['resources/css/reportes.css', 'resources/js/solicitudes-proveedor.js'])
@endpush
@section('content')
<div class="page reportes-page">
    <div class="page-header"><div class="page-title"><h1>Revisar solicitudes</h1><p>Edita cada mensaje antes de abrir WhatsApp o tu aplicación de correo. No se guarda ni se envía automáticamente.</p></div></div>
    <p><a class="btn-outline" href="{{ route('reportes.index', ['seccion' => 'inventario']) }}">Volver al inventario</a></p>
    @foreach($solicitudes as $solicitud)
        @php
            $proveedor = $solicitud['proveedor'];
            $mensaje = "Hola {$proveedor->nombre}, quisiera solicitar disponibilidad para los siguientes productos:\n";
            foreach ($solicitud['lineas'] as $linea) {
                $mensaje .= "- {$linea['producto']->nombre} ({$linea['producto']->codigo}): {$linea['cantidad']} unidades\n";
            }
            $mensaje .= "Muchas gracias.";
            $telefono = preg_replace('/[\s()+.\-]/', '', $proveedor->telefono ?? '');
            $telefonoValido = preg_match('/^[1-9][0-9]{7,14}$/', $telefono)
                && (str_starts_with(trim($proveedor->telefono ?? ''), '+') || preg_match('/^56[0-9]{9}$/', $telefono));
            $correoValido = filter_var($proveedor->correo, FILTER_VALIDATE_EMAIL);
        @endphp
        <section class="panel solicitud-mensaje" data-telefono="{{ $telefonoValido ? $telefono : '' }}" data-correo="{{ $correoValido ? $proveedor->correo : '' }}">
            <div class="panel-head"><h2>{{ $proveedor->nombre }}</h2></div>
            <div class="panel-body">
                <label for="mensaje-{{ $loop->index }}">Mensaje para {{ $proveedor->nombre }}</label>
                <textarea class="form-control" id="mensaje-{{ $loop->index }}" rows="7">{{ $mensaje }}</textarea>
                <div class="solicitud-acciones">
                    @if($telefonoValido)<a class="btn-solid solicitud-whatsapp" href="https://wa.me/{{ $telefono }}?text={{ rawurlencode($mensaje) }}" target="_blank" rel="noopener noreferrer">Abrir WhatsApp</a>@else<p>Sin teléfono válido con código de país para WhatsApp.</p>@endif
                    @if($correoValido)<a class="btn-outline solicitud-correo" href="mailto:{{ $proveedor->correo }}?subject={{ rawurlencode('Solicitud de productos') }}&body={{ rawurlencode($mensaje) }}">Redactar correo</a>@else<p>Sin correo válido registrado.</p>@endif
                </div>
                <noscript>Para incluir tus cambios en los enlaces, activa JavaScript o copia el mensaje.</noscript>
            </div>
        </section>
    @endforeach
</div>
@endsection
