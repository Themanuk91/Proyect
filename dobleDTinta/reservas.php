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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="mb-4 text-center">
        <h1 class="titulo-negro">Reservas</h1>
        <p class="texto-negro">
          Para reservar una cita de tatuaje necesitas iniciar sesión o crear una cuenta.
        </p>
      </header>

      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

          <div class="card">
            <div class="card-body text-center">
              <h4 class="titulo-negro mb-3">Reserva tu cita</h4>

              <p>
                Desde tu cuenta podrás elegir el tipo de tatuaje, seleccionar una fecha,
                indicar el horario y consultar el estado de tus reservas.
              </p>

              <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a class="btn btn-negro" href="login.php">Iniciar sesión</a>
                <a class="btn btn-outline-dark" href="register.php">Crear cuenta</a>
              </div>
            </div>
          </div>

        </div>
      </div>

      <section class="mt-5">
        <h3 class="titulo-negro text-center mb-4">¿Cómo funciona?</h3>

        <div class="row g-4">

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="titulo-negro">1. Entra en tu cuenta</h5>
                <p>Inicia sesión o crea una cuenta nueva para poder acceder al sistema de reservas.</p>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="titulo-negro">2. Elige el tatuaje</h5>
                <p>Selecciona una categoría de tatuaje y elige el producto que quieres reservar.</p>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="titulo-negro">3. Selecciona fecha y hora</h5>
                <p>Escoge una fecha disponible, indica el horario y confirma tu reserva.</p>
              </div>
            </div>
          </div>

        </div>
      </section>

    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>