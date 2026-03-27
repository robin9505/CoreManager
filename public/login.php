<?php
// public/login.php
require_once '../config/database.php';
require_once '../src/Repository/UserRepository.php';
require_once '../src/Services/AuthService.php';

$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $userRepo = new UserRepository($db);
    $auth = new AuthService($userRepo);

    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($auth->login($user, $pass)) {
        header("Location: index.php");
        exit();
    } else {
        $mensaje_error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CoreManager - Login</title>
   <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h1>CoreManager</h1>
        
        <?php if ($mensaje_error): ?>
            <div class="error-msg"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Ingresar al Sistema</button>
        </form>
    </div>
</body>
</html>