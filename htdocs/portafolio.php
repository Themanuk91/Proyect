<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$productos = $pdo->query("SELECT id_producto, nombre, descripcion, precio, imagen
                          FROM productos
                          WHERE activo = 1 AND tipo_producto = 'merch'
                          ORDER BY id_producto DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Portafolio - Doble de Tinta</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Tienda</h1>
        <p class="texto-negro">Productos disponibles para comprar en Doble de Tinta.</p>
      </header>

      <section class="mb-4">
        <div class="row g-4">

          <?php if (count($productos) === 0): ?>
            <div class="col-12">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="titulo-negro">Sin productos todavía</h5>
                  <p>No hay productos dados de alta en la tienda.</p>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($productos as $p): ?>
              <?php
                $img = trim((string)($p['imagen'] ?? ''));
                $imgPath = $img !== '' ? "img/" . htmlspecialchars($img) : "img/placeholder.png";
              ?>
              <div class="col-12 col-md-4">
                <div class="card h-100">
                  <img
                    src="<?= $imgPath ?>"
                    class="card-img-top"
                    alt="<?= htmlspecialchars($p['nombre']) ?>"
                    onerror="this.src='img/placeholder.png';"
                  >

                  <div class="card-body">
                    <h5 class="titulo-negro"><?= htmlspecialchars($p['nombre']) ?></h5>
                    <p><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>
                    <p><strong><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</strong></p>

                    <a href="carrito.php?add=<?= (int)$p['id_producto'] ?>" class="btn btn-negro mt-2">
                      Añadir al carrito
                    </a>
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
