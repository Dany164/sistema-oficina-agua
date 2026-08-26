<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Contadores</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Gestión de contadores</li>
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
            <i class="fas fa-gauge me-1"></i>
            Listado de contadores
        </div>

        <a href="<?= base_url('contadores/crear') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>
            Nuevo contador
        </a>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Número de serie</th>
                        <th>Lectura inicial</th>
                        <th>Fecha de instalación</th>
                        <th>Fecha de retiro</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($contadores)): ?>

                        <?php foreach ($contadores as $contador): ?>

                            <tr>

                                <td><?= $contador['id'] ?></td>

                                <td><?= $contador['servicio_id'] ?></td>

                                <td>
                                    <?= esc($contador['numero_serie'] ?? 'Sin número') ?>
                                </td>

                                <td>
                                    <?= esc($contador['lectura_inicial']) ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= esc($contador['fecha_instalacion']) ?>
                                </td>

                                <td class="text-nowrap">
                                    <?= esc($contador['fecha_retiro'] ?? '—') ?>
                                </td>

                                <td>

                                    <?php if ($contador['activo']): ?>

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
                                            href="<?= base_url('contadores/editar/' . $contador['id']) ?>"
                                            class="btn btn-warning btn-sm">

                                            <i class="fas fa-pen me-1"></i>
                                            Editar

                                        </a>

                                        <?php if ($contador['activo']): ?>

                                            <form
                                                action="<?= base_url('contadores/retirar/' . $contador['id']) ?>"
                                                method="post"
                                                onsubmit="return confirm('¿Seguro que desea retirar este contador? El contador quedará registrado como retirado en el historial.');">

                                                <?= csrf_field() ?>

                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-ban me-1"></i>
                                                    Retirar
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="text-center py-4">
                                No hay contadores registrados.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?= $this->endSection() ?>