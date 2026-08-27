<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4"><?= isset($cliente['id']) ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= base_url('clientes') ?>">Clientes</a></li>
        <li class="breadcrumb-item active"><?= isset($cliente['id']) ? 'Editar' : 'Crear' ?></li>
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
        $action = isset($cliente['id'])
            ? base_url('clientes/update/' . $cliente['id'])
            : base_url('clientes/create');

        $nombre = old('nombre', $cliente['nombre'] ?? '');
        $telefono = old('telefono', $cliente['telefono'] ?? '');
        $activo = old('activo', $cliente['activo'] ?? 1);
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

                <div class="mt-4 d-flex gap-2">
                    <input type="hidden" name="activo" value="0">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="activo"
                            name="activo"
                            value="1"
                            <?= (string) $activo === '1' ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="activo">Cliente activo</label>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= isset($cliente['id']) ? 'Actualizar cliente' : 'Guardar cliente' ?>
                    </button>
                    <a href="<?= base_url('clientes') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>
