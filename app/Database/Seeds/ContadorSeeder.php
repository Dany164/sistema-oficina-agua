<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContadorSeeder extends Seeder
{
    public function run()
    {
        $contadores = [
            ['numero_registro' => 'CNT-0001', 'direccion_servicio' => 'Zona 1, Calle Principal 12',        'estado' => 1, 'cliente_id' => 1,  'tipo_servicio_id' => 1],
            ['numero_registro' => 'CNT-0002', 'direccion_servicio' => 'Zona 3, Avenida Central 45',        'estado' => 1, 'cliente_id' => 2,  'tipo_servicio_id' => 2],
            ['numero_registro' => 'CNT-0003', 'direccion_servicio' => 'Zona 2, Callejón Las Rosas 8',      'estado' => 1, 'cliente_id' => 3,  'tipo_servicio_id' => 1],
            ['numero_registro' => 'CNT-0004', 'direccion_servicio' => 'Zona 5, Colonia El Progreso 22',    'estado' => 1, 'cliente_id' => 4,  'tipo_servicio_id' => 2],
            ['numero_registro' => 'CNT-0005', 'direccion_servicio' => 'Zona 1, Barrio San José 3',         'estado' => 0, 'cliente_id' => 5,  'tipo_servicio_id' => 1],
            ['numero_registro' => 'CNT-0006', 'direccion_servicio' => 'Zona 4, Residenciales del Valle 15','estado' => 1, 'cliente_id' => 6,  'tipo_servicio_id' => 2],
            ['numero_registro' => 'CNT-0007', 'direccion_servicio' => 'Zona 2, Sector La Ceiba 9',         'estado' => 1, 'cliente_id' => 7,  'tipo_servicio_id' => 1],
            ['numero_registro' => 'CNT-0008', 'direccion_servicio' => 'Zona 3, Colonia Buenos Aires 30',   'estado' => 1, 'cliente_id' => 8,  'tipo_servicio_id' => 1],
            ['numero_registro' => 'CNT-0009', 'direccion_servicio' => 'Zona 6, Aldea San Antonio 5',       'estado' => 0, 'cliente_id' => 9,  'tipo_servicio_id' => 2],
            ['numero_registro' => 'CNT-0010', 'direccion_servicio' => 'Zona 1, Calle Real 18',             'estado' => 1, 'cliente_id' => 10, 'tipo_servicio_id' => 1],
        ];

        foreach ($contadores as $contador) {
            $existe = $this->db->table('Tb_Contadores')
                                ->where('numero_registro', $contador['numero_registro'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Contadores')->insert($contador);
            }
        }
    }
}