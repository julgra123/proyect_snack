<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('America/La_Paz');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'snacks');
define('DB_USER', 'root');
define('DB_PASS', '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function logged(): bool { return isset($_SESSION['user']); }
function admin(): bool { return logged() && $_SESSION['user']['rol'] === 'admin'; }
function require_login(): void {
    if (!logged()) { header('Location: login.php'); exit; }
}
function require_admin(): void {
    require_login();
    if (!admin()) { http_response_code(403); exit('Acceso exclusivo para administrador.'); }
}
function money($n): string { return number_format((float)$n, 2, ',', '.').' Bs'; }
function flash(string $msg, string $type='ok'): void { $_SESSION['flash']=['msg'=>$msg,'type'=>$type]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f=$_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert '.h($f['type']).'">'.h($f['msg']).'</div>';
    }
}
