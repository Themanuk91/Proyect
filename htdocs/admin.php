<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: cliente.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/stylescliente.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="titulo-negro mb-0">Panel de Administración</h2>
        <a href="logout.php" class="btn btn-negro">Salir</a>
      </div>

      <div class="row">

        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Usuarios</h5>
              <p>Gestión de cuentas, roles y estados.</p>
              <a href="admin_usuarios.php" class="btn btn-negro">Gestionar</a>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Productos</h5>
              <p>Activar, desactivar y editar recursos.</p>
              <a href="admin_productos.php" class="btn btn-negro">Gestionar</a>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="titulo-negro">Reservas</h5>
              <p>Ver y cancelar reservas existentes.</p>
              <a href="admin_reservas.php" class="btn btn-negro">Gestionar</a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
