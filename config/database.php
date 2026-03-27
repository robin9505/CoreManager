<?php
/**
 * CoreManager - Configuración de Conexión a Base de Datos
 * Entorno: Local (XAMPP)
 */

class Database {
    private $host = "localhost";
    private $db_name = "coremanager";
    private $username = "root";
    private $password = ""; 
    public $conn;

    /**
     * Obtiene la conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Configuramos el DSN (Data Source Name)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Opciones de configuración de PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa consultas preparadas reales
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $exception) {
            // En un entorno de producción, aquí podrías loguear el error en /logs
            error_log("Error de conexión: " . $exception->getMessage());
            die("Error crítico: No se pudo conectar a la base de datos.");
        }

        return $this->conn;
    }
}