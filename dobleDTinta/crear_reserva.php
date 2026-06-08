<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$idProducto = (int)($_GET['id_producto'] ?? 0);
$error = '';

$stmt = $pdo->prepare("
    SELECT id_producto, nombre, descripcion, precio, tipo_producto
    FROM productos
    WHERE id_producto = ?
      AND activo = 1
      AND tipo_producto = 'tatuaje'
    LIMIT 1
");
$stmt->execute([$idProducto]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: mis_reservas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'] ?? '';
    $inicio = $_POST['hora_inicio'] ?? '';
    $fin = $_POST['hora_fin'] ?? '';
    $obs = trim($_POST['observaciones'] ?? '');

    $hoy = date('Y-m-d');

    if ($fecha === '' || $inicio === '' || $fin === '') {
        $error = 'Debes completar la fecha y las horas.';
    } elseif ($fecha < $hoy) {
        $error = 'No puedes reservar una fecha anterior a hoy.';
    } elseif ($inicio >= $fin) {
        $error = 'La hora de fin debe ser mayor que la hora de inicio.';
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM reservas
            WHERE id_producto = ?
              AND fecha = ?
              AND estado = 'activa'
              AND (hora_inicio < ? AND hora_fin > ?)
        ");
        $stmt->execute([$idProducto, $fecha, $fin, $inicio]);

        if ((int)$stmt->fetchColumn() > 0) {
            $error = 'Ese horario no está disponible.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO reservas 
                (id_usuario, id_producto, fecha, hora_inicio, hora_fin, estado, observaciones)
                VALUES (?, ?, ?, ?, ?, 'activa', ?)
            ");

            $stmt->execute([
                $userId,
                $idProducto,
                $fecha,
                $inicio,
                $fin,
                $obs
            ]);

            header('Location: mis_reservas.php?ok=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear reserva - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="titulo-negro mb-0">
            Reservar: <?= htmlspecialchars($producto['nombre']) ?>
          </h2>

          <p class="texto-negro mb-0">
            <?= htmlspecialchars($producto['descripcion'] ?? '') ?>
          </p>

          <p class="texto-negro mb-0">
            Precio orientativo:
            <strong><?= number_format((float)$producto['precio'], 2, ',', '.') ?> €</strong>
          </p>
        </div>

        <a class="btn btn-negro" href="mis_reservas.php">Volver</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="card card-body" id="bookingForm">

        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Fecha</label>
            <input
              type="date"
              name="fecha"
              class="form-control"
              required
              min="<?= date('Y-m-d') ?>"
              value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>"
            >
          </div>

          <div class="col-md-4 mb-3">
            <label>Hora de inicio</label>
            <input
              type="time"
              name="hora_inicio"
              class="form-control"
              required
              value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '') ?>"
            >
          </div>

          <div class="col-md-4 mb-3">
            <label>Hora de fin</label>
            <input
              type="time"
              name="hora_fin"
              class="form-control"
              required
              value="<?= htmlspecialchars($_POST['hora_fin'] ?? '') ?>"
            >
          </div>
        </div>

        <div class="mb-3">
          <label>Notas para el tatuador</label>
          <input
            type="text"
            name="observaciones"
            class="form-control"
            maxlength="255"
            placeholder="Ejemplo: zona del cuerpo, idea del diseño, tamaño aproximado..."
            value="<?= htmlspecialchars($_POST['observaciones'] ?? '') ?>"
          >
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-negro">Confirmar reserva</button>
          <a class="btn btn-outline-dark" href="mis_reservas.php">Cancelar</a>
        </div>

      </form>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script>
    document.getElementById('bookingForm')?.addEventListener('submit', function (e) {
      const start = document.querySelector('[name="hora_inicio"]').value;
      const end = document.querySelector('[name="hora_fin"]').value;

      if (start && end && start >= end) {
        e.preventDefault();
        alert('La hora de fin debe ser mayor que la hora de inicio.');
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>