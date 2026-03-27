<?php
session_start();
require_once '../src/Auth/Security.php';
Security::onlyAdmin(); // Bloqueo de seguridad

require_once '../config/database.php';
require_once '../src/Repository/ToppingRepository.php';

$database = new Database();
$db = $database->getConnection();
$repo = new ToppingRepository($db);

$mensaje = "";
$topping_edit = null;

// --- LÓGICA DE ACCIONES ---
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'save') {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $id = $_POST['id'] ?? null;

        if ($id) {
            $repo->update($id, $nombre, $precio);
            $mensaje = "Topping actualizado.";
        } else {
            $repo->create($nombre, $precio);
            $mensaje = "Topping creado.";
        }
    }
}

if (isset($_GET['delete'])) {
    $repo->delete($_GET['delete']);
    header("Location: toppings.php");
}

if (isset($_GET['edit'])) {
    $topping_edit = $repo->getById($_GET['edit']);
}

$toppings = $repo->getAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CoreManager - Toppings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8fafc; }
        .btn-edit { color: var(--primary); text-decoration: none; margin-right: 10px; }
        .btn-delete { color: var(--error); text-decoration: none; }
        .form-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<header>
    <div class="logo">CoreManager</div>
    <nav><a href="index.php" style="color:white; text-decoration:none;">⬅️ Volver</a></nav>
</header>

<main class="dashboard-content">
    <h1>Gestión de Toppings</h1>
    <p>Administra los ingredientes extras y sus precios.</p>

    <section class="form-section">
        <h3><?php echo $topping_edit ? 'Editar Topping' : 'Nuevo Topping'; ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <?php if($topping_edit): ?>
                <input type="hidden" name="id" value="<?php echo $topping_edit['id_topping']; ?>">
            <?php endif; ?>

            <div style="display: flex; gap: 15px; align-items: flex-end;">
                <div style="flex: 2;">
                    <label>Nombre del Topping</label>
                    <input type="text" name="nombre" required value="<?php echo $topping_edit['nombre_topping'] ?? ''; ?>">
                </div>
                <div style="flex: 1;">
                    <label>Precio Extra ($)</label>
                    <input type="number" step="0.01" name="precio" required value="<?php echo $topping_edit['precio_topping'] ?? ''; ?>">
                </div>
                <div style="flex: 1;">
                    <button type="submit"><?php echo $topping_edit ? 'Actualizar' : 'Guardar'; ?></button>
                    <?php if($topping_edit): ?>
                        <a href="toppings.php" style="display:block; text-align:center; font-size:12px; margin-top:5px;">Cancelar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </section>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio Extra</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($toppings as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['nombre_topping']); ?></td>
                <td>$<?php echo number_format($t['precio_topping'], 2); ?></td>
                <td>
                    <a href="?edit=<?php echo $t['id_topping']; ?>" class="btn-edit">Editar</a>
                    <a href="?delete=<?php echo $t['id_topping']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar topping?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>