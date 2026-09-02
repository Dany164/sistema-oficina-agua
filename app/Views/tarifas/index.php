<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Tarifas</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('/') ?>">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Tarifas</li>
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
            <i class="fas fa-money-bill-wave me-1"></i>
            Listado de tarifas
        </div>

        <a href="<?= base_url('tarifas/new') ?>" class="btn btn-primary btn-sm">
            Nueva tarifa
        </a>
    </div>

    <div class="card-body">

        <table id="datatablesSimple" class="table table-striped table-bordered">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo de servicio</th>
                    <th>Monto por unidad</th>
                    <th>Vigente desde</th>
                    <th>Vigente hasta</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($tarifas as $tarifa): ?>

                    <tr>

                        <td>
                            <?= esc($tarifa['tarifa_id']) ?>
                        </td>

                        <td>
                            <?= esc($tarifa['tipo_servicio'] ?? 'Sin tipo') ?>
                        </td>

                        <td>
                            Q <?= number_format((float) $tarifa['monto_por_unidad'], 2) ?>
                        </td>

                        <td>
                            <?= esc($tarifa['vigente_desde']) ?>
                        </td>

                        <td>
                            <?php if (!empty($tarifa['vigente_hasta'])): ?>
                                <?= esc($tarifa['vigente_hasta']) ?>
                            <?php else: ?>
                                <span class="badge bg-success">Vigente</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a
                                href="<?= base_url('tarifas/edit/' . $tarifa['tarifa_id']) ?>"
                                class="btn btn-warning btn-sm">
                                Editar
                            </a>
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