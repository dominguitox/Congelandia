<?php

namespace App\Support;

use Illuminate\Http\Request;

class DeviceTheme
{
    public const COOKIE = 'congelandia_appearance';

    public function resolve(Request $request): array
    {
        $cookie = $request->cookie(self::COOKIE);
        $preferences = is_string($cookie) ? json_decode($cookie, true) : null;
        $preferences = is_array($preferences) ? $preferences : [];
        $styles = config('theme.styles');
        $colors = config('theme.colors');
        $style = $preferences['theme'] ?? null;
        $color = $preferences['color'] ?? null;
        $style = is_string($style) && array_key_exists($style, $styles) ? $style : config('theme.style');
        $color = is_string($color) && array_key_exists($color, $colors) ? $color : config('theme.color');

        return [
            'style' => $style,
            'color' => $color,
            'style_asset' => $styles[$style],
            'color_asset' => $colors[$color],
        ];
    }
}
