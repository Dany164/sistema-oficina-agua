<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Nueva lectura</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('main') ?>">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="<?= base_url('lecturas') ?>">Lecturas</a>
    </li>
    <li class="breadcrumb-item active">Nueva lectura</li>
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

<div class="card mb-4">

    <div class="card-header">
        <i class="fas fa-tachometer-alt me-1"></i>
        Registrar nueva lectura
    </div>

    <div class="card-body">

        <form method="post" action="<?= base_url('lecturas/create') ?>">

            <?= csrf_field() ?>

            <div class="row g-3">

                <!-- Contador -->
                <div class="col-md-12">
                    <label for="contador_id" class="form-label">
                        Contador
                    </label>

                    <select
                        class="form-select"
                        id="contador_id"
                        name="contador_id"
                        required>

                        <option value="">
                            Seleccione un contador
                        </option>

                        <?php if (!empty($contadores)): ?>

                            <?php foreach ($contadores as $contador): ?>

                                <option
                                    value="<?= esc($contador['contador_id']) ?>"
                                    <?= (string) old('contador_id') === (string) $contador['contador_id'] ? 'selected' : '' ?>>

                                    <?= esc($contador['numero_registro']) ?>
                                    -
                                    <?= esc($contador['cliente']) ?>
                                    -
                                    <?= esc($contador['tipo_servicio']) ?>

                                </option>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <option value="" disabled>
                                No hay contadores activos registrados
                            </option>

                        <?php endif; ?>

                    </select>

                    <div class="form-text">
                        Seleccione el contador al que corresponde la lectura.
                    </div>
                </div>

                <!-- Fecha -->
                <div class="col-md-6">

                    <label for="fecha" class="form-label">
                        Fecha de lectura
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fecha"
                        name="fecha"
                        value="<?= esc(old('fecha', date('Y-m-d'))) ?>"
                        required>

                </div>

                <!-- Lectura anterior -->
                <div class="col-md-6">

                    <label for="lectura_anterior" class="form-label">
                        Lectura anterior
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="lectura_anterior"
                        name="lectura_anterior"
                        value="<?= esc(old('lectura_anterior')) ?>"
                        min="0">

                    <div class="form-text">
                        Solo es necesaria cuando el contador no tiene una lectura anterior registrada.
                    </div>

                </div>

                <!-- Lectura actual -->
                <div class="col-md-6">

                    <label for="lectura_actual" class="form-label">
                        Lectura actual
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="lectura_actual"
                        name="lectura_actual"
                        value="<?= esc(old('lectura_actual')) ?>"
                        min="0"
                        required>

                    <div class="form-text">
                        Ingrese la lectura actual mostrada por el contador.
                    </div>

                </div>

            </div>

            <div class="mt-4 d-flex gap-2">

                <button type="submit" class="btn btn-primary">
                    Registrar lectura
                </button>

                <a
                    href="<?= base_url('lecturas') ?>"
                    class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>
</div>

<?= $this->endSection() ?>