<?php
session_start();
require_once __DIR__ . '/config/db.php';

$isLogged = isset($_SESSION['user_id']);

$stmt = $pdo->query("
    SELECT id_producto, nombre, descripcion, precio, imagen, stock
    FROM productos
    WHERE activo = 1
      AND tipo_producto = 'merch'
    ORDER BY id_producto DESC
    LIMIT 4
");
$productos = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT id_categoria, nombre, descripcion
    FROM categorias
    WHERE activa = 1
      AND id_padre IS NULL
    ORDER BY nombre
");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>InkTime - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="text-center my-5">
        <h1 class="titulo-principal">InkTime · Doble de Tinta</h1>

        <p class="subtitulo">
          Reserva tu tatuaje y descubre productos exclusivos de Doble de Tinta.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
          <a href="reservas.php" class="btn btn-negro">Reservar cita</a>
          <a href="portafolio.php" class="btn btn-negro">Ver tienda</a>

          <?php if ($isLogged): ?>
            <a href="cliente.php" class="btn btn-outline-dark">Mi cuenta</a>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-dark">Iniciar sesión</a>
            <a href="register.php" class="btn btn-outline-dark">Crear cuenta</a>
          <?php endif; ?>
        </div>
      </header>

      <section class="my-5">
        <h2 class="titulo-negro text-center mb-4">¿Qué puedes hacer?</h2>

        <div class="row g-4">

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="titulo-negro">Reservar tatuajes</h5>
                <p>Elige el tipo de tatuaje, selecciona fecha y hora, y consulta el estado de tus reservas.</p>
                <a href="reservas.php" class="btn btn-negro mt-auto">Reservar</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="titulo-negro">Comprar productos</h5>
                <p>Añade productos al carrito, modifica cantidades y finaliza la compra.</p>
                <a href="portafolio.php" class="btn btn-negro mt-auto">Ir a tienda</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="titulo-negro">Gestionar tu cuenta</h5>
                <p>Accede a tu perfil, revisa tus reservas y consulta tu historial de compras.</p>

                <?php if ($isLogged): ?>
                  <a href="cliente.php" class="btn btn-negro mt-auto">Mi cuenta</a>
                <?php else: ?>
                  <a href="login.php" class="btn btn-negro mt-auto">Iniciar sesión</a>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </section>

      <section class="my-5">
        <h2 class="titulo-negro text-center mb-4">Categorías principales</h2>

        <div class="row g-4">

          <?php if (!$categorias): ?>

            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="titulo-negro">Sin categorías</h5>
                  <p>Todavía no hay categorías activas.</p>
                </div>
              </div>
            </div>

          <?php else: ?>

            <?php foreach ($categorias as $cat): ?>
              <div class="col-12 col-md-6">
                <div class="card h-100">
                  <div class="card-body d-flex flex-column">
                    <h5 class="titulo-negro"><?= htmlspecialchars($cat['nombre']) ?></h5>

                    <p>
                      <?= htmlspecialchars($cat['descripcion'] ?? 'Categoría disponible en Doble de Tinta.') ?>
                    </p>

                    <?php if ($cat['nombre'] === 'Tatuajes'): ?>
                      <a href="mis_reservas.php" class="btn btn-negro mt-auto">Ver tatuajes</a>
                    <?php else: ?>
                      <a href="portafolio.php" class="btn btn-negro mt-auto">Ver productos</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

          <?php endif; ?>

        </div>
      </section>

      <section class="my-5">
        <h2 class="titulo-negro text-center mb-4">Últimos productos de la tienda</h2>

        <div class="row g-4">

          <?php if (!$productos): ?>

            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="titulo-negro">Sin productos disponibles</h5>
                  <p>No hay productos activos en la tienda en este momento.</p>
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

              <div class="col-12 col-md-6 col-xl-3">
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
                      Stock: <strong><?= $stock ?></strong>
                    </p>

                    <div class="mt-auto">
                      <?php if ($stock > 0): ?>
                        <a href="carrito.php?add=<?= (int)$p['id_producto'] ?>" class="btn btn-negro">
                          Añadir al carrito
                        </a>
                      <?php else: ?>
                        <button class="btn btn-outline-dark" disabled>
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

        <div class="text-center mt-4">
          <a href="portafolio.php" class="btn btn-negro">Ver tienda completa</a>
        </div>
      </section>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>