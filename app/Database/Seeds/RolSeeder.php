<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['nombre' => 'Administrador'],
            ['nombre' => 'Secretaria'],
            ['nombre' => 'Lector'],
        ];

        foreach ($roles as $rol) {
            $existe = $this->db->table('Tb_Roles')
                                ->where('nombre', $rol['nombre'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Roles')->insert($rol);
            }
        }
    }
}