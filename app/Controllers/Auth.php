<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
     public function login()
    {
        // Si ya hay sesión activa, no tiene sentido ver el login de nuevo
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('main'));
        }

        return view('auth/login', ['title' => 'Login']);
    }

    public function attemptLogin()
    {
        // 1. Validación de datos de entrada
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
        return redirect()->back()
        ->withInput()
        ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // 2. Protección contra fuerza bruta (rate limiting)
        // Máximo 5 intentos por minuto por IP+email combinados
        $throttler = service('throttler');
        if ($throttler->check(md5($this->request->getIPAddress() . $email), 5, MINUTE) === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Demasiados intentos. Espera un momento antes de volver a intentar.');
        }

        // 3. Buscar usuario activo por email
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->findByEmail($email);

        // 4. Verificar credenciales
        // el mensaje de error es EL MISMO si el email no existe o si
        // la contraseña es incorrecta. Nunca reveles cuál de los dos falló;
        // eso le dice a un atacante qué correos sí están registrados.
        if (! $usuario || ! password_verify($password, $usuario['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Correo o contraseña incorrectos.');
        }

        // 5. Regenerar el ID de sesión ANTES de autenticar
        // Previene session fixation: cualquier ID de sesión previo a este
        // punto queda invalidado.
        session()->regenerate(true);

        // 6. Guardar en sesión SOLO lo necesario. Nunca el password_hash.
        session()->set([
            'usuario_id'  => $usuario['id'],
            'nombre'      => $usuario['nombre'],
            'rol_id'      => $usuario['rol_id'],
            'logged_in'   => true,
        ]);

        return redirect()->to(base_url('main'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}