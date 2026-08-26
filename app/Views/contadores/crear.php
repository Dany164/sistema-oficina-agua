<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar contador</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container py-4 py-md-5">

    <div class="row justify-content-center">

        <div class="col-12 col-lg-10">

            <h1 class="mb-4">Registrar contador</h1>

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

            <form action="<?= base_url('contadores/guardar') ?>" method="post">

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

                            <option value="">
                                Seleccione un servicio
                            </option>

                            <?php foreach ($servicios as $servicio): ?>

                                <option
                                    value="<?= $servicio['id'] ?>"
                                    <?= old('servicio_id') == $servicio['id'] ? 'selected' : '' ?>>

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
                            value="<?= esc(old('numero_serie')) ?>">

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
                            value="<?= esc(old('lectura_inicial', '0')) ?>"
                            min="0"
                            step="0.01"
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
                            value="<?= esc(old('fecha_instalacion')) ?>"
                            required>

                    </div>

                </div>

                <div class="d-grid gap-2 d-sm-flex mt-4">

                    <button type="submit" class="btn btn-primary">
                        Guardar contador
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

</div>

</body>
</html>