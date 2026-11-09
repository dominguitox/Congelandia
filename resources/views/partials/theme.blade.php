{{-- Load after module CSS so the selected design system controls every component. --}}
@if(config('theme.color_asset'))
    @vite([config('theme.color_asset')])
@endif
@if(config('theme.style_asset'))
    @vite([config('theme.style_asset')])
@endif
