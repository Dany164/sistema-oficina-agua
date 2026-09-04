<?php

namespace App\Controllers;

use App\Models\PagoModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Pagos extends BaseController
{
    protected $pagoModel;

    public function __construct()
    {
        $this->pagoModel = new PagoModel();
    }

    public function index()
    {
        $request = $this->request;
        $builder = db_connect()->table('Tb_Pagos p')
            ->select([
                'p.*',
                'l.fecha AS fecha_lectura',
                'c.numero_registro',
                'cl.nombre AS cliente',
                'm.metodo',
                'u.nombre AS usuario',
            ])
            ->join('Tb_Lecturas l', 'l.lectura_id = p.lectura_id')
            ->join('Tb_Contadores c', 'c.contador_id = l.contador_id')
            ->join('Tb_Clientes cl', 'cl.cliente_id = c.cliente_id')
            ->join('Tb_Metodos_Pago m', 'm.metodos_pago_id = p.metodos_pago_id')
            ->join('Tb_Usuarios u', 'u.usuario_id = p.usuario_id');

        if ($request->getGet('fecha_desde')) {
            $builder->where('p.fecha_pago >=', $request->getGet('fecha_desde'));
        }
        if ($request->getGet('fecha_hasta')) {
            $builder->where('p.fecha_pago <=', $request->getGet('fecha_hasta'));
        }
        if ($request->getGet('cliente')) {
            $builder->like('cl.nombre', $request->getGet('cliente'));
        }
        if ($request->getGet('estado') === 'pagado') {
            $builder->where('p.anulado', 0);
        } elseif ($request->getGet('estado') === 'anulado') {
            $builder->where('p.anulado', 1);
        }

        $pagos = $builder->orderBy('p.fecha_pago', 'DESC')
            ->orderBy('p.pago_id', 'DESC')
            ->get()
            ->getResultArray();

        return view('pagos/index', [
            'title' => 'Pagos',
            'pagos' => $pagos,
            'filtros' => $request->getGet(),
        ]);
    }

    public function new()
    {
        return view('pagos/form', [
            'title' => 'Nuevo pago',
            'pago' => [
                'fecha_pago' => date('Y-m-d'),
                'numero_recibo' => '',
                'lectura_id' => '',
                'metodos_pago_id' => '',
                'observaciones' => '',
            ],
            'lecturas' => $this->obtenerLecturasPendientes(),
            'metodosPago' => $this->obtenerMetodosPago(),
            'errors' => [],
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost();
        $lectura = $this->obtenerLecturaPendiente($data['lectura_id'] ?? '');

        if (!$lectura) {
            return redirect()->back()->withInput()->with(
                'error',
                'La lectura seleccionada no existe o ya tiene un pago.'
            );
        }

        if (!$this->validarPago($data)) {
            return $this->mostrarFormularioConErrores(
                'Nuevo pago',
                $data,
                $this->validator->getErrors()
            );
        }

        try {
            $this->establecerUsuarioAuditoria();
            $this->pagoModel->insert([
                'monto' => $lectura['monto_total'],
                'fecha_pago' => $data['fecha_pago'],
                'numero_recibo' => trim($data['numero_recibo']),
                'lectura_id' => $lectura['lectura_id'],
                'usuario_id' => session()->get('usuario_id'),
                'metodos_pago_id' => $data['metodos_pago_id'],
                'observaciones' => trim($data['observaciones'] ?? '') ?: null,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with(
                'error',
                'No se pudo guardar el pago. Verifica que el número de recibo no esté repetido.'
            );
        }

        session()->setFlashdata('success', 'Pago registrado correctamente.');
        return redirect()->to('/pagos');
    }

    public function edit($id)
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pago no encontrado.');
        }

        return view('pagos/form', [
            'title' => 'Editar pago',
            'pago' => $pago,
            'lecturas' => $this->obtenerLectura($pago['lectura_id']),
            'metodosPago' => $this->obtenerMetodosPago(),
            'errors' => [],
        ]);
    }

    public function update($id)
    {
        $pago = $this->pagoModel->find($id);

        if (!$pago) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pago no encontrado.');
        }

        if ((int) $pago['anulado'] === 1) {
            return redirect()->to('/pagos')->with(
                'error',
                'No se puede modificar un pago anulado.'
            );
        }

        $data = $this->request->getPost();

        if (!$this->validarPago($data, $id)) {
            return $this->mostrarFormularioConErrores(
                'Editar pago',
                array_merge($pago, $data),
                $this->validator->getErrors(),
                $pago['lectura_id']
            );
        }

        try {
            $this->establecerUsuarioAuditoria();
            $this->pagoModel->update($id, [
                'fecha_pago' => $data['fecha_pago'],
                'numero_recibo' => trim($data['numero_recibo']),
                'metodos_pago_id' => $data['metodos_pago_id'],
                'observaciones' => trim($data['observaciones'] ?? '') ?: null,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with(
                'error',
                'No se pudo actualizar el pago. Verifica que el número de recibo no esté repetido.'
            );
        }

        session()->setFlashdata('success', 'Pago actualizado correctamente.');
        return redirect()->to('/pagos');
    }

    public function annul($id)
    {
        $pago = $this->pagoModel->find($id);
        if (!$pago) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pago no encontrado.');
        }

        if ((int) $pago['anulado'] === 1) {
            return redirect()->to('/pagos')->with('error', 'El pago ya está anulado.');
        }

        $this->establecerUsuarioAuditoria();
        $this->pagoModel->update($id, [
            'anulado' => 1,
            'anulado_at' => date('Y-m-d H:i:s'),
            'anulado_por' => session()->get('usuario_id'),
        ]);
        session()->setFlashdata('success', 'Pago anulado correctamente.');
        return redirect()->to('/pagos');
    }

    public function receipt($id)
    {
        $pago = db_connect()->table('Tb_Pagos p')
            ->select(['p.*', 'l.fecha AS fecha_lectura', 'l.lectura_anterior', 'l.lectura_actual', 'l.consumo_litros', 'l.monto_base', 'l.monto_exceso', 'c.numero_registro', 'c.direccion_servicio', 'cl.nombre AS cliente', 'cl.telefono', 'cl.direccion AS direccion_cliente', 'ts.tipo_servicio', 'm.metodo', 'u.nombre AS usuario'])
            ->join('Tb_Lecturas l', 'l.lectura_id = p.lectura_id')
            ->join('Tb_Contadores c', 'c.contador_id = l.contador_id')
            ->join('Tb_Clientes cl', 'cl.cliente_id = c.cliente_id')
            ->join('Tb_Tipos_Servicio ts', 'ts.tipo_servicio_id = c.tipo_servicio_id')
            ->join('Tb_Metodos_Pago m', 'm.metodos_pago_id = p.metodos_pago_id')
            ->join('Tb_Usuarios u', 'u.usuario_id = p.usuario_id')
            ->where('p.pago_id', $id)
            ->get()->getRowArray();

        if (!$pago) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pago no encontrado.');
        }

        return view('pagos/receipt', ['title' => 'Recibo de pago', 'pago' => $pago]);
    }

    private function validarPago(array $data, ?int $id = null): bool
    {
        $reciboRule = 'required|max_length[20]';
        if ($id === null) {
            $reciboRule .= '|is_unique[Tb_Pagos.numero_recibo]';
        } else {
            $reciboRule .= "|is_unique[Tb_Pagos.numero_recibo,numero_recibo,pago_id,{$id}]";
        }

        return $this->validate([
            'fecha_pago' => 'required|valid_date[Y-m-d]',
            'numero_recibo' => $reciboRule,
            'metodos_pago_id' => 'required|integer',
            'observaciones' => 'permit_empty|max_length[255]',
        ], [
            'fecha_pago' => [
                'required' => 'La fecha del pago es obligatoria.',
                'valid_date' => 'La fecha del pago no es válida.',
            ],
            'numero_recibo' => [
                'required' => 'El número de recibo es obligatorio.',
                'max_length' => 'El número de recibo no puede superar 20 caracteres.',
                'is_unique' => 'Ya existe un pago con ese número de recibo.',
            ],
            'metodos_pago_id' => [
                'required' => 'El método de pago es obligatorio.',
                'integer' => 'El método de pago no es válido.',
            ],
            'observaciones' => [
                'max_length' => 'Las observaciones no pueden superar 255 caracteres.',
            ],
        ]);
    }

    private function mostrarFormularioConErrores(
        string $title,
        array $pago,
        array $errors,
        ?int $lecturaId = null
    ) {
        return view('pagos/form', [
            'title' => $title,
            'pago' => $pago,
            'lecturas' => $lecturaId
                ? $this->obtenerLectura($lecturaId)
                : $this->obtenerLecturasPendientes(),
            'metodosPago' => $this->obtenerMetodosPago(),
            'errors' => $errors,
        ]);
    }

    private function obtenerLecturasPendientes(): array
    {
        return db_connect()->table('Tb_Lecturas l')
            ->select(['l.lectura_id', 'l.fecha', 'l.monto_total', 'c.numero_registro', 'cl.nombre AS cliente'])
            ->join('Tb_Contadores c', 'c.contador_id = l.contador_id')
            ->join('Tb_Clientes cl', 'cl.cliente_id = c.cliente_id')
            ->join('Tb_Pagos p', 'p.lectura_id = l.lectura_id', 'left')
            ->where('p.pago_id IS NULL', null, false)
            ->orderBy('l.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    private function obtenerLectura($id): array
    {
        return db_connect()->table('Tb_Lecturas l')
            ->select(['l.lectura_id', 'l.fecha', 'l.monto_total', 'c.numero_registro', 'cl.nombre AS cliente'])
            ->join('Tb_Contadores c', 'c.contador_id = l.contador_id')
            ->join('Tb_Clientes cl', 'cl.cliente_id = c.cliente_id')
            ->where('l.lectura_id', $id)
            ->get()
            ->getResultArray();
    }

    private function obtenerLecturaPendiente($id): ?array
    {
        $lecturas = $this->obtenerLecturasPendientes();
        foreach ($lecturas as $lectura) {
            if ((string) $lectura['lectura_id'] === (string) $id) {
                return $lectura;
            }
        }
        return null;
    }

    private function obtenerMetodosPago(): array
    {
        return db_connect()->table('Tb_Metodos_Pago')
            ->orderBy('metodo', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function establecerUsuarioAuditoria(): void
    {
        $usuarioId = session()->get('usuario_id');

        if (!$usuarioId) {
            throw new \RuntimeException('La sesión no contiene un usuario válido.');
        }

        db_connect()->query('SET @usuario_actual = ?', [$usuarioId]);
    }
}
