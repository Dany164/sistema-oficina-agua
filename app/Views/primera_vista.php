<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4">¡Hola! Esta es la primera vista con el layout de SB Admin</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Saludo</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-check-circle me-1"></i>
            Prueba del layout
        </div>
        <div class="card-body">
            Si ves esta tarjeta con el sidebar oscuro a la izquierda y el navbar arriba, el layout está funcionando correctamente.
        </div>
    </div>

<?= $this->endSection() ?>