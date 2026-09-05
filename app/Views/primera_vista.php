<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Contadores pendientes de lectura
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Contador</th>
                        <th>Dirección</th>
                        <th>Cliente</th>
                        <th>Última lectura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contadoresPendientes)) : ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No hay contadores pendientes de lectura.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($contadoresPendientes as $contador) : ?>
                            <tr>
                                <td><?= esc($contador['numero_registro']) ?></td>
                                <td><?= esc($contador['direccion_servicio']) ?></td>
                                <td><?= esc($contador['cliente']) ?></td>
                                <td>
                                    <?php if ($contador['ultima_lectura'] !== null) : ?>
                                        <?= esc($contador['ultima_lectura']) ?>
                                    <?php else : ?>
                                        Nunca leído
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('lecturas/new?contador_id=' . $contador['contador_id']) ?>" class="btn btn-sm btn-warning">
                                        Registrar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
         </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Clientes al día vs. pendientes de pago
                </div>
                <div class="card-body">
                    <canvas id="clientesChart" width="100%" height="35"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users me-1"></i>
                Estado de cuenta por cliente
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Última lectura</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente) : ?>
                        <tr>
                            <td><?= esc($cliente['cliente']) ?></td>
                            <td>
                                <?php if ($cliente['ultima_lectura'] !== null) : ?>
                                    <?= esc($cliente['ultima_lectura']) ?>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cliente['estado'] === 'Pendiente') : ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                <?php else : ?>
                                    <span class="badge bg-success">Al día</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('lecturas') ?>" class="btn btn-sm btn-primary">
                                    Ver detalles
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('assets/js/datatables-simple-demo.js') ?>"></script>

   <script>
    const ctx = document.getElementById('clientesChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Al día', 'Pendientes'],
            datasets: [{
                label: 'Número de clientes',
                backgroundColor: ['#1cc88a', '#e74a3b'],
                data: [
                    <?= (int) $clientesAlDia ?>,
                    <?= (int) $clientesPendientes ?>
                ],
            }],
        },
        options: {
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        precision: 0,
                    },
                }],
            },
        },
    });
</script>

     

<?= $this->endSection() ?>