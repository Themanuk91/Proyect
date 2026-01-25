<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT id_usuario, nombre, apellidos, email, telefono, direccion, ciudad, cp, provincia, pais, rol, activo
                       FROM usuarios WHERE id_usuario = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
  session_unset();
  session_destroy();
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  $nombre = trim($_POST['nombre'] ?? '');
  $apellidos = trim($_POST['apellidos'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $ciudad = trim($_POST['ciudad'] ?? '');
  $cp = trim($_POST['cp'] ?? '');
  $provincia = trim($_POST['provincia'] ?? '');
  $pais = trim($_POST['pais'] ?? '');

  if ($nombre === '' || $apellidos === '') {
    $error = 'Name and surname are required';
  } else {
    $stmt = $pdo->prepare("UPDATE usuarios
      SET nombre=?, apellidos=?, telefono=?, direccion=?, ciudad=?, cp=?, provincia=?, pais=?
      WHERE id_usuario=? LIMIT 1");
    $stmt->execute([$nombre,$apellidos,$telefono,$direccion,$ciudad,$cp,$provincia,$pais,$userId]);

    $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellidos, email, telefono, direccion, ciudad, cp, provincia, pais, rol, activo
                           FROM usuarios WHERE id_usuario=? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $_SESSION['name'] = $user['nombre'];
    $message = 'Profile updated';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deactivate') {
  $pdo->prepare("UPDATE usuarios SET activo=0 WHERE id_usuario=? LIMIT 1")->execute([$userId]);
  session_unset();
  session_destroy();
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi perfil</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="titulo-negro mb-0">Mi perfil</h2>
        <div class="d-flex gap-2">
          <a class="btn btn-negro" href="cliente.php">Volver</a>
          <a class="btn btn-negro" href="logout.php">Salir</a>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="row mt-3">
        <div class="col-lg-8 mb-4">
          <form method="POST" class="card card-body">
            <input type="hidden" name="action" value="update">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Nombre *</label>
                <input class="form-control" name="nombre" value="<?= htmlspecialchars($user['nombre'] ?? '') ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Apellidos *</label>
                <input class="form-control" name="apellidos" value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>" required>
              </div>
            </div>

            <div class="mb-3">
              <label>Email</label>
              <input class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Teléfono</label>
                <input class="form-control" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label>Código Postal</label>
                <input class="form-control" name="cp" value="<?= htmlspecialchars($user['cp'] ?? '') ?>">
              </div>
            </div>

            <div class="mb-3">
              <label>Dirección</label>
              <input class="form-control" name="direccion" value="<?= htmlspecialchars($user['direccion'] ?? '') ?>">
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label>Ciudad</label>
                <input class="form-control" name="ciudad" value="<?= htmlspecialchars($user['ciudad'] ?? '') ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label>Provincia</label>
                <input class="form-control" name="provincia" value="<?= htmlspecialchars($user['provincia'] ?? '') ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label>País</label>
                <input class="form-control" name="pais" value="<?= htmlspecialchars($user['pais'] ?? '') ?>">
              </div>
            </div>

            <button class="btn btn-negro">Guardar cambios</button>
          </form>
        </div>

        <div class="col-lg-4 mb-4">
          <div class="card card-body">
            <h5 class="titulo-negro">Cuenta</h5>
            <p class="mb-1"><strong>Rol:</strong> <?= htmlspecialchars($user['rol']) ?></p>
            <p class="mb-3"><strong>Estado:</strong> <?= ((int)$user['activo'] === 1) ? 'Activa' : 'Inactiva' ?></p>

            <form method="POST" onsubmit="return confirm('¿Seguro que quieres desactivar la cuenta?');">
              <input type="hidden" name="action" value="deactivate">
              <button class="btn btn-outline-danger w-100">Desactivar cuenta</button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
