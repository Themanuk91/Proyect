<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/stripe.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sessionId = $_GET['session_id'] ?? '';

if ($sessionId === '') {
    header('Location: carrito.php');
    exit;
}

if (strpos(STRIPE_SECRET_KEY, 'PON_AQUI') !== false) {
    die('Falta configurar la clave secreta de Stripe en config/stripe.php');
}

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.stripe.com/v1/checkout/sessions/' . urlencode($sessionId),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET => true,
    CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($response === false || $curlError) {
    die('Error conectando con Stripe: ' . htmlspecialchars($curlError));
}

$data = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300) {
    echo '<h2>Error al verificar el pago con Stripe</h2>';
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit;
}

if (($data['payment_status'] ?? '') !== 'paid') {
    header('Location: checkout_cancel.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT id_compra, estado
    FROM compras
    WHERE stripe_session_id = ?
      AND id_usuario = ?
    LIMIT 1
");
$stmt->execute([$sessionId, $userId]);
$compra = $stmt->fetch();

if (!$compra) {
    die('No se ha encontrado la compra asociada a esta sesión de Stripe.');
}

$idCompra = (int)$compra['id_compra'];

if ($compra['estado'] !== 'pagada') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT id_producto, cantidad
            FROM compra_detalles
            WHERE id_compra = ?
        ");
        $stmt->execute([$idCompra]);
        $detalles = $stmt->fetchAll();

        foreach ($detalles as $detalle) {
            if (!empty($detalle['id_producto'])) {
                $stmtStock = $pdo->prepare("
                    UPDATE productos
                    SET stock = stock - ?
                    WHERE id_producto = ?
                      AND stock >= ?
                ");
                $stmtStock->execute([
                    (int)$detalle['cantidad'],
                    (int)$detalle['id_producto'],
                    (int)$detalle['cantidad']
                ]);
            }
        }

        $stmt = $pdo->prepare("
            UPDATE compras
            SET estado = 'pagada'
            WHERE id_compra = ?
              AND id_usuario = ?
        ");
        $stmt->execute([$idCompra, $userId]);

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die('Error al confirmar la compra: ' . $e->getMessage());
    }
}

$_SESSION['carrito'] = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Compra realizada - Doble de Tinta</title>
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
              <h2 class="titulo-negro mb-3">Compra realizada correctamente</h2>

              <p>
                Tu pago se ha confirmado y la compra ha quedado registrada.
              </p>

              <p>
                Número de compra:
                <strong>#<?= (int)$idCompra ?></strong>
              </p>

              <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a href="mis_compras.php" class="btn btn-negro">Ver mis compras</a>
                <a href="portafolio.php" class="btn btn-outline-dark">Volver a la tienda</a>
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