<?php
// Diagnóstico rápido (borra este archivo al terminar si quieres)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<h2>Diagnóstico Doble de Tinta</h2>';
echo '<p><b>PHP:</b> ' . phpversion() . '</p>';

echo '<p><b>Ruta actual:</b> ' . __DIR__ . '</p>';

echo '<h3>Prueba conexión BD</h3>';
try {
    require_once __DIR__ . '/config/db.php';
    $stmt = $pdo->query('SELECT 1');
    echo '<p style="color:green;"><b>OK:</b> conexión a BD correcta.</p>';
} catch (Throwable $e) {
    echo '<p style="color:red;"><b>ERROR:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '<h3>Prueba sesión</h3>';
session_start();
$_SESSION['__test'] = 'ok';
echo '<p>SESSION OK</p>';
