<?php
class UserRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findByUsername($username) {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM Usuarios u 
                JOIN Roles r ON u.id_rol = r.id_rol 
                WHERE u.username = :username 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch();
    }


public function getAllRoles() {
    $sql = "SELECT * FROM Roles";
    return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
        public function createUser($username, $password, $id_rol) {
            // IMPORTANTE: Encriptamos la contraseña antes de guardarla
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO Usuarios (username, password_hash, id_rol) 
                    VALUES (:username, :hash, :id_rol)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':username' => $username,
                ':hash'     => $hash,
                ':id_rol'   => $id_rol
            ]);
        }
}