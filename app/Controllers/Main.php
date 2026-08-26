<?php

namespace App\Controllers;

class Main extends BaseController
{
    public function index()
    {

        return view('primera_vista');
    }


   // Funcion Temporal para la plantilla, se recomienda hacer su propio Controlador
    public function tablas()
    {
    $data = [
        'title' => 'Tablas'
    ];

    return view('tablas', $data);
    }
}