<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $esEdicion = isset($usuario['usuario_id']); ?>
<h1 class="mt-4"><?= $esEdicion ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= base_url('usuarios') ?>">Usuarios</a></li>
    <li class="breadcrumb-item active"><?= $esEdicion ? 'Editar' : 'Crear' ?></li>
</ol>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0">
        <?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<?php
$action = $esEdicion ? base_url('usuarios/update/' . $usuario['usuario_id']) : base_url('usuarios/create');
$nombre = old('nombre', $usuario['nombre'] ?? '');
$email = old('email', $usuario['email'] ?? '');
$rolId = old('rol_id', $usuario['rol_id'] ?? '');
?>
<div class="card mb-4"><div class="card-body">
    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= esc($nombre) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= esc($email) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="rol_id" class="form-label">Rol</label>
                <select class="form-select" id="rol_id" name="rol_id" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= esc($rol['rol_id']) ?>" <?= (string) $rolId === (string) $rol['rol_id'] ? 'selected' : '' ?>><?= esc($rol['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" minlength="6" maxlength="72" <?= $esEdicion ? '' : 'required' ?>>
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <?php if ($esEdicion): ?><div class="form-text">Déjala vacía para conservar la contraseña actual.</div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label for="password_confirmacion" class="form-label">Confirmar contraseña</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password_confirmacion" name="password_confirmacion" minlength="6" maxlength="72" <?= $esEdicion ? '' : 'required' ?>>
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password_confirmacion" aria-label="Mostrar confirmación de contraseña" title="Mostrar confirmación de contraseña">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Actualizar usuario' : 'Guardar usuario' ?></button>
            <a href="<?= base_url('usuarios') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div></div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.toggle-password').forEach(function (boton) {
    boton.addEventListener('click', function () {
        const campo = document.getElementById(this.dataset.target);
        const icono = this.querySelector('i');
        const mostrar = campo.type === 'password';

        campo.type = mostrar ? 'text' : 'password';
        icono.classList.toggle('fa-eye', !mostrar);
        icono.classList.toggle('fa-eye-slash', mostrar);
        this.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        this.setAttribute('title', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
});
</script>
<?= $this->endSection() ?>
