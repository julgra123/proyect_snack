<?php require_once 'config.php'; require_login(); ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($page_title??'Snacks')?></title><link rel="stylesheet" href="assets/style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php">🍿 Snacks</a><nav>
<a href="venta.php">Ventas</a><?php if(admin()): ?><a href="productos.php">Productos</a><a href="inventario.php">Inventario</a><a href="usuarios.php">Usuarios</a><a href="informe.php">Informes</a><?php endif; ?>
</nav><div class="user"><?=h($_SESSION['user']['nombre'])?> · <?=h($_SESSION['user']['rol'])?> <a class="logout" href="logout.php">Salir</a></div></header>
<main class="container"><?php show_flash(); ?>
