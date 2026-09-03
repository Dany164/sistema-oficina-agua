<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Lecturas</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('main') ?>">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Lecturas</li>
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
            <i class="fas fa-tachometer-alt me-1"></i>
            Listado de lecturas
        </div>

        <a href="<?= base_url('lecturas/new') ?>" class="btn btn-primary btn-sm">
            Nueva lectura
        </a>
    </div>

    <div class="card-body">

        <table id="datatablesSimple" class="table table-striped table-bordered">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Contador</th>
                    <th>Cliente</th>
                    <th>Tipo de servicio</th>
                    <th>Lectura anterior</th>
                    <th>Lectura actual</th>
                    <th>Consumo (L)</th>
                    <th>Exceso (L)</th>
                    <th>Monto total</th>
                    <th>Lector</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($lecturas as $lectura): ?>

                    <tr>

                        <td>
                            <?= esc($lectura['lectura_id']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['fecha']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['numero_registro']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['cliente']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['tipo_servicio']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['lectura_anterior']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['lectura_actual']) ?>
                        </td>

                        <td>
                            <?= esc($lectura['consumo_litros']) ?>
                        </td>

                        <td>
                            <?php if ($lectura['litros_exceso'] !== null): ?>
                                <?= esc($lectura['litros_exceso']) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            Q <?= number_format((float) $lectura['monto_total'], 2) ?>
                        </td>

                        <td>
                            <?= esc($lectura['lector']) ?>
                        </td>
                        <td>
                            <a
                                href="<?= base_url('lecturas/recibo/' . $lectura['lectura_id']) ?>"
                                class="btn btn-sm btn-primary"
                                target="_blank">
                                Ver recibo
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