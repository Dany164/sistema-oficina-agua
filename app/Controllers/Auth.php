<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login()
    {
        $data = [
            'title' => 'Login'
        ];

        return view('auth/login', $data);
    }

    public function attemptLogin()
    {
        // TODO: Cuando la base de datos esté lista, aquí va la validación
        // real del email y password contra el Model de usuarios.

        return redirect()->to(base_url('main'));
    }
}