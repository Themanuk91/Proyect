<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$idCategoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$categoriaActual = '';

if ($idCategoria > 0) {
    $stmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id_categoria = ? AND activa = 1 LIMIT 1");
    $stmt->execute([$idCategoria]);
    $categoriaActual = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT id_producto, nombre, descripcion, precio, imagen, stock
        FROM productos
        WHERE activo = 1
          AND tipo_producto = 'merch'
          AND id_categoria = ?
        ORDER BY id_producto DESC
    ");
    $stmt->execute([$idCategoria]);
    $productos = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT id_producto, nombre, descripcion, precio, imagen, stock
        FROM productos
        WHERE activo = 1
          AND tipo_producto = 'merch'
        ORDER BY id_producto DESC
    ");
    $productos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tienda - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Tienda</h1>

        <?php if ($categoriaActual): ?>
          <p class="texto-negro">
            Categoría seleccionada:
            <strong><?= htmlspecialchars($categoriaActual) ?></strong>
          </p>
        <?php else: ?>
          <p class="texto-negro">
            Productos disponibles para comprar en Doble de Tinta.
          </p>
        <?php endif; ?>
      </header>

      <?php if ($categoriaActual): ?>
        <div class="text-center mb-4">
          <a href="portafolio.php" class="btn btn-negro">Ver toda la tienda</a>
        </div>
      <?php endif; ?>

      <section class="mb-4">
        <div class="row g-4">

          <?php if (count($productos) === 0): ?>

            <div class="col-12">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="titulo-negro">Sin productos en esta categoría</h5>
                  <p>No hay productos dados de alta para mostrar.</p>
                </div>
              </div>
            </div>

          <?php else: ?>

            <?php foreach ($productos as $p): ?>
              <?php
                $img = trim((string)($p['imagen'] ?? ''));

                if ($img !== '' && !preg_match('/\.(png|jpg|jpeg|webp)$/i', $img)) {
                    $img .= '.png';
                }

                $imgPath = ($img !== '') ? "img/{$img}" : "img/placeholder.png";
                $stock = (int)($p['stock'] ?? 0);
              ?>

              <div class="col-12 col-md-4">
                <div class="card h-100">
                  <img
                    src="<?= htmlspecialchars($imgPath) ?>"
                    class="card-img-top"
                    alt="<?= htmlspecialchars($p['nombre']) ?>"
                    onerror="this.src='img/placeholder.png';"
                  >

                  <div class="card-body d-flex flex-column">
                    <h5 class="titulo-negro"><?= htmlspecialchars($p['nombre']) ?></h5>

                    <p><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>

                    <p>
                      <strong><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</strong>
                    </p>

                    <p class="texto-negro">
                      Stock:
                      <strong><?= $stock ?></strong>
                    </p>

                    <div class="mt-auto">
                      <?php if ($stock > 0): ?>
                        <a href="carrito.php?add=<?= (int)$p['id_producto'] ?>" class="btn btn-negro mt-2">
                          Añadir al carrito
                        </a>
                      <?php else: ?>
                        <button class="btn btn-outline-dark mt-2" disabled>
                          Sin stock
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

            <?php endforeach; ?>

          <?php endif; ?>

        </div>
      </section>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>