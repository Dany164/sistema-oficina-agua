<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Principal</div>
            <a class="nav-link" href="<?= base_url('main') ?>">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>

            <?php $rol = strtolower(trim((string) session()->get('rol_nombre'))); ?>

            <?php if (in_array($rol, ['administrador', 'secretaria'], true)): ?>
            <a class="nav-link" href="<?= base_url('clientes') ?>">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Clientes
            </a>
            <?php endif; ?>
            <?php if ($rol === 'administrador'): ?>
            <a class="nav-link" href="<?= base_url('contadores') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-gauge-high"></i>
                </div>
                Contadores
            </a>
            <?php endif; ?>

            <a class="nav-link" href="<?= base_url('lecturas') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                Lecturas
            </a>


            <?php if ($rol === 'administrador'): ?>
            <a class="nav-link" href="<?= base_url('usuarios') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                Usuarios
            </a>
            <a class="nav-link" href="<?= base_url('servicios') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-faucet-drip"></i>
                </div>
                Servicios
            </a>

            <a class="nav-link" href="<?= base_url('tarifas') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                Tarifas

            </a>
            <?php endif; ?>

            <?php if (in_array($rol, ['administrador', 'secretaria'], true)): ?>
            <a class="nav-link" href="<?= base_url('pagos') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                Pagos
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="sb-sidenav-footer">
    <div class="small">Conectado como:</div>
    <?= esc(session()->get('nombre')) ?> (<?= esc(session()->get('rol_nombre')) ?>)
    </div>
</nav>