<?php
class AuthService {
    private $userRepository;

    public function __construct($userRepository) {
        $this->userRepository = $userRepository;
    }

    public function login($username, $password) {
        $user = $this->userRepository->findByUsername($username);

        // Verificamos si el usuario existe y la contraseña coincide
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol'] = $user['nombre_rol'];
            return true;
        }

        return false;
    }

    public static function logout() {
        session_start();
        session_destroy();
        header("Location: login.php");
    }
}