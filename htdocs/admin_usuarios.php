<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: cliente.php'); exit; }

$adminId = (int)$_SESSION['user_id'];

if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id !== $adminId) {
        $pdo->prepare("UPDATE usuarios SET activo = 1 - activo WHERE id_usuario = ?")->execute([$id]);
    }
    header('Location: admin_usuarios.php'); exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'role') {
    $id = (int)($_POST['id_usuario'] ?? 0);
    $rol = $_POST['rol'] ?? 'cliente';
    $allowed = ['cliente','empleado','admin'];

    if ($id !== $adminId && in_array($rol, $allowed, true)) {
        $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?")->execute([$rol, $id]);
    }
    header('Location: admin_usuarios.php'); exit;
}

$stmt = $pdo->query("SELECT id_usuario, nombre, apellidos, email, rol, activo, fecha_alta
                     FROM usuarios
                     ORDER BY fecha_alta DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin - Usuarios</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/stylescliente.css">
</head>
<body>
  <div class="marco-chicle">
    <div class="container">

      <?php require_once __DIR__ . '/includes/navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="titulo-negro mb-0">Usuarios</h2>
        <div class="d-flex gap-2">
          <a href="admin.php" class="btn btn-negro">Volver</a>
          <a href="logout.php" class="btn btn-negro">Salir</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><?= (int)$u['id_usuario'] ?></td>
                <td><?= htmlspecialchars($u['nombre'].' '.$u['apellidos']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <?php if ((int)$u['id_usuario'] === $adminId): ?>
                    <span class="badge bg-dark">admin</span>
                  <?php else: ?>
                    <form method="POST" class="d-flex flex-wrap gap-2">
                      <input type="hidden" name="action" value="role">
                      <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                      <select name="rol" class="form-select form-select-sm w-auto">
                        <?php foreach (['cliente','empleado','admin'] as $r): ?>
                          <option value="<?= $r ?>" <?= $u['rol']===$r?'selected':'' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button class="btn btn-sm btn-negro">Guardar</button>
                    </form>
                  <?php endif; ?>
                </td>
                <td><?= ((int)$u['activo']===1)?'Sí':'No' ?></td>
                <td>
                  <?php if ((int)$u['id_usuario'] === $adminId): ?>
                    <span class="text-muted">No editable</span>
                  <?php else: ?>
                    <a class="btn btn-sm btn-outline-danger"
                       href="admin_usuarios.php?toggle=1&id=<?= (int)$u['id_usuario'] ?>"
                       onclick="return confirm('¿Cambiar estado activo?');">
                       Activar/Desactivar
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
