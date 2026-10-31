<?php
declare(strict_types=1);

// Iniciar sesión una sola vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/La_Paz');

// Configuración de Aiven mediante variables de entorno
define('DB_HOST', getenv('DB_HOST') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'sistema_snacks');
define('DB_USER', getenv('DB_USER') ?: 'avnadmin');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'AVNS_kkfSvEBvaIvhMHJRys8');
define('DB_PORT', getenv('DB_PORT') ?: '17820');
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '');

function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        if (DB_HOST === '' || DB_PASS === '') {
            throw new RuntimeException(
                'Faltan las variables de conexión a la base de datos.'
            );
        }

        $dsn = 'mysql:host=' . DB_HOST .
               ';port=' . DB_PORT .
               ';dbname=' . DB_NAME .
               ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        // Certificado SSL de Aiven
        if (DB_SSL_CA !== '') {
            if (!file_exists(DB_SSL_CA)) {
                throw new RuntimeException(
                    'No se encontró el certificado SSL de Aiven.'
                );
            }

            $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
        }

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
    $_SESSION['flash'] = [
        'msg' => $msg,
        'type' => $type
    ];
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
