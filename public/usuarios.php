<?php
session_start();
// Protección de ruta: Solo logueados pueden crear usuarios
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../src/Auth/Security.php';

// ESTO BLOQUEA A CUALQUIERA QUE NO SEA ADMIN
Security::onlyAdmin();
require_once '../config/database.php';
require_once '../src/Repository/UserRepository.php';

$database = new Database();
$db = $database->getConnection();
$userRepo = new UserRepository($db);

$mensaje = "";
$tipo_alerta = ""; // Para diferenciar éxito de error

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_user = $_POST['username'] ?? '';
    $nuevo_pass = $_POST['password'] ?? '';
    $id_rol     = $_POST['id_rol'] ?? '';

    if (!empty($nuevo_user) && !empty($nuevo_pass) && !empty($id_rol)) {
        if ($userRepo->createUser($nuevo_user, $nuevo_pass, $id_rol)) {
            $mensaje = "✅ Usuario '$nuevo_user' creado correctamente.";
            $tipo_alerta = "success";
        } else {
            $mensaje = "❌ Error al crear el usuario.";
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
        $tipo_alerta = "error";
    }
}

// Obtener roles para el combo (select)
$roles = $userRepo->getAllRoles();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreManager - Usuarios</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Estilos rápidos específicos para formularios internos */
        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            max-width: 500px;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <header>
        <div class="logo">CoreManager</div>
        <nav>
            <a href="index.php" style="color: white; text-decoration: none; font-size: 0.9rem;">⬅️ Volver al Panel</a>
        </nav>
    </header>

    <main class="dashboard-content">
        <h1>Gestión de Usuarios</h1>
        <p>Registra nuevos accesos para el personal del negocio.</p>
        <br>

        <?php if ($mensaje): ?>
            <div class="alert <?php echo ($tipo_alerta === 'success') ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <section class="form-card">
            <h3>Crear Nuevo Acceso</h3>
            <br>
            <form method="POST" action="usuarios.php">
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" name="username" required placeholder="Ej: vendedora_ana">
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Rol del Sistema</label>
                    <select name="id_rol" required>
                        <option value="">-- Selecciona un Rol --</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id_rol']; ?>">
                                <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <br>
                <button type="submit">Guardar Usuario</button>
            </form>
        </section>
    </main>

</body>
</html>