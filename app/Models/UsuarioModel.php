<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['rol_id', 'nombre', 'email', 'password_hash', 'activo'];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Nunca se debe devolver el hash en un array de resultados por accidente
    protected $returnType       = 'array';

    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[120]',
        'email'  => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
    ];

    public function findByEmail(string $email)
    {
        return $this->where('email', $email)
                     ->where('activo', 1)
                     ->first();
    }
}