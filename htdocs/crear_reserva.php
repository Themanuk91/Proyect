<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
}

$userId = (int)$_SESSION['user_id'];
$idProducto = (int)($_GET['id_producto'] ?? 0);
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT id_producto, nombre FROM productos WHERE id_producto=? AND activo=1 LIMIT 1");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch();
if (!$producto) die('Producto no válido');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fecha = $_POST['fecha'] ?? '';
  $inicio = $_POST['hora_inicio'] ?? '';
  $fin = $_POST['hora_fin'] ?? '';
  $obs = trim($_POST['observaciones'] ?? '');

  if ($fecha === '' || $inicio === '' || $fin === '') {
    $error = 'Completa fecha y horas';
  } elseif ($inicio >= $fin) {
    $error = 'La hora fin debe ser mayor';
  } else {
    $stmt = $pdo->prepare("
      SELECT COUNT(*) FROM reservas
      WHERE id_producto=? AND fecha=? AND estado='activa'
        AND (hora_inicio < ? AND hora_fin > ?)
    ");
    $stmt->execute([$idProducto, $fecha, $fin, $inicio]);

    if ((int)$stmt->fetchColumn() > 0) {
      $error = 'Horario no disponible';
    } else {
      $stmt = $pdo->prepare("
        INSERT INTO reservas (id_usuario,id_producto,fecha,hora_inicio,hora_fin,estado,observaciones)
        VALUES (?,?,?,?,?,'activa',?)
      ");
      $stmt->execute([$userId,$idProducto,$fecha,$inicio,$fin,$obs]);
      $success = 'Reserva creada';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear reserva</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/stylesreserva.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="titulo-negro mb-0">Reservar: <?= htmlspecialchars($producto['nombre']) ?></h2>
        <a class="btn btn-negro" href="mis_reservas.php">Volver</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
        <a class="btn btn-negro" href="mis_reservas.php">Ir a mis reservas</a>
      <?php endif; ?>

      <?php if (!$success): ?>
        <form method="POST" class="mt-3 card card-body" id="bookingForm">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label>Fecha</label>
              <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
              <label>Hora inicio</label>
              <input type="time" name="hora_inicio" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
              <label>Hora fin</label>
              <input type="time" name="hora_fin" class="form-control" required>
            </div>
          </div>

          <div class="mb-3">
            <label>Notas</label>
            <input type="text" name="observaciones" class="form-control" maxlength="255">
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-negro">Reservar</button>
            <a class="btn btn-outline-dark" href="mis_reservas.php">Cancelar</a>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </div>

  <script>
    document.getElementById('bookingForm')?.addEventListener('submit', function (e) {
      const start = document.querySelector('[name="hora_inicio"]').value;
      const end = document.querySelector('[name="hora_fin"]').value;
      if (start && end && start >= end) {
        e.preventDefault();
        alert('La hora fin debe ser mayor que la de inicio');
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
