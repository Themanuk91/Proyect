<?php
$server = $_SERVER['HTTP_HOST'] ?? '';

if ($server === 'localhost' || $server === '127.0.0.1') {
    $host   = 'localhost';
    $port   = 3306;
    $dbname = 'doble_tinta';
    $user   = 'root';
    $pass   = '';
} else {
    $host   = 'TU_HOST_ONLINE';
    $port   = 3306;
    $dbname = 'TU_BASE_DE_DATOS';
    $user   = 'TU_USUARIO';
    $pass   = 'TU_PASSWORD';
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $e) {
    die("Error de conexión BD: " . $e->getMessage());
}