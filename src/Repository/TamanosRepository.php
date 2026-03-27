<?php
/**
 * CoreManager - Repositorio de la tabla Tamanos
 */

class TamanosRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los registros de la tabla Tamanos
     */
    public function getAll() {
        $sql = "SELECT id_tamano, nombre_tamano FROM Tamanos ORDER BY id_tamano ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un registro por su clave primaria id_tamano
     */
    public function getById($id) {
        $sql = "SELECT * FROM Tamanos WHERE id_tamano = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo nombre_tamano
     */
    public function create($nombre) {
        $sql = "INSERT INTO Tamanos (nombre_tamano) VALUES (:nombre)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':nombre' => $nombre]);
    }

    /**
     * Actualiza un registro basado en su id_tamano
     */
    public function update($id, $nombre) {
        $sql = "UPDATE Tamanos SET nombre_tamano = :nombre WHERE id_tamano = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de la tabla Tamanos
     */
    public function delete($id) {
        $sql = "DELETE FROM Tamanos WHERE id_tamano = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}