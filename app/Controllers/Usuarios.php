<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Usuarios extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        $request = $this->request;
        $buscar = trim((string) $request->getGet('buscar'));
        $rol = (int) $request->getGet('rol');
        $ordenes = ['nombre' => 'u.nombre', 'email' => 'u.email', 'rol' => 'r.nombre'];
        $orden = $ordenes[$request->getGet('orden')] ?? 'u.nombre';
        $direccion = strtolower((string) $request->getGet('direccion')) === 'desc' ? 'DESC' : 'ASC';

        $consulta = db_connect()->table('Tb_Usuarios u')
            ->select(['u.usuario_id', 'u.nombre', 'u.email', 'u.rol_id', 'u.activo', 'u.ultimo_acceso', 'r.nombre AS rol_nombre'])
            ->join('Tb_Roles r', 'r.rol_id = u.rol_id')
            ->orderBy($orden, $direccion);

        if ($buscar !== '') {
            $consulta->groupStart()
                ->like('u.nombre', $buscar)
                ->orLike('u.email', $buscar)
                ->groupEnd();
        }
        if ($rol > 0) {
            $consulta->where('u.rol_id', $rol);
        }

        return view('usuarios/index', [
            'title' => 'Usuarios',
            'usuarios' => $consulta->get()->getResultArray(),
            'roles' => $this->obtenerRoles(),
            'filtros' => compact('buscar', 'rol', 'orden', 'direccion'),
        ]);
    }

    public function new()
    {
        return view('usuarios/form', [
            'title' => 'Nuevo usuario',
            'usuario' => [
                'nombre' => '',
                'email' => '',
                'rol_id' => '',
                'activo' => 1,
            ],
            'roles' => $this->obtenerRoles(),
            'errors' => [],
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost();

        if (! $this->validarUsuario($data, true)) {
            return $this->mostrarFormularioConErrores(
                'Nuevo usuario',
                $data,
                $this->validator->getErrors()
            );
        }

        $this->usuarioModel->insert([
            'nombre' => trim($data['nombre']),
            'email' => trim($data['email']),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'rol_id' => (int) $data['rol_id'],
            'activo' => 1,
        ]);
        $this->registrarAuditoria('INSERT', (int) $this->usuarioModel->getInsertID(), null, [
            'nombre' => trim($data['nombre']), 'email' => trim($data['email']),
            'rol_id' => (int) $data['rol_id'], 'activo' => 1,
        ]);

        session()->setFlashdata('success', 'Usuario creado correctamente.');
        return redirect()->to('/usuarios');
    }

    public function edit($id)
    {
        $usuario = $this->usuarioModel->find($id);

        if (! $usuario) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usuario no encontrado.');
        }

        return view('usuarios/form', [
            'title' => 'Editar usuario',
            'usuario' => $usuario,
            'roles' => $this->obtenerRoles(),
            'errors' => [],
        ]);
    }

    public function update($id)
    {
        $usuario = $this->usuarioModel->find($id);

        if (! $usuario) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usuario no encontrado.');
        }

        $data = $this->request->getPost();

        if (! $this->validarUsuario($data, false, (int) $id)) {
            return $this->mostrarFormularioConErrores(
                'Editar usuario',
                array_merge($usuario, $data),
                $this->validator->getErrors()
            );
        }

        $actualizacion = [
            'nombre' => trim($data['nombre']),
            'email' => trim($data['email']),
            'rol_id' => (int) $data['rol_id'],
        ];

        if (! empty($data['password'])) {
            $actualizacion['password_hash'] = password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            );
        }

        $this->registrarAuditoria('UPDATE', (int) $id, $usuario, [
            'nombre' => $actualizacion['nombre'], 'email' => $actualizacion['email'],
            'rol_id' => $actualizacion['rol_id'], 'activo' => $usuario['activo'] ?? 1,
        ]);
        $this->usuarioModel->update($id, $actualizacion);

        if ((int) session()->get('usuario_id') === (int) $id) {
            session()->set([
                'nombre' => $actualizacion['nombre'],
                'rol_id' => $actualizacion['rol_id'],
                'rol_nombre' => $this->obtenerNombreRol($actualizacion['rol_id']),
            ]);
        }

        session()->setFlashdata('success', 'Usuario actualizado correctamente.');
        return redirect()->to('/usuarios');
    }

    public function delete($id)
    {
        if ((int) session()->get('usuario_id') === (int) $id) {
            return redirect()->to('/usuarios')->with(
                'error',
                'No puedes eliminar el usuario con el que estás conectado.'
            );
        }

        if (! $this->usuarioModel->find($id)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usuario no encontrado.');
        }

        if ($this->esUltimoAdministrador((int) $id)) {
            return redirect()->to('/usuarios')->with('error', 'No puedes eliminar al último administrador.');
        }

        $usuario = $this->usuarioModel->find($id);
        try {
            $this->usuarioModel->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->to('/usuarios')->with(
                'error',
                'No se puede eliminar este usuario porque tiene registros asociados.'
            );
        }
        $this->registrarAuditoria('DELETE', (int) $id, $usuario, null);

        session()->setFlashdata('success', 'Usuario eliminado correctamente.');
        return redirect()->to('/usuarios');
    }

    public function toggle($id)
    {
        $usuario = $this->usuarioModel->find($id);
        if (! $usuario) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Usuario no encontrado.');
        }
        $activo = (int) ($usuario['activo'] ?? 1);
        if ($activo === 1 && $this->esUltimoAdministrador((int) $id)) {
            return redirect()->to('/usuarios')->with('error', 'No puedes desactivar al último administrador.');
        }
        $nuevoEstado = $activo === 1 ? 0 : 1;
        $this->registrarAuditoria('UPDATE', (int) $id, $usuario, ['activo' => $nuevoEstado]);
        $this->usuarioModel->update($id, ['activo' => $nuevoEstado]);
        return redirect()->to('/usuarios')->with('success', $nuevoEstado ? 'Usuario activado.' : 'Usuario bloqueado.');
    }

    private function validarUsuario(array $data, bool $passwordRequired, ?int $id = null): bool
    {
        $emailRule = 'required|valid_email|max_length[150]';
        $emailRule .= $id === null
            ? '|is_unique[Tb_Usuarios.email]'
            : "|is_unique[Tb_Usuarios.email,usuario_id,{$id}]";

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]|trim',
            'email' => $emailRule,
            'rol_id' => 'required|integer|is_not_unique[Tb_Roles.rol_id]',
            'password' => $passwordRequired
                ? 'required|min_length[6]|max_length[72]'
                : 'permit_empty|min_length[6]|max_length[72]',
            'password_confirmacion' => $passwordRequired
                ? 'required|matches[password]'
                : 'permit_empty|matches[password]',
        ];

        return $this->validate($rules, [
            'nombre' => [
                'required' => 'El nombre es obligatorio.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
                'max_length' => 'El nombre no puede superar 100 caracteres.',
            ],
            'email' => [
                'required' => 'El correo es obligatorio.',
                'valid_email' => 'El correo no es válido.',
                'max_length' => 'El correo no puede superar 150 caracteres.',
                'is_unique' => 'Ya existe un usuario con ese correo.',
            ],
            'rol_id' => [
                'required' => 'El rol es obligatorio.',
                'integer' => 'El rol no es válido.',
                'is_not_unique' => 'El rol seleccionado no existe.',
            ],
            'password' => [
                'required' => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
                'max_length' => 'La contraseña no puede superar 72 caracteres.',
            ],
            'password_confirmacion' => [
                'required' => 'La confirmación de contraseña es obligatoria.',
                'matches' => 'Las contraseñas no coinciden.',
            ],
        ]);
    }

    private function mostrarFormularioConErrores(string $title, array $usuario, array $errors)
    {
        return view('usuarios/form', [
            'title' => $title,
            'usuario' => $usuario,
            'roles' => $this->obtenerRoles(),
            'errors' => $errors,
        ]);
    }

    private function obtenerRoles(): array
    {
        return db_connect()->table('Tb_Roles')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function obtenerNombreRol(int $id): string
    {
        $rol = db_connect()->table('Tb_Roles')
            ->where('rol_id', $id)
            ->get()
            ->getRowArray();

        return $rol['nombre'] ?? '';
    }

    private function esUltimoAdministrador(int $id): bool
    {
        $rol = db_connect()->table('Tb_Roles')->where('nombre', 'Administrador')->get()->getRowArray();
        if (! $rol) {
            return false;
        }
        return db_connect()->table('Tb_Usuarios')
            ->where('rol_id', $rol['rol_id'])
            ->where('activo', 1)
            ->where('usuario_id !=', $id)
            ->countAllResults() === 0;
    }

    private function registrarAuditoria(string $accion, int $id, ?array $anteriores, ?array $nuevos): void
    {
        db_connect()->table('Tb_Auditorias')->insert([
            'tabla' => 'Tb_Usuarios',
            'registro_id' => $id,
            'accion' => $accion,
            'datos_anteriores' => $anteriores ? json_encode($this->datosAuditables($anteriores)) : null,
            'datos_nuevos' => $nuevos ? json_encode($this->datosAuditables($nuevos)) : null,
            'usuario_id' => session()->get('usuario_id'),
        ]);
    }

    private function datosAuditables(array $datos): array
    {
        unset($datos['password_hash'], $datos['password'], $datos['password_confirmacion']);
        return $datos;
    }
}
