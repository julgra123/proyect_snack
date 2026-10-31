<?php
require_once 'config.php';

// Si ya inició sesión, redirigir según su rol
if (logged()) {
    if ($_SESSION['user']['rol'] === 'admin') {
        header('Location: productos.php');
        exit;
    } else {
        header('Location: venta.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $u = trim($_POST['usuario'] ?? '');
    $p = $_POST['clave'] ?? '';

    $st = db()->prepare(
        "SELECT * FROM usuarios WHERE usuario = ? AND activo = 1 LIMIT 1"
    );

    $st->execute([$u]);
    $user = $st->fetch();

    if ($user && password_verify($p, $user['clave'])) {

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'usuario' => $user['usuario'],
            'nombre' => $user['nombre'],
            'rol' => $user['rol']
        ];

        // Redireccionar según el tipo de usuario
        if ($user['rol'] === 'admin') {
            header('Location: productos.php');
            exit;
        } elseif ($user['rol'] === 'empleado') {
            header('Location: venta.php');
            exit;
        } else {
            session_destroy();
            $error = 'El rol del usuario no es válido.';
        }

    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ingresar - Snacks</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="login-bg">

<div class="login-card">

    <div class="brand">🍿 Snacks</div>

    <h1>Iniciar sesión</h1>

    <p class="muted">Sistema de ventas e inventario</p>

    <?php if ($error): ?>
        <div class="alert error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <label>Usuario</label>
        <input
            name="usuario"
            required
            autofocus
        >

        <label>Contraseña</label>
        <input
            type="password"
            name="clave"
            required
        >

        <button class="btn primary full">
            Ingresar
        </button>

    </form>

    <p class="hint">
        Acceso para administrador y empleado.
    </p>

</div>

</body>
</html>
