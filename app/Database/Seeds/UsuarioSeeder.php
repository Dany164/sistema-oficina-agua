<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $usuarios = [
            [
                'rol_id'        => 1, // Administrador
                'nombre'        => 'Administrador General',
                'email'         => 'admin@oficinaagua.com',
                'password_hash' => password_hash('CambiaEstaClave123!', PASSWORD_DEFAULT),
            ],
            [
                'rol_id'        => 2, // Secretaria
                'nombre'        => 'Maria',
                'email'         => 'maria@oficinaagua.com',
                'password_hash' => password_hash('Test123!', PASSWORD_DEFAULT),
            ],
            [
                'rol_id'        => 3, // Lector
                'nombre'        => 'Pedro',
                'email'         => 'pedro@oficinaagua.com',
                'password_hash' => password_hash('Test123!', PASSWORD_DEFAULT),
            ],
        ];

        foreach ($usuarios as $usuario) {
        $existe = $this->db->table('Tb_Usuarios')
                            ->where('email', $usuario['email'])
                            ->get()
                            ->getRow();

        if (! $existe) {
            $this->db->table('Tb_Usuarios')->insert($usuario);
        }
      }
    }
}