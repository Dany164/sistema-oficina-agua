<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContadorSeeder extends Seeder
{
    public function run()
    {
        // Obtener clientes directamente con sus datos completos
        $clientes = $this->db->table('Tb_Clientes')->get()->getResultArray();

        // Mapear tipos de servicio por nombre -> tipo_servicio_id
        $tipos = $this->db->table('Tb_Tipos_Servicio')->get()->getResultArray();
        $tipoPorNombre = [];
        foreach ($tipos as $t) {
            $tipoPorNombre[$t['tipo_servicio']] = $t['tipo_servicio_id'];
        }

        $idCuartoPaja = $tipoPorNombre['1/4 paja'] ?? null;
        $idMediaPaja  = $tipoPorNombre['1/2 paja'] ?? null;

        $contadores = [];
        $numero = 1;

        foreach ($clientes as $i => $cliente) {
            // Alterna el tipo de servicio entre 1/4 y 1/2 paja
            $tipoServicioId = ($i % 2 === 0) ? $idCuartoPaja : $idMediaPaja;

            if ($tipoServicioId === null) {
                continue; // Por si algún tipo de servicio no existe todavía
            }

            // 2 de cada 10 contadores quedan inactivos, para variedad
            $estado = ($i % 10 === 9) ? 0 : 1;

            // Extrae la dirección directamente del registro del cliente
            $direccionCliente = $cliente['direccion'] ?? 'Dirección sin registrar';

            $contadores[] = [
                'numero_registro'    => 'CNT-' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
                'direccion_servicio' => $direccionCliente,
                'estado'             => $estado,
                'cliente_id'         => $cliente['cliente_id'],
                'tipo_servicio_id'   => $tipoServicioId,
            ];

            $numero++;
        }

        foreach ($contadores as $contador) {
            $existe = $this->db->table('Tb_Contadores')
                                ->where('numero_registro', $contador['numero_registro'])
                                ->get()
                                ->getRow();

            if (! $existe) {
                $this->db->table('Tb_Contadores')->insert($contador);
            }
        }
    }
}