<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contadores</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container py-4 py-md-5">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">

        <h1 class="mb-0">Contadores</h1>

        <a href="<?= base_url('contadores/crear') ?>" class="btn btn-primary">
            Nuevo contador
        </a>

    </div>

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

                                    <span class="badge text-bg-success">
                                        Activo
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-secondary">
                                        Retirado
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a
                                        href="<?= base_url('contadores/editar/' . $contador['id']) ?>"
                                        class="btn btn-warning btn-sm">

                                        Editar

                                    </a>

                                    <?php if ($contador['activo']): ?>

                                        <form
                                            action="<?= base_url('contadores/retirar/' . $contador['id']) ?>"
                                            method="post"
                                            onsubmit="return confirm('¿Está seguro de retirar este contador?');">

                                            <?= csrf_field() ?>

                                            <button type="submit" class="btn btn-danger btn-sm">
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

</body>
</html>