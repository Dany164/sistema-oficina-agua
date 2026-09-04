<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $esEdicion = isset($pago['pago_id']); ?>
<h1 class="mt-4"><?= $esEdicion ? 'Editar pago' : 'Registrar pago' ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= base_url('pagos') ?>">Pagos</a></li>
    <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Crear' ?></li>
</ol>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0">
        <?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>
<?php if ($message = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif; ?>

<?php
$action = $esEdicion ? base_url('pagos/update/' . $pago['pago_id']) : base_url('pagos/create');
$lecturaSeleccionada = old('lectura_id', $pago['lectura_id'] ?? '');
$lecturaSeleccionada = $lecturaSeleccionada ?: ($_GET['lectura_id'] ?? '');
$lecturas = $lecturas ?? [];
?>
<div class="card mb-4"><div class="card-body">
    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="lectura_id" class="form-label">Lectura</label>
                <?php if ($esEdicion): ?>
                    <?php $lectura = $lecturas[0] ?? null; ?>
                    <input type="text" class="form-control" value="<?= $lectura ? esc($lectura['cliente'] . ' - ' . $lectura['fecha']) : 'Lectura no disponible' ?>" disabled>
                    <input type="hidden" name="lectura_id" value="<?= esc($pago['lectura_id']) ?>">
                <?php else: ?>
                    <select class="form-select" id="lectura_id" name="lectura_id" required>
                        <option value="">Seleccione una lectura pendiente</option>
                        <?php foreach ($lecturas as $lectura): ?>
                            <option value="<?= esc($lectura['lectura_id']) ?>" data-monto="<?= esc($lectura['monto_total']) ?>" <?= (string) $lecturaSeleccionada === (string) $lectura['lectura_id'] ? 'selected' : '' ?>>
                                <?= esc($lectura['cliente'] . ' - ' . $lectura['fecha'] . ' - Q ' . number_format((float) $lectura['monto_total'], 2)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <label for="monto" class="form-label">Monto</label>
                <input type="text" class="form-control" id="monto" value="<?= $esEdicion ? 'Q ' . number_format((float) ($pago['monto'] ?? 0), 2) : 'Seleccione una lectura' ?>" readonly>
                <div class="form-text">Se calcula desde la lectura.</div>
            </div>
            <div class="col-md-3">
                <label for="fecha_pago" class="form-label">Fecha de pago</label>
                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?= esc(old('fecha_pago', $pago['fecha_pago'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="numero_recibo" class="form-label">Número de recibo</label>
                <input type="text" class="form-control" id="numero_recibo" name="numero_recibo" maxlength="20" value="<?= esc(old('numero_recibo', $pago['numero_recibo'] ?? '')) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="metodos_pago_id" class="form-label">Método de pago</label>
                <select class="form-select" id="metodos_pago_id" name="metodos_pago_id" required>
                    <option value="">Seleccione un método</option>
                    <?php foreach ($metodosPago as $metodo): ?>
                        <option value="<?= esc($metodo['metodos_pago_id']) ?>" <?= (string) old('metodos_pago_id', $pago['metodos_pago_id'] ?? '') === (string) $metodo['metodos_pago_id'] ? 'selected' : '' ?>><?= esc($metodo['metodo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" maxlength="255" rows="3"><?= esc(old('observaciones', $pago['observaciones'] ?? '')) ?></textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Actualizar pago' : 'Guardar pago' ?></button>
            <a href="<?= base_url('pagos') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div></div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if (!$esEdicion): ?>
<script>
    document.getElementById('lectura_id').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        document.getElementById('monto').value = option.dataset.monto ? 'Q ' + Number(option.dataset.monto).toFixed(2) : 'Seleccione una lectura';
    });
</script>
<?php endif; ?>
<?= $this->endSection() ?>
