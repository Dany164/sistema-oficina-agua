<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifaModel extends Model
{
    protected $table = 'Tb_Tarifas';
    protected $primaryKey = 'tarifa_id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $protectedFields = true;



    protected $allowedFields = [
        'monto_por_unidad',
        'vigente_desde',
        'vigente_hasta',
        'tipo_servicio_id',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'monto_por_unidad' => 'required|decimal|greater_than[0]',
        'vigente_desde' => 'required|valid_date[Y-m-d]',
        'vigente_hasta' => 'permit_empty|valid_date[Y-m-d]',
        'tipo_servicio_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'monto_por_unidad' => [
            'required' => 'El monto por unidad es obligatorio.',
            'decimal' => 'El monto por unidad debe ser un valor decimal válido.',
            'greater_than' => 'El monto por unidad debe ser mayor que cero.',
        ],
        'vigente_desde' => [
            'required' => 'La fecha de inicio de vigencia es obligatoria.',
            'valid_date' => 'La fecha de vigencia desde debe tener un formato válido (YYYY-MM-DD).',
        ],
        'vigente_hasta' => [
            'valid_date' => 'La fecha de vigencia hasta debe tener un formato válido (YYYY-MM-DD).',
        ],
        'tipo_servicio_id' => [
            'required' => 'El ID del tipo de servicio es obligatorio.',
            'integer' => 'El ID del tipo de servicio debe ser un número entero.',
        ],
    ];
}
