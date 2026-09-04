<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1 class="mt-4">Pagos</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= base_url('main') ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Pagos</li>
</ol>

<?php if ($message = session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc($message) ?></div>
<?php endif; ?>
<?php if ($message = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body border-bottom">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filtros['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filtros['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <input type="text" name="cliente" class="form-control" value="<?= esc($filtros['cliente'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pagado" <?= ($filtros['estado'] ?? '') === 'pagado' ? 'selected' : '' ?>>Pagados</option>
                    <option value="anulado" <?= ($filtros['estado'] ?? '') === 'anulado' ? 'selected' : '' ?>>Anulados</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">Filtrar</button>
                <a class="btn btn-outline-secondary" href="<?= base_url('pagos') ?>">Limpiar</a>
            </div>
        </form>
    </div>
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-money-bill-wave me-1"></i> Listado de pagos</span>
        <a href="<?= base_url('pagos/new') ?>" class="btn btn-primary btn-sm">Registrar pago</a>
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Contador</th>
                    <th>Monto</th>                    <th>Método</th><th>Estado</th><th>Usuario</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $pago): ?>
                    <tr>
                        <td><?= esc($pago['numero_recibo']) ?></td>
                        <td><?= esc($pago['fecha_pago']) ?></td>
                        <td><?= esc($pago['cliente']) ?></td>
                        <td><?= esc($pago['numero_registro']) ?></td>
                        <td>Q <?= number_format((float) $pago['monto'], 2) ?></td>
                        <td><?= esc($pago['metodo']) ?></td>
                        <td>
                            <?php if ((int) $pago['anulado'] === 1): ?>
                                <span class="badge bg-danger">Anulado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($pago['usuario']) ?></td>
                        <td>
                            <a href="<?= base_url('pagos/receipt/' . $pago['pago_id']) ?>" class="btn btn-primary btn-sm" target="_blank">Recibo</a>
                            <?php if ((int) $pago['anulado'] === 0): ?>
                            <a href="<?= base_url('pagos/edit/' . $pago['pago_id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <form action="<?= base_url('pagos/annul/' . $pago['pago_id']) ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas anular este pago?')">Anular</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/datatables-simple-demo.js') ?>"></script>
<?= $this->endSection() ?>
