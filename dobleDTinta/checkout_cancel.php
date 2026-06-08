<?php
session_start();

require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$idCompra = (int)($_GET['id_compra'] ?? 0);

if ($idCompra > 0) {
    $stmt = $pdo->prepare("
        UPDATE compras
        SET estado = 'cancelada'
        WHERE id_compra = ?
          AND id_usuario = ?
          AND estado = 'pendiente'
    ");
    $stmt->execute([$idCompra, $userId]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Compra cancelada - Doble de Tinta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="row justify-content-center">
        <div class="col-12 col-md-8">

          <div class="card">
            <div class="card-body text-center">

              <h2 class="titulo-negro mb-3">Compra cancelada</h2>

              <p>
                El proceso de pago se ha cancelado y la compra no se ha finalizado.
              </p>

              <p>
                Los productos siguen en tu carrito por si quieres intentarlo de nuevo.
              </p>

              <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a href="carrito.php" class="btn btn-negro">Volver al carrito</a>
                <a href="portafolio.php" class="btn btn-outline-dark">Seguir comprando</a>
              </div>

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