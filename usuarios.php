<?php
require_once 'config.php';
require_admin();

// Crear o actualizar cuentas. Las contraseñas siempre se guardan cifradas con password_hash.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $usuario = trim($_POST['usuario'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $rol = $_POST['rol'] ?? 'empleado';
    $clave = $_POST['clave'] ?? '';

    if ($usuario === '' || $nombre === '' || !in_array($rol, ['admin', 'empleado'], true)) {
        flash('Completa los datos y selecciona un rol válido.', 'error');
        header('Location: usuarios.php'); exit;
    }

    try {
        if ($id > 0) {
            // Al editar, la contraseña solo cambia si se escribe una nueva.
            if ($clave !== '') {
                if (strlen($clave) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres.');
                $st = db()->prepare('UPDATE usuarios SET usuario=?, nombre=?, rol=?, clave=? WHERE id=?');
                $st->execute([$usuario, $nombre, $rol, password_hash($clave, PASSWORD_DEFAULT), $id]);
            } else {
                $st = db()->prepare('UPDATE usuarios SET usuario=?, nombre=?, rol=? WHERE id=?');
                $st->execute([$usuario, $nombre, $rol, $id]);
            }
            flash('Usuario actualizado correctamente.');
        } else {
            if (strlen($clave) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres.');
            $st = db()->prepare('INSERT INTO usuarios(usuario, clave, nombre, rol) VALUES(?,?,?,?)');
            $st->execute([$usuario, password_hash($clave, PASSWORD_DEFAULT), $nombre, $rol]);
            flash($rol === 'admin' ? 'Nuevo administrador registrado.' : 'Nuevo empleado registrado.');
        }
    } catch (PDOException $e) {
        flash($e->getCode() === '23000' ? 'Ese nombre de usuario ya está registrado.' : 'No se pudo guardar el usuario.', 'error');
    } catch (Throwable $e) {
        flash($e->getMessage(), 'error');
    }
    header('Location: usuarios.php'); exit;
}

// Desactivar una cuenta sin borrar el historial de ventas asociado.
if (isset($_GET['desactivar'])) {
    $id = (int)$_GET['desactivar'];
    if ($id !== (int)$_SESSION['user']['id']) {
        db()->prepare("UPDATE usuarios SET activo=0 WHERE id=?")->execute([$id]);
        flash('Cuenta desactivada.');
    } else {
        flash('No puedes desactivar tu propia cuenta.', 'error');
    }
    header('Location: usuarios.php'); exit;
}

$page_title = 'Administrar usuarios';
include 'header.php';
$edit = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT id, usuario, nombre, rol FROM usuarios WHERE id=? AND activo=1');
    $st->execute([(int)$_GET['edit']]); $edit = $st->fetch();
}
$users = db()->query('SELECT id, usuario, nombre, rol, activo, creado_en FROM usuarios ORDER BY creado_en DESC')->fetchAll();
?>
<h1>Administrar usuarios</h1>
<p class="muted">Solo el administrador puede crear administradores y empleados.</p>
<section class="form-card">
    <h2><?= $edit ? 'Editar cuenta' : 'Registrar administrador o empleado' ?></h2>
    <form method="post" class="form-grid" autocomplete="off">
        <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">
        <label>Nombre completo<input name="nombre" required maxlength="100" value="<?= h($edit['nombre'] ?? '') ?>"></label>
        <label>Nombre de usuario<input name="usuario" required maxlength="50" value="<?= h($edit['usuario'] ?? '') ?>"></label>
        <label>Rol<select name="rol" required><option value="empleado" <?= (($edit['rol'] ?? '') === 'empleado') ? 'selected' : '' ?>>Empleado</option><option value="admin" <?= (($edit['rol'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option></select></label>
        <label>Contraseña <?= $edit ? '(dejar vacía para no cambiar)' : '' ?><input type="password" name="clave" <?= $edit ? '' : 'required' ?> minlength="6" autocomplete="new-password"></label>
        <button class="btn primary"><?= $edit ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <?php if ($edit): ?><a class="btn secondary" href="usuarios.php">Cancelar</a><?php endif; ?>
    </form>
</section>
<h2>Cuentas registradas</h2>
<div class="table-wrap"><table>
<tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
<?php foreach ($users as $u): ?><tr>
<td><?= h($u['nombre']) ?></td><td><?= h($u['usuario']) ?></td><td><?= $u['rol'] === 'admin' ? 'Administrador' : 'Empleado' ?></td>
<td><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><?php if ($u['activo']): ?><a href="?edit=<?= (int)$u['id'] ?>">Editar</a><?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']): ?> · <a class="danger-link" href="?desactivar=<?= (int)$u['id'] ?>" onclick="return confirm('¿Desactivar esta cuenta?')">Desactivar</a><?php endif; ?><?php else: ?>—<?php endif; ?></td>
</tr><?php endforeach; ?>
</table></div>
<?php include 'footer.php'; ?>
