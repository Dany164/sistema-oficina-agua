<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TipoServicioSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            ['tipo_servicio' => '1/4 paja', 'litros_incluidos' => 15000],
            ['tipo_servicio' => '1/2 paja', 'litros_incluidos' => 60000],
            ['tipo_servicio' => 'Exceso',   'litros_incluidos' => null],
        ];

        foreach ($tipos as $tipo) {
            $existe = $this->db->table('Tb_Tipos_Servicio')
                                ->where('tipo_servicio', $tipo['tipo_servicio'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Tipos_Servicio')->insert($tipo);
            }
        }
    }
}