<?php

namespace App\Http\Controllers;

use App\Support\DeviceTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Cookie;

class AjustesController extends Controller
{
    public function index(Request $request, DeviceTheme $theme): View
    {
        return view('ajustes.index', ['appearance' => $theme->resolve($request)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $preferences = $request->validate([
            'theme' => ['required', 'string', Rule::in(array_keys(config('theme.styles')))],
            'color' => ['required', 'string', Rule::in(array_keys(config('theme.colors')))],
        ], attributes: ['theme' => 'tema', 'color' => 'color']);

        // A persistent, encrypted cookie belongs to this browser, not the account.
        $cookie = Cookie::create(DeviceTheme::COOKIE)
            ->withValue(json_encode($preferences, JSON_THROW_ON_ERROR))
            ->withExpires(now()->addYear())
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);

        return redirect()->route('ajustes.index')
            ->withCookie($cookie)
            ->with('success', 'Tema y color guardados en este dispositivo.');
    }
}
