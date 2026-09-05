<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1 class="mt-4">Usuarios</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= base_url('main') ?>">Dashboard</a></li>
    <li class="breadcrumb-item active">Usuarios</li>
</ol>

<?php if ($message = session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc($message) ?></div>
<?php endif; ?>
<?php if ($message = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user-shield me-1"></i> Listado de usuarios</span>
        <a href="<?= base_url('usuarios/new') ?>" class="btn btn-primary btn-sm">Nuevo usuario</a>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-md-4"><input class="form-control" name="buscar" placeholder="Buscar nombre o correo" value="<?= esc($filtros['buscar']) ?>"></div>
            <div class="col-md-3"><select class="form-select" name="rol"><option value="">Todos los roles</option><?php foreach ($roles as $rol): ?><option value="<?= esc($rol['rol_id']) ?>" <?= (int) $filtros['rol'] === (int) $rol['rol_id'] ? 'selected' : '' ?>><?= esc($rol['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><select class="form-select" name="orden"><option value="nombre" <?= $filtros['orden'] === 'u.nombre' ? 'selected' : '' ?>>Nombre</option><option value="email" <?= $filtros['orden'] === 'u.email' ? 'selected' : '' ?>>Correo</option><option value="rol" <?= $filtros['orden'] === 'r.nombre' ? 'selected' : '' ?>>Rol</option></select></div>
            <div class="col-md-2"><select class="form-select" name="direccion"><option value="asc" <?= $filtros['direccion'] === 'ASC' ? 'selected' : '' ?>>Ascendente</option><option value="desc" <?= $filtros['direccion'] === 'DESC' ? 'selected' : '' ?>>Descendente</option></select></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Filtrar</button></div>
        </form>
        <table id="datatablesSimple" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= esc($usuario['nombre']) ?></td>
                        <td><?= esc($usuario['email']) ?></td>
                        <td><?= esc($usuario['rol_nombre']) ?></td>
                        <td><span class="badge bg-<?= ($usuario['activo'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($usuario['activo'] ?? 1) ? 'Activo' : 'Bloqueado' ?></span></td>
                        <td><?= esc($usuario['ultimo_acceso'] ?? 'Nunca') ?></td>
                        <td>
                            <a href="<?= base_url('usuarios/edit/' . $usuario['usuario_id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <?php if ((int) $usuario['usuario_id'] !== (int) session()->get('usuario_id')): ?>
                                <form action="<?= base_url('usuarios/toggle/' . $usuario['usuario_id']) ?>" method="post" class="d-inline"><?= csrf_field() ?><button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('¿Deseas cambiar el estado de este usuario?')"><?= ($usuario['activo'] ?? 1) ? 'Bloquear' : 'Activar' ?></button></form>
                            <?php endif; ?>
                            <?php if ((int) $usuario['usuario_id'] !== (int) session()->get('usuario_id')): ?>
                                <form action="<?= base_url('usuarios/delete/' . $usuario['usuario_id']) ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este usuario?')">Eliminar</button>
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
