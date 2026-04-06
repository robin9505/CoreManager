<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../src/Repository/ProductosRepository.php';
require_once '../src/Repository/VentasRepository.php';

$database = new Database();
$db = $database->getConnection();

$prodRepo = new ProductosRepository($db);
$ventRepo = new VentasRepository($db);

$username = $_SESSION['username'];

// 1. Obtener Toppings
$stmtT = $db->query("SELECT id_topping, nombre_topping, precio_topping AS precio FROM toppings ORDER BY nombre_topping ASC");
$toppingsDB = $stmtT->fetchAll(PDO::FETCH_ASSOC);

// 2. Agrupar Productos por Nombre
$todasLasVariantes = $prodRepo->getAllVariants();
$productosAgrupados = [];
foreach ($todasLasVariantes as $v) {
    $productosAgrupados[$v['nombre_producto']][] = $v;
}

// Lógica de guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['venta_data'])) {
    $res = $ventRepo->registrarVenta($_SESSION['user_id'], $_POST['venta_data']);
    if ($res) { 
        header("Location: ventas.php?success=1"); 
        exit(); 
    }
}
$pageTitle = "Punto de Venta";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/head.php'; ?>
    <link rel="stylesheet" href="assets/css/pos.css">
</head>
<body class="pos-screen">

    <?php include 'includes/header.php'; ?>

    <main class="pos-wrapper">
        <div class="pos-container">
            
            <div class="catalog-area">
                <div class="search-box">
                    <input type="text" id="search-prod" placeholder="🔍 Buscar producto..." onkeyup="filterProducts()">
                </div>
                
                <div class="catalog" id="main-catalog">
                    <?php foreach($productosAgrupados as $nombre => $variantes): 
                        $descripcion = $variantes[0]['descripcion'] ?? 'Sin descripción';
                    ?>
                    <div class="product-card" 
                         data-nombre="<?php echo strtolower(htmlspecialchars($nombre)); ?>"
                         onclick='abrirSeleccionTamano(<?php echo json_encode($variantes, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                        <div style="font-size: 2rem;">🍦</div>
                        <h3><?php echo htmlspecialchars($nombre); ?></h3>
                        <p><?php echo htmlspecialchars($descripcion); ?></p>
                        <small class="tag-tamano"><?php echo count($variantes); ?> Tamaños</small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ticket">
                <h3 class="ticket-title">RESUMEN DE VENTA</h3>
                <div id="ticket-items" class="ticket-list">
                    <p class="empty-msg">El ticket está vacío</p>
                </div>
                
                <div class="ticket-footer">
                    <div class="total-container">
                        <span>TOTAL:</span>
                        <h2 id="total-text">$0.00</h2>
                    </div>
                    <form method="POST" id="form-venta" onsubmit="return prepararEnvio()">
                        <input type="hidden" name="venta_data" id="venta_data">
                        <button type="submit" id="btn-cobrar">REGISTRAR VENTA</button>
                    </form>
                </div>
            </div>

        </div>
    </main>
<?php include 'includes/footer.php'; ?>
    <div id="modal-tamanos" class="modal">
        <div class="modal-content">
            <h2 id="t-prod-name">Elegir Tamaño</h2>
            <div id="tamanos-list" style="max-height: 300px; overflow-y: auto; margin-top: 15px;"></div>
            <button onclick="cerrarModales()" style="width:100%; margin-top:15px; background:#f1f5f9; border:none; padding:12px; border-radius:8px; cursor:pointer;">Cancelar</button>
        </div>
    </div>

    <div id="modal-toppings" class="modal">
        <div class="modal-content">
            <h2 id="m-title">Toppings</h2>
            <p id="m-desc" style="margin-bottom:10px; font-weight:bold; color:var(--dark);"></p>
            
            <div class="search-box">
                <input type="text" id="search-top" placeholder="🔍 Buscar topping..." onkeyup="filterRows('search-top', 'top-row')">
            </div>
            
            <div id="toppings-selection" style="max-height: 200px; overflow-y: auto; border: 1px solid #f1f5f9; padding: 5px; border-radius: 8px;">
                <?php foreach($toppingsDB as $t): ?>
                <div class="opt-row top-row" data-nombre="<?php echo strtolower(htmlspecialchars($t['nombre_topping'])); ?>" onclick="toggleCheckbox(this)">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" class="t-check" 
                               data-id="<?php echo $t['id_topping']; ?>"
                               data-nombre="<?php echo htmlspecialchars($t['nombre_topping']); ?>"
                               data-precio="<?php echo $t['precio']; ?>" onclick="event.stopPropagation()">
                        <span><?php echo htmlspecialchars($t['nombre_topping']); ?></span>
                    </div>
                    <strong>+$<?php echo number_format($t['precio'], 2); ?></strong>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="qty-container">
                <span style="font-weight: bold;">Cantidad:</span>
                <input type="number" id="item-qty" class="qty-input" value="1" min="1">
            </div>

            <div style="display:flex; gap:10px; margin-top:15px;">
                <button onclick="confirmarVentaItem()" style="flex:2; background:var(--success); color:white; border:none; padding:14px; border-radius:8px; cursor:pointer; font-weight:bold;">LISTO</button>
                <button onclick="regresarATamanos()" style="flex:1; background:#f1f5f9; border:none; padding:14px; border-radius:8px; cursor:pointer;">ATRÁS</button>
            </div>
        </div>
    </div>

    <script>
        let carrito = [];
        let itemSeleccionado = null;
        let variantesActuales = [];

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                alert("✅ ¡Venta registrada con éxito!");
                window.history.replaceState({}, document.title, "ventas.php");
            }
        };

        function filterProducts() {
            const query = document.getElementById('search-prod').value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.toggle('hidden', !card.dataset.nombre.includes(query));
            });
        }

        function filterRows(inputId, rowClass) {
            const query = document.getElementById(inputId).value.toLowerCase();
            document.querySelectorAll('.' + rowClass).forEach(row => {
                row.classList.toggle('hidden', !row.dataset.nombre.includes(query));
            });
        }

        function abrirSeleccionTamano(variantes) {
            variantesActuales = variantes;
            const list = document.getElementById('tamanos-list');
            list.innerHTML = "";
            document.getElementById('t-prod-name').innerText = variantes[0].nombre_producto;
            
            variantes.forEach(v => {
                const div = document.createElement('div');
                div.className = "opt-row";
                div.innerHTML = `<span>${v.nombre_tamano}</span><strong>$${v.precio}</strong>`;
                div.onclick = () => abrirToppings(v);
                list.appendChild(div);
            });
            document.getElementById('modal-tamanos').style.display = 'flex';
        }

        function abrirToppings(variante) {
            itemSeleccionado = variante;
            document.getElementById('modal-tamanos').style.display = 'none';
            document.getElementById('m-title').innerText = "Personalizar " + variante.nombre_tamano;
            document.getElementById('m-desc').innerText = `🎁 Incluye ${variante.toppings_incluidos} toppings gratis`;
            
            // Resetear Modal
            document.getElementById('item-qty').value = 1;
            document.getElementById('search-top').value = "";
            filterRows('search-top', 'top-row');
            document.querySelectorAll('.t-check').forEach(cb => cb.checked = false);
            
            document.getElementById('modal-toppings').style.display = 'flex';
        }

        function regresarATamanos() {
            document.getElementById('modal-toppings').style.display = 'none';
            document.getElementById('modal-tamanos').style.display = 'flex';
        }

        function toggleCheckbox(div) {
            const cb = div.querySelector('.t-check');
            cb.checked = !cb.checked;
        }

        function confirmarVentaItem() {
            let seleccionados = [];
            document.querySelectorAll('.t-check:checked').forEach(cb => {
                seleccionados.push({ id: cb.dataset.id, nombre: cb.dataset.nombre, precio: parseFloat(cb.dataset.precio) });
            });

            const cantidad = parseInt(document.getElementById('item-qty').value) || 1;
            let precioBase = parseFloat(itemSeleccionado.precio);
            let limite = parseInt(itemSeleccionado.toppings_incluidos);
            let subtotalUnitario = precioBase;
            let topsFinales = [];

            seleccionados.forEach((t, i) => {
                let esExtra = (i >= limite) ? 1 : 0;
                let costo = esExtra ? t.precio : 0;
                subtotalUnitario += costo;
                topsFinales.push({ id_topping: t.id, nombre: t.nombre, es_extra: esExtra, precio_cobrado: costo });
            });

            // Agregamos al carrito con la cantidad
            carrito.push({
                id_producto_tamano: itemSeleccionado.id_producto_tamano,
                nombre: itemSeleccionado.nombre_producto,
                tamano: itemSeleccionado.nombre_tamano,
                cantidad: cantidad,
                precio_base: precioBase,
                subtotal_linea: subtotalUnitario * cantidad, // Precio Total de la línea
                toppings: topsFinales
            });

            renderTicket();
            cerrarModales();
        }

        function renderTicket() {
            const container = document.getElementById('ticket-items');
            if(carrito.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#94a3b8; margin-top:20px;">El ticket está vacío</p>';
                document.getElementById('total-text').innerText = "$0.00";
                return;
            }
            container.innerHTML = "";
            let totalVenta = 0;
            carrito.forEach((prod, index) => {
                totalVenta += prod.subtotal_linea;
                const div = document.createElement('div');
                div.className = "t-item";
                div.onclick = () => eliminarDelCarrito(index);
                div.innerHTML = `
                    <div style="display:flex; justify-content:space-between;">
                        <strong>(${prod.cantidad}) ${prod.nombre}</strong>
                        <span>$${prod.subtotal_linea.toFixed(2)}</span>
                    </div>
                    <small style="display:block; color:#64748b;">
                        ${prod.tamano} | Toppings: ${prod.toppings.map(t => t.nombre + (t.es_extra ? ' (Ex)':'')).join(', ') || 'Ninguno'}
                    </small>
                `;
                container.appendChild(div);
            });
            document.getElementById('total-text').innerText = "$" + totalVenta.toFixed(2);
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            renderTicket();
        }

        function cerrarModales() { 
            document.getElementById('modal-tamanos').style.display = 'none'; 
            document.getElementById('modal-toppings').style.display = 'none'; 
        }

        function prepararEnvio() {
            if (carrito.length === 0) {
                alert("Agrega al menos un producto al ticket");
                return false;
            }
            document.getElementById('venta_data').value = JSON.stringify(carrito);
            document.getElementById('btn-cobrar').innerText = "PROCESANDO...";
            document.getElementById('btn-cobrar').disabled = true;
            return true;
        }
    </script>
</body>
</html>