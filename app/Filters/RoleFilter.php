<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = strtolower(trim((string) session()->get('rol_nombre')));
        $allowedRoles = array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            $arguments ?? []
        );

        if (! in_array($role, $allowedRoles, true)) {
            return redirect()->to(base_url('main'))
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
