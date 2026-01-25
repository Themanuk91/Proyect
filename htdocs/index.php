<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>InkTime - Doble de Tinta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">
      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <header class="text-center my-5">
        <h1 class="titulo-principal">InkTime · Doble de Tinta</h1>
        <p class="subtitulo">Reserva tu tatuaje en un entorno profesional con el estilo único de David.</p>
        <a href="reservas.php" class="btn btn-negro mt-3">Reservar cita</a>
      </header>

      <section class="my-5">
        <h2 class="text-center mb-4">Algunos artículos de la tienda</h2>

        <div class="row g-4">
          <div class="col-md-3">
            <div class="card tarjeta-tienda h-100">
              <div class="card-body">
                <h5>Tatuajes</h5>
                <p>Sesiones personalizadas en negro y color.</p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card tarjeta-tienda h-100">
              <div class="card-body">
                <h5>Camisetas</h5>
                <p>Diseños originales “Doble de Tinta”.</p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card tarjeta-tienda h-100">
              <div class="card-body">
                <h5>Bolsas de tela</h5>
                <p>Ilustraciones exclusivas para tattoo-lovers.</p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card tarjeta-tienda h-100">
              <div class="card-body">
                <h5>Láminas y figuras</h5>
                <p>Arte para decorar tu espacio.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-4">
          <a href="portafolio.php" class="btn btn-negro">Ver portafolio completo</a>
        </div>
      </section>
    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
