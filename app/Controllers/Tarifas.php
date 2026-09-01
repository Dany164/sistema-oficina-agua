<?php

namespace App\Controllers;

use App\Models\TarifaModel;
use CodeIgniter\Exceptions\pageNotFoundException;

class Tarifas extends BaseController
{
    protected $tarifaModel;

    public function __construct()
    {
        $this->tarifaModel = new TarifaModel();
    }

    /**
     * Mostrar listado de tarifas
      */ 

    public function index()
    {
        $data = [
            'titulo' => 'Tarifas',
            'tarifas' => $this->tarifaModel->findAll(),
        ];

        return view('tarifas/index', $data);
    }

    /**
     * Mostrar formulario para crear una nueva tarifa
     */

    public function new()
    {
        $data = [
            'titulo' => 'Nueva Tarifa',
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
            'vigente_hasta' => $this->request->getPost('vigente_hasta'),
            'tipo_servicio_id' => $this->request->getPost('tipo_servicio_id'),
        ];

        if ($this->tarifaModel->validate($data)) {
            return redirect()->back()
            ->withInput()
            ->with('errors', $this->tarifaModel->errors());
        } 

        //La fecha de finnalización no puede ser anterior a la fecha de inicio

        if (
            !empty($data['vigente_hasta']) &&
            $data['vigente_hasta'] < $data['vigente_desde']
        ) {
            return redirect()->back()
                ->withInput()
                ->with('errors', 'La fecha de finnalización no puede ser anterior a la fecha de inicio.');
        }

                /*
         * Regla de negocio:
         * solo puede existir una tarifa vigente para cada
         * tipo de servicio.
         *
         * Por ahora no implementamos el cambio automático
         * de la tarifa anterior. Primero validaremos cómo
         * quedará el flujo de vigencias.
         */
        if (!$this->tarifaModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No fue posible guardar la tarifa.');
        }

        return redirect()->to('/tarifas')
            ->with('success', 'Tarifa creada correctamente.');
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
            'monto_por_unidad' => $this->request->getPost('monto_por_unidad'),
            'vigente_desde' => $this->request->getPost('vigente_desde'),
            'vigente_hasta' => $this->request->getPost('vigente_hasta'),
            'tipo_servicio_id' => $this->request->getPost('tipo_servicio_id'),
        ];

        if (!$this->tarifaModel->validate($data)) {
            return redirect()->back()
            ->withInput()
            ->with('errors', $this->tarifaModel->errors());
        }

        if (
            !empty($data['vigente_hasta']) &&
            $data['vigente_hasta'] < $data['vigente_desde']
        ) {
            return redirect()->back()
                ->withInput()
                ->with('errors', 'La fecha de finnalización no puede ser anterior a la fecha de inicio.');
        }

        if (!$this->tarifaModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No fue posible actualizar la tarifa.');
        }
        return redirect()->to('/tarifas')
            ->with('success', 'Tarifa actualizada correctamente.');
    }

}