<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once '../config/database.php';
require_once '../src/Repository/VentasRepository.php';

$database = new Database();
$db = $database->getConnection();
$ventRepo = new VentasRepository($db);

// Definir rango de fechas (por defecto hoy)
$fecha_inicio = $_GET['desde'] ?? date('Y-m-d');
$fecha_fin = $_GET['hasta'] ?? date('Y-m-d');

$ventas = $ventRepo->getVentasPorRango($fecha_inicio, $fecha_fin);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CoreManager - Reporte de Ventas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <style>
        .report-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; }
        .filter-bar { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; border: 1px solid #e2e8f0; }
        .detalle-row { background: #f1f5f9 !important; font-size: 0.9rem; }
        .badge-extra { background: #dcfce7; color: #166534; padding: 2px 5px; border-radius: 4px; font-size: 0.7rem; }
    </style>
</head>
<body>
    <header>
        <div class="logo">CoreManager - Reportes</div>
        <nav><a href="index.php" style="color:white;">⬅️ Volver al Panel</a></nav>
    </header>

    <main class="dashboard-content">
        <h1>Reporte de Ventas</h1>

        <form class="filter-bar" method="GET">
            <div>
                <label>Desde:</label><br>
                <input type="date" name="desde" value="<?php echo $fecha_inicio; ?>" style="padding:8px; border-radius:5px; border:1px solid #ccc;">
            </div>
            <div>
                <label>Hasta:</label><br>
                <input type="date" name="hasta" value="<?php echo $fecha_fin; ?>" style="padding:8px; border-radius:5px; border:1px solid #ccc;">
            </div>
            <button type="submit" style="background:var(--dark); color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Filtrar Rango</button>
        </form>

        <div class="report-container">
            <table id="tablaVentas" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha/Hora</th>
                        <th>Vendedor</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><strong>#<?php echo $v['id_venta']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td><?php echo htmlspecialchars($v['username']); ?></td>
                        <td>
                            <?php foreach ($v['detalles'] as $d): ?>
                                <div style="margin-bottom: 5px; border-bottom: 1px dashed #ccc; padding-bottom: 2px;">
                                    <b><?php echo $d['cantidad']; ?>x <?php echo $d['nombre_producto']; ?></b> (<?php echo $d['tamano']; ?>)
                                    <br>
                                    <small>
                                        <?php foreach ($d['toppings'] as $t): ?>
                                            • <?php echo $t['nombre']; ?> <?php echo $t['es_extra'] ? '<span class="badge-extra">EXTRA</span>' : ''; ?>
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td style="font-weight:bold;">$<?php echo number_format($v['total'], 2); ?></td>
                        <td>
                            <span style="padding:4px 8px; border-radius:15px; font-size:0.8rem; background:<?php echo $v['estado_despacho'] == 'entregado' ? '#dcfce7' : '#fef9c3'; ?>;">
                                <?php echo strtoupper($v['estado_despacho']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaVentas').DataTable({
                responsive: true,
                order: [[1, 'desc']], // Ordenar por fecha (columna 1) de forma descendente
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' // Traducción a español
                },
                pageLength: 10,
                dom: 'Bfrtip', // Para botones si quisieras exportar luego
            });
        });
    </script>
</body>
</html>