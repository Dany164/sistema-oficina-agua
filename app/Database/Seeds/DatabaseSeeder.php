<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // El orden importa: Roles antes de Usuarios (por la FK rol_id),
        // Métodos de Pago es independiente pero se agrupa aquí por conveniencia.
        $this->call('RolSeeder');
        $this->call('MetodoPagoSeeder');
        $this->call('UsuarioSeeder');
        $this->call('TipoServicioSeeder');
        $this->call('ClienteSeeder');
        $this->call('ContadorSeeder'); 
        //Ejecutar ContadorSeeder solo si la base de datos es nueva, de lo contrario habra error con el id de los clientes
    }
}