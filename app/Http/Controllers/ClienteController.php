<?php

namespace App\Http\Controllers;


use App\Models\Cliente;


class ClienteController extends Controller
{


    public function index()
    {


        return view('clientes.index');


    }


}