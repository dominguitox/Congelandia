<?php

// Only registered assets can be selected, including when configuration is cached.
$styles = [
    'congelandia' => null,
    'md3' => 'resources/css/theme/md3.css',
];
$colors = [
    'base' => null,
    'esmeralda' => 'resources/css/theme/themes/esmeralda.css',
    'aurora' => 'resources/css/theme/themes/aurora.css',
    'coral' => 'resources/css/theme/themes/coral.css',
];

$requestedTheme = strtolower(trim((string) env('APP_TEMA', 'congelandia')));
// Older installations used APP_TEMA to select a color palette.
$legacyColor = array_key_exists($requestedTheme, $colors) ? $requestedTheme : 'base';
$requestedColor = strtolower(trim((string) env('APP_COLOR', $legacyColor)));
$style = array_key_exists($requestedTheme, $styles) ? $requestedTheme : 'congelandia';
$color = array_key_exists($requestedColor, $colors) ? $requestedColor : 'base';

return [
    'style' => $style,
    'color' => $color,
    'style_asset' => $styles[$style],
    'color_asset' => $colors[$color],
];
