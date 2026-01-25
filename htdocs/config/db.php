<?php
$host   = 'sql107.infinityfree.com';
$port   = 3306;
$dbname = 'if0_40646869_doble_tinta';
$user   = 'if0_40646869';
$pass   = 'CKGNHaQtygk'; 

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Error de conexión BD: " . $e->getMessage());
}