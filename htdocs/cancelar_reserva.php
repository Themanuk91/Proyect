<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];
$idReserva = (int)($_GET['id_reserva'] ?? 0);

$pdo->prepare("
  UPDATE reservas
  SET estado = 'cancelada'
  WHERE id_reserva = ? AND id_usuario = ? AND estado = 'activa'
  LIMIT 1
")->execute([$idReserva, $userId]);

header('Location: mis_reservas.php');
exit;
