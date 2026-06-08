<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$idReserva = (int)($_GET['id_reserva'] ?? 0);

if ($idReserva <= 0) {
    header('Location: mis_reservas.php');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE reservas
    SET estado = 'cancelada'
    WHERE id_reserva = ?
      AND id_usuario = ?
      AND estado = 'activa'
    LIMIT 1
");

$stmt->execute([
    $idReserva,
    $userId
]);

header('Location: mis_reservas.php?cancelada=1');
exit;