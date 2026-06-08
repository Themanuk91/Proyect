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
$tipoError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Debes completar todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'El usuario no existe. Puedes crear una cuenta nueva.';
            $tipoError = 'registro';
        } elseif ((int)$user['activo'] !== 1) {
            $error = 'La cuenta está desactivada. Puedes solicitar la reactivación.';
            $tipoError = 'inactiva';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'La contraseña no es correcta.';
        } else {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['role'] = $user['rol'];
            $_SESSION['name'] = $user['nombre'];

            if ($user['rol'] === 'admin' || $user['rol'] === 'empleado') {
                header('Location: admin.php');
            } else {
                header('Location: cliente.php');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

          <div class="card">
            <div class="card-body">

              <h2 class="titulo-negro mb-3 text-center">Iniciar sesión</h2>
              <p class="texto-negro text-center">
                Accede a tu cuenta para gestionar tus reservas, compras y datos personales.
              </p>

              <?php if ($error): ?>
                <div class="alert alert-danger">
                  <?= htmlspecialchars($error) ?>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-3">
                  <?php if ($tipoError === 'registro'): ?>
                    <a class="btn btn-negro" href="register.php">Crear cuenta</a>
                  <?php endif; ?>

                  <?php if ($tipoError === 'inactiva'): ?>
                    <a class="btn btn-outline-dark" href="reactivate.php?email=<?= urlencode($email ?? '') ?>">
                      Reactivar cuenta
                    </a>
                  <?php endif; ?>

                  <a class="btn btn-outline-dark" href="forgot_password.php">
                    He olvidado mi contraseña
                  </a>
                </div>
              <?php endif; ?>

              <form method="POST" class="mt-3">

                <div class="mb-3">
                  <label>Email</label>
                  <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                    value="<?= htmlspecialchars($email ?? '') ?>"
                  >
                </div>

                <div class="mb-3">
                  <label>Contraseña</label>
                  <div class="input-group">
                    <input
                      id="pwd"
                      type="password"
                      name="password"
                      class="form-control"
                      required
                      minlength="6"
                    >
                    <button class="btn btn-outline-dark" type="button" id="togglePwd">
                      Ver
                    </button>
                  </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button class="btn btn-negro">Entrar</button>
                  <a class="btn btn-outline-dark" href="register.php">Crear cuenta</a>
                </div>

              </form>

            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script>
    document.getElementById('togglePwd').addEventListener('click', function () {
      const input = document.getElementById('pwd');

      if (input.type === 'password') {
        input.type = 'text';
        this.textContent = 'Ocultar';
      } else {
        input.type = 'password';
        this.textContent = 'Ver';
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>