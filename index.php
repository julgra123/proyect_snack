<?php
require_once __DIR__ . '/config.php';
if (!logged()) {
    header('Location: login.php');
    exit;
}
header('Location: ' . (admin() ? 'productos.php' : 'venta.php'));
exit;
