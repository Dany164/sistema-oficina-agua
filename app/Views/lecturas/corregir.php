<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">

    <h1 class="mt-4">Corregir lectura</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="<?= base_url('main') ?>">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= base_url('lecturas') ?>">Lecturas</a>
        </li>
        <li class="breadcrumb-item active">Corregir</li>
    </ol>

    <div class="alert alert-warning">
        <strong>Atención:</strong>
        esta acción modifica una lectura histórica.
        Los valores de consumo y monto serán recalculados automáticamente.
    </div>

    <div class="card mb-4">

        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Información de la lectura
        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-6">
                    <label class="form-label">Contador</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= esc($lectura['numero_registro']) ?>"
                        readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= esc($lectura['cliente']) ?>"
                        readonly>
                </div>

            </div>

            <div class="row mb-3">

                <div class="col-md-4">
                    <label class="form-label">Fecha</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= date('d/m/Y', strtotime($lectura['fecha'])) ?>"
                        readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Lectura anterior</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= number_format($lectura['lectura_anterior'], 0, ',', '.') ?> L"
                        readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Lector original</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= esc($lectura['lector']) ?>"
                        readonly>
                </div>

            </div>

            <hr>

            <form
                method="post"
                action="<?= base_url('lecturas/corregir/' . $lectura['lectura_id']) ?>">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label for="lectura_actual" class="form-label">
                        Nueva lectura actual
                    </label>

                    <input
                        type="number"
                        name="lectura_actual"
                        id="lectura_actual"
                        class="form-control"
                        min="<?= esc($lectura['lectura_anterior']) ?>"
                        value="<?= old('lectura_actual', $lectura['lectura_actual']) ?>"
                        required>

                    <div class="form-text">
                        Debe ser mayor o igual que la lectura anterior.
                    </div>

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>
                        Guardar corrección
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

</div>

<?= $this->endSection() ?>