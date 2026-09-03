<?php

namespace App\Models;

use CodeIgniter\Model;

class ContadorModel extends Model
{
    protected $table      = 'tb_contadores';
    protected $primaryKey = 'contador_id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'numero_registro',
        'direccion_servicio',
        'estado',
        'cliente_id',
        'tipo_servicio_id'
    ];
}