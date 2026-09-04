<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class Clientes extends BaseController
{
    protected $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Clientes',
            'clientes' => $this->clienteModel->orderBy('nombre', 'ASC')->findAll(),
        ];

        return view('clientes/index', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Nuevo cliente',
            'cliente' => [
                'nombre' => '',
                'telefono' => '',
                'direccion' => '',
            ],
            'errors' => [],
        ];

        return view('clientes/form', $data);
    }

    public function create()
    {
        $data = $this->request->getPost();

        $data['telefono'] = $this->clienteModel->normalizeTelefono($data['telefono'] ?? '');

        if (!$this->validate([
            'nombre' => 'required|max_length[100]|trim',
            'telefono' => 'permit_empty|max_length[20]|regex_match[/^(\+502\s?)?[0-9\s\-\(\)]{8,20}$/]',
            'direccion' => 'required|max_length[255]|trim',
        ], [
            'nombre' => [
                'required' => 'El nombre del cliente es obligatorio.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
            'telefono' => [
                'regex_match' => 'El teléfono no es válido. Ejemplo: 4545-6789 o +502 4545-6789.',
                'max_length' => 'El teléfono es demasiado largo.',
            ],
            'direccion' => [
                'required' => 'La dirección del cliente es obligatoria.',
                'max_length' => 'La dirección no puede superar los 255 caracteres.',
            ],
        ])) {
            return view('clientes/form', [
                'title' => 'Nuevo cliente',
                'cliente' => $data,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        if ($this->clienteModel->save($data)) {
            session()->setFlashdata('success', 'Cliente guardado correctamente.');
            return redirect()->to('/clientes');
        }

        session()->setFlashdata('error', 'No se pudo guardar el cliente.');
        return redirect()->back()->withInput();
    }

    public function edit($id)
    {
        $cliente = $this->clienteModel->find($id);

        if (!$cliente) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cliente no encontrado.');
        }

        return view('clientes/form', [
            'title' => 'Editar cliente',
            'cliente' => $cliente,
            'errors' => [],
        ]);
    }

    public function update($id)
    {
        $cliente = $this->clienteModel->find($id);

        if (!$cliente) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cliente no encontrado.');
        }

        $data = $this->request->getPost();

        $data['telefono'] = $this->clienteModel->normalizeTelefono($data['telefono'] ?? '');

        if (!$this->validate([
            'nombre' => 'required|max_length[100]|trim',
            'telefono' => 'permit_empty|max_length[20]|regex_match[/^(\+502\s?)?[0-9\s\-\(\)]{8,20}$/]',
            'direccion' => 'required|max_length[255]|trim',
        ], [
            'nombre' => [
                'required' => 'El nombre del cliente es obligatorio.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
            'telefono' => [
                'regex_match' => 'El teléfono no es válido para Guatemala. Ejemplo: 4545-6789 o +502 4545-6789.',
                'max_length' => 'El teléfono es demasiado largo.',
            ],
            'direccion' => [
                'required' => 'La dirección del cliente es obligatoria.',
                'max_length' => 'La dirección no puede superar los 255 caracteres.',
            ],
        ])) {
            return view('clientes/form', [
                'title' => 'Editar cliente',
                'cliente' => array_merge($cliente, $data),
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->clienteModel->update($id, $data);
        session()->setFlashdata('success', 'Cliente actualizado correctamente.');

        return redirect()->to('/clientes');
    }

    public function delete($id)
    {
        $cliente = $this->clienteModel->find($id);

        if (!$cliente) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cliente no encontrado.');
        }

        $this->clienteModel->delete($id);
        session()->setFlashdata('success', 'Cliente eliminado correctamente.');

        return redirect()->to('/clientes');
    }
}
