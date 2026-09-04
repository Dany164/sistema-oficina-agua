<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'Tb_Clientes';
    protected $primaryKey       = 'cliente_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'telefono',
        'direccion',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nombre' => 'required|max_length[100]|trim',
        'telefono' => 'permit_empty|max_length[20]|regex_match[/^(\+502\s?)?[0-9\s\-\(\)]{8,20}$/]',
        'direccion' => 'required|max_length[255]|trim',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del cliente es obligatorio.',
            'max_length' => 'El nombre no puede superar los 100 caracteres.',
        ],
        'telefono' => [
            'regex_match' => 'El teléfono no es válido. Ejemplo: 4545-6789 o +502 4545-6789.',
            'max_length' => 'El teléfono es demasiado largo.',
        ],
        'direccion' => [
            'required' => 'La dirección del cliente es obligatoria.',
            'max_length' => 'La dirección no puede superar los 255 caracteres.',
        ],
    ];

    public function normalizeTelefono(?string $telefono): string
    {
        if ($telefono === null) {
            return '';
        }

        $telefono = trim($telefono);

        if ($telefono === '') {
            return '';
        }

        $prefix = '';

        if (preg_match('/^\+?\s*502\s*/', $telefono)) {
            $prefix = '+502 ';
            $telefono = preg_replace('/^\+?\s*502\s*/', '', $telefono);
        }

        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        if (strlen($telefono) === 8) {
            return $prefix . substr($telefono, 0, 4) . '-' . substr($telefono, 4, 4);
        }

        if ($prefix !== '') {
            return $prefix . preg_replace('/(\d{4})(\d{4})$/', '$1-$2', $telefono);
        }

        return $telefono;
    }

    public function isValidTelefono(?string $telefono): bool
    {
        if ($telefono !== null && trim($telefono) !== '' && $this->normalizeTelefono($telefono) === '') {
            return false;
        }

        $telefono = $this->normalizeTelefono($telefono);

        if ($telefono === '') {
            return true;
        }

        return preg_match('/^(\+502\s)?\d{4}-\d{4}$/', $telefono) === 1;
    }
}
