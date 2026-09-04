<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4">Clientes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Clientes</li>
    </ol>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users me-1"></i>
                Listado de clientes
            </div>
            <a href="<?= base_url('clientes/new') ?>" class="btn btn-primary btn-sm">Nuevo cliente</a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= esc($cliente['nombre']) ?></td>
                            <td><?= esc($cliente['telefono'] ?: 'Sin teléfono') ?></td>
                            <td><?= esc($cliente['direccion']) ?></td>
                            <td>
                                <a href="<?= base_url('clientes/edit/' . $cliente['cliente_id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <form action="<?= base_url('clientes/delete/' . $cliente['cliente_id']) ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este cliente?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('assets/js/datatables-simple-demo.js') ?>"></script>
<?= $this->endSection() ?>
