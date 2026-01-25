<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = 'Please fill in all fields';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email format';
  } else {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
      $error = 'User not found. Please register.';
    } elseif ((int)$user['activo'] !== 1) {
      $error = 'Account inactive. You can reactivate it.';
    } elseif (!password_verify($password, $user['password_hash'])) {
      $error = 'Incorrect password';
    } else {
      $_SESSION['user_id'] = $user['id_usuario'];
      $_SESSION['role'] = $user['rol'];  // cliente | empleado | admin
      $_SESSION['name'] = $user['nombre'];

      if ($user['rol'] === 'admin') {
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Login</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php if (str_contains($error, 'register')): ?><a class="btn btn-outline-primary" href="register.php">Create account</a><?php endif; ?>
    <a class="btn btn-outline-secondary" href="forgot_password.php">Forgot password</a>
    <?php if (str_contains($error, 'inactive')): ?><a class="btn btn-outline-warning" href="reactivate.php?email=<?= urlencode($email ?? '') ?>">Reactivate</a><?php endif; ?>
  <?php endif; ?>

  <form method="POST" class="mt-3">
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3">
      <label>Password</label>
      <div class="input-group">
        <input id="pwd" type="password" name="password" class="form-control" required minlength="6">
        <button class="btn btn-outline-secondary" type="button" id="togglePwd">👁</button>
      </div>
    </div>
    <button class="btn btn-primary">Login</button>
    <a class="btn btn-outline-secondary" href="register.php">Create account</a>
  </form>

  <script>
    document.getElementById('togglePwd').addEventListener('click', function () {
      const input = document.getElementById('pwd');
      input.type = (input.type === 'password') ? 'text' : 'password';
    });
  </script>
</body>
</html>
