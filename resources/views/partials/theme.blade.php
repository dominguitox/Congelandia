{{-- Load after module CSS so the selected design system controls every component. --}}
@if($appTheme['color_asset'])
    @vite([$appTheme['color_asset']])
@endif
@if($appTheme['style_asset'])
    @vite([$appTheme['style_asset']])
@endif
