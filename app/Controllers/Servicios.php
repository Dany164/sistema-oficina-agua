<?php

namespace App\Controllers;

use App\Models\TipoServicioModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Servicios extends BaseController
{
    protected $tipoServicioModel;

    public function __construct()
    {
        $this->tipoServicioModel = new TipoServicioModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Servicios',
            'servicios' => $this->tipoServicioModel
                ->orderBy('tipo_servicio', 'ASC')
                ->findAll(),
        ];

        return view('servicios/index', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Nuevo servicio',
            'servicio' => [
                'tipo_servicio' => '',
                'litros_incluidos' => '',
            ],
            'errors' => [],
        ];

        return view('servicios/form', $data);
    }

    public function create()
    {
        $data = $this->request->getPost();

        if (!$this->validate([
            'tipo_servicio' => 'required|max_length[50]|is_unique[tb_tipos_servicio.tipo_servicio]',
            'litros_incluidos' => 'permit_empty|integer|greater_than_equal_to[0]',
        ], [
            'tipo_servicio' => [
                'required' => 'El tipo de servicio es obligatorio.',
                'max_length' => 'El tipo de servicio no puede superar los 50 caracteres.',
                'is_unique' => 'Ya existe un servicio con ese nombre.',
            ],
            'litros_incluidos' => [
                'integer' => 'Los litros incluidos deben ser un número entero.',
                'greater_than_equal_to' => 'Los litros incluidos no pueden ser negativos.',
            ],
        ])) {
            return view('servicios/form', [
                'title' => 'Nuevo servicio',
                'servicio' => $data,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->tipoServicioModel->insert([
            'tipo_servicio' => trim($data['tipo_servicio']),
            'litros_incluidos' => $data['litros_incluidos'] !== ''
                ? $data['litros_incluidos']
                : null,
        ]);

        session()->setFlashdata('success', 'Servicio guardado correctamente.');

        return redirect()->to('/servicios');
    }

    public function edit($id)
    {
        $servicio = $this->tipoServicioModel->find($id);

        if (!$servicio) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Servicio no encontrado.'
            );
        }

        return view('servicios/form', [
            'title' => 'Editar servicio',
            'servicio' => $servicio,
            'errors' => [],
        ]);
    }

    public function update($id)
    {
        $servicio = $this->tipoServicioModel->find($id);

        if (!$servicio) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Servicio no encontrado.'
            );
        }

        $data = $this->request->getPost();

        if (!$this->validate([
            'tipo_servicio' => "required|max_length[50]|is_unique[tb_tipos_servicio.tipo_servicio,tipo_servicio_id,{$id}]",
            'litros_incluidos' => 'permit_empty|integer|greater_than_equal_to[0]',
        ], [
            'tipo_servicio' => [
                'required' => 'El tipo de servicio es obligatorio.',
                'max_length' => 'El tipo de servicio no puede superar los 50 caracteres.',
                'is_unique' => 'Ya existe un servicio con ese nombre.',
            ],
            'litros_incluidos' => [
                'integer' => 'Los litros incluidos deben ser un número entero.',
                'greater_than_equal_to' => 'Los litros incluidos no pueden ser negativos.',
            ],
        ])) {
            return view('servicios/form', [
                'title' => 'Editar servicio',
                'servicio' => array_merge($servicio, $data),
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->tipoServicioModel->update($id, [
            'tipo_servicio' => trim($data['tipo_servicio']),
            'litros_incluidos' => $data['litros_incluidos'] !== ''
                ? $data['litros_incluidos']
                : null,
        ]);

        session()->setFlashdata('success', 'Servicio actualizado correctamente.');

        return redirect()->to('/servicios');
    }

    public function delete($id)
    {
        $servicio = $this->tipoServicioModel->find($id);

        if (!$servicio) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Servicio no encontrado.'
            );
        }

        try {
            $this->tipoServicioModel->delete($id);

            session()->setFlashdata(
                'success',
                'Servicio eliminado correctamente.'
            );
        } catch (DatabaseException $e) {
            session()->setFlashdata(
                'error',
                'No se puede eliminar este servicio porque está siendo utilizado.'
            );
        }

        return redirect()->to('/servicios');
    }
}