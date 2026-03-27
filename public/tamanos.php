<?php
session_start();
require_once '../src/Auth/Security.php';
Security::onlyAdmin(); 

require_once '../config/database.php';
require_once '../src/Repository/TamanosRepository.php'; // Nombre corregido

$database = new Database();
$db = $database->getConnection();
$repo = new TamanosRepository($db); // Clase corregida

$mensaje = "";
$tamano_edit = null;

// --- PROCESAR ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_tamano'] ?? '';
    $id = $_POST['id_tamano'] ?? null;

    if (!empty($nombre)) {
        if ($id) {
            if ($repo->update($id, $nombre)) {
                $mensaje = "✅ Tamaño actualizado correctamente.";
            }
        } else {
            if ($repo->create($nombre)) {
                $mensaje = "✅ Nuevo tamaño registrado.";
            }
        }
        $tamano_edit = null; 
    }
}

// --- PROCESAR ELIMINACIÓN (GET) ---
if (isset($_GET['delete'])) {
    try {
        if ($repo->delete($_GET['delete'])) {
            $mensaje = "🗑️ Tamaño eliminado.";
        }
    } catch (Exception $e) {
        $mensaje = "❌ No se puede eliminar: Este tamaño está siendo usado por productos.";
    }
}

// --- PREPARAR EDICIÓN (GET) ---
if (isset($_GET['edit'])) {
    $tamano_edit = $repo->getById($_GET['edit']);
}

$tamanos = $repo->getAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CoreManager - Tamaños</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .crud-container { display: flex; gap: 2rem; align-items: flex-start; }
        .form-side { flex: 1; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; position: sticky; top: 20px; }
        .table-side { flex: 2; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .badge-id { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>

    <header>
        <div class="logo">CoreManager</div>
        <nav><a href="index.php" style="color:white; text-decoration:none;">⬅️ Volver al Panel</a></nav>
    </header>

    <main class="dashboard-content">
        <h1>Configuración de Tamaños</h1>
        <p>Define las presentaciones (Litro, Chico, etc.) para tus productos.</p>
        <br>

        <?php if ($mensaje): ?>
            <div style="padding: 1rem; background: #f1f5f9; border-left: 4px solid var(--primary); margin-bottom: 1.5rem;">
                <strong><?php echo $mensaje; ?></strong>
            </div>
        <?php endif; ?>

        <div class="crud-container">
            <section class="form-side">
                <h3><?php echo $tamano_edit ? 'Editar Tamaño' : 'Nuevo Tamaño'; ?></h3>
                <br>
                <form method="POST" action="tamanos.php">
                    <?php if ($tamano_edit): ?>
                        <input type="hidden" name="id_tamano" value="<?php echo $tamano_edit['id_tamano']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nombre de la Presentación</label>
                        <input type="text" name="nombre_tamano" required 
                               value="<?php echo $tamano_edit['nombre_tamano'] ?? ''; ?>">
                    </div>
                    <br>
                    <button type="submit"><?php echo $tamano_edit ? 'Actualizar' : 'Guardar'; ?></button>
                    <?php if ($tamano_edit): ?>
                        <a href="tamanos.php" style="display:block; margin-top:10px; font-size:0.8rem; color:#666;">Cancelar edición</a>
                    <?php endif; ?>
                </form>
            </section>

            <section class="table-side">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tamanos as $t): ?>
                        <tr>
                            <td><span class="badge-id">#<?php echo $t['id_tamano']; ?></span></td>
                            <td><strong><?php echo htmlspecialchars($t['nombre_tamano']); ?></strong></td>
                            <td style="text-align: right;">
                                <a href="?edit=<?php echo $t['id_tamano']; ?>" style="color: var(--primary); text-decoration:none; font-weight:600; margin-right:15px;">Editar</a>
                                <a href="?delete=<?php echo $t['id_tamano']; ?>" style="color: var(--error); text-decoration:none; font-weight:600;" onclick="return confirm('¿Borrar este tamaño?')">Borrar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html>