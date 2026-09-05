<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        $clientes = [
            ['nombre' => 'María Fernanda López',   'telefono' => '5511-2233', 'direccion' => 'Zona 1, Calle Principal 12'],
            ['nombre' => 'Carlos Alberto Ramírez',  'telefono' => '5522-3344', 'direccion' => 'Zona 3, Avenida Central 45'],
            ['nombre' => 'Ana Lucía Gómez',         'telefono' => '5533-4455', 'direccion' => 'Zona 2, Callejón Las Rosas 8'],
            ['nombre' => 'José Manuel Herrera',     'telefono' => '5544-5566', 'direccion' => 'Zona 5, Colonia El Progreso 22'],
            ['nombre' => 'Rosa Elvira Castillo',    'telefono' => '5555-6677', 'direccion' => 'Zona 1, Barrio San José 3'],
            ['nombre' => 'Luis Fernando Morales',   'telefono' => '5566-7788', 'direccion' => 'Zona 4, Residenciales del Valle 15'],
            ['nombre' => 'Patricia Elizabeth Ruiz', 'telefono' => '5577-8899', 'direccion' => 'Zona 2, Sector La Ceiba 9'],
            ['nombre' => 'Miguel Ángel Sandoval',   'telefono' => '5588-9900', 'direccion' => 'Zona 3, Colonia Buenos Aires 30'],
            ['nombre' => 'Claudia Beatriz Pérez',   'telefono' => '5599-0011', 'direccion' => 'Zona 6, Aldea San Antonio 5'],
            ['nombre' => 'Roberto Carlos Méndez',   'telefono' => '5500-1122', 'direccion' => 'Zona 1, Calle Real 18'],
        ];

        foreach ($clientes as $cliente) {
            $existe = $this->db->table('Tb_Clientes')
                                ->where('nombre', $cliente['nombre'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Clientes')->insert($cliente);
            }
        }
    }
}