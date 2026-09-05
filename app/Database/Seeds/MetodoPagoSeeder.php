<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    public function run()
    {
        $metodos = [
            ['metodo' => 'Efectivo'],
            ['metodo' => 'Transferencia'],
            ['metodo' => 'Tarjeta'],
        ];

        foreach ($metodos as $metodo) {
            $existe = $this->db->table('Tb_Metodos_Pago')
                                ->where('metodo', $metodo['metodo'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Metodos_Pago')->insert($metodo);
            }
        }
    }
}