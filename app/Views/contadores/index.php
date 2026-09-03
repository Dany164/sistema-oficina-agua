<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Contadores</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('main') ?>">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Contadores</li>
</ol>

<?php if (session()->getFlashdata('mensaje')): ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('mensaje')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="card mb-4">

    <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">

        <div>
            <i class="fas fa-gauge-high me-1"></i>
            Listado de contadores
        </div>

        <a href="<?= base_url('contadores/crear') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Nuevo contador
        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table id="datatablesSimple" class="table table-bordered table-striped align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Número de registro</th>
                        <th>Cliente</th>
                        <th>Tipo de servicio</th>
                        <th>Dirección del servicio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($contadores as $contador): ?>

                        <tr>

                            <td>
                                <?= esc($contador['contador_id']) ?>
                            </td>

                            <td>
                                <?= esc($contador['numero_registro']) ?>
                            </td>

                            <td>
                                <?= esc($contador['cliente']) ?>
                            </td>

                            <td>
                                <?= esc($contador['tipo_servicio']) ?>
                            </td>

                            <td>
                                <?= esc($contador['direccion_servicio']) ?>
                            </td>

                            <td>

                                <?php if ($contador['estado']): ?>

                                    <span class="badge bg-success">
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Retirado
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a
                                        href="<?= base_url(
                                            'contadores/editar/' .
                                            $contador['contador_id']
                                        ) ?>"
                                        class="btn btn-warning btn-sm">

                                        <i class="fas fa-pen me-1"></i>
                                        Editar

                                    </a>

                                    <?php if ($contador['estado']): ?>

                                        <form
                                            action="<?= base_url(
                                                'contadores/retirar/' .
                                                $contador['contador_id']
                                            ) ?>"
                                            method="post"
                                            onsubmit="return confirm('¿Está seguro de que desea retirar este contador?');">

                                            <?= csrf_field() ?>

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-ban me-1"></i>
                                                Retirar
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <form
                                            action="<?= base_url(
                                                'contadores/reactivar/' .
                                                $contador['contador_id']
                                            ) ?>"
                                            method="post"
                                            onsubmit="return confirm('¿Está seguro de que desea reactivar este contador?');">

                                            <?= csrf_field() ?>

                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-rotate-left me-1"></i>
                                                Reactivar
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="<?= base_url('assets/js/datatables-simple-demo.js') ?>"></script>

<?= $this->endSection() ?>