<?php
// src/Auth/Security.php

class Security {
    /**
     * Verifica si el usuario tiene permiso de Administrador
     * Si no, lo redirige al index o muestra un error.
     */
    public static function onlyAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no es Administrador, lo mandamos al dashboard con un mensaje
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
            header("Location: index.php?error=no_permission");
            exit();
        }
    }
}