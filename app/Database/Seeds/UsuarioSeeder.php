<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
                $data = [
            'rol_id'        => 1, // Administrador
            'nombre'        => 'Administrador General',
            'email'         => 'admin@oficinaagua.com',
            'password_hash' => password_hash('CambiaEstaClave123!', PASSWORD_DEFAULT),

        ];

        $this->db->table('Tb_Usuarios')->insert($data);
    }
}
