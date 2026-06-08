<?php

define('STRIPE_SECRET_KEY', 'sk_test_PON_AQUI_TU_CLAVE_SECRETA');
define('STRIPE_PUBLIC_KEY', 'pk_test_PON_AQUI_TU_CLAVE_PUBLICA');

$server = $_SERVER['HTTP_HOST'] ?? '';

if ($server === 'localhost' || $server === '127.0.0.1') {
    define('BASE_URL', 'http://localhost/proyecto');
} else {
    define('BASE_URL', 'http://TU_DOMINIO_ONLINE');
}