<?php

namespace App\Controllers;

class Main extends BaseController
{
    public function index()
{
    $db = db_connect();

    // Estado de cuenta por cliente: última lectura y si tiene pendientes
    $clientes = $db->table('Tb_Clientes c')
        ->select("
            c.cliente_id,
            c.nombre AS cliente,
            MAX(l.fecha) AS ultima_lectura,
            SUM(CASE WHEN p.pago_id IS NULL THEN 1 ELSE 0 END) AS lecturas_pendientes
        ")
        ->join('Tb_Contadores ct', 'ct.cliente_id = c.cliente_id', 'left')
        ->join('Tb_Lecturas l', 'l.contador_id = ct.contador_id', 'left')
        ->join('Tb_Pagos p', 'p.lectura_id = l.lectura_id', 'left')
        ->groupBy('c.cliente_id, c.nombre')
        ->orderBy('c.nombre', 'asc')
        ->get()
        ->getResultArray();

    $clientesAlDia      = 0;
    $clientesPendientes = 0;

    foreach ($clientes as &$cliente) {
        if ((int) $cliente['lecturas_pendientes'] > 0) {
            $cliente['estado'] = 'Pendiente';
            $clientesPendientes++;
        } else {
            $cliente['estado'] = 'Al día';
            $clientesAlDia++;
        }
    }
    unset($cliente); // buena práctica al terminar un foreach por referencia

    // Contadores pendientes de lectura: nunca leídos, o su última
    // lectura tiene más de un mes de antigüedad
    $contadoresPendientes = $db->table('Tb_Contadores ct')
        ->select("
            ct.contador_id,
            ct.numero_registro,
            ct.direccion_servicio,
            c.nombre AS cliente,
            MAX(l.fecha) AS ultima_lectura
        ")
        ->join('Tb_Clientes c', 'c.cliente_id = ct.cliente_id', 'left')
        ->join('Tb_Lecturas l', 'l.contador_id = ct.contador_id', 'left')
        ->where('ct.estado', 1) // solo contadores activos
        ->groupBy('ct.contador_id, ct.numero_registro, ct.direccion_servicio, c.nombre')
        ->having("MAX(l.fecha) IS NULL OR MAX(l.fecha) <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)", null, false)
        ->orderBy('ultima_lectura', 'asc') // los más atrasados primero (NULLs al inicio)
        ->get()
        ->getResultArray();

    $data = [
        'title'                => 'Dashboard',
        'clientes'             => $clientes,
        'clientesAlDia'        => $clientesAlDia,
        'clientesPendientes'   => $clientesPendientes,
        'contadoresPendientes' => $contadoresPendientes,
    ];

    return view('primera_vista', $data);
    }
}