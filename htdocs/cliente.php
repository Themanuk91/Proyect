<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Área Cliente - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Área del Cliente</h1>
        <p class="texto-negro">Consulta tu perfil, tus compras y tus reservas.</p>
      </header>

      <div class="row">

        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Mi Perfil</h5>
              <p>Ver o modificar tus datos personales.</p>
              <a href="profile.php" class="btn btn-negro">Entrar</a>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Mis Reservas</h5>
              <p>Consulta y cancela tus citas.</p>
              <a href="mis_reservas.php" class="btn btn-negro">Ver reservas</a>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Mis Compras</h5>
              <p>Carrito e historial (si lo implementas).</p>
              <a href="carrito.php" class="btn btn-negro">Ver carrito</a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
