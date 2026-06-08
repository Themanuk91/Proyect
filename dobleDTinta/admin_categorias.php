<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role !== 'admin' && $role !== 'empleado') {
    header('Location: cliente.php');
    exit;
}

$idEditar = 0;
$nombre = '';
$descripcion = '';
$idPadre = '';
$error = '';

if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("UPDATE categorias SET activa = 1 - activa WHERE id_categoria = ?");
    $stmt->execute([$id]);

    header('Location: admin_categorias.php');
    exit;
}

if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categoria = ?");
        $stmt->execute([$id]);

        header('Location: admin_categorias.php');
        exit;
    } catch (PDOException $e) {
        $error = 'No se puede eliminar esta categoría porque está relacionada con productos o subcategorías. Puedes desactivarla.';
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int)$_GET['editar'];

    $stmt = $pdo->prepare("
        SELECT id_categoria, id_padre, nombre, descripcion, activa
        FROM categorias
        WHERE id_categoria = ?
    ");
    $stmt->execute([$idEditar]);
    $categoriaEditar = $stmt->fetch();

    if ($categoriaEditar) {
        $nombre = $categoriaEditar['nombre'] ?? '';
        $descripcion = $categoriaEditar['descripcion'] ?? '';
        $idPadre = $categoriaEditar['id_padre'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $idPadre = ($_POST['id_padre'] !== '') ? (int)$_POST['id_padre'] : null;
    $idEditar = (int)($_POST['id_editar'] ?? 0);

    if ($nombre !== '') {
        if ($idEditar > 0) {
            $stmt = $pdo->prepare("
                UPDATE categorias
                SET nombre = ?, descripcion = ?, id_padre = ?
                WHERE id_categoria = ?
            ");
            $stmt->execute([$nombre, $descripcion, $idPadre, $idEditar]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO categorias (nombre, descripcion, id_padre, activa)
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([$nombre, $descripcion, $idPadre]);
        }

        header('Location: admin_categorias.php');
        exit;
    } else {
        $error = 'El nombre de la categoría es obligatorio.';
    }
}

$stmt = $pdo->query("
    SELECT id_categoria, nombre
    FROM categorias
    WHERE id_padre IS NULL
    ORDER BY nombre
");
$categoriasPadre = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT 
        c.id_categoria,
        c.id_padre,
        c.nombre,
        c.descripcion,
        c.activa,
        p.nombre AS nombre_padre
    FROM categorias c
    LEFT JOIN categorias p ON c.id_padre = p.id_categoria
    ORDER BY c.id_padre IS NULL DESC, c.id_padre ASC, c.nombre ASC
");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de categorías</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="marco-chicle">
    <div class="container">

        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="titulo-negro mb-0">Gestión de categorías</h2>
                <p class="texto-negro mb-0">
                    Crear, editar, activar, desactivar o eliminar categorías y subcategorías.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="admin.php" class="btn btn-negro">Volver</a>
                <a href="logout.php" class="btn btn-negro">Salir</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h4 class="titulo-negro mb-3">
                    <?= $idEditar > 0 ? 'Editar categoría' : 'Crear categoría o subcategoría' ?>
                </h4>

                <form method="POST">
                    <input type="hidden" name="id_editar" value="<?= (int)$idEditar ?>">

                    <div class="mb-3">
                        <label>Nombre</label>
                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($nombre) ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label>Descripción</label>
                        <input
                            type="text"
                            name="descripcion"
                            class="form-control"
                            value="<?= htmlspecialchars($descripcion) ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label>Categoría padre</label>
                        <select name="id_padre" class="form-select">
                            <option value="">Sin padre</option>

                            <?php foreach ($categoriasPadre as $cat): ?>
                                <?php if ((int)$cat['id_categoria'] !== (int)$idEditar): ?>
                                    <option
                                        value="<?= (int)$cat['id_categoria'] ?>"
                                        <?= ((string)$idPadre === (string)$cat['id_categoria']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn btn-negro">
                        <?= $idEditar > 0 ? 'Guardar cambios' : 'Crear categoría' ?>
                    </button>

                    <?php if ($idEditar > 0): ?>
                        <a href="admin_categorias.php" class="btn btn-outline-dark">Cancelar edición</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Padre</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Activa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?= (int)$cat['id_categoria'] ?></td>

                                <td>
                                    <?= $cat['nombre_padre'] ? htmlspecialchars($cat['nombre_padre']) : '-' ?>
                                </td>

                                <td><?= htmlspecialchars($cat['nombre']) ?></td>

                                <td><?= htmlspecialchars($cat['descripcion'] ?? '') ?></td>

                                <td>
                                    <?php if ((int)$cat['activa'] === 1): ?>
                                        <span class="badge-estado estado-si">Sí</span>
                                    <?php else: ?>
                                        <span class="badge-estado estado-no">No</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a
                                            href="admin_categorias.php?editar=<?= (int)$cat['id_categoria'] ?>"
                                            class="btn btn-sm btn-negro"
                                        >
                                            Editar
                                        </a>

                                        <a
                                            href="admin_categorias.php?toggle=1&id=<?= (int)$cat['id_categoria'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Cambiar estado de la categoría?');"
                                        >
                                            Activar/Desactivar
                                        </a>

                                        <a
                                            href="admin_categorias.php?eliminar=1&id=<?= (int)$cat['id_categoria'] ?>"
                                            class="btn btn-sm btn-outline-dark"
                                            onclick="return confirm('¿Seguro que quieres eliminar esta categoría?');"
                                        >
                                            Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($categorias) === 0): ?>
                            <tr>
                                <td colspan="6">No hay categorías registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>