@extends('layouts.app')

@section('title', 'Iniciar sesión')

@push('css')
    @vite(['resources/css/auth.css'])
@endpush

@section('content')

<div class="page auth-page">

    <div class="auth-card">

        <div class="auth-brand">
            <div class="auth-logo">❄️</div>
            <h1>Congelandia</h1>
            <p>El mundo de los congelados</p>
        </div>

        <form method="POST" action="/login" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="email" placeholder="correo@congelandia.cl"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="••••••••"
                    class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <label class="auth-remember">
                <input type="checkbox" name="remember">
                <span>Recordarme</span>
            </label>

            <button type="submit" class="btn-solid auth-submit">Iniciar sesión</button>

        </form>

    </div>

</div>

@endsection
