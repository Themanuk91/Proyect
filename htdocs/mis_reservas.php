<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];

$pdo->prepare("UPDATE reservas SET estado='pasada'
               WHERE id_usuario=? AND estado='activa' AND fecha < CURDATE()")
    ->execute([$userId]);

$filtro = $_GET['filtro'] ?? 'todas';
$allowed = ['todas','activas','canceladas','pasadas'];
if (!in_array($filtro, $allowed, true)) $filtro = 'todas';

$where = '';
if ($filtro === 'activas') $where = " AND r.estado='activa'";
if ($filtro === 'canceladas') $where = " AND r.estado='cancelada'";
if ($filtro === 'pasadas') $where = " AND r.estado='pasada'";

$productos = $pdo->query("SELECT id_producto, nombre, descripcion, precio
                          FROM productos
                          WHERE activo=1 AND tipo_producto='tatuaje'
                          ORDER BY nombre")->fetchAll();

$stmt = $pdo->prepare("SELECT r.*, p.nombre AS producto_nombre
                       FROM reservas r
                       JOIN productos p ON p.id_producto = r.id_producto
                       WHERE r.id_usuario=? $where
                       ORDER BY r.fecha DESC, r.hora_inicio DESC");
$stmt->execute([$userId]);
$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis reservas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <h2 class="mb-3 titulo-negro">Mis reservas</h2>

      <hr>

      <h4 class="titulo-negro">Tatuajes disponibles (reservas)</h4>
      <div class="row">
        <?php if (count($productos) === 0): ?>
          <div class="col-12">
            <div class="alert alert-info">No hay tatuajes disponibles para reservar.</div>
          </div>
        <?php else: ?>
          <?php foreach ($productos as $p): ?>
            <div class="col-md-4 mb-3">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="titulo-negro"><?= htmlspecialchars($p['nombre']) ?></h5>
                  <p><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>
                  <p><strong><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</strong></p>
                  <a href="crear_reserva.php?id_producto=<?= (int)$p['id_producto'] ?>" class="btn btn-negro">Reservar</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <hr>

      <h4 class="titulo-negro">Mis reservas</h4>

      <form method="GET" class="mb-3">
        <select name="filtro" class="form-select w-auto" onchange="this.form.submit()">
          <option value="todas" <?= $filtro==='todas'?'selected':'' ?>>Todas</option>
          <option value="activas" <?= $filtro==='activas'?'selected':'' ?>>Activas</option>
          <option value="canceladas" <?= $filtro==='canceladas'?'selected':'' ?>>Canceladas</option>
          <option value="pasadas" <?= $filtro==='pasadas'?'selected':'' ?>>Pasadas</option>
        </select>
      </form>

      <?php if (!$reservas): ?>
        <div class="alert alert-info">No hay reservas</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Producto</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reservas as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['fecha']) ?></td>
                  <td><?= htmlspecialchars(substr($r['hora_inicio'], 0, 5)) ?> - <?= htmlspecialchars(substr($r['hora_fin'], 0, 5)) ?></td>
                  <td><?= htmlspecialchars($r['producto_nombre']) ?></td>
                  <td><?= htmlspecialchars($r['estado']) ?></td>
                  <td>
                    <?php if ($r['estado'] === 'activa'): ?>
                      <a class="btn btn-sm btn-outline-danger"
                         href="cancelar_reserva.php?id_reserva=<?= (int)$r['id_reserva'] ?>"
                         onclick="return confirm('¿Cancelar reserva?');">Cancelar</a>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
