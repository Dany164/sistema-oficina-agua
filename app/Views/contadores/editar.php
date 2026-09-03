<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h1 class="mt-4">Editar contador</h1>

<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item">
        <a href="<?= base_url('main') ?>">Dashboard</a>
    </li>

    <li class="breadcrumb-item">
        <a href="<?= base_url('contadores') ?>">Contadores</a>
    </li>

    <li class="breadcrumb-item active">
        Editar contador
    </li>
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
        Datos del contador
    </div>

    <div class="card-body">

        <form
            action="<?= base_url(
                'contadores/actualizar/' .
                $contador['contador_id']
            ) ?>"
            method="post"
        >

            <?= csrf_field() ?>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <label for="numero_registro" class="form-label">
                        Número de registro
                    </label>

                    <input
                        type="text"
                        name="numero_registro"
                        id="numero_registro"
                        class="form-control"
                        maxlength="50"
                        value="<?= esc(
                            old(
                                'numero_registro',
                                $contador['numero_registro']
                            )
                        ) ?>"
                        required
                    >

                </div>

                <div class="col-12 col-md-6">

                    <label for="direccion_servicio" class="form-label">
                        Dirección del servicio
                    </label>

                    <input
                        type="text"
                        name="direccion_servicio"
                        id="direccion_servicio"
                        class="form-control"
                        maxlength="50"
                        value="<?= esc(
                            old(
                                'direccion_servicio',
                                $contador['direccion_servicio']
                            )
                        ) ?>"
                        required
                    >

                </div>

                <div class="col-12 col-md-6">

                    <label for="cliente_id" class="form-label">
                        Cliente
                    </label>

                    <select
                        name="cliente_id"
                        id="cliente_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Seleccione un cliente
                        </option>

                        <?php foreach ($clientes as $cliente): ?>

                            <?php
                                $clienteSeleccionado = old(
                                    'cliente_id',
                                    $contador['cliente_id']
                                );
                            ?>

                            <option
                                value="<?= $cliente['cliente_id'] ?>"
                                <?= $clienteSeleccionado == $cliente['cliente_id']
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= esc($cliente['nombre']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-12 col-md-6">

                    <label for="tipo_servicio_id" class="form-label">
                        Tipo de servicio
                    </label>

                    <select
                        name="tipo_servicio_id"
                        id="tipo_servicio_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Seleccione un tipo de servicio
                        </option>

                        <?php foreach ($tiposServicio as $tipoServicio): ?>

                            <?php
                                $tipoSeleccionado = old(
                                    'tipo_servicio_id',
                                    $contador['tipo_servicio_id']
                                );
                            ?>

                            <option
                                value="<?= $tipoServicio['tipo_servicio_id'] ?>"
                                <?= $tipoSeleccionado == $tipoServicio['tipo_servicio_id']
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= esc($tipoServicio['tipo_servicio']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <div class="d-grid gap-2 d-sm-flex mt-4">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Actualizar contador
                </button>

                <a
                    href="<?= base_url('contadores') ?>"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</div>

<?= $this->endSection() ?>