<?php
namespace App\Http\Controllers;

use App\Models\Version;

class VersionController extends Controller
{
    public function index()
    {
        $version = Version::where('activa', 1)->latest('id')->first();

        return view('dashboard', compact('version'));
    }
}