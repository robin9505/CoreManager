<?php
class ToppingRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM Toppings ORDER BY nombre_topping ASC")->fetchAll();
    }

    public function create($nombre, $precio) {
        $sql = "INSERT INTO Toppings (nombre_topping, precio_topping) VALUES (:nombre, :precio)";
        return $this->db->prepare($sql)->execute([':nombre' => $nombre, ':precio' => $precio]);
    }

    public function update($id, $nombre, $precio) {
        $sql = "UPDATE Toppings SET nombre_topping = :nombre, precio_topping = :precio WHERE id_topping = :id";
        return $this->db->prepare($sql)->execute([':id' => $id, ':nombre' => $nombre, ':precio' => $precio]);
    }

    public function delete($id) {
        $sql = "DELETE FROM Toppings WHERE id_topping = :id";
        return $this->db->prepare($sql)->execute([':id' => $id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM Toppings WHERE id_topping = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}