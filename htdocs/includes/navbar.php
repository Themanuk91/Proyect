<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo '<pre style="background:#fff;border:2px solid #000;padding:10px;margin-bottom:10px;">';
    echo "SESSION DEBUG\n";
    print_r($_SESSION);
    echo "</pre>";
}

$isLogged = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null; 
$name = $_SESSION['name'] ?? '';


$current = basename($_SERVER['PHP_SELF']);
$active = function($file) use ($current) {
    return $current === $file ? 'active' : '';
};


$totalUnidades = 0;
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $totalUnidades += (int)($item['cantidad'] ?? 0);
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">Doble de Tinta</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">

 
        <li class="nav-item"><a class="nav-link <?= $active('index.php') ?>" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link <?= $active('portafolio.php') ?>" href="portafolio.php">Portafolio</a></li>
        <li class="nav-item"><a class="nav-link <?= $active('reservas.php') ?>" href="reservas.php">Reserva</a></li>

        <li class="nav-item">
          <a class="nav-link <?= $active('carrito.php') ?>" href="carrito.php">
            Carrito<?= $totalUnidades > 0 ? " ($totalUnidades)" : "" ?>
          </a>
        </li>

        <?php if (!$isLogged): ?>
          <li class="nav-item"><a class="nav-link <?= $active('login.php') ?>" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link <?= $active('register.php') ?>" href="register.php">Crear cuenta</a></li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= $active('cliente.php') ?>" href="cliente.php">Área cliente</a></li>
          <li class="nav-item"><a class="nav-link <?= $active('profile.php') ?>" href="profile.php">Mi perfil</a></li>
          <li class="nav-item"><a class="nav-link <?= $active('mis_reservas.php') ?>" href="mis_reservas.php">Mis reservas</a></li>

          <?php if ($role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link <?= $active('admin.php') ?>" href="admin.php">Admin</a></li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link" href="logout.php">Salir<?= $name ? ' ('.htmlspecialchars($name).')' : '' ?></a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>
