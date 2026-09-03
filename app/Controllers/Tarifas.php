<?php

namespace App\Controllers;

use App\Models\TarifaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Tarifas extends BaseController
{
    protected $tarifaModel;

    public function __construct()
    {
        $this->tarifaModel = new TarifaModel();
    }

    private function obtenerTiposServicio(): array
    {
        return db_connect()
            ->table('Tb_Tipos_Servicio')
            ->orderBy('tipo_servicio', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mostrar listado de tarifas
     */
    public function index()
    {
        $db = db_connect();

        $tarifas = $db->table('Tb_Tarifas t')
            ->select('t.*, ts.tipo_servicio')
            ->join(
                'Tb_Tipos_Servicio ts',
                'ts.tipo_servicio_id = t.tipo_servicio_id',
                'left'
            )
            ->orderBy('t.vigente_desde', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Tarifas',
            'tarifas' => $tarifas,
        ];

        return view('tarifas/index', $data);
    }

    /**
     * Mostrar formulario para crear una nueva tarifa
     */
    public function new()
    {
        $data = [
            'title' => 'Nueva Tarifa',
            'tiposServicio' => $this->obtenerTiposServicio(),
        ];

        return view('tarifas/form', $data);
    }

    /**
     * Guardar una nueva tarifa
     */
    public function create()
    {
        $data = [
            'monto_por_unidad' => $this->request->getPost('monto_por_unidad'),
            'vigente_desde' => $this->request->getPost('vigente_desde'),
            'vigente_hasta' => $this->request->getPost('vigente_hasta') ?: null,
            'tipo_servicio_id' => $this->request->getPost('tipo_servicio_id'),
        ];
        // Validar datos
        if (!$this->tarifaModel->validate($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->tarifaModel->errors());
        }

        // La fecha de finalización no puede ser anterior a la fecha de inicio
        if (
            !empty($data['vigente_hasta']) &&
            $data['vigente_hasta'] < $data['vigente_desde']
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'La fecha de finalización no puede ser anterior a la fecha de inicio.'
                );
        }

        $db = db_connect();

        // Usuario que realiza la operación
        $usuarioId = session()->get('usuario_id');

        // Iniciar transacción
        $db->transBegin();

        try {

            /*
         * Establecer el usuario actual en la sesión de MySQL.
         * Los triggers de Tb_Tarifas utilizan esta variable
         * para registrar quién realizó la operación.
         */
            $db->query(
                'SET @usuario_actual = ?',
                [$usuarioId]
            );

            // Buscar la tarifa actualmente vigente para este tipo de servicio
            $tarifaVigente = $db->table('Tb_Tarifas')
                ->where('tipo_servicio_id', $data['tipo_servicio_id'])
                ->where('vigente_hasta IS NULL', null, false)
                ->get()
                ->getRowArray();

            // Si existe una tarifa vigente, debe cerrarse antes de crear la nueva
            if ($tarifaVigente) {

                // La nueva tarifa no puede comenzar antes o el mismo día
                // que la tarifa actualmente vigente
                if ($data['vigente_desde'] <= $tarifaVigente['vigente_desde']) {
                    $db->transRollback();

                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            'La nueva tarifa debe comenzar después de la fecha de inicio de la tarifa vigente actual.'
                        );
                }

                // La tarifa anterior termina el día anterior
                // al inicio de la nueva tarifa
                $fechaFinAnterior = date(
                    'Y-m-d',
                    strtotime($data['vigente_desde'] . ' -1 day')
                );

                // Cerrar la tarifa anterior
                $db->table('Tb_Tarifas')
                    ->where('tarifa_id', $tarifaVigente['tarifa_id'])
                    ->update([
                        'vigente_hasta' => $fechaFinAnterior,
                    ]);
            }

            // Crear la nueva tarifa usando la misma conexión
            $db->table('Tb_Tarifas')->insert($data);

            // Verificar si ocurrió algún error durante las operaciones
            if ($db->transStatus() === false) {
                $db->transRollback();

                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'No fue posible guardar la tarifa. La operación fue revertida.'
                    );
            }

            // Confirmar todas las operaciones
            $db->transCommit();

            return redirect()->to('/tarifas')
                ->with('success', 'Tarifa creada correctamente.');
        } catch (\Throwable $e) {

            // Revertir cualquier cambio realizado
            $db->transRollback();

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Ocurrió un error al guardar la tarifa. La operación fue revertida.'
                );
        }
    }

    /**
     * Mostrar formulario para editar una tarifa existente
     */
    public function edit($id)
    {
        $tarifa = $this->tarifaModel->find($id);

        if (!$tarifa) {
            throw PageNotFoundException::forPageNotFound(
                'La tarifa con ID ' . $id . ' no existe.'
            );
        }

        $data = [
            'title' => 'Editar Tarifa',
            'tarifa' => $tarifa,
            'tiposServicio' => $this->obtenerTiposServicio(),
        ];

        return view('tarifas/form', $data);
    }

    /**
     * Actualizar una tarifa existente
     */
    public function update($id)
    {
        $tarifa = $this->tarifaModel->find($id);

        if (!$tarifa) {
            throw PageNotFoundException::forPageNotFound(
                'La tarifa con ID ' . $id . ' no existe.'
            );
        }

        /*
     * Las tarifas históricas no deben modificarse.
     * Para cambiar una tarifa histórica se debe crear una nueva
     * tarifa que conserve el historial existente.
     */
        if (!empty($tarifa['vigente_hasta'])) {
            return redirect()->to('/tarifas')
                ->with(
                    'error',
                    'Las tarifas históricas no pueden modificarse. Cree una nueva tarifa para registrar un cambio.'
                );
        }

        /*
     * Una tarifa vigente solamente permite modificar
     * el monto por unidad.
     *
     * El tipo de servicio y las fechas forman parte del
     * historial de la tarifa y no deben modificarse.
     */
        $data = [
            'monto_por_unidad' => $this->request->getPost('monto_por_unidad'),
        ];

        // Validar el monto
        if (!$this->tarifaModel->validate($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->tarifaModel->errors());
        }

        // Si el monto no cambió, no realizar una actualización innecesaria
        if ((float) $data['monto_por_unidad'] === (float) $tarifa['monto_por_unidad']) {
            return redirect()->to('/tarifas')
                ->with('success', 'No se realizaron cambios en la tarifa.');
        }

        $db = db_connect();

        // Usuario que realiza la operación
        $usuarioId = session()->get('usuario_id');

        // Iniciar transacción
        $db->transBegin();

        try {

            // Establecer usuario para los triggers de auditoría
            $db->query(
                'SET @usuario_actual = ?',
                [$usuarioId]
            );

            /*
         * Actualizar únicamente el monto.
         * No se permite modificar:
         * - tipo_servicio_id
         * - vigente_desde
         * - vigente_hasta
         */
            $db->table('Tb_Tarifas')
                ->where('tarifa_id', $id)
                ->update($data);

            // Verificar si la operación tuvo algún error
            if ($db->transStatus() === false) {
                $db->transRollback();

                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'No fue posible actualizar la tarifa. La operación fue revertida.'
                    );
            }

            // Confirmar la transacción
            $db->transCommit();

            return redirect()->to('/tarifas')
                ->with('success', 'Tarifa actualizada correctamente.');
        } catch (\Throwable $e) {

            // Revertir cualquier cambio realizado
            $db->transRollback();

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Ocurrió un error al actualizar la tarifa. La operación fue revertida.'
                );
        }
    }
}
