<?php
session_start();

// Si no hay sesión, redirigir al login inmediatamente
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Datos del usuario para mostrar en el Dashboard

$rol = $_SESSION['rol'];
$pageTitle = "Dashboard";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="dashboard-content">
        <h1>Panel de Control</h1>
        <p>Selecciona una opción para comenzar la gestión de tu negocio:</p>

        <div class="grid-menu">
            <a href="ventas.php" class="card">
                <h3>🛒 Nueva Venta</h3>
                <p>Registrar pedidos con tamaños y toppings personalizados.</p>
            </a>
            <a href="despacho.php" class="card" style="border-color: #10b981;">
        <h3>👨‍🍳 Despacho</h3>
        <p>Ver pedidos pendientes y marcar como entregados.</p>
    </a>
            <?php if ($rol === 'Administrador'): ?>
            <a href="reporte_ventas.php" class="card">
                <h3>📊 Reporte de ventas</h3>
                <p>Visualiza las ventas del día y el rendimiento del negocio.</p>
            </a>

            <a href="productos.php" class="card">
                <h3>🍦 Productos</h3>
                <p>Gestionar catálogo, tamaños y precios base.</p>
            </a>

            <a href="toppings.php" class="card">
                <h3>🍬 Toppings</h3>
                <p>Configurar ingredientes extras y sus costos.</p>
            </a>

            <a href="tamanos.php" class="card">
                <h3>📏 Tamaños</h3>
                <p>Define las presentaciones (Litro, Chico, Grande, etc.)</p>
            </a>
            <a href="usuarios.php" class="card" style="border-top-color: var(--success);">
                <h3>👥 Usuarios</h3>
                <p>Gestionar accesos, roles y personal del sistema.</p>
            </a>
            <?php endif; ?>
        </div>
        
    </main>
<?php include 'includes/footer.php'; ?>
</body>
</html>