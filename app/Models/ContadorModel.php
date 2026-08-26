<?php

namespace App\Models;

use CodeIgniter\Model;

class ContadorModel extends Model
{
    protected $table = 'contadores';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'servicio_id',
        'numero_serie',
        'lectura_inicial',
        'fecha_instalacion',
        'fecha_retiro',
        'activo'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}