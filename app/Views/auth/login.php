<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header">
            <h3 class="text-center font-weight-light my-4">Iniciar Sesión</h3>
        </div>
        <div class="card-body">

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')) : ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-floating mb-3">
                    <input
                        class="form-control"
                        id="inputEmail"
                        name="email"
                        type="email"
                        placeholder="nombre@ejemplo.com"
                        value="<?= esc(old('email')) ?>"
                    />
                    <label for="inputEmail">Correo electrónico</label>
                </div>
                <div class="mb-3">
                    <label for="inputPassword" class="form-label"></label>
                    <div class="input-group">
                        <input
                            class="form-control"
                            id="inputPassword"
                            name="password"
                            type="password"
                            placeholder="Contraseña"
                        />
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-end mt-4 mb-0">
                    <button class="btn btn-primary" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('inputPassword');
            const icon = document.getElementById('togglePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Mostrar contraseña');
            }
        });
    </script>
<?= $this->endSection() ?>