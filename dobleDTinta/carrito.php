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

    $stmt = $pdo->prepare("
        SELECT id_producto, nombre, precio, imagen, activo, tipo_producto, stock
        FROM productos
        WHERE id_producto IN ($placeholders)
    ");
    $stmt->execute($ids);
    $productosBD = $stmt->fetchAll();

    $map = [];

    foreach ($productosBD as $p) {
        $map[(int)$p['id_producto']] = $p;
    }

    foreach ($ids as $id) {
        $id = (int)$id;

        if (!isset($map[$id])) {
            unset($_SESSION['carrito'][$id]);
            $eliminados++;
            continue;
        }

        $producto = $map[$id];

        if ((int)$producto['activo'] !== 1 || ($producto['tipo_producto'] ?? '') !== 'merch' || (int)$producto['stock'] <= 0) {
            unset($_SESSION['carrito'][$id]);
            $eliminados++;
            continue;
        }

        if ((int)$_SESSION['carrito'][$id]['cantidad'] > (int)$producto['stock']) {
            $_SESSION['carrito'][$id]['cantidad'] = (int)$producto['stock'];
        }

        $_SESSION['carrito'][$id]['nombre'] = $producto['nombre'];
        $_SESSION['carrito'][$id]['precio'] = (float)$producto['precio'];
        $_SESSION['carrito'][$id]['imagen'] = $producto['imagen'];
        $_SESSION['carrito'][$id]['stock'] = (int)$producto['stock'];
    }
}

if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];

    $stmt = $pdo->prepare("
        SELECT id_producto, nombre, precio, imagen, tipo_producto, activo, stock
        FROM productos
        WHERE id_producto = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto || (int)$producto['activo'] !== 1) {
        $mensaje = 'Producto no disponible.';
    } elseif (($producto['tipo_producto'] ?? '') !== 'merch') {
        $mensaje = 'Este producto no se puede comprar. Para tatuajes usa la sección de reservas.';
    } elseif ((int)$producto['stock'] <= 0) {
        $mensaje = 'No hay stock disponible de este producto.';
    } else {
        $cantidadActual = isset($_SESSION['carrito'][$id]) ? (int)$_SESSION['carrito'][$id]['cantidad'] : 0;

        if ($cantidadActual >= (int)$producto['stock']) {
            $mensaje = 'No puedes añadir más unidades porque no hay más stock.';
        } else {
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                $_SESSION['carrito'][$id] = [
                    'id'       => (int)$producto['id_producto'],
                    'nombre'   => $producto['nombre'],
                    'precio'   => (float)$producto['precio'],
                    'imagen'   => $producto['imagen'],
                    'cantidad' => 1,
                    'stock'    => (int)$producto['stock']
                ];
            }
        }
    }

    header('Location: carrito.php' . ($mensaje ? '?msg=' . urlencode($mensaje) : ''));
    exit;
}

if (isset($_GET['sumar'])) {
    $id = (int)$_GET['sumar'];

    if (isset($_SESSION['carrito'][$id])) {
        $stmt = $pdo->prepare("
            SELECT stock, activo, tipo_producto
            FROM productos
            WHERE id_producto = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();

        if ($producto && (int)$producto['activo'] === 1 && ($producto['tipo_producto'] ?? '') === 'merch') {
            if ((int)$_SESSION['carrito'][$id]['cantidad'] < (int)$producto['stock']) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                $mensaje = 'No hay más stock disponible.';
            }
        } else {
            unset($_SESSION['carrito'][$id]);
            $mensaje = 'Producto no disponible.';
        }
    }

    header('Location: carrito.php' . ($mensaje ? '?msg=' . urlencode($mensaje) : ''));
    exit;
}

if (isset($_GET['restar'])) {
    $id = (int)$_GET['restar'];

    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad']--;

        if ((int)$_SESSION['carrito'][$id]['cantidad'] <= 0) {
            unset($_SESSION['carrito'][$id]);
        }
    }

    header('Location: carrito.php');
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
$totalPrecio = 0.0;

foreach ($_SESSION['carrito'] as $item) {
    $totalUnidades += (int)$item['cantidad'];
    $totalPrecio += (int)$item['cantidad'] * (float)$item['precio'];
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
          Revisa tus productos, modifica cantidades y finaliza la compra.
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
          <div class="card-body text-center">
            <div class="icono-panel mb-3">🛒</div>
            <h5 class="titulo-negro">Carrito vacío</h5>
            <p>No has añadido ningún producto todavía.</p>
            <a href="portafolio.php" class="btn btn-negro mt-2">Ir a la tienda</a>
          </div>
        </div>

      <?php else: ?>

        <div class="row g-4">

          <div class="col-12 col-lg-8">
            <div class="card">
              <div class="card-body table-responsive">
                <table class="table table-bordered table-striped mb-0">
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Imagen</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                      <th>Stock</th>
                      <th>Subtotal</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                      <?php
                        $img = trim((string)($item['imagen'] ?? ''));

                        if ($img !== '' && !preg_match('/\.(png|jpg|jpeg|webp)$/i', $img)) {
                            $img .= '.png';
                        }

                        $imgPath = ($img !== '') ? "img/{$img}" : "img/placeholder.png";
                      ?>

                      <tr>
                        <td>
                          <strong><?= htmlspecialchars($item['nombre']) ?></strong>
                        </td>

                        <td style="width:120px;">
                          <img
                            src="<?= htmlspecialchars($imgPath) ?>"
                            alt="<?= htmlspecialchars($item['nombre']) ?>"
                            class="img-fluid"
                            onerror="this.src='img/placeholder.png';"
                          >
                        </td>

                        <td><?= number_format((float)$item['precio'], 2, ',', '.') ?> €</td>

                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <a href="carrito.php?restar=<?= (int)$item['id'] ?>" class="btn btn-sm btn-negro">-</a>

                            <span class="badge-estado estado-pendiente">
                              <?= (int)$item['cantidad'] ?>
                            </span>

                            <a href="carrito.php?sumar=<?= (int)$item['id'] ?>" class="btn btn-sm btn-negro">+</a>
                          </div>
                        </td>

                        <td>
                          <span class="stock-pill">
                            <?= (int)($item['stock'] ?? 0) ?>
                          </span>
                        </td>

                        <td>
                          <strong>
                            <?= number_format((int)$item['cantidad'] * (float)$item['precio'], 2, ',', '.') ?> €
                          </strong>
                        </td>

                        <td>
                          <a
                            href="carrito.php?del=<?= (int)$item['id'] ?>"
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('¿Quitar este producto del carrito?');"
                          >
                            Quitar
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="mt-3">
              <a href="portafolio.php" class="btn btn-negro">Seguir comprando</a>

              <a
                href="carrito.php?vaciar=1"
                class="btn btn-outline-danger"
                onclick="return confirm('¿Vaciar todo el carrito?');"
              >
                Vaciar carrito
              </a>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="card">
              <div class="card-body">
                <h4 class="titulo-negro mb-3">Resumen del pedido</h4>

                <div class="d-flex justify-content-between mb-2">
                  <span>Unidades</span>
                  <strong><?= (int)$totalUnidades ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-3">
                  <span>Total</span>
                  <strong class="product-price">
                    <?= number_format((float)$totalPrecio, 2, ',', '.') ?> €
                  </strong>
                </div>

                <hr>

                <p class="texto-negro">
                  La compra se finalizará mediante Stripe en modo prueba.
                </p>

                <a href="finalizar_compra.php" class="btn btn-negro w-100">
                  Finalizar compra
                </a>
              </div>
            </div>
          </div>

        </div>

      <?php endif; ?>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>