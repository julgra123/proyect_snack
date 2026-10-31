<?php
declare(strict_types=1);

// La sesión debe iniciarse antes de enviar HTML, espacios o texto.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/La_Paz');

// Estas variables se configuran en Render > Environment.
define('DB_HOST', getenv('DB_HOST') ?: 'mysql-3f9a09c4-juliograjeda70-f8cc.f.aivencloud.com');
define('DB_NAME', getenv('DB_NAME') ?: 'sistema_snacks');
define('DB_USER', getenv('DB_USER') ?: 'avnadmin');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'AVNS_kkfSvEBvaIvhMHJRys8');
define('DB_PORT', getenv('DB_PORT') ?: '17820');
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '-----BEGIN CERTIFICATE-----
MIIERDCCAqygAwIBAgIUUGCuo8dio/0u/U4myjfvgG17rVowDQYJKoZIhvcNAQEM
BQAwOjE4MDYGA1UEAwwvNmRkMGMxZWQtN2Q3Ny00Nzk3LWJjZTgtODYyYTJlYWVk
ODJlIFByb2plY3QgQ0EwHhcNMjYxMDMxMDAyMTU5WhcNMzYxMDI4MDAyMTU5WjA6
MTgwNgYDVQQDDC82ZGQwYzFlZC03ZDc3LTQ3OTctYmNlOC04NjJhMmVhZWQ4MmUg
UHJvamVjdCBDQTCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCCAYoCggGBAMk4scoF
MDo+07InB6HZdIgKvtzIpGQ6EibtiiHmmDiNuTSwdI+BkstpLSI2nbgnelSZBU1H
PV9pr1ELVlHFKXdy9R8EN7nkcPP3IPomn/6B3UAGzINuuasB3H/ziYHm2iO2f5uD
wA5mrb3KSzLXCuhHKQwD0Fvca5I4kxVGXpXDhPcJd2epHx48RTxDP1F2LPRqfOpR
BzBQNp836+YTdAnRifhblVoSAZw1Y9UHh6dwKxB/QctgetATK3nsCqeYNm6eqo/K
21UbcSwYvUuLXgzNYLVMmkdzskwWHxqUlv5vFIkSn0cVFn/gf1UQxPW82kRr04B0
+8T/9SL8sZquX9dc2/eLp4A88alePLlN+tHTyWo9OTyOOeFg8DZR+mpFNXAyZq/i
Z4sM9nizaAPnjC5wDi3GaghDwrEw/RDdodc3RvqhznO7r/qQc9PINYJZ1vrLipfj
d2EpSzH/AWU0laVbVaohH8P0s2cObZ1emrqX0vBVQF0KSfoPI4mKLP8QWQIDAQAB
o0IwQDAdBgNVHQ4EFgQUqUYamWhjLBU8HGk1P5Is/HAwQS0wEgYDVR0TAQH/BAgw
BgEB/wIBADALBgNVHQ8EBAMCAQYwDQYJKoZIhvcNAQEMBQADggGBAKE9wu9WeHeo
a7r1rZ6zGuJRmIdSkMkPbb4YxjNDh/SEWBbIxwS01NY1WGKjOkVFL6YZY48iu222
ciQGJ1kGrjs77/vXgHBrCQHHzjx/8K7JXpmFlAmc42GXfz/QnmIU416O/rjFRUi4
/F7/jOe/LpJ03vJEF+mVbnYPp8a54bLgsnQ6ogykm6YuayK3aDlZiyFIL3VJFI3h
b0PlSPRr0K15N5U9xIoMeB8U798aiEMHbrh0PYx6aA6sMGMsZqUAz0Er8bf2PcYT
q47Ahy8oQuLBSX7G8r3HeDTgs/J2BGZVYAWQy8h6ZDzsA2Ecn9C2WX6uOz+DCJsc
cK8V5qrEy9rR3b+fpjMP25cTlXH1sq2wGtbPb+BUhGak1fbU8YAWPJWft9b7FdqM
UnRnPApzetOeWaFlLARJmqWa2TR7oSQ63ORxh2zusE1p5LLjNdPrOlqLhcC31OMP
r6ufgvRn79gaz/Q/AExd3Ltd+jWuXIfTve6UkBsTYrkn3OoSiud/mQ==
-----END CERTIFICATE-----
');
//if (!file_exists(DB_SSL_CA)) {
  //  die('No existe el certificado: ' . DB_SSL_CA);
//}

//if (!is_readable(DB_SSL_CA)) {
  //  die('El certificado existe, pero PHP no puede leerlo.');
//}
function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        if (DB_HOST === '' || DB_PASS === '') {
            throw new RuntimeException(
                'Faltan DB_HOST o DB_PASSWORD en las variables de entorno de Render.'
            );
        }

        $dsn = 'mysql:host=' . DB_HOST .
               ';port=' . DB_PORT .
               ';dbname=' . DB_NAME .
               ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Aiven requiere TLS. En Render se configura DB_SSL_CA con la ruta
        // del archivo CA cargado en Settings/Environment como Secret File.
        if (!is_file(DB_SSL_CA)) {
    throw new RuntimeException(
        'No existe el certificado CA: ' . DB_SSL_CA
    );
}

if (!is_readable(DB_SSL_CA)) {
    throw new RuntimeException(
        'PHP no puede leer el certificado CA. Revisa el Secret File de Render.'
    );
}

        $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function logged(): bool {
    return isset($_SESSION['user']);
}

function admin(): bool {
    return logged() && $_SESSION['user']['rol'] === 'admin';
}

function require_login(): void {
    if (!logged()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if (!admin()) {
        http_response_code(403);
        exit('Acceso exclusivo para administrador.');
    }
}

function money($n): string {
    return number_format((float)$n, 2, ',', '.') . ' Bs';
}

function flash(string $msg, string $type = 'ok'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . h($f['type']) . '">' .
             h($f['msg']) .
             '</div>';
    }
}
