<?php

namespace App\Controllers;

use App\Models\LecturaModel;

class Lecturas extends BaseController
{
    protected $lecturaModel;

    public function __construct()
    {
        $this->lecturaModel = new LecturaModel();
    }

    /**
     * Listado de lecturas.
     */
    public function index()
    {
        $db = db_connect();

        $lecturas = $db->table('Tb_lecturas l')
            ->select([
                'l.lectura_id',
                'l.lectura_anterior',
                'l.lectura_actual',
                'l.consumo_litros',
                'l.litros_exceso',
                'l.monto_base',
                'l.monto_exceso',
                'l.monto_total',
                'l.fecha',
                'l.contador_id',
                'l.usuario_lector_id',
                'l.tarifa_base_id',
                'l.tarifa_exceso_id',
                'c.numero_registro',
                'c.direccion_servicio',
                'cl.nombre AS cliente',
                'ts.tipo_servicio',
                'u.nombre AS lector',
            ])
            ->join(
                'Tb_Contadores c',
                'c.contador_id = l.contador_id'
            )
            ->join(
                'Tb_Clientes cl',
                'cl.cliente_id = c.cliente_id'
            )
            ->join(
                'Tb_Tipos_Servicio ts',
                'ts.tipo_servicio_id = c.tipo_servicio_id'
            )
            ->join(
                'Tb_Usuarios u',
                'u.usuario_id = l.usuario_lector_id'
            )
            ->orderBy('l.fecha', 'DESC')
            ->orderBy('l.lectura_id', 'DESC')
            ->get()
            ->getResultArray();

        return view('lecturas/index', [
            'lecturas' => $lecturas,
        ]);
    }

    /**
     * Formulario para registrar una nueva lectura.
     */
    public function new()
    {
        $contadores = $this->obtenerContadoresActivos();

        return view('lecturas/form', [
            'contadores' => $contadores,
        ]);
    }

    /**
     * Registrar una nueva lectura.
     */
    public function create()
    {
        $rules = [
            'contador_id'     => 'required|integer',
            'fecha'           => 'required|valid_date[Y-m-d]',
            'lectura_actual'  => 'required|integer|greater_than_equal_to[0]',
            'lectura_anterior' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $contadorId = (int) $this->request->getPost('contador_id');
        $fecha = $this->request->getPost('fecha');
        $lecturaActual = (int) $this->request->getPost('lectura_actual');

        $lecturaAnteriorIngresada = $this->request->getPost('lectura_anterior');

        if (
            $lecturaAnteriorIngresada !== null &&
            $lecturaAnteriorIngresada !== ''
        ) {
            $lecturaAnteriorIngresada = (int) $lecturaAnteriorIngresada;
        } else {
            $lecturaAnteriorIngresada = null;
        }

        $db = db_connect();

        /*
         * 1. Verificar que el contador exista y esté activo.
         */
        $contador = $db->table('Tb_Contadores c')
            ->select([
                'c.contador_id',
                'c.numero_registro',
                'c.direccion_servicio',
                'c.estado',
                'c.cliente_id',
                'c.tipo_servicio_id',
                'ts.tipo_servicio',
                'ts.litros_incluidos',
            ])
            ->join(
                'Tb_Tipos_Servicio ts',
                'ts.tipo_servicio_id = c.tipo_servicio_id'
            )
            ->where('c.contador_id', $contadorId)
            ->get()
            ->getRowArray();

        if (!$contador) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'El contador seleccionado no existe.');
        }

        if (!(bool) $contador['estado']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se puede registrar una lectura para un contador inactivo.');
        }

        /*
         * 2. Verificar que no exista otra lectura del mismo contador
         *    en la misma fecha.
         *
         *    Esta restricción también existe en la base de datos:
         *    UNIQUE (contador_id, fecha)
         */
        $lecturaMismaFecha = $this->lecturaModel
            ->where('contador_id', $contadorId)
            ->where('fecha', $fecha)
            ->first();

