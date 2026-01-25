<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: cliente.php'); exit; }

if (isset($_GET['cancel']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE reservas SET estado='cancelada' WHERE id_reserva=? AND estado='activa'")->execute([$id]);
    header('Location: admin_reservas.php'); exit;
}

$stmt = $pdo->query("
  SELECT r.*, u.email, CONCAT(u.nombre,' ',u.apellidos) AS usuario_nombre, p.nombre AS producto_nombre
  FROM reservas r
  JOIN usuarios u ON u.id_usuario = r.id_usuario
  JOIN productos p ON p.id_producto = r.id_producto
  ORDER BY r.fecha DESC, r.hora_inicio DESC
");
$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin - Reservas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/stylescliente.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="titulo-negro mb-0">Reservas</h2>
        <div class="d-flex gap-2">
          <a href="admin.php" class="btn btn-negro">Volver</a>
          <a href="logout.php" class="btn btn-negro">Salir</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Usuario</th>
                <th>Producto</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($reservas as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['fecha']) ?></td>
                <td><?= htmlspecialchars(substr($r['hora_inicio'],0,5)) ?> - <?= htmlspecialchars(substr($r['hora_fin'],0,5)) ?></td>
                <td>
                  <?= htmlspecialchars($r['usuario_nombre']) ?><br>
                  <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($r['producto_nombre']) ?></td>
                <td><?= htmlspecialchars($r['estado']) ?></td>
                <td>
                  <?php if ($r['estado'] === 'activa'): ?>
                    <a class="btn btn-sm btn-outline-danger"
                       href="admin_reservas.php?cancel=1&id=<?= (int)$r['id_reserva'] ?>"
                       onclick="return confirm('¿Cancelar esta reserva?');">
                      Cancelar
                    </a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
