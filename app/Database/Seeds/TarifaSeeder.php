<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TarifaSeeder extends Seeder
{
    public function run()
    {
        $tipos = $this->db->table('Tb_Tipos_Servicio')->get()->getResultArray();

        $tipoPorNombre = [];
        foreach ($tipos as $tipo) {
            $tipoPorNombre[$tipo['tipo_servicio']] = $tipo['tipo_servicio_id'];
        }

        $inicioAntiguo = date('Y-m-d', strtotime('-12 months'));
        $finAntiguo    = date('Y-m-d', strtotime('-4 months -1 day'));
        $inicioActual  = date('Y-m-d', strtotime('-4 months'));

        $tarifas = [
            ['tipo' => '1/4 paja', 'monto' => 60.00,  'desde' => $inicioAntiguo, 'hasta' => $finAntiguo],
            ['tipo' => '1/4 paja', 'monto' => 75.00,  'desde' => $inicioActual,  'hasta' => null],

            ['tipo' => '1/2 paja', 'monto' => 120.00, 'desde' => $inicioAntiguo, 'hasta' => $finAntiguo],
            ['tipo' => '1/2 paja', 'monto' => 150.00, 'desde' => $inicioActual,  'hasta' => null],

            ['tipo' => 'Exceso',   'monto' => 4.00,   'desde' => $inicioAntiguo, 'hasta' => $finAntiguo],
            ['tipo' => 'Exceso',   'monto' => 5.00,   'desde' => $inicioActual,  'hasta' => null],
        ];

        foreach ($tarifas as $t) {
            $tipoServicioId = $tipoPorNombre[$t['tipo']] ?? null;

            if ($tipoServicioId === null) {
                continue;
            }

            $existe = $this->db->table('Tb_Tarifas')
                                ->where('tipo_servicio_id', $tipoServicioId)
                                ->where('vigente_desde', $t['desde'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Tarifas')->insert([
                    'tipo_servicio_id' => $tipoServicioId,
                    'monto_por_unidad' => $t['monto'],
                    'vigente_desde'    => $t['desde'],
                    'vigente_hasta'    => $t['hasta'],
                ]);
            }
        }
    }
}