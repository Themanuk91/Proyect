<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$idCategoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$categoriaActual = '';

$pdo->prepare("
    UPDATE reservas
    SET estado = 'pasada'
    WHERE id_usuario = ?
      AND estado = 'activa'
      AND fecha < CURDATE()
")->execute([$userId]);

$filtro = $_GET['filtro'] ?? 'todas';
$allowed = ['todas', 'activas', 'canceladas', 'pasadas'];

if (!in_array($filtro, $allowed, true)) {
    $filtro = 'todas';
}

$where = '';

if ($filtro === 'activas') {
    $where = " AND r.estado = 'activa'";
}

if ($filtro === 'canceladas') {
    $where = " AND r.estado = 'cancelada'";
}

if ($filtro === 'pasadas') {
    $where = " AND r.estado = 'pasada'";
}

if ($idCategoria > 0) {
    $stmt = $pdo->prepare("
        SELECT nombre
        FROM categorias
        WHERE id_categoria = ?
          AND activa = 1
        LIMIT 1
    ");
    $stmt->execute([$idCategoria]);
    $categoriaActual = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT id_producto, nombre, descripcion, precio
        FROM productos
        WHERE activo = 1
          AND tipo_producto = 'tatuaje'
          AND id_categoria = ?
        ORDER BY nombre
    ");
    $stmt->execute([$idCategoria]);
    $productos = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT id_producto, nombre, descripcion, precio
        FROM productos
        WHERE activo = 1
          AND tipo_producto = 'tatuaje'
        ORDER BY nombre
    ");
    $productos = $stmt->fetchAll();
}

$stmt = $pdo->prepare("
    SELECT r.*, p.nombre AS producto_nombre
    FROM reservas r
    JOIN productos p ON p.id_producto = r.id_producto
    WHERE r.id_usuario = ? $where
    ORDER BY r.fecha DESC, r.hora_inicio DESC
");
$stmt->execute([$userId]);
$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis reservas - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="titulo-negro mb-0">Mis reservas</h2>
          <p class="texto-negro mb-0">
            Reserva una cita para tatuajes y consulta el estado de tus reservas.
          </p>
        </div>

        <div class="d-flex gap-2">
          <a href="cliente.php" class="btn btn-negro">Volver</a>
          <a href="reservas.php" class="btn btn-negro">Reservar</a>
        </div>
      </div>

      <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">
          Reserva creada correctamente.
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['cancelada'])): ?>
        <div class="alert alert-success">
          Reserva cancelada correctamente.
        </div>
      <?php endif; ?>

      <hr>

      <h4 class="titulo-negro">Tatuajes disponibles</h4>

      <?php if ($categoriaActual): ?>
        <p class="texto-negro">
          Categoría seleccionada:
          <strong><?= htmlspecialchars($categoriaActual) ?></strong>
        </p>

        <div class="mb-3">
          <a href="mis_reservas.php" class="btn btn-negro">Ver todos los tatuajes</a>
        </div>
      <?php endif; ?>

      <div class="row g-4 mb-4">

        <?php if (count($productos) === 0): ?>

          <div class="col-12">
            <div class="alert alert-info">
              No hay tatuajes disponibles para esta categoría.
            </div>
          </div>

        <?php else: ?>

          <?php foreach ($productos as $p): ?>
            <div class="col-12 col-md-4">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <h5 class="titulo-negro"><?= htmlspecialchars($p['nombre']) ?></h5>

                  <p><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>

                  <p>
                    <strong><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</strong>
                  </p>

                  <a href="crear_reserva.php?id_producto=<?= (int)$p['id_producto'] ?>" class="btn btn-negro mt-auto">
                    Reservar
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

        <?php endif; ?>

      </div>

      <hr>

      <h4 class="titulo-negro">Historial de reservas</h4>

      <form method="GET" class="mb-3 d-flex gap-2 flex-wrap align-items-center">

        <?php if ($idCategoria > 0): ?>
          <input type="hidden" name="categoria" value="<?= (int)$idCategoria ?>">
        <?php endif; ?>

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
                          href="cancelar_reserva.php?id_reserva=<?= (int)$r['id_reserva'] ?>"
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