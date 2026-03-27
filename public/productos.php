<?php
session_start();
require_once '../src/Auth/Security.php';
Security::onlyAdmin();

require_once '../config/database.php';
require_once '../src/Repository/ProductosRepository.php';
require_once '../src/Repository/TamanosRepository.php';

$database = new Database();
$db = $database->getConnection();
$prodRepo = new ProductosRepository($db);
$tamRepo = new TamanosRepository($db);

$mensaje = "";
$producto_edit = null;

// CARGAR DATOS PARA EDICIÓN
if (isset($_GET['edit'])) {
    $producto_edit = $prodRepo->getById($_GET['edit']);
}

// PROCESAR FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_producto'] ?? '';
    $desc = $_POST['descripcion'] ?? '';
    $variantes = $_POST['variantes'] ?? [];
    $id = $_POST['id_producto'] ?? null;

    if ($prodRepo->save($nombre, $desc, $variantes, $id)) {
        header("Location: productos.php?res=ok");
        exit();
    } else {
        $mensaje = "❌ Error en el proceso de guardado.";
    }
}

// ELIMINAR PRODUCTO COMPLETO
if (isset($_GET['delete'])) {
    if ($prodRepo->deleteProduct($_GET['delete'])) {
        header("Location: productos.php?res=del");
        exit();
    }
}

if (isset($_GET['res'])) {
    if ($_GET['res'] == 'ok') $mensaje = "✅ Producto actualizado correctamente.";
    if ($_GET['res'] == 'del') $mensaje = "🗑️ Producto eliminado del catálogo.";
}

$tamanosDisponibles = $tamRepo->getAll();
$catalogo = $prodRepo->getAllVariants();

// Función helper para la UI
function findVariant($id_tamano, $producto_edit) {
    if (!$producto_edit) return null;
    foreach ($producto_edit['variantes'] as $v) {
        if ($v['id_tamano'] == $id_tamano) return $v;
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CoreManager - Catálogo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .grid-tamanos { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; margin: 15px 0; }
        .card-tamano { background: #fff; border: 2px solid #e2e8f0; padding: 1rem; border-radius: 8px; transition: 0.3s; }
        .card-tamano.active { border-color: var(--primary); background: #f0f7ff; }
        .input-v { width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; margin-top: 5px; }
        .input-v:disabled { background: #f1f5f9; cursor: not-allowed; opacity: 0.5; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 2rem; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .badge-t { background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>

    <header>
        <div class="logo">CoreManager</div>
        <nav><a href="index.php" style="color:white; text-decoration:none;">⬅️ Volver</a></nav>
    </header>

    <main class="dashboard-content">
        <h1>Gestión de Productos</h1>
        <?php if($mensaje): ?><div style="padding:15px; background:#f1f5f9; border-left:5px solid var(--primary); margin-bottom:20px;"><?php echo $mensaje; ?></div><?php endif; ?>

        <section class="form-card" style="max-width: 1000px;">
            <h3><?php echo $producto_edit ? '🛠️ Editando: ' . htmlspecialchars($producto_edit['nombre_producto']) : '📦 Nuevo Producto'; ?></h3>
            <form method="POST">
                <?php if($producto_edit): ?>
                    <input type="hidden" name="id_producto" value="<?php echo $producto_edit['id_producto']; ?>">
                <?php endif; ?>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label>Nombre del Producto</label>
                        <input type="text" name="nombre_producto" required value="<?php echo $producto_edit['nombre_producto'] ?? ''; ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" value="<?php echo $producto_edit['descripcion'] ?? ''; ?>">
                    </div>
                </div>

                <label><strong>Variantes de Tamaño:</strong></label>
                <div class="grid-tamanos">
                    <?php foreach ($tamanosDisponibles as $t): 
                        $variante = findVariant($t['id_tamano'], $producto_edit);
                        $estaActivo = !is_null($variante);
                    ?>
                    <div class="card-tamano <?php echo $estaActivo ? 'active' : ''; ?>" id="card-<?php echo $t['id_tamano']; ?>">
                        <label style="cursor:pointer; font-weight:bold; display:block;">
                            <input type="checkbox" onchange="toggleSize(this, <?php echo $t['id_tamano']; ?>)" <?php echo $estaActivo ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($t['nombre_tamano']); ?>
                        </label>
                        <div style="margin-top:10px;">
                            <small>Precio Venta:</small>
                            <input type="number" step="0.01" name="variantes[<?php echo $t['id_tamano']; ?>][precio]" 
                                   id="p-<?php echo $t['id_tamano']; ?>" class="input-v" 
                                   placeholder="$ 0.00" <?php echo $estaActivo ? 'required' : 'disabled'; ?> value="<?php echo $variante['precio'] ?? ''; ?>">
                        </div>
                        <div style="margin-top:5px;">
                            <small>Toppings Incluidos:</small>
                            <input type="number" name="variantes[<?php echo $t['id_tamano']; ?>][toppings]" 
                                   id="t-<?php echo $t['id_tamano']; ?>" class="input-v" 
                                   placeholder="Cantidad" <?php echo $estaActivo ? '' : 'disabled'; ?> value="<?php echo $variante['toppings_incluidos'] ?? '0'; ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit"><?php echo $producto_edit ? 'Guardar Cambios' : 'Crear Producto'; ?></button>
                <?php if($producto_edit): ?> <a href="productos.php" style="margin-left:15px; color:#666;">Cancelar</a> <?php endif; ?>
            </form>
        </section>

        <h3>📋 Listado de Variantes Activas</h3>
        <table>
            <thead><tr><th>Producto</th><th>Tamaño</th><th>Precio</th><th>Incluye</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php $lastP = ''; foreach ($catalogo as $item): ?>
                <tr>
                    <td><?php if($lastP != $item['nombre_producto']): ?><strong><?php echo htmlspecialchars($item['nombre_producto']); ?></strong><?php endif; ?></td>
                    <td><?php echo htmlspecialchars($item['nombre_tamano']); ?></td>
                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                    <td><span class="badge-t"><?php echo $item['toppings_incluidos']; ?> toppings</span></td>
                    <td>
                        <a href="?edit=<?php echo $item['id_producto']; ?>" style="color:var(--primary); font-weight:600; text-decoration:none; margin-right:10px;">Editar</a>
                        <a href="?delete=<?php echo $item['id_producto']; ?>" style="color:var(--error); font-weight:600; text-decoration:none;" onclick="return confirm('¿Borrar todo el producto y sus tamaños?')">Borrar</a>
                    </td>
                </tr>
                <?php $lastP = $item['nombre_producto']; endforeach; ?>
            </tbody>
        </table>
    </main>

    <script>
        function toggleSize(checkbox, id) {
            const precio = document.getElementById('p-' + id);
            const toppings = document.getElementById('t-' + id);
            const card = document.getElementById('card-' + id);
            
            precio.disabled = toppings.disabled = !checkbox.checked;
            
            if(checkbox.checked) {
                precio.required = true;
                card.classList.add('active');
                precio.focus();
            } else {
                precio.required = false;
                precio.value = toppings.value = '';
                card.classList.remove('active');
            }
        }
    </script>
</body>
</html>