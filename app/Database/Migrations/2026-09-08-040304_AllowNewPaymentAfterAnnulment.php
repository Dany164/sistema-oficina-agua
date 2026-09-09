<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AllowNewPaymentAfterAnnulment extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE Tb_Pagos
         ADD INDEX idx_pagos_lectura (lectura_id),
         DROP INDEX uk_pagos_lectura'
        );

        $this->db->query(
            'ALTER TABLE Tb_Pagos
         ADD COLUMN lectura_activa_id INT UNSIGNED
         GENERATED ALWAYS AS (
             CASE
                 WHEN anulado = 0 THEN lectura_id
                 ELSE NULL
             END
         ) STORED'
        );

        $this->db->query(
            'ALTER TABLE Tb_Pagos
         ADD UNIQUE INDEX uk_pagos_lectura_activa
         (lectura_activa_id)'
        );
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE Tb_Pagos
             DROP INDEX uk_pagos_lectura_activa'
        );

        $this->db->query(
            'ALTER TABLE Tb_Pagos
             DROP COLUMN lectura_activa_id'
        );

        $this->db->query(
            'ALTER TABLE Tb_Pagos
             DROP INDEX idx_pagos_lectura'
        );

        $this->db->query(
            'ALTER TABLE Tb_Pagos
             ADD UNIQUE INDEX uk_pagos_lectura (lectura_id)'
        );
    }
}
