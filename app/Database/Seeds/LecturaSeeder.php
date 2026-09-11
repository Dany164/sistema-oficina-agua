<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LecturaSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // Usuario que "realiza" las lecturas históricas (para auditoría)
        $lector = $db->table('Tb_Usuarios')
                     ->where('email', 'pedro@oficinaagua.com')
                     ->get()
                     ->getRowArray();

        if (! $lector) {
            $lector = $db->table('Tb_Usuarios')->get()->getRowArray();
        }

        $usuarioLectorId = $lector['usuario_id'];

        // Contadores activos, con su tipo de servicio
        $contadores = $db->table('Tb_Contadores c')
                          ->select('c.contador_id, c.tipo_servicio_id, ts.litros_incluidos, ts.tipo_servicio')
                          ->join('Tb_Tipos_Servicio ts', 'ts.tipo_servicio_id = c.tipo_servicio_id')
                          ->where('c.estado', 1)
                          ->get()
                          ->getResultArray();

        // Tipo "Exceso" y sus tarifas
        $tipoExceso = $db->table('Tb_Tipos_Servicio')
                          ->where('tipo_servicio', 'Exceso')
                          ->get()
                          ->getRowArray();

        foreach ($contadores as $index => $contador) {
            // Distribuye en 3 categorías: normal, atrasado, nunca leído
            $categoria = $index % 5;

            if ($categoria === 0) {
                continue; // "nunca leído" — no se generan lecturas
            }

            $mesesAtras = ($categoria === 1)
                ? [8, 7, 6, 5, 4, 3]  // "atrasado": deja de leerse hace 3 meses
                : [8, 7, 6, 5, 4, 3, 2, 1]; // "normal": hasta el mes pasado

            $litrosIncluidos = $contador['litros_incluidos'] !== null
                ? (int) $contador['litros_incluidos']
                : null;

            $lecturaAnterior = rand(500, 2000); // punto de partida del contador

            foreach ($mesesAtras as $meses) {
                $fecha = date('Y-m-15', strtotime("-{$meses} months"));

                // Ya existe esta lectura, se omite (idempotencia)
                $existe = $db->table('Tb_Lecturas')
                             ->where('contador_id', $contador['contador_id'])
                             ->where('fecha', $fecha)
                             ->get()
                             ->getRow();

                if ($existe) {
                    $lecturaAnterior = (int) $existe->lectura_actual;
                    continue;
                }

                // Consumo mensual simulado, según el tipo de servicio
                $consumo = ($litrosIncluidos !== null && $litrosIncluidos <= 15000)
                    ? rand(8000, 16000)   // 1/4 paja
                    : rand(30000, 65000); // 1/2 paja

                $lecturaActual = $lecturaAnterior + $consumo;

                // Tarifa base vigente en esa fecha
                $tarifaBase = $db->table('Tb_Tarifas')
                                  ->where('tipo_servicio_id', $contador['tipo_servicio_id'])
                                  ->where('vigente_desde <=', $fecha)
                                  ->groupStart()
                                      ->where('vigente_hasta >=', $fecha)
                                      ->orWhere('vigente_hasta IS NULL', null, false)
                                  ->groupEnd()
                                  ->orderBy('vigente_desde', 'DESC')
                                  ->get()
                                  ->getRowArray();

                if (! $tarifaBase) {
                    $lecturaAnterior = $lecturaActual;
                    continue; // sin tarifa vigente para esa fecha, se omite
                }

                $montoBase      = (float) $tarifaBase['monto_por_unidad'];
                $litrosExceso   = null;
                $montoExceso    = null;
                $tarifaExcesoId = null;

                if ($litrosIncluidos !== null && $consumo > $litrosIncluidos && $tipoExceso) {
                    $litrosExceso = $consumo - $litrosIncluidos;

                    $tarifaExceso = $db->table('Tb_Tarifas')
                                        ->where('tipo_servicio_id', $tipoExceso['tipo_servicio_id'])
                                        ->where('vigente_desde <=', $fecha)
                                        ->groupStart()
                                            ->where('vigente_hasta >=', $fecha)
                                            ->orWhere('vigente_hasta IS NULL', null, false)
                                        ->groupEnd()
                                        ->orderBy('vigente_desde', 'DESC')
                                        ->get()
                                        ->getRowArray();

                    if ($tarifaExceso) {
                        $tarifaExcesoId = $tarifaExceso['tarifa_id'];
                        $unidadesExceso = (int) ceil($litrosExceso / 1000);
                        $montoExceso    = $unidadesExceso * (float) $tarifaExceso['monto_por_unidad'];
                    }
                }

                $montoTotal = $montoBase + ($montoExceso ?? 0);

                $db->query('SET @usuario_actual = ?', [$usuarioLectorId]);

                $db->table('Tb_Lecturas')->insert([
                    'lectura_anterior'  => $lecturaAnterior,
                    'lectura_actual'    => $lecturaActual,
                    'consumo_litros'    => $consumo,
                    'litros_exceso'     => $litrosExceso,
                    'monto_base'        => $montoBase,
                    'monto_exceso'      => $montoExceso,
                    'monto_total'       => $montoTotal,
                    'fecha'             => $fecha,
                    'contador_id'       => $contador['contador_id'],
                    'usuario_lector_id' => $usuarioLectorId,
                    'tarifa_base_id'    => $tarifaBase['tarifa_id'],
                    'tarifa_exceso_id'  => $tarifaExcesoId,
                ]);

                $lecturaAnterior = $lecturaActual;
            }
        }
    }
}