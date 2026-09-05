<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserStatusAndLastAccess extends Migration
{
    public function up()
    {
        $fields = [];

        if (! $this->db->fieldExists('activo', 'Tb_Usuarios')) {
            $fields['activo'] = [
                'type'       => 'BOOLEAN',
                'default'    => 1,
                'null'       => false,
                'after'      => 'rol_id',
            ];
        }

        if (! $this->db->fieldExists('ultimo_acceso', 'Tb_Usuarios')) {
            $fields['ultimo_acceso'] = [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'activo',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('Tb_Usuarios', $fields);
        }
    }

    public function down()
    {
        $fields = [];

        if ($this->db->fieldExists('ultimo_acceso', 'Tb_Usuarios')) {
            $fields[] = 'ultimo_acceso';
        }
        if ($this->db->fieldExists('activo', 'Tb_Usuarios')) {
            $fields[] = 'activo';
        }

        if ($fields !== []) {
            $this->forge->dropColumn('Tb_Usuarios', $fields);
        }
    }
}
