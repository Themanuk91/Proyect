<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$mensaje = '';
$eliminados = 0;

if (!empty($_SESSION['carrito'])) {
    $ids = array_keys($_SESSION['carrito']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT id_producto, activo, tipo_producto
                           FROM productos
                           WHERE id_producto IN ($placeholders)");
    $stmt->execute($ids);
    $validos = $stmt->fetchAll();

    $map = [];
    foreach ($validos as $v) {
        $map[(int)$v['id_producto']] = $v;
    }

    foreach ($ids as $id) {
        $id = (int)$id;

        if (!isset($map[$id])) {
            unset($_SESSION['carrito'][$id]);
            $eliminados++;
            continue;
        }

        if ((int)$map[$id]['activo'] !== 1 || ($map[$id]['tipo_producto'] ?? '') !== 'merch') {
            unset($_SESSION['carrito'][$id]);
            $eliminados++;
            continue;
        }
    }
}

if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];

    $stmt = $pdo->prepare("SELECT id_producto, nombre, precio, imagen, tipo_producto, activo
                           FROM productos
                           WHERE id_producto = ?
                           LIMIT 1");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto || (int)$producto['activo'] !== 1) {
        $mensaje = 'Producto no disponible.';
    } elseif (($producto['tipo_producto'] ?? '') !== 'merch') {
        $mensaje = 'Este producto no se puede comprar. Para tatuajes usa la sección de reservas.';
    } else {
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad']++;
        } else {
            $_SESSION['carrito'][$id] = [
                'id'       => (int)$producto['id_producto'],
                'nombre'   => $producto['nombre'],
                'precio'   => (float)$producto['precio'],
                'imagen'   => $producto['imagen'],
                'cantidad' => 1
            ];
        }
    }

    header('Location: carrito.php' . ($mensaje ? '?msg=' . urlencode($mensaje) : ''));
    exit;
}

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if (isset($_SESSION['carrito'][$id])) {
        unset($_SESSION['carrito'][$id]);
    }
    header('Location: carrito.php');
    exit;
}

if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header('Location: carrito.php');
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] !== '') {
    $mensaje = (string)$_GET['msg'];
}

$totalUnidades = 0;
$totalPrecio   = 0.0;

foreach ($_SESSION['carrito'] as $item) {
    $totalUnidades += (int)$item['cantidad'];
    $totalPrecio   += (int)$item['cantidad'] * (float)$item['precio'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Carrito - Doble de Tinta</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Carrito de la compra</h1>
        <p class="texto-negro">
          Tienes <?= (int)$totalUnidades ?> artículo(s) en el carrito.
          Total: <strong><?= number_format((float)$totalPrecio, 2, ',', '.') ?> €</strong>
        </p>
      </header>

      <?php if ($eliminados > 0): ?>
        <div class="alert alert-warning">
          Se han eliminado <?= (int)$eliminados ?> producto(s) del carrito porque ya no están disponibles.
        </div>
      <?php endif; ?>

      <?php if ($mensaje): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <?php if ($totalUnidades === 0): ?>
        <div class="card">
          <div class="card-body">
            <h5 class="titulo-negro">Carrito vacío</h5>
            <p>No has añadido ningún producto todavía.</p>
            <a href="portafolio.php" class="btn btn-negro mt-2">Ir a la tienda</a>
          </div>
        </div>
      <?php else: ?>

        <div class="card mb-3">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-striped mb-0">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Imagen</th>
                  <th>Precio</th>
                  <th>Cantidad</th>
                  <th>Subtotal</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($_SESSION['carrito'] as $item): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td style="width:120px;">
                      <img src="img/<?= htmlspecialchars($item['imagen'] ?? 'placeholder.png') ?>"
                           alt="<?= htmlspecialchars($item['nombre']) ?>"
                           class="img-fluid"
                           onerror="this.src='img/placeholder.png';">
                    </td>
                    <td><?= number_format((float)$item['precio'], 2, ',', '.') ?> €</td>
                    <td><?= (int)$item['cantidad'] ?></td>
                    <td><?= number_format((int)$item['cantidad'] * (float)$item['precio'], 2, ',', '.') ?> €</td>
                    <td>
                      <a href="carrito.php?del=<?= (int)$item['id'] ?>" class="btn btn-sm btn-negro">
                        Quitar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div>
            <a href="portafolio.php" class="btn btn-negro">Seguir comprando</a>
          </div>
          <div class="d-flex gap-2">
            <a href="carrito.php?vaciar=1" class="btn btn-negro">Vaciar carrito</a>
            <a href="#" class="btn btn-negro">Finalizar compra (más adelante)</a>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
