<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4"><?= isset($cliente['cliente_id']) ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('clientes') ?>">Clientes</a></li>
        <li class="breadcrumb-item active"><?= isset($cliente['cliente_id']) ? 'Editar' : 'Crear' ?></li>
    </ol>

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
        $action = isset($cliente['cliente_id'])
            ? base_url('clientes/update/' . $cliente['cliente_id'])
            : base_url('clientes/create');

        $nombre = old('nombre', $cliente['nombre'] ?? '');
        $telefono = old('telefono', $cliente['telefono'] ?? '');
        $direccion = old('direccion', $cliente['direccion'] ?? '');
    ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="post" action="<?= $action ?>">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= esc($nombre) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= esc($telefono) ?>" placeholder="4545-6789 o +502 4545-6789">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <textarea class="form-control" id="direccion" name="direccion" rows="3" maxlength="255" required><?= esc($direccion) ?></textarea>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= isset($cliente['cliente_id']) ? 'Actualizar cliente' : 'Guardar cliente' ?>
                    </button>
                    <a href="<?= base_url('clientes') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>