        if ($lecturaMismaFecha) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Este contador ya tiene una lectura registrada para la fecha seleccionada.'
                );
        }

        /*
         * 3. Buscar la última lectura anterior del contador.
         */
        $ultimaLectura = $this->lecturaModel
            ->where('contador_id', $contadorId)
            ->where('fecha <', $fecha)
            ->orderBy('fecha', 'DESC')
            ->orderBy('lectura_id', 'DESC')
            ->first();

        if ($ultimaLectura) {
            /*
             * Si existe una lectura anterior, el sistema la utiliza.
             * No permitimos alterar manualmente la lectura anterior.
             */
            $lecturaAnterior = (int) $ultimaLectura['lectura_actual'];
        } else {
            /*
             * Primera lectura del contador.
             *
             * Como Tb_Contadores no posee lectura_inicial,
             * debemos recibirla desde el formulario.
             */
            if ($lecturaAnteriorIngresada === null) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Este contador no tiene lecturas anteriores. Debes indicar la lectura anterior para registrar la primera lectura.'
                    );
            }

            $lecturaAnterior = $lecturaAnteriorIngresada;
        }

        /*
         * 4. La lectura actual nunca puede ser menor
         *    que la lectura anterior.
         */
        if ($lecturaActual < $lecturaAnterior) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'La lectura actual no puede ser menor que la lectura anterior.'
                );
        }

        /*
         * 5. Calcular consumo.
         */
        $consumoLitros = $lecturaActual - $lecturaAnterior;

        /*
         * 6. Buscar la tarifa base vigente para el tipo de servicio
         *    en la fecha de la lectura.
         */
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

        if (!$tarifaBase) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'No existe una tarifa vigente para el tipo de servicio en la fecha seleccionada.'
                );
        }

        /*
         * 7. El monto base corresponde al monto de la tarifa
         *    del tipo de servicio.
         */
        $montoBase = (float) $tarifaBase['monto_por_unidad'];

        /*
         * 8. Determinar si existe exceso.
         */
        $litrosIncluidos = $contador['litros_incluidos'] !== null
            ? (int) $contador['litros_incluidos']
            : null;

        $litrosExceso = null;
        $montoExceso = null;
        $tarifaExcesoId = null;

        if (
            $litrosIncluidos !== null &&
            $consumoLitros > $litrosIncluidos
        ) {
            $litrosExceso = $consumoLitros - $litrosIncluidos;

            /*
             * Buscar el tipo de servicio "Exceso".
             *
             * No utilizamos directamente un ID fijo.
             * Buscamos por nombre para evitar depender de que
             * el ID sea siempre 3.
             */
            $tipoExceso = $db->table('Tb_Tipos_Servicio')
                ->where('tipo_servicio', 'Exceso')
                ->get()
                ->getRowArray();

            if (!$tipoExceso) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'No existe el tipo de servicio "Exceso" configurado.'
                    );
            }

            /*
             * Buscar la tarifa de exceso vigente.
             */
            $tarifaExceso = $db->table('Tb_Tarifas')
                ->where(
                    'tipo_servicio_id',
                    $tipoExceso['tipo_servicio_id']
                )
                ->where('vigente_desde <=', $fecha)
                ->groupStart()
                ->where('vigente_hasta >=', $fecha)
                ->orWhere('vigente_hasta IS NULL', null, false)
                ->groupEnd()
                ->orderBy('vigente_desde', 'DESC')
                ->get()
                ->getRowArray();

            if (!$tarifaExceso) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'El consumo tiene exceso, pero no existe una tarifa de exceso vigente para la fecha seleccionada.'
                    );
            }

            $tarifaExcesoId = (int) $tarifaExceso['tarifa_id'];

            /*
             * La tarifa de exceso representa el precio
             * por cada unidad de 1,000 litros.
             *
             * Se redondea hacia arriba para cobrar cualquier
             * fracción de 1,000 litros como una unidad completa.
             */
            $unidadesExceso = (int) ceil($litrosExceso / 1000);

            $montoExceso = $unidadesExceso
                * (float) $tarifaExceso['monto_por_unidad'];
        }

        /*
         * 9. Calcular monto total.
         */
        $montoTotal = $montoBase + ($montoExceso ?? 0);

        /*
         * 10. Usuario autenticado.
         */
        $usuarioId = session()->get('usuario_id');

        if (!$usuarioId) {
            return redirect()
                ->to(base_url('login'))
                ->with('error', 'La sesión ha expirado. Inicia sesión nuevamente.');
        }

        /*
         * 11. Registrar lectura.
         *
         * También establecemos @usuario_actual para que los triggers
         * de auditoría puedan registrar quién realizó la operación.
         */
        $db->transStart();

        $db->query(
            'SET @usuario_actual = ?',
            [$usuarioId]
        );

        $this->lecturaModel->insert([
            'lectura_anterior'  => $lecturaAnterior,
            'lectura_actual'    => $lecturaActual,
            'consumo_litros'    => $consumoLitros,
            'litros_exceso'     => $litrosExceso,
            'monto_base'        => $montoBase,
            'monto_exceso'      => $montoExceso,
            'monto_total'       => $montoTotal,
            'fecha'             => $fecha,
            'contador_id'       => $contadorId,
            'usuario_lector_id' => $usuarioId,
            'tarifa_base_id'    => $tarifaBase['tarifa_id'],
            'tarifa_exceso_id'  => $tarifaExcesoId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'No fue posible registrar la lectura.'
                );
        }

        return redirect()
            ->to(base_url('lecturas'))
            ->with(
                'mensaje',
                'Lectura registrada correctamente.'
            );
    }

    public function recibo($id)
    {
        $db = db_connect();

        $lectura = $db->table('Tb_lecturas l')
            ->select('
            l.*,
            c.numero_registro,
            c.direccion_servicio,
            cl.nombre AS cliente,
            cl.telefono,
            cl.direccion AS direccion_cliente,
            ts.tipo_servicio,
            u.nombre AS lector
        ')
            ->join('Tb_Contadores c', 'c.contador_id = l.contador_id')
            ->join('Tb_Clientes cl', 'cl.cliente_id = c.cliente_id')
            ->join('Tb_Tipos_Servicio ts', 'ts.tipo_servicio_id = c.tipo_servicio_id')
            ->join('Tb_Usuarios u', 'u.usuario_id = l.usuario_lector_id')
            ->where('l.lectura_id', $id)
            ->get()
            ->getRowArray();

        if (!$lectura) {
            return redirect()->to('/lecturas')
                ->with('error', 'La lectura solicitada no existe.');
        }

        return view('lecturas/recibo', [
            'title'   => 'Recibo de lectura',
            'lectura' => $lectura,
        ]);
    }

    /**
     * Obtener únicamente contadores activos.
     */
    private function obtenerContadoresActivos(): array
    {
        return db_connect()
            ->table('Tb_Contadores c')
            ->select([
                'c.contador_id',
                'c.numero_registro',
                'c.direccion_servicio',
                'c.cliente_id',
                'c.tipo_servicio_id',
                'cl.nombre AS cliente',
                'ts.tipo_servicio',
                'ts.litros_incluidos',
            ])
            ->join(
                'Tb_Clientes cl',
                'cl.cliente_id = c.cliente_id'
            )
            ->join(
                'Tb_Tipos_Servicio ts',
                'ts.tipo_servicio_id = c.tipo_servicio_id'
            )
            ->where('c.estado', 1)
            ->orderBy('c.numero_registro', 'ASC')
            ->get()
            ->getResultArray();
    }
}
