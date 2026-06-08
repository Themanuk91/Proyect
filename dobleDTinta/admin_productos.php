<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin' && $role !== 'empleado') {
    header('Location: cliente.php');
    exit;
}

$idEditar = 0;
$nombre = '';
$descripcion = '';
$precio = '';
$imagen = '';
$idCategoria = '';
$tipoProducto = 'merch';
$stock = 0;
$mensaje = '';
$error = '';

if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("UPDATE productos SET activo = 1 - activo WHERE id_producto = ?");
    $stmt->execute([$id]);

    header('Location: admin_productos.php');
    exit;
}

if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->execute([$id]);

        header('Location: admin_productos.php');
        exit;
    } catch (PDOException $e) {
        $error = 'No se puede eliminar este producto porque está relacionado con reservas o compras. Puedes desactivarlo.';
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$idEditar]);
    $productoEditar = $stmt->fetch();

    if ($productoEditar) {
        $nombre = $productoEditar['nombre'] ?? '';
        $descripcion = $productoEditar['descripcion'] ?? '';
        $precio = $productoEditar['precio'] ?? '';
        $imagen = $productoEditar['imagen'] ?? '';
        $idCategoria = $productoEditar['id_categoria'] ?? '';
        $tipoProducto = $productoEditar['tipo_producto'] ?? 'merch';
        $stock = $productoEditar['stock'] ?? 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEditar = (int)($_POST['id_editar'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = str_replace(',', '.', trim($_POST['precio'] ?? '0'));
    $imagen = trim($_POST['imagen'] ?? '');
    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
    $tipoProducto = $_POST['tipo_producto'] ?? 'merch';
    $stock = (int)($_POST['stock'] ?? 0);

    $tiposPermitidos = ['merch', 'tatuaje'];

    if ($nombre === '') {
        $error = 'El nombre es obligatorio.';
    } elseif (!is_numeric($precio) || (float)$precio < 0) {
        $error = 'El precio debe ser un número válido.';
    } elseif ($idCategoria <= 0) {
        $error = 'Debes elegir una categoría.';
    } elseif (!in_array($tipoProducto, $tiposPermitidos, true)) {
        $error = 'Tipo de producto no válido.';
    } else {
        if ($idEditar > 0) {
            $stmt = $pdo->prepare("
                UPDATE productos
                SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, id_categoria = ?, tipo_producto = ?, stock = ?
                WHERE id_producto = ?
            ");

            $stmt->execute([
                $nombre,
                $descripcion,
                (float)$precio,
                $imagen,
                $idCategoria,
                $tipoProducto,
                $stock,
                $idEditar
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO productos
                (nombre, descripcion, precio, imagen, id_categoria, tipo_producto, stock, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");

            $stmt->execute([
                $nombre,
                $descripcion,
                (float)$precio,
                $imagen,
                $idCategoria,
                $tipoProducto,
                $stock
            ]);
        }

        header('Location: admin_productos.php');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT id_categoria, nombre
    FROM categorias
    WHERE activa = 1
    ORDER BY nombre
");
$categorias = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    ORDER BY p.id_producto DESC
");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de productos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="titulo-negro mb-0">Gestión de productos</h2>
          <p class="texto-negro mb-0">Crear, modificar, activar, desactivar o eliminar productos.</p>
        </div>

        <div class="d-flex gap-2">
          <a href="admin.php" class="btn btn-negro">Volver</a>
          <a href="logout.php" class="btn btn-negro">Salir</a>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <div class="card mb-4">
        <div class="card-body">
          <h4 class="titulo-negro mb-3">
            <?= $idEditar > 0 ? 'Editar producto' : 'Crear producto' ?>
          </h4>

          <form method="POST">
            <input type="hidden" name="id_editar" value="<?= (int)$idEditar ?>">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
              </div>

              <div class="col-md-3 mb-3">
                <label>Precio</label>
                <input type="number" name="precio" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars((string)$precio) ?>">
              </div>

              <div class="col-md-3 mb-3">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars((string)$stock) ?>">
              </div>
            </div>

            <div class="mb-3">
              <label>Descripción</label>
              <textarea name="descripcion" class="form-control"><?= htmlspecialchars($descripcion) ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label>Imagen</label>
                <input type="text" name="imagen" class="form-control" value="<?= htmlspecialchars($imagen) ?>" placeholder="ejemplo.png">
              </div>

              <div class="col-md-4 mb-3">
                <label>Categoría</label>
                <select name="id_categoria" class="form-select" required>
                  <option value="">Selecciona categoría</option>

                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id_categoria'] ?>" <?= ((string)$idCategoria === (string)$cat['id_categoria']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4 mb-3">
                <label>Tipo</label>
                <select name="tipo_producto" class="form-select" required>
                  <option value="merch" <?= $tipoProducto === 'merch' ? 'selected' : '' ?>>Merch</option>
                  <option value="tatuaje" <?= $tipoProducto === 'tatuaje' ? 'selected' : '' ?>>Tatuaje</option>
                </select>
              </div>
            </div>

            <button class="btn btn-negro">
              <?= $idEditar > 0 ? 'Guardar cambios' : 'Crear producto' ?>
            </button>

            <?php if ($idEditar > 0): ?>
              <a href="admin_productos.php" class="btn btn-outline-dark">Cancelar edición</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
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
                  <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                  <td><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</td>
                  <td><?= (int)$p['stock'] ?></td>
                  <td><?= htmlspecialchars($p['tipo_producto']) ?></td>

                  <td>
                    <?php if ((int)$p['activo'] === 1): ?>
                      <span class="badge-estado estado-si">Sí</span>
                    <?php else: ?>
                      <span class="badge-estado estado-no">No</span>
                    <?php endif; ?>
                  </td>

                  <td>
                    <div class="d-flex gap-2 flex-wrap">
                      <a href="admin_productos.php?editar=<?= (int)$p['id_producto'] ?>" class="btn btn-sm btn-negro">
                        Editar
                      </a>

                      <a href="admin_productos.php?toggle=1&id=<?= (int)$p['id_producto'] ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('¿Cambiar estado activo?');">
                        Activar/Desactivar
                      </a>

                      <a href="admin_productos.php?eliminar=1&id=<?= (int)$p['id_producto'] ?>"
                         class="btn btn-sm btn-outline-dark"
                         onclick="return confirm('¿Seguro que quieres eliminar este producto?');">
                        Eliminar
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>

              <?php if (count($productos) === 0): ?>
                <tr>
                  <td colspan="8">No hay productos registrados.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>