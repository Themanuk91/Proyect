<?php
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/stripe.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

if (strpos(STRIPE_SECRET_KEY, 'PON_AQUI') !== false) {
    die('Falta configurar la clave secreta de Stripe en config/stripe.php');
}

$userId = (int)$_SESSION['user_id'];
$carrito = $_SESSION['carrito'];

$ids = array_keys($carrito);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT id_producto, nombre, precio, activo, tipo_producto, stock
    FROM productos
    WHERE id_producto IN ($placeholders)
");
$stmt->execute($ids);
$productosBD = $stmt->fetchAll();

$mapProductos = [];

foreach ($productosBD as $p) {
    $mapProductos[(int)$p['id_producto']] = $p;
}

$itemsCompra = [];
$total = 0;

foreach ($carrito as $id => $item) {
    $id = (int)$id;

    if (!isset($mapProductos[$id])) {
        unset($_SESSION['carrito'][$id]);
        continue;
    }

    $producto = $mapProductos[$id];

    if ((int)$producto['activo'] !== 1 || $producto['tipo_producto'] !== 'merch') {
        unset($_SESSION['carrito'][$id]);
        continue;
    }

    $cantidad = (int)($item['cantidad'] ?? 0);

    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$id]);
        continue;
    }

    if ($cantidad > (int)$producto['stock']) {
        header('Location: carrito.php?msg=' . urlencode('No hay stock suficiente de ' . $producto['nombre']));
        exit;
    }

    $precio = (float)$producto['precio'];
    $subtotal = $cantidad * $precio;

    $itemsCompra[] = [
        'id_producto' => (int)$producto['id_producto'],
        'nombre' => $producto['nombre'],
        'precio' => $precio,
        'cantidad' => $cantidad,
        'subtotal' => $subtotal
    ];

    $total += $subtotal;
}

if (empty($itemsCompra)) {
    header('Location: carrito.php?msg=' . urlencode('No hay productos válidos en el carrito.'));
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO compras (id_usuario, total, estado, fecha_compra)
        VALUES (?, ?, 'pendiente', NOW())
    ");
    $stmt->execute([$userId, $total]);

    $idCompra = (int)$pdo->lastInsertId();

    $stmtDetalle = $pdo->prepare("
        INSERT INTO compra_detalles
        (id_compra, id_producto, nombre_producto, precio_unitario, cantidad, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($itemsCompra as $item) {
        $stmtDetalle->execute([
            $idCompra,
            $item['id_producto'],
            $item['nombre'],
            $item['precio'],
            $item['cantidad'],
            $item['subtotal']
        ]);
    }

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    die('Error al crear la compra: ' . $e->getMessage());
}

$params = [
    'mode' => 'payment',
    'client_reference_id' => $idCompra,
    'success_url' => BASE_URL . '/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => BASE_URL . '/checkout_cancel.php?id_compra=' . $idCompra,
    'metadata' => [
        'id_compra' => $idCompra,
        'id_usuario' => $userId
    ]
];

foreach ($itemsCompra as $index => $item) {
    $params['line_items'][$index] = [
        'quantity' => $item['cantidad'],
        'price_data' => [
            'currency' => 'eur',
            'unit_amount' => (int)round($item['precio'] * 100),
            'product_data' => [
                'name' => $item['nombre']
            ]
        ]
    ];
}

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.stripe.com/v1/checkout/sessions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($response === false || $curlError) {
    $stmt = $pdo->prepare("UPDATE compras SET estado = 'error' WHERE id_compra = ?");
    $stmt->execute([$idCompra]);

    die('Error conectando con Stripe: ' . htmlspecialchars($curlError));
}

$data = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300 || !isset($data['url'])) {
    $stmt = $pdo->prepare("UPDATE compras SET estado = 'error' WHERE id_compra = ?");
    $stmt->execute([$idCompra]);

    echo '<h2>Error al crear la sesión de Stripe</h2>';
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit;
}

$stmt = $pdo->prepare("
    UPDATE compras
    SET stripe_session_id = ?
    WHERE id_compra = ?
");
$stmt->execute([
    $data['id'],
    $idCompra
]);

header('Location: ' . $data['url']);
exit;