<?php
require_once __DIR__ . '/config/db.php';
$email = trim($_GET['email'] ?? '');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = 'Invalid email';
  } else {
    $stmt = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $message = ($stmt->rowCount() > 0) ? 'Account reactivated. You can login now.' : 'No account found with that email.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reactivate account</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <h2>Reactivate account</h2>
  <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>"></div>
    <button class="btn btn-warning">Reactivate</button>
    <a class="btn btn-outline-secondary" href="login.php">Back</a>
  </form>
</body>
</html>
