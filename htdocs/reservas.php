<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: mis_reservas.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reservas - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Reservas</h1>
        <p class="texto-negro">Para reservar necesitas iniciar sesión.</p>
      </header>

      <div class="card">
        <div class="card-body text-center">
          <a class="btn btn-negro me-2" href="login.php">Login</a>
          <a class="btn btn-outline-dark" href="register.php">Crear cuenta</a>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
