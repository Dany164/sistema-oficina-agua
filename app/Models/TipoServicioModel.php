<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoServicioModel extends Model
{
    protected $table      = 'Tb_Tipos_Servicio';
    protected $primaryKey = 'tipo_servicio_id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'tipo_servicio',
        'litros_incluidos'
    ];
}