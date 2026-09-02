<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4"><?= esc($title) ?></h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="<?= base_url('main') ?>">Dashboard</a>
        </li>

        <li class="breadcrumb-item">
            <a href="<?= base_url('servicios') ?>">Servicios</a>
        </li>

        <li class="breadcrumb-item active">
            <?= esc($title) ?>
        </li>
    </ol>

    <div class="card mb-4">

        <div class="card-header">
            <i class="fas fa-faucet-drip me-1"></i>
            <?= esc($title) ?>
        </div>

        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
                $esEdicion = !empty($servicio['tipo_servicio_id']);

                $accion = $esEdicion
                    ? base_url(
                        'servicios/update/' .
                        $servicio['tipo_servicio_id']
                    )
                    : base_url('servicios/create');
            ?>

            <form action="<?= $accion ?>" method="post">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="tipo_servicio" class="form-label">
                        Tipo de servicio
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="tipo_servicio"
                        name="tipo_servicio"
                        maxlength="50"
                        required
                        value="<?= esc(
                            old(
                                'tipo_servicio',
                                $servicio['tipo_servicio'] ?? ''
                            )
                        ) ?>"
                    >
                </div>

                <div class="mb-3">
                    <label for="litros_incluidos" class="form-label">
                        Litros incluidos
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="litros_incluidos"
                        name="litros_incluidos"
                        min="0"
                        step="1"
                        value="<?= esc(
                            old(
                                'litros_incluidos',
                                $servicio['litros_incluidos'] ?? ''
                            )
                        ) ?>"
                    >

                    <div class="form-text">
                        Puedes dejar este campo vacío si el servicio no tiene
                        una cantidad específica de litros incluidos.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <?= $esEdicion ? 'Actualizar servicio' : 'Guardar servicio' ?>
                </button>

                <a
                    href="<?= base_url('servicios') ?>"
                    class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>

    </div>

<?= $this->endSection() ?>