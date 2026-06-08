<?php
session_start();
require_once __DIR__ . '/config/db.php';

$email = trim($_GET['email'] ?? '');
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Debes introducir tu email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario, activo FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $error = 'No existe ninguna cuenta con ese email.';
        } elseif ((int)$usuario['activo'] === 1) {
            $message = 'La cuenta ya está activa. Puedes iniciar sesión.';
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);

            $message = 'Cuenta reactivada correctamente. Ya puedes iniciar sesión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reactivar cuenta - Doble de Tinta</title>
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

              <h2 class="titulo-negro text-center mb-3">Reactivar cuenta</h2>

              <p class="texto-negro text-center">
                Introduce tu email para volver a activar tu cuenta.
              </p>

              <?php if ($error): ?>
                <div class="alert alert-danger">
                  <?= htmlspecialchars($error) ?>
                </div>
              <?php endif; ?>

              <?php if ($message): ?>
                <div class="alert alert-success">
                  <?= htmlspecialchars($message) ?>
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
                    value="<?= htmlspecialchars($email) ?>"
                  >
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button class="btn btn-negro">Reactivar cuenta</button>
                  <a class="btn btn-outline-dark" href="login.php">Volver al login</a>
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