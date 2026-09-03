<?php

namespace App\Controllers;

use App\Models\ContadorModel;

class Contadores extends BaseController
{
    protected $contadorModel;

    public function __construct()
    {
        $this->contadorModel = new ContadorModel();
    }

    public function index()
    {
        $db = db_connect();

        $data['contadores'] = $db->table('tb_contadores c')
            ->select('
                c.contador_id,
                c.numero_registro,
                c.direccion_servicio,
                c.estado,
                c.cliente_id,
                c.tipo_servicio_id,
                cl.nombre AS cliente,
                ts.tipo_servicio
            ')
            ->join('tb_clientes cl', 'cl.cliente_id = c.cliente_id')
            ->join(
                'tb_tipos_servicio ts',
                'ts.tipo_servicio_id = c.tipo_servicio_id'
            )
            ->orderBy('c.contador_id', 'DESC')
            ->get()
            ->getResultArray();

        return view('contadores/index', $data);
    }

    public function crear()
    {
        $db = db_connect();

        $data['clientes'] = $db->table('tb_clientes')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();

        $data['tiposServicio'] = $db->table('tb_tipos_servicio')
            ->orderBy('tipo_servicio', 'ASC')
            ->get()
            ->getResultArray();

        return view('contadores/crear', $data);
    }

    public function guardar()
    {
        $reglas = [
            'numero_registro' => [
                'rules' => 'required|max_length[50]|is_unique[tb_contadores.numero_registro]',
                'errors' => [
                    'required' => 'El número de registro es obligatorio.',
                    'max_length' => 'El número de registro no puede superar los 50 caracteres.',
                    'is_unique' => 'Ese número de registro ya existe.',
                ],
            ],

            'direccion_servicio' => [
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'La dirección del servicio es obligatoria.',
                    'max_length' => 'La dirección no puede superar los 50 caracteres.',
                ],
            ],

            'cliente_id' => [
                'rules' => 'required|integer|is_not_unique[tb_clientes.cliente_id]',
                'errors' => [
                    'required' => 'Debe seleccionar un cliente.',
                    'is_not_unique' => 'El cliente seleccionado no existe.',
                ],
            ],

            'tipo_servicio_id' => [
                'rules' => 'required|integer|is_not_unique[tb_tipos_servicio.tipo_servicio_id]',
                'errors' => [
                    'required' => 'Debe seleccionar un tipo de servicio.',
                    'is_not_unique' => 'El tipo de servicio seleccionado no existe.',
                ],
            ],
        ];

        if (!$this->validate($reglas)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errores', $this->validator->getErrors());
        }

        $this->contadorModel->insert([
            'numero_registro' => trim(
                (string) $this->request->getPost('numero_registro')
            ),
            'direccion_servicio' => trim(
                (string) $this->request->getPost('direccion_servicio')
            ),
            'cliente_id' => $this->request->getPost('cliente_id'),
            'tipo_servicio_id' => $this->request->getPost('tipo_servicio_id'),
            'estado' => 1,
        ]);

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador registrado correctamente.');
    }

    public function editar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado.'
            );
        }

        $db = db_connect();

        $clientes = $db->table('tb_clientes')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();

        $tiposServicio = $db->table('tb_tipos_servicio')
            ->orderBy('tipo_servicio', 'ASC')
            ->get()
            ->getResultArray();

        return view('contadores/editar', [
            'contador' => $contador,
            'clientes' => $clientes,
            'tiposServicio' => $tiposServicio,
        ]);
    }

    public function actualizar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado.'
            );
        }

        $reglas = [
            'numero_registro' => 'required|max_length[50]',
            'direccion_servicio' => 'required|max_length[50]',
            'cliente_id' => 'required|integer|is_not_unique[tb_clientes.cliente_id]',
            'tipo_servicio_id' => 'required|integer|is_not_unique[tb_tipos_servicio.tipo_servicio_id]',
        ];

        if (!$this->validate($reglas)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errores', $this->validator->getErrors());
        }

        $numeroRegistro = trim(
            (string) $this->request->getPost('numero_registro')
        );

        $registroExistente = $this->contadorModel
            ->where('numero_registro', $numeroRegistro)
            ->where('contador_id !=', $id)
            ->first();

        if ($registroExistente) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'El número de registro ya pertenece a otro contador.'
                );
        }

        $this->contadorModel->update($id, [
            'numero_registro' => $numeroRegistro,
            'direccion_servicio' => trim(
                (string) $this->request->getPost('direccion_servicio')
            ),
            'cliente_id' => $this->request->getPost('cliente_id'),
            'tipo_servicio_id' => $this->request->getPost('tipo_servicio_id'),
        ]);

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador actualizado correctamente.');
    }

    public function retirar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado.'
            );
        }

        if (!$contador['estado']) {
            return redirect()
                ->to(base_url('contadores'))
                ->with('error', 'El contador ya está retirado.');
        }

        $this->contadorModel->update($id, [
            'estado' => 0,
        ]);

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador retirado correctamente.');
    }

    public function reactivar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado.'
            );
        }

        if ($contador['estado']) {
            return redirect()
                ->to(base_url('contadores'))
                ->with('error', 'El contador ya se encuentra activo.');
        }

        $this->contadorModel->update($id, [
            'estado' => 1,
        ]);

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador reactivado correctamente.');
    }
}