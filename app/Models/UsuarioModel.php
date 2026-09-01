<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'Tb_Usuarios';
    protected $primaryKey       = 'usuario_id';
    protected $allowedFields    = ['rol_id', 'nombre', 'email', 'password_hash'];

    protected $returnType       = 'array';

    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[100]',
        'email'  => 'required|valid_email|is_unique[Tb_Usuarios.email,usuario_id,{usuario_id}]',
    ];

    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }
}