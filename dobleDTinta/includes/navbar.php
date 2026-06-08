<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$isLogged = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? '';

$current = basename($_SERVER['PHP_SELF']);

$active = function ($file) use ($current) {
    return $current === $file ? 'active' : '';
};

$activeIn = function ($files) use ($current) {
    return in_array($current, $files, true) ? 'active' : '';
};

$totalUnidades = 0;

if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $totalUnidades += (int)($item['cantidad'] ?? 0);
    }
}

$stmt = $pdo->query("SELECT id_categoria, id_padre, nombre
                     FROM categorias
                     WHERE activa = 1
                     ORDER BY id_padre ASC, nombre ASC");
$categorias = $stmt->fetchAll();

$principales = [];
$subcategorias = [];

foreach ($categorias as $cat) {
    if ($cat['id_padre'] === null) {
        $principales[] = $cat;
    } else {
        $subcategorias[(int)$cat['id_padre']][] = $cat;
    }
}

$puedeGestionar = ($role === 'admin' || $role === 'empleado');
?>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">Doble de Tinta</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-lg-center">

        <li class="nav-item">
          <a class="nav-link <?= $active('index.php') ?>" href="index.php">Inicio</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active('portafolio.php') ?>" href="portafolio.php">Tienda</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $activeIn(['portafolio.php', 'mis_reservas.php']) ?>" href="#" id="categoriasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Categorías
          </a>

          <ul class="dropdown-menu" aria-labelledby="categoriasDropdown">
            <?php foreach ($principales as $principal): ?>
              <li>
                <h6 class="dropdown-header"><?= htmlspecialchars($principal['nombre']) ?></h6>
              </li>

              <?php if (isset($subcategorias[(int)$principal['id_categoria']])): ?>
                <?php foreach ($subcategorias[(int)$principal['id_categoria']] as $sub): ?>
                  <li>
                    <?php if ($principal['nombre'] === 'Tatuajes'): ?>
                      <a class="dropdown-item" href="mis_reservas.php?categoria=<?= (int)$sub['id_categoria'] ?>">
                        <?= htmlspecialchars($sub['nombre']) ?>
                      </a>
                    <?php else: ?>
                      <a class="dropdown-item" href="portafolio.php?categoria=<?= (int)$sub['id_categoria'] ?>">
                        <?= htmlspecialchars($sub['nombre']) ?>
                      </a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <?php if ($principal['nombre'] === 'Tatuajes'): ?>
                  <li>
                    <a class="dropdown-item" href="mis_reservas.php?categoria=<?= (int)$principal['id_categoria'] ?>">
                      Ver <?= htmlspecialchars($principal['nombre']) ?>
                    </a>
                  </li>
                <?php else: ?>
                  <li>
                    <a class="dropdown-item" href="portafolio.php?categoria=<?= (int)$principal['id_categoria'] ?>">
                      Ver <?= htmlspecialchars($principal['nombre']) ?>
                    </a>
                  </li>
                <?php endif; ?>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active('reservas.php') ?>" href="reservas.php">Reserva</a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $active('carrito.php') ?>" href="carrito.php">
            Carrito<?= $totalUnidades > 0 ? " ($totalUnidades)" : "" ?>
          </a>
        </li>

        <?php if (!$isLogged): ?>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= $activeIn(['login.php', 'register.php']) ?>" href="#" id="accesoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Acceso
            </a>
            <ul class="dropdown-menu" aria-labelledby="accesoDropdown">
              <li><a class="dropdown-item" href="login.php">Iniciar sesión</a></li>
              <li><a class="dropdown-item" href="register.php">Crear cuenta</a></li>
            </ul>
          </li>

        <?php else: ?>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= $activeIn(['cliente.php', 'profile.php', 'mis_reservas.php', 'mis_compras.php']) ?>" href="#" id="cuentaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Mi cuenta
            </a>

            <ul class="dropdown-menu" aria-labelledby="cuentaDropdown">
              <li><a class="dropdown-item" href="cliente.php">Área cliente</a></li>
              <li><a class="dropdown-item" href="profile.php">Mi perfil</a></li>
              <li><a class="dropdown-item" href="mis_reservas.php">Mis reservas</a></li>
              <li><a class="dropdown-item" href="mis_compras.php">Mis compras</a></li>
            </ul>
          </li>

          <?php if ($puedeGestionar): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle <?= $activeIn(['admin.php', 'admin_usuarios.php', 'admin_productos.php', 'admin_reservas.php', 'admin_categorias.php']) ?>" href="#" id="gestionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Gestión
              </a>

              <ul class="dropdown-menu" aria-labelledby="gestionDropdown">
                <li><a class="dropdown-item" href="admin.php">Panel de gestión</a></li>

                <?php if ($role === 'admin'): ?>
                  <li><a class="dropdown-item" href="admin_usuarios.php">Usuarios</a></li>
                <?php endif; ?>

                <li><a class="dropdown-item" href="admin_productos.php">Productos</a></li>
                <li><a class="dropdown-item" href="admin_reservas.php">Reservas</a></li>
                <li><a class="dropdown-item" href="admin_categorias.php">Categorías</a></li>
              </ul>
            </li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              Salir<?= $name ? ' (' . htmlspecialchars($name) . ')' : '' ?>
            </a>
          </li>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>