<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PagoSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        $usuarioPago = $db->table('Tb_Usuarios')
                           ->where('email', 'maria@oficinaagua.com')
                           ->get()
                           ->getRowArray();

        if (! $usuarioPago) {
            $usuarioPago = $db->table('Tb_Usuarios')->get()->getRowArray();
        }

        $usuarioId = $usuarioPago['usuario_id'];

        $metodos = $db->table('Tb_Metodos_Pago')->get()->getResultArray();
        if (empty($metodos)) {
            return;
        }

        $lecturas = $db->table('Tb_Lecturas l')
                       ->select('l.lectura_id, l.contador_id, l.monto_total, l.fecha')
                       ->orderBy('l.contador_id', 'ASC')
                       ->orderBy('l.fecha', 'ASC')
                       ->get()
                       ->getResultArray();

        // Agrupa lecturas por contador, para saber cuál es la "última" de cada uno
        $porContador = [];
        foreach ($lecturas as $lectura) {
            $porContador[$lectura['contador_id']][] = $lectura;
        }

        $contadorIndex = 0;

        foreach ($porContador as $contadorId => $lecturasDelContador) {
            // 1 de cada 3 contadores deja su última lectura sin pagar
            $dejarUltimaPendiente = ($contadorIndex % 3 === 0);
            $contadorIndex++;

            $totalLecturas = count($lecturasDelContador);

            foreach ($lecturasDelContador as $i => $lectura) {
                $esLaUltima = ($i === $totalLecturas - 1);

                if ($esLaUltima && $dejarUltimaPendiente) {
                    continue; // se deja sin pago a propósito
                }

                $existePago = $db->table('Tb_Pagos')
                                  ->where('lectura_id', $lectura['lectura_id'])
                                  ->get()
                                  ->getRow();

                if ($existePago) {
                    continue;
                }

                $metodo   = $metodos[array_rand($metodos)];
                $fechaPago = date('Y-m-d', strtotime($lectura['fecha'] . ' +5 days'));

                $db->table('Tb_Pagos')->insert([
                    'monto'           => $lectura['monto_total'],
                    'fecha_pago'      => $fechaPago,
                    'numero_recibo'   => 'REC-' . str_pad((string) $lectura['lectura_id'], 6, '0', STR_PAD_LEFT),
                    'lectura_id'      => $lectura['lectura_id'],
                    'usuario_id'      => $usuarioId,
                    'metodos_pago_id' => $metodo['metodos_pago_id'],
                    'anulado'         => 0,
                ]);
            }
        }
    }
}