<?php
require_once 'config.php';
try {
    $sql = file_get_contents(__DIR__.'/schema.sql');
    // Ejecutar schema.sql desde phpMyAdmin antes de abrir este archivo.
    $q = db()->prepare("SELECT COUNT(*) FROM usuarios");
    $q->execute();
    if ((int)$q->fetchColumn() === 0) {
        $ins=db()->prepare("INSERT INTO usuarios(usuario,clave,nombre,rol) VALUES(?,?,?,?)");
        $ins->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrador', 'admin']);
        $ins->execute(['empleado', password_hash('empleado123', PASSWORD_DEFAULT), 'Empleado', 'empleado']);
    }
    echo '<h2>Configuración completada</h2><p>Administrador: <b>admin</b> / <b>admin123</b></p><p>Empleado: <b>empleado</b> / <b>empleado123</b></p><p><a href="login.php">Ir al inicio de sesión</a></p><b>Por seguridad, elimina setup.php después de usarlo y cambia las contraseñas.</b>';
} catch(Throwable $e) { http_response_code(500); echo 'Error: '.h($e->getMessage()); }
