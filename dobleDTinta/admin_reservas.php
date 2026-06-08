<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ? LIMIT 1");
$stmt->execute([$userId]);
$u = $stmt->fetch();

$role = $u['rol'] ?? '';

if ($role !== 'admin' && $role !== 'empleado') {
  header('Location: cliente.php');
  exit;
}

$pdo->query("
  UPDATE reservas
  SET estado = 'pasada'
  WHERE estado = 'activa'
    AND fecha < CURDATE()
");

if (isset($_GET['cancel'], $_GET['id'])) {
  $id = (int)$_GET['id'];

  $stmt = $pdo->prepare("
    UPDATE reservas
    SET estado = 'cancelada'
    WHERE id_reserva = ?
      AND estado = 'activa'
  ");
  $stmt->execute([$id]);

  header('Location: admin_reservas.php');
  exit;
}

$filtro = $_GET['filtro'] ?? 'todas';
$allowed = ['todas', 'activas', 'canceladas', 'pasadas'];

if (!in_array($filtro, $allowed, true)) {
  $filtro = 'todas';
}

$where = '';

if ($filtro === 'activas') {
  $where = " WHERE r.estado = 'activa'";
}

if ($filtro === 'canceladas') {
  $where = " WHERE r.estado = 'cancelada'";
}

if ($filtro === 'pasadas') {
  $where = " WHERE r.estado = 'pasada'";
}

$stmt = $pdo->query("
  SELECT 
    r.*,
    u.email,
    CONCAT(u.nombre, ' ', u.apellidos) AS usuario_nombre,
    p.nombre AS producto_nombre
  FROM reservas r
  JOIN usuarios u ON u.id_usuario = r.id_usuario
  JOIN productos p ON p.id_producto = r.id_producto
  $where
  ORDER BY r.fecha DESC, r.hora_inicio DESC
");

$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de reservas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="titulo-negro mb-0">Gestión de reservas</h2>
          <p class="texto-negro mb-0">
            Desde aquí se pueden consultar y cancelar reservas.
          </p>
        </div>

        <div class="d-flex gap-2">
          <a href="admin.php" class="btn btn-negro">Volver</a>
          <a href="logout.php" class="btn btn-negro">Salir</a>
        </div>
      </div>

      <form method="GET" class="mb-3">
        <select name="filtro" class="form-select w-auto" onchange="this.form.submit()">
          <option value="todas" <?= $filtro === 'todas' ? 'selected' : '' ?>>Todas</option>
          <option value="activas" <?= $filtro === 'activas' ? 'selected' : '' ?>>Activas</option>
          <option value="canceladas" <?= $filtro === 'canceladas' ? 'selected' : '' ?>>Canceladas</option>
          <option value="pasadas" <?= $filtro === 'pasadas' ? 'selected' : '' ?>>Pasadas</option>
        </select>
      </form>

      <?php if (!$reservas): ?>

        <div class="alert alert-info">No hay reservas para mostrar.</div>

      <?php else: ?>

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

                    <td>
                      <?= htmlspecialchars(substr($r['hora_inicio'], 0, 5)) ?>
                      -
                      <?= htmlspecialchars(substr($r['hora_fin'], 0, 5)) ?>
                    </td>

                    <td>
                      <?= htmlspecialchars($r['usuario_nombre']) ?><br>
                      <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                    </td>

                    <td><?= htmlspecialchars($r['producto_nombre']) ?></td>

                    <td>
                      <?php
                        $estado = $r['estado'] ?? '';
                        $claseEstado = 'estado-pendiente';

                        if ($estado === 'activa') {
                            $claseEstado = 'estado-activa';
                        } elseif ($estado === 'cancelada') {
                            $claseEstado = 'estado-cancelada';
                        } elseif ($estado === 'pasada') {
                            $claseEstado = 'estado-pasada';
                        }
                      ?>

                      <span class="badge-estado <?= $claseEstado ?>">
                        <?= htmlspecialchars($estado) ?>
                      </span>
                    </td>

                    <td>
                      <?php if (($r['estado'] ?? '') === 'activa'): ?>
                        <a
                          class="btn btn-sm btn-outline-danger"
                          href="admin_reservas.php?cancel=1&id=<?= (int)$r['id_reserva'] ?>"
                          onclick="return confirm('¿Cancelar esta reserva?');"
                        >
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

      <?php endif; ?>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
