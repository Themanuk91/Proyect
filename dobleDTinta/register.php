<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'empleado') {
        header('Location: admin.php');
    } else {
        header('Location: cliente.php');
    }
    exit;
}

$error = '';
$success = '';

$nombre = '';
$apellidos = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nombre === '' || $apellidos === '' || $email === '' || $password === '' || $password2 === '') {
        $error = 'Debes completar todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Este email ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO usuarios 
                (nombre, apellidos, email, password_hash, rol, activo, fecha_alta)
                VALUES (?, ?, ?, ?, 'cliente', 1, NOW())
            ");

            $stmt->execute([
                $nombre,
                $apellidos,
                $email,
                $hash
            ]);

            $success = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';

            $nombre = '';
            $apellidos = '';
            $email = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear cuenta - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">

          <div class="card">
            <div class="card-body">

              <h2 class="titulo-negro mb-3 text-center">Crear cuenta</h2>

              <p class="texto-negro text-center">
                Regístrate para poder reservar citas, comprar productos y gestionar tus datos personales.
              </p>

              <?php if ($error): ?>
                <div class="alert alert-danger">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <?php if ($success): ?>
                <div class="alert alert-success">
                  <?= htmlspecialchars($success) ?>
                </div>

                <div class="text-center mb-3">
                  <a class="btn btn-negro" href="login.php">Ir a iniciar sesión</a>
                </div>
              <?php endif; ?>

              <form method="POST" class="mt-3">

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label>Nombre</label>
                    <input
                      type="text"
                      name="nombre"
                      class="form-control"
                      required
                      value="<?= htmlspecialchars($nombre) ?>"
                    >
                  </div>

                  <div class="col-md-6 mb-3">
                    <label>Apellidos</label>
                    <input
                      type="text"
                      name="apellidos"
                      class="form-control"
                      required
                      value="<?= htmlspecialchars($apellidos) ?>"
                    >
                  </div>
                </div>

                <div class="mb-3">
                  <label>Email</label>
                  <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars($email) ?>"
                  >
                </div>

                <div class="mb-3">
                  <label>Contraseña</label>
                  <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                    minlength="6"
                  >
                  <div class="form-text texto-negro">
                    Mínimo 6 caracteres.
                  </div>
                </div>

                <div class="mb-3">
                  <label>Repetir contraseña</label>
                  <input
                    type="password"
                    name="password2"
                    class="form-control"
                    required
                    minlength="6"
                  >
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button class="btn btn-negro">Crear cuenta</button>
                  <a class="btn btn-outline-dark" href="login.php">Ya tengo cuenta</a>
                </div>

              </form>

            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>