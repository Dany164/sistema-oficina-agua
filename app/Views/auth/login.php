<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header">
            <h3 class="text-center font-weight-light my-4">Iniciar Sesión</h3>
        </div>
        <div class="card-body">
            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-floating mb-3">
                    <input class="form-control" id="inputEmail" name="email" type="email" placeholder="nombre@ejemplo.com" />
                    <label for="inputEmail">Correo electrónico</label>
                </div>
                <div class="form-floating mb-3">
                    <input class="form-control" id="inputPassword" name="password" type="password" placeholder="Contraseña" />
                    <label for="inputPassword">Contraseña</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="inputRememberPassword" name="remember" type="checkbox" value="1" />
                    <label class="form-check-label" for="inputRememberPassword">Recordar contraseña</label>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                    <a class="small" href="#!">¿Olvidaste tu contraseña?</a>
                    <button class="btn btn-primary" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3">
            <div class="small"><a href="#!">¿Necesitas una cuenta? Regístrate</a></div>
        </div>
    </div>

<?= $this->endSection() ?>