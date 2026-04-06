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
$pageTitle = "Despacho de pedidos";
?>

<!DOCTYPE html>
<html lang="es">
<head>
       <?php include 'includes/head.php'; ?>
    <meta http-equiv="refresh" content="30">
    Aquí tienes el bloque de <style> completo y optimizado. Está diseñado para que el Header y el Footer se queden en su lugar (fijos) y solo el contenido de los pedidos haga scroll. Además, añadí un ajuste para que si una orden es muy larga, el scroll sea interno en la tarjeta y el botón de "Entregar" siempre esté a la vista.

Copia y sustituye todo tu bloque de <style> actual en despacho.php:

CSS
<style>
    /* 1. Reset y Estructura de Pantalla Completa */
    :root {
        --success: #10b981;
        --white: #ffffff;
        --dark: #1e293b;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        height: 100vh;
        height: 100dvh; /* Altura dinámica para móviles */
        font-family: 'Inter', sans-serif; /* O la que uses en CoreVenta */
        background-color: #f1f5f9;
        overflow: hidden; /* Evita el scroll doble en el body */
    }

    header, footer {
        flex-shrink: 0; /* Impide que el header/footer se aplasten */
        z-index: 100;
    }

    /* 2. Contenedor Principal con Scroll Propio */
    .dashboard-content {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        -webkit-overflow-scrolling: touch; /* Scroll suave en iOS */
    }

    /* 3. Grid de Pedidos Responsivo */
    .grid-despacho {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
        align-items: start;
    }

    /* 4. Card de Pedido con Scroll Interno */
    .card-pedido {
        background: var(--white);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border-top: 4px solid var(--success);
        max-height: 550px; /* Altura máxima para no romper el layout */
        overflow: hidden;
    }

    .pedido-header {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .pedido-body {
        padding: 1rem;
        flex: 1;
        overflow-y: auto; /* Scroll interno si la orden tiene muchos items */
        background: #fff;
    }

    /* 5. Items y Toppings */
    .item-preparar {
        padding: 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        position: relative;
    }

    .qty-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--dark);
        color: white;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .topping-tag {
        display: inline-block;
        background: #e2e8f0;
        color: #475569;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        margin: 2px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* 6. Botón de Acción (Siempre visible al final de la card) */
    form {
        margin: 0;
        flex-shrink: 0;
    }

    .btn-ready {
        width: 100%;
        background-color: var(--success);
        color: var(--white);
        border: none;
        padding: 1rem;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-ready:hover {
        background-color: #059669;
        filter: brightness(1.1);
    }

    .btn-ready:active {
        transform: scale(0.98);
    }

    /* 7. Ajustes para Celulares Económicos */
    @media (max-width: 600px) {
        .dashboard-content {
            padding: 10px;
        }
        
        .grid-despacho {
            grid-template-columns: 1fr; /* Una sola orden por fila */
            gap: 1rem;
        }

        h1 { font-size: 1.4rem; }
        
        .card-pedido {
            max-height: 450px; /* Más compacto en móvil */
        }
    }
</style>
</head>
<body>

     <?php include 'includes/header.php'; ?>

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
<?php include 'includes/footer.php'; ?>
</body>
</html>