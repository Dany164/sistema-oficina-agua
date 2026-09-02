<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4">Servicios</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="<?= base_url('main') ?>">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Servicios</li>
    </ol>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-faucet-drip me-1"></i>
                Listado de servicios
            </div>

            <a href="<?= base_url('servicios/new') ?>"
               class="btn btn-primary btn-sm">
                Nuevo servicio
            </a>
        </div>

        <div class="card-body">

            <table id="datatablesSimple"
                   class="table table-striped table-bordered">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de servicio</th>
                        <th>Litros incluidos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($servicios as $servicio): ?>

                        <tr>

                            <td>
                                <?= esc($servicio['tipo_servicio_id']) ?>
                            </td>

                            <td>
                                <?= esc($servicio['tipo_servicio']) ?>
                            </td>

                            <td>
                                <?= $servicio['litros_incluidos'] !== null
                                    ? esc($servicio['litros_incluidos'])
                                    : 'No especificado' ?>
                            </td>

                            <td>

                                <a href="<?= base_url(
                                    'servicios/edit/' .
                                    $servicio['tipo_servicio_id']
                                ) ?>"
                                   class="btn btn-warning btn-sm">
                                    Editar
                                </a>

                                <form
                                    action="<?= base_url(
                                        'servicios/delete/' .
                                        $servicio['tipo_servicio_id']
                                    ) ?>"
                                    method="post"
                                    class="d-inline">

                                    <?= csrf_field() ?>

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm(
                                            '¿Deseas eliminar este servicio?'
                                        )">

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

    <script src="<?= base_url(
        'assets/js/datatables-simple-demo.js'
    ) ?>"></script>

<?= $this->endSection() ?>