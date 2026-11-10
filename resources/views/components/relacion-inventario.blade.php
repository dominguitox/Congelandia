@props(['id', 'field', 'nameField', 'label', 'noneLabel', 'newLabel', 'newNameLabel', 'items', 'modal', 'canCreate' => null, 'stockInput' => null])
@php
    $canCreate = $canCreate ?? auth()->user()->tieneRol('Administrador');
    $recover = old('_modal') === $modal;
    $selected = $recover ? old($field, '') : '';
    $creating = $canCreate && $selected === 'nuevo';
    $enabled = $creating && (!$stockInput || ($recover && old($stockInput, 0) > 0));
@endphp
<div data-relacion-inventario @if($stockInput) data-stock-input="{{ $stockInput }}" @endif>
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $field }}" class="form-select @if($recover && $errors->has($field)) is-invalid @endif" data-relacion-select aria-controls="{{ $id }}_nuevo">
        <option value="">{{ $noneLabel }}</option>
        @foreach($items as $item)
            <option value="{{ $item->getKey() }}" @selected((string) $selected === (string) $item->getKey())>{{ $item->nombre }}</option>
        @endforeach
        @if($canCreate)
            <option value="nuevo" @selected($creating)>{{ $newLabel }}</option>
        @endif
    </select>
    @if($recover)
        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @endif
    @if($canCreate)
        <div id="{{ $id }}_nuevo" class="mt-2" @if(!$creating) hidden @endif>
            <label for="{{ $id }}_nombre" class="form-label">{{ $newNameLabel }} *</label>
            <input id="{{ $id }}_nombre" name="{{ $nameField }}" type="text" maxlength="100" class="form-control @if($recover && $errors->has($nameField)) is-invalid @endif" value="{{ $recover ? old($nameField) : '' }}" data-relacion-nombre @disabled(!$enabled) @required($enabled) aria-describedby="{{ $id }}_ayuda">
            <div id="{{ $id }}_ayuda" class="form-text">{{ $stockInput ? 'Se guardará al registrar stock inicial mayor que cero.' : 'Se guardará junto con este formulario.' }}</div>
            @if($recover)
                @error($nameField)<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
        </div>
    @endif
</div>
