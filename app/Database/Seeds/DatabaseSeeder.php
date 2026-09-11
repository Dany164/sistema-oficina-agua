<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('RolSeeder');
        $this->call('MetodoPagoSeeder');
        $this->call('UsuarioSeeder');
        $this->call('TipoServicioSeeder');
        $this->call('ClienteSeeder');
        $this->call('ContadorSeeder');
        $this->call('TarifaSeeder');
        $this->call('LecturaSeeder');
        $this->call('PagoSeeder');
    }
}