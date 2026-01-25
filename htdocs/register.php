<?php
require_once __DIR__ . '/config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre'] ?? '');
  $apellidos = trim($_POST['apellidos'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($nombre === '' || $apellidos === '' || $email === '' || $password === '') {
    $error = 'Please fill in all fields';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email format';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters';
  } else {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $error = 'Email already registered';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, apellidos, email, password_hash, rol, activo, fecha_alta)
         VALUES (?, ?, ?, ?, 'cliente', 1, NOW())"
      );
      $stmt->execute([$nombre, $apellidos, $email, $hash]);

      $success = 'Account created successfully. You can login now.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create account</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Create account</h2>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><a class="btn btn-primary" href="login.php">Go to login</a><?php endif; ?>

  <form method="POST" class="mt-3">
    <div class="mb-3"><label>Name</label><input type="text" name="nombre" class="form-control" required></div>
    <div class="mb-3"><label>Surname</label><input type="text" name="apellidos" class="form-control" required></div>
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
    <button class="btn btn-success">Create account</button>
    <a class="btn btn-outline-secondary" href="login.php">I already have an account</a>
  </form>
</body>
</html>
