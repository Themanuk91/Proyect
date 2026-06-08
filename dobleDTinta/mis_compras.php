<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT id_compra, total, estado, fecha_compra
    FROM compras
    WHERE id_usuario = ?
    ORDER BY fecha_compra DESC
");
$stmt->execute([$userId]);
$compras = $stmt->fetchAll();

$detallesPorCompra = [];

if ($compras) {
    $ids = array_column($compras, 'id_compra');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id_compra, nombre_producto, precio_unitario, cantidad, subtotal
        FROM compra_detalles
        WHERE id_compra IN ($placeholders)
        ORDER BY id_detalle ASC
    ");
    $stmt->execute($ids);
    $detalles = $stmt->fetchAll();

    foreach ($detalles as $detalle) {
        $detallesPorCompra[(int)$detalle['id_compra']][] = $detalle;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis compras - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="titulo-negro mb-0">Mis compras</h2>
          <p class="texto-negro mb-0">
            Consulta aquí el historial de productos comprados en la tienda.
          </p>
        </div>

        <div class="d-flex gap-2">
          <a href="cliente.php" class="btn btn-negro">Volver</a>
          <a href="portafolio.php" class="btn btn-negro">Ir a la tienda</a>
        </div>
      </div>

      <?php if (!$compras): ?>

        <div class="card">
          <div class="card-body">
            <h5 class="titulo-negro">No tienes compras todavía</h5>
            <p>Cuando finalices una compra, aparecerá en esta sección.</p>
            <a href="portafolio.php" class="btn btn-negro">Ver tienda</a>
          </div>
        </div>

      <?php else: ?>

        <?php foreach ($compras as $compra): ?>
          <?php
            $estado = $compra['estado'] ?? '';
            $claseEstado = 'estado-pendiente';

            if ($estado === 'pagada') {
                $claseEstado = 'estado-pagada';
            } elseif ($estado === 'cancelada') {
                $claseEstado = 'estado-cancelada';
            } elseif ($estado === 'error') {
                $claseEstado = 'estado-error';
            } elseif ($estado === 'pendiente') {
                $claseEstado = 'estado-pendiente';
            }
          ?>

          <div class="card mb-4">
            <div class="card-body">

              <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-3">
                <div>
                  <h5 class="titulo-negro mb-1">
                    Compra #<?= (int)$compra['id_compra'] ?>
                  </h5>

                  <p class="mb-1">
                    Fecha:
                    <strong><?= htmlspecialchars($compra['fecha_compra']) ?></strong>
                  </p>

                  <p class="mb-0">
                    Estado:
                    <span class="badge-estado <?= $claseEstado ?>">
                      <?= htmlspecialchars($estado) ?>
                    </span>
                  </p>
                </div>

                <div>
                  <h5 class="titulo-negro">
                    Total:
                    <?= number_format((float)$compra['total'], 2, ',', '.') ?> €
                  </h5>
                </div>
              </div>

              <?php if (!empty($detallesPorCompra[(int)$compra['id_compra']])): ?>
                <div class="table-responsive">
                  <table class="table table-striped align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Producto</th>
                        <th>Precio unidad</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php foreach ($detallesPorCompra[(int)$compra['id_compra']] as $detalle): ?>
                        <tr>
                          <td><?= htmlspecialchars($detalle['nombre_producto']) ?></td>
                          <td><?= number_format((float)$detalle['precio_unitario'], 2, ',', '.') ?> €</td>
                          <td><?= (int)$detalle['cantidad'] ?></td>
                          <td><?= number_format((float)$detalle['subtotal'], 2, ',', '.') ?> €</td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="alert alert-info mb-0">
                  Esta compra no tiene detalles registrados.
                </div>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>