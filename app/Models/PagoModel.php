<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table = 'Tb_Pagos';
    protected $primaryKey = 'pago_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'monto',
        'fecha_pago',
        'numero_recibo',
        'lectura_id',
        'usuario_id',
        'metodos_pago_id',
        'observaciones',
        'anulado',
        'anulado_at',
        'anulado_por',
    ];
    protected $useTimestamps = false;
}
