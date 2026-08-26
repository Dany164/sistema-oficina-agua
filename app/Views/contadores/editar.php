<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Editar contador</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('contadores') ?>">Contadores</a>
    </li>
    <li class="breadcrumb-item active">Editar contador</li>
</ol>

<?php if (session()->getFlashdata('errores')): ?>

    <div class="alert alert-danger">

        <ul class="mb-0">

            <?php foreach (session()->getFlashdata('errores') as $error): ?>

                <li><?= esc($error) ?></li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>

    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>

<?php endif; ?>

<div class="card mb-4">

    <div class="card-header">
        <i class="fas fa-pen me-1"></i>
        Modificar contador
    </div>

    <div class="card-body">

        <form
            action="<?= base_url('contadores/actualizar/' . $contador['id']) ?>"
            method="post">

            <?= csrf_field() ?>

            <div class="row g-3">

                <div class="col-12">

                    <label for="servicio_id" class="form-label">
                        Servicio
                    </label>

                    <select
                        name="servicio_id"
                        id="servicio_id"
                        class="form-select"
                        required>

                        <?php foreach ($servicios as $servicio): ?>

                            <option
                                value="<?= $servicio['id'] ?>"
                                <?= old('servicio_id', $contador['servicio_id']) == $servicio['id'] ? 'selected' : '' ?>>

                                <?= esc($servicio['codigo']) ?>
                                -
                                <?= esc($servicio['cliente']) ?>
                                -
                                <?= esc($servicio['direccion'] ?? 'Sin dirección') ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-12 col-md-6">

                    <label for="numero_serie" class="form-label">
                        Número de serie
                    </label>

                    <input
                        type="text"
                        name="numero_serie"
                        id="numero_serie"
                        class="form-control"
                        maxlength="40"
                        value="<?= esc(old('numero_serie', $contador['numero_serie'])) ?>">

                </div>

                <div class="col-12 col-md-6">

                    <label for="lectura_inicial" class="form-label">
                        Lectura inicial
                    </label>

                    <input
                        type="number"
                        name="lectura_inicial"
                        id="lectura_inicial"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?= esc(old('lectura_inicial', $contador['lectura_inicial'])) ?>"
                        required>

                </div>

                <div class="col-12 col-md-6">

                    <label for="fecha_instalacion" class="form-label">
                        Fecha de instalación
                    </label>

                    <input
                        type="date"
                        name="fecha_instalacion"
                        id="fecha_instalacion"
                        class="form-control"
                        value="<?= esc(old('fecha_instalacion', $contador['fecha_instalacion'])) ?>"
                        required>

                </div>

            </div>

            <div class="d-grid gap-2 d-sm-flex mt-4">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Guardar cambios
                </button>

                <a
                    href="<?= base_url('contadores') ?>"
                    class="btn btn-secondary">

                    Cancelar

                </a>

            </div>

        </form>

    </div>

</div>

<?= $this->endSection() ?>