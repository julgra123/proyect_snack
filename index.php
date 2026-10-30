<?php
require_once 'config.php';
if (!logged()) { header('Location: login.php'); exit; }
header('Location: '.(admin()?'admin.php':'venta.php'));
