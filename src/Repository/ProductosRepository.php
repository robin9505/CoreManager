<?php
/**
 * CoreManager - Repositorio de Productos
 * Maneja la persistencia atómica de Productos y sus variantes de Tamaño.
 */
class ProductosRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllVariants() {
        $sql = "SELECT p.id_producto, p.nombre_producto, p.descripcion, t.nombre_tamano, 
                       pt.precio, pt.toppings_incluidos, pt.id_producto_tamano 
                FROM productos p
                JOIN producto_tamano pt ON p.id_producto = pt.id_producto
                JOIN tamanos t ON pt.id_tamano = t.id_tamano
                ORDER BY p.nombre_producto ASC, pt.precio ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos del producto y sus tamaños activos para edición
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id_producto = :id");
        $stmt->execute([':id' => $id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $stmt = $this->db->prepare("SELECT id_tamano, precio, toppings_incluidos FROM producto_tamano WHERE id_producto = :id");
            $stmt->execute([':id' => $id]);
            $producto['variantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $producto;
    }

    /**
     * Guarda o Actualiza un producto. 
     * Si se quita un checkbox, la variante se elimina gracias al DELETE inicial en modo edición.
     */
    public function save($nombre, $descripcion, $variantes, $id = null) {
        try {
            $this->db->beginTransaction();

            if ($id) {
                // Modo Edición
                $stmt = $this->db->prepare("UPDATE productos SET nombre_producto = :nombre, descripcion = :desc WHERE id_producto = :id");
                $stmt->execute([':nombre' => $nombre, ':desc' => $descripcion, ':id' => $id]);
                
                // Limpieza de variantes para sincronización (Elimina los tamaños que el admin desmarcó)
                $this->db->prepare("DELETE FROM producto_tamano WHERE id_producto = :id")->execute([':id' => $id]);
                $id_producto = $id;
            } else {
                // Modo Inserción
                $stmt = $this->db->prepare("INSERT INTO productos (nombre_producto, descripcion) VALUES (:nombre, :desc)");
                $stmt->execute([':nombre' => $nombre, ':desc' => $descripcion]);
                $id_producto = $this->db->lastInsertId();
            }

            $sql = "INSERT INTO producto_tamano (id_producto, id_tamano, precio, toppings_incluidos) VALUES (:id_p, :id_t, :precio, :toppings)";
            $stmt_pt = $this->db->prepare($sql);

            foreach ($variantes as $id_tamano => $datos) {
                // Solo guardamos si el checkbox envió un precio válido
                if (!empty($datos['precio']) && $datos['precio'] > 0) {
                    $stmt_pt->execute([
                        ':id_p' => $id_producto,
                        ':id_t' => $id_tamano,
                        ':precio' => $datos['precio'],
                        ':toppings' => !empty($datos['toppings']) ? (int)$datos['toppings'] : 0
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteProduct($id) {
        $this->db->prepare("DELETE FROM producto_tamano WHERE id_producto = :id")->execute([':id' => $id]);
        return $this->db->prepare("DELETE FROM productos WHERE id_producto = :id")->execute([':id' => $id]);
    }
}