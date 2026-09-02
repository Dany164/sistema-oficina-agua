<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">
    <?= isset($tarifa['tarifa_id']) ? 'Editar tarifa' : 'Nueva tarifa' ?>
</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('tarifas') ?>">Tarifas</a>
    </li>
    <li class="breadcrumb-item active">
        <?= isset($tarifa['tarifa_id']) ? 'Editar' : 'Crear' ?>
    </li>
</ol>

<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php if (is_array($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><?= esc($errors) ?></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($error = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= esc($error) ?>
    </div>
<?php endif; ?>

<?php
$esEdicion = isset($tarifa['tarifa_id']);

$action = $esEdicion
    ? base_url('tarifas/update/' . $tarifa['tarifa_id'])
    : base_url('tarifas/create');

$monto = old(
    'monto_por_unidad',
    $tarifa['monto_por_unidad'] ?? ''
);

$vigenteDesde = old(
    'vigente_desde',
    $tarifa['vigente_desde'] ?? ''
);

$vigenteHasta = old(
    'vigente_hasta',
    $tarifa['vigente_hasta'] ?? ''
);

$tipoServicioId = old(
    'tipo_servicio_id',
    $tarifa['tipo_servicio_id'] ?? ''
);
?>

<div class="card mb-4">
    <div class="card-body">

        <form method="post" action="<?= $action ?>">

            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Tipo de servicio -->
                <div class="col-md-6">
                    <label for="tipo_servicio_id" class="form-label">
                        Tipo de servicio
                    </label>

                    <select
                        class="form-select"
                        id="tipo_servicio_id"
                        name="tipo_servicio_id"
                        required
                        <?= $esEdicion ? 'disabled' : '' ?>>

                        <option value="">
                            Seleccione un tipo de servicio
                        </option>

                        <?php if (!empty($tiposServicio)): ?>

                            <?php foreach ($tiposServicio as $tipo): ?>

                                <option
                                    value="<?= esc($tipo['tipo_servicio_id']) ?>"
                                    <?= (string) $tipoServicioId === (string) $tipo['tipo_servicio_id'] ? 'selected' : '' ?>>

                                    <?= esc($tipo['tipo_servicio']) ?>

                                </option>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <option value="" disabled>
                                No hay tipos de servicio registrados
                            </option>

                        <?php endif; ?>

                    </select>

                    <?php if ($esEdicion): ?>
                        <div class="form-text">
                            El tipo de servicio no puede modificarse después de crear la tarifa.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Monto -->
                <div class="col-md-6">
                    <label for="monto_por_unidad" class="form-label">
                        Monto por unidad
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">Q</span>

                        <input
                            type="number"
                            class="form-control"
                            id="monto_por_unidad"
                            name="monto_por_unidad"
                            value="<?= esc($monto) ?>"
                            min="0.01"
                            step="0.01"
                            required>

                    </div>
                </div>

                <!-- Fecha inicio -->
                <div class="col-md-6">
                    <label for="vigente_desde" class="form-label">
                        Vigente desde
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="vigente_desde"
                        name="vigente_desde"
                        value="<?= esc($vigenteDesde) ?>"
                        required
                        <?= $esEdicion ? 'disabled' : '' ?>>

                    <?php if ($esEdicion): ?>
                        <div class="form-text">
                            La fecha de inicio forma parte del historial y no puede modificarse.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fecha fin -->
                <div class="col-md-6">
                    <label for="vigente_hasta" class="form-label">
                        Vigente hasta
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="vigente_hasta"
                        name="vigente_hasta"
                        value="<?= esc($vigenteHasta) ?>"
                        <?= $esEdicion ? 'disabled' : '' ?>>

                    <?php if ($esEdicion): ?>

                        <div class="form-text">
                            La vigencia se administra mediante el registro de nuevas tarifas.
                        </div>

                    <?php else: ?>

                        <div class="form-text">
                            Deje este campo vacío si la tarifa continúa vigente.
                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <div class="mt-4 d-flex gap-2">

                <button type="submit" class="btn btn-primary">
                    <?= $esEdicion
                        ? 'Actualizar tarifa'
                        : 'Guardar tarifa'
                    ?>
                </button>

                <a
                    href="<?= base_url('tarifas') ?>"
                    class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>
</div>
<?= $this->endSection() ?>