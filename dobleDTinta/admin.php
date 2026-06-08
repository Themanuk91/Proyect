<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin' && $role !== 'empleado') {
    header('Location: cliente.php');
    exit;
}

$esAdmin = ($role === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Gestión - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Panel de Gestión</h1>

        <p class="texto-negro">
          Rol actual:
          <strong><?= htmlspecialchars($role) ?></strong>
        </p>
      </header>

      <div class="row g-4">

        <?php if ($esAdmin): ?>
          <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100">
              <div class="card-body d-flex flex-column text-center">
                <div class="icono-panel mb-3">👥</div>
                <h5 class="titulo-negro">Usuarios</h5>
                <p>Gestiona cuentas, roles y estados de los usuarios registrados.</p>
                <a href="admin_usuarios.php" class="btn btn-negro mt-auto">Gestionar</a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">📦</div>
              <h5 class="titulo-negro">Productos</h5>
              <p>Crea, modifica, activa, desactiva o elimina productos de la tienda.</p>
              <a href="admin_productos.php" class="btn btn-negro mt-auto">Gestionar</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">📅</div>
              <h5 class="titulo-negro">Reservas</h5>
              <p>Consulta las citas creadas por los clientes y cancela reservas activas.</p>
              <a href="admin_reservas.php" class="btn btn-negro mt-auto">Gestionar</a>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body d-flex flex-column text-center">
              <div class="icono-panel mb-3">🏷️</div>
              <h5 class="titulo-negro">Categorías</h5>
              <p>Crea, edita, activa o desactiva categorías y subcategorías del menú.</p>
              <a href="admin_categorias.php" class="btn btn-negro mt-auto">Gestionar</a>
            </div>
          </div>
        </div>

      </div>

      <div class="text-center mt-4">
        <a href="cliente.php" class="btn btn-outline-dark">Ir al área cliente</a>
        <a href="logout.php" class="btn btn-negro">Salir</a>
      </div>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>