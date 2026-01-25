<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: cliente.php'); exit; }

if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE productos SET activo = 1 - activo WHERE id_producto = ?")->execute([$id]);
    header('Location: admin_productos.php'); exit;
}

$stmt = $pdo->query("SELECT id_producto, nombre, precio, stock, tipo_producto, activo
                     FROM productos
                     ORDER BY id_producto DESC");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin - Productos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/stylescliente.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="titulo-negro mb-0">Productos (recursos)</h2>
        <div class="d-flex gap-2">
          <a href="admin.php" class="btn btn-negro">Volver</a>
          <a href="logout.php" class="btn btn-negro">Salir</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Tipo</th>
                <th>Activo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
                <tr>
                  <td><?= (int)$p['id_producto'] ?></td>
                  <td><?= htmlspecialchars($p['nombre']) ?></td>
                  <td><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</td>
                  <td><?= (int)$p['stock'] ?></td>
                  <td><?= htmlspecialchars($p['tipo_producto']) ?></td>
                  <td><?= ((int)$p['activo']===1)?'Sí':'No' ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-danger"
                       href="admin_productos.php?toggle=1&id=<?= (int)$p['id_producto'] ?>"
                       onclick="return confirm('¿Cambiar estado activo?');">
                       Activar/Desactivar
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
