<?php

namespace App\Http\Controllers;

use App\Services\HistorialService;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function index(Request $request, HistorialService $historial)
    {
        return view('historial.index', $historial->datos($request));
    }
}
