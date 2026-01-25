<?php
require_once __DIR__ . '/config/db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $newPassword = $_POST['new_password'] ?? '';

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = 'Invalid email';
  } elseif (strlen($newPassword) < 6) {
    $message = 'Password must be at least 6 characters';
  } else {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE email = ? LIMIT 1");
    $stmt->execute([$hash, $email]);

    $message = ($stmt->rowCount() > 0) ? 'Password updated. You can login now.' : 'No account found with that email.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Forgot password</h2>
  <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <form method="POST" class="mt-3">
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label>New password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
    <button class="btn btn-primary">Change password</button>
    <a class="btn btn-outline-secondary" href="login.php">Back</a>
  </form>
</body>
</html>
