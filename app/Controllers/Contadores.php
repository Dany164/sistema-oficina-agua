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
        $data['contadores'] = $this->contadorModel->findAll();

        return view('contadores/index', $data);
    }

    public function crear()
    {
        $db = db_connect();

        $data['servicios'] = $db->table('servicios')
            ->select('servicios.id, servicios.codigo, servicios.direccion, clientes.nombre AS cliente')
            ->join('clientes', 'clientes.id = servicios.cliente_id')
            ->where('servicios.estado', 'activo')
            ->orderBy('servicios.codigo', 'ASC')
            ->get()
            ->getResultArray();

        return view('contadores/crear', $data);
    }

    public function guardar()
    {
        $reglas = [
            'servicio_id' => 'required|integer',
            'numero_serie' => 'permit_empty|max_length[40]|is_unique[contadores.numero_serie]',
            'lectura_inicial' => 'required|decimal|greater_than_equal_to[0]',
            'fecha_instalacion' => 'required|valid_date[Y-m-d]'
        ];

        if (!$this->validate($reglas)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errores', $this->validator->getErrors());
        }

        $servicioId = $this->request->getPost('servicio_id');
        $fechaInstalacion = $this->request->getPost('fecha_instalacion');

        $numeroSerie = trim((string) $this->request->getPost('numero_serie'));

        if ($numeroSerie === '') {
            $numeroSerie = null;
        }

        $db = db_connect();

        $db->transStart();

        // Buscar si el servicio ya tiene un contador activo.
        $contadorAnterior = $this->contadorModel
            ->where('servicio_id', $servicioId)
            ->where('activo', 1)
            ->first();

        // Si existe, se retira antes de instalar el nuevo.
        if ($contadorAnterior) {
            $this->contadorModel->update($contadorAnterior['id'], [
                'activo'       => 0,
                'fecha_retiro' => $fechaInstalacion
            ]);
        }

        // Registrar el nuevo contador.
        $this->contadorModel->insert([
            'servicio_id'       => $servicioId,
            'numero_serie'      => $numeroSerie,
            'lectura_inicial'   => $this->request->getPost('lectura_inicial'),
            'fecha_instalacion' => $fechaInstalacion,
            'fecha_retiro'      => null,
            'activo'            => 1
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No fue posible registrar el contador.');
        }

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador registrado correctamente.');
    }

    public function editar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado'
            );
        }

        $db = db_connect();

        $servicios = $db->table('servicios')
            ->select('servicios.id, servicios.codigo, servicios.direccion, clientes.nombre AS cliente')
            ->join('clientes', 'clientes.id = servicios.cliente_id')
            ->orderBy('servicios.codigo', 'ASC')
            ->get()
            ->getResultArray();

        return view('contadores/editar', [
            'contador'  => $contador,
            'servicios' => $servicios
        ]);
    }

    public function actualizar($id)
    {
        $contador = $this->contadorModel->find($id);

        if (!$contador) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Contador no encontrado'
            );
        }

        $reglas = [
            'servicio_id'       => 'required|integer',
            'numero_serie'      => 'permit_empty|max_length[40]',
            'lectura_inicial'   => 'required|decimal|greater_than_equal_to[0]',
            'fecha_instalacion' => 'required|valid_date[Y-m-d]'
        ];

        if (!$this->validate($reglas)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errores', $this->validator->getErrors());
        }

        $numeroSerie = trim((string) $this->request->getPost('numero_serie'));

        if ($numeroSerie === '') {
            $numeroSerie = null;
        }

        // Comprobar que el número de serie no pertenezca a otro contador.
        if ($numeroSerie !== null) {
            $serieExistente = $this->contadorModel
                ->where('numero_serie', $numeroSerie)
                ->where('id !=', $id)
                ->first();

            if ($serieExistente) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'El número de serie ya está registrado.');
            }
        }

        $this->contadorModel->update($id, [
            'servicio_id'       => $this->request->getPost('servicio_id'),
            'numero_serie'      => $numeroSerie,
            'lectura_inicial'   => $this->request->getPost('lectura_inicial'),
            'fecha_instalacion' => $this->request->getPost('fecha_instalacion')
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
                'Contador no encontrado'
            );
        }

        if (!$contador['activo']) {
            return redirect()
                ->to(base_url('contadores'))
                ->with('error', 'El contador ya está retirado.');
        }

        $fechaRetiro = date('Y-m-d');

        if ($fechaRetiro < $contador['fecha_instalacion']) {
            return redirect()
                ->to(base_url('contadores'))
                ->with('error', 'La fecha de retiro no puede ser anterior a la fecha de instalación.');
        }

        $this->contadorModel->update($id, [
            'activo'       => 0,
            'fecha_retiro' => $fechaRetiro
        ]);

        return redirect()
            ->to(base_url('contadores'))
            ->with('mensaje', 'Contador retirado correctamente.');
    }


}