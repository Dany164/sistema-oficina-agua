<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
    <div class="sb-sidenav-menu">
        <div class="nav">
            <div class="sb-sidenav-menu-heading">Principal</div>
            <a class="nav-link" href="<?= base_url('main') ?>">
                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                Dashboard
            </a>

            <a class="nav-link" href="<?= base_url('clientes') ?>">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Clientes
            </a>
            <a class="nav-link" href="<?= base_url('contadores') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-gauge-high"></i>
                </div>
                Contadores
            </a>


            <a class="nav-link" href="<?= base_url('servicios') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-faucet-drip"></i>
                </div>
                Servicios

            <a class="nav-link" href="<?= base_url('tarifas') ?>">
                <div class="sb-nav-link-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                Tarifas

            </a>
        </div>
    </div>
    <div class="sb-sidenav-footer">
    <div class="small">Conectado como:</div>
    <?= esc(session()->get('nombre')) ?> (<?= esc(session()->get('rol_nombre')) ?>)
    </div>
</nav>