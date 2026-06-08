<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$nombreUsuario = $_SESSION['name'] ?? '';
$role = $_SESSION['role'] ?? 'cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Área Cliente - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Área del Cliente</h1>

        <?php if ($nombreUsuario): ?>
          <p class="texto-negro">
            Bienvenido/a, <strong><?= htmlspecialchars($nombreUsuario) ?></strong>.
          </p>
        <?php else: ?>
          <p class="texto-negro">Consulta tu perfil, tus compras y tus reservas.</p>
        <?php endif; ?>
      </header>

      <div class="row g-4">

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">👤</div>
              <h5 class="titulo-negro">Mi perfil</h5>
              <p>Consulta o modifica tus datos personales.</p>
              <a href="profile.php" class="btn btn-negro mt-auto">Entrar</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">📅</div>
              <h5 class="titulo-negro">Mis reservas</h5>
              <p>Consulta tus citas, revisa el estado y cancela reservas activas.</p>
              <a href="mis_reservas.php" class="btn btn-negro mt-auto">Ver reservas</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">🛒</div>
              <h5 class="titulo-negro">Carrito</h5>
              <p>Revisa productos, modifica cantidades y finaliza la compra.</p>
              <a href="carrito.php" class="btn btn-negro mt-auto">Ver carrito</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">🧾</div>
              <h5 class="titulo-negro">Mis compras</h5>
              <p>Consulta el historial de compras realizadas en la tienda.</p>
              <a href="mis_compras.php" class="btn btn-negro mt-auto">Ver compras</a>
            </div>
          </div>
        </div>

      </div>

      <?php if ($role === 'admin' || $role === 'empleado'): ?>
        <div class="text-center mt-4">
          <a href="admin.php" class="btn btn-negro">Ir al panel de gestión</a>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>