<?php
session_start();

// Si no hay sesión, redirigir al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../src/Repository/VentasRepository.php';

$database = new Database();
$db = $database->getConnection();
$ventRepo = new VentasRepository($db);

// Lógica de actualización (Procesa el botón de Entregar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_venta'])) {
    $ventRepo->actualizarEstado($_POST['id_venta'], 'entregado');
    header("Location: despacho.php");
    exit();
}

$pedidos = $ventRepo->getVentasPendientes(); 
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreManager - Despacho</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta http-equiv="refresh" content="30">
    <style>
        /* Estilos específicos para la Grid de pedidos */
        .grid-despacho {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card-pedido {
            background: var(--white);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border-top: 4px solid var(--success); /* Verde para indicar operativo */
            overflow: hidden;
        }

        .pedido-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .pedido-body {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .item-preparar {
            padding: 0.75rem;
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            position: relative; /* Para el badge de cantidad */
        }

        /* Estilo para la burbuja de cantidad */
        .qty-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--dark);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            border: 2px solid white;
        }

        .topping-tag {
            display: inline-block;
            background: var(--white);
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 2px;
            font-weight: 600;
        }

        .btn-ready {
            width: 100%;
            background-color: var(--success);
            color: var(--white);
            border: none;
            padding: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-ready:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">CoreManager</div>
        <nav>
            <a href="index.php" style="color:white; text-decoration:none; margin-right: 20px;">⬅️ Volver al Panel</a>
            <span>Usuario: <strong><?php echo htmlspecialchars($username); ?></strong></span>
        </nav>
    </header>

    <main class="dashboard-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Área de Despacho</h1>
                <p>Gestiona los pedidos que entran desde la caja en tiempo real.</p>
            </div>
            <div style="background: var(--dark); color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold;">
                Ordenes: <?php echo count($pedidos); ?>
            </div>
        </div>

        <div class="grid-despacho">
            <?php if (empty($pedidos)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 5rem; background: white; border-radius: 12px;">
                    <h2 style="color: #94a3b8;">☕ Sin pedidos por ahora...</h2>
                </div>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="card-pedido">
                        <div class="pedido-header">
                            <strong>ORDEN #<?php echo $pedido['id_venta']; ?></strong>
                            <span style="font-size: 0.8rem; color: #64748b;">🕒 <?php echo date('H:i', strtotime($pedido['fecha_venta'])); ?></span>
                        </div>

                        <div class="pedido-body">
                            <?php foreach ($pedido['detalles'] as $item): ?>
                                <div class="item-preparar">
                                    <div class="qty-badge"><?php echo $item['cantidad']; ?></div>
                                    
                                    <div style="font-weight: bold; color: var(--dark);">
                                         <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; margin-bottom: 5px;">
                                        Cantidad: <?php echo $item['cantidad']; ?>
                                    </div>
                                    <div style="font-size: 0.85rem; margin-bottom: 5px;">
                                        Tamaño: <?php echo $item['tamano']; ?>
                                    </div>
                                    
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        <?php if (!empty($item['toppings'])): ?>
                                            <?php foreach ($item['toppings'] as $top): ?>
                                                <span class="topping-tag"><?php echo htmlspecialchars($top['nombre']); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="id_venta" value="<?php echo $pedido['id_venta']; ?>">
                            <button type="submit" class="btn-ready">MARCAR COMO ENTREGADO</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>