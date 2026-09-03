<?php

namespace App\Models;

use CodeIgniter\Model;

class LecturaModel extends Model
{
    protected $table = 'Tb_Lecturas';
    protected $primaryKey = 'lectura_id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $protectFields = true;



    protected $allowedFields = [
        'lectura_anterior',
        'lectura_actual',
        'consumo_litros',
        'litros_exceso',
        'monto_base',
        'monto_exceso',
        'monto_total',
        'fecha',
        'contador_id',
        'usuario_lector_id',
        'tarifa_base_id',
        'tarifa_exceso_id',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'lectura_anterior' => 'required|integer|greater_than_equal_to[0]',
        'lectura_actual'   => 'required|integer|greater_than_equal_to[0]',
        'consumo_litros'   => 'required|integer|greater_than_equal_to[0]',
        'litros_exceso'    => 'permit_empty|integer|greater_than_equal_to[0]',
        'monto_base'       => 'required|decimal|greater_than_equal_to[0]',
        'monto_exceso'     => 'permit_empty|decimal|greater_than_equal_to[0]',
        'monto_total'      => 'required|decimal|greater_than_equal_to[0]',
        'fecha'            => 'required|valid_date[Y-m-d]',
        'contador_id'      => 'required|integer',
        'usuario_lector_id' => 'required|integer',
        'tarifa_base_id'   => 'required|integer',
        'tarifa_exceso_id' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'lectura_anterior' => [
            'required'            => 'La lectura anterior es obligatoria.',
            'integer'             => 'La lectura anterior debe ser un número entero.',
            'greater_than_equal_to' => 'La lectura anterior no puede ser negativa.',
        ],
        'lectura_actual' => [
            'required'            => 'La lectura actual es obligatoria.',
            'integer'             => 'La lectura actual debe ser un número entero.',
            'greater_than_equal_to' => 'La lectura actual no puede ser negativa.',
        ],
        'consumo_litros' => [
            'required'            => 'El consumo es obligatorio.',
            'integer'             => 'El consumo debe ser un número entero.',
            'greater_than_equal_to' => 'El consumo no puede ser negativo.',
        ],
        'litros_exceso' => [
            'integer'             => 'Los litros de exceso deben ser un número entero.',
            'greater_than_equal_to' => 'Los litros de exceso no pueden ser negativos.',
        ],
        'monto_base' => [
            'required'            => 'El monto base es obligatorio.',
            'decimal'             => 'El monto base debe ser un valor decimal válido.',
            'greater_than_equal_to' => 'El monto base no puede ser negativo.',
        ],
        'monto_exceso' => [
            'decimal'             => 'El monto de exceso debe ser un valor decimal válido.',
            'greater_than_equal_to' => 'El monto de exceso no puede ser negativo.',
        ],
        'monto_total' => [
            'required'            => 'El monto total es obligatorio.',
            'decimal'             => 'El monto total debe ser un valor decimal válido.',
            'greater_than_equal_to' => 'El monto total no puede ser negativo.',
        ],
        'fecha' => [
            'required'   => 'La fecha de la lectura es obligatoria.',
            'valid_date' => 'La fecha de la lectura no es válida.',
        ],
        'contador_id' => [
            'required' => 'El contador es obligatorio.',
            'integer'  => 'El contador seleccionado no es válido.',
        ],
        'usuario_lector_id' => [
            'required' => 'El usuario lector es obligatorio.',
            'integer'  => 'El usuario lector no es válido.',
        ],
        'tarifa_base_id' => [
            'required' => 'La tarifa base es obligatoria.',
            'integer'  => 'La tarifa base no es válida.',
        ],
        'tarifa_exceso_id' => [
            'integer' => 'La tarifa de exceso no es válida.',
        ],
    ];
}
