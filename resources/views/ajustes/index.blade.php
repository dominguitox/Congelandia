@extends('layouts.app')

@section('title', 'Ajustes')

@section('content')
<div class="page">
    <div class="page-header">
        <div class="page-title">
            <h1>Ajustes</h1>
            <p>Personaliza la apariencia del panel en este dispositivo.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="panel" aria-labelledby="apariencia-titulo">
        <div class="panel-head">
            <div>
                <h2 id="apariencia-titulo">Apariencia</h2>
                <p>La elección se guarda en este navegador y se conserva al cerrar sesión.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('ajustes.update') }}" class="p-3 p-md-4">
            @csrf
            @method('PUT')
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <label for="theme" class="form-label">Tema</label>
                    <select id="theme" name="theme" class="form-select @error('theme') is-invalid @enderror"
                        @error('theme') aria-invalid="true" aria-describedby="theme-error" @enderror required>
                        @foreach(['congelandia' => 'Congelandia', 'md3' => 'Material Design 3', 'win7' => 'Windows 7'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('theme', $appearance['style']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('theme')<div id="theme-error" class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="color" class="form-label">Color</label>
                    <select id="color" name="color" class="form-select @error('color') is-invalid @enderror"
                        @error('color') aria-invalid="true" aria-describedby="color-error" @enderror required>
                        @foreach(['base' => 'Base (celeste)', 'esmeralda' => 'Esmeralda', 'aurora' => 'Aurora', 'coral' => 'Coral'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('color', $appearance['color']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('color')<div id="color-error" class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <p>Se aplica al panel y al inicio de sesión en este navegador. Los demás dispositivos y el sitio público mantienen su apariencia.</p>
            <button type="submit" class="btn-solid">Guardar en este dispositivo</button>
        </form>
    </section>
</div>
@endsection
