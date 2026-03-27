<?php
class VentasRepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function registrarVenta($id_usuario, $productos_json) {
        try {
            $this->db->beginTransaction();

            $productos = json_decode($productos_json, true);
            $total_venta = 0;

            foreach ($productos as $p) { 
                $total_venta += $p['subtotal_linea']; 
            }

            // 1. Insertar Cabecera
            $stmt = $this->db->prepare("INSERT INTO ventas (id_usuario, total, estado_despacho, fecha_venta) 
                                       VALUES (:u, :t, 'pendiente', NOW())");
            $stmt->execute([':u' => $id_usuario, ':t' => $total_venta]);
            $id_venta = $this->db->lastInsertId();

            // 2. Insertar Detalles
            foreach ($productos as $p) {
                $stmtD = $this->db->prepare("INSERT INTO detalle_ventas (id_venta, id_producto_tamano, cantidad, subtotal) 
                                             VALUES (:iv, :ipt, :can, :sub)");
                $stmtD->execute([
                    ':iv'  => $id_venta,
                    ':ipt' => $p['id_producto_tamano'],
                    ':can' => $p['cantidad'], // <--- CAMBIO: Ahora guarda la cantidad real
                    ':sub' => $p['subtotal_linea']
                ]);
                $id_detalle = $this->db->lastInsertId();

                // 3. Insertar Toppings
                if (!empty($p['toppings'])) {
                    foreach ($p['toppings'] as $top) {
                        $stmtT = $this->db->prepare("INSERT INTO detalle_toppings (id_detalle, id_topping, es_extra, precio_aplicado) 
                                                     VALUES (:id_d, :id_t, :extra, :precio)");
                        $stmtT->execute([
                            ':id_d'   => $id_detalle,
                            ':id_t'   => $top['id_topping'],
                            ':extra'  => $top['es_extra'],
                            ':precio' => $top['precio_cobrado']
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $id_venta;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function getVentasPendientes() {
        $query = "SELECT v.id_venta, v.fecha_venta, v.total, v.estado_despacho 
                  FROM ventas v 
                  WHERE v.estado_despacho != 'entregado' 
                  ORDER BY v.fecha_venta ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ventas as &$v) {
            $v['detalles'] = $this->getDetallesConToppings($v['id_venta']);
        }
        return $ventas;
    }

    private function getDetallesConToppings($id_venta) {
        // CAMBIO: Agregamos dv.cantidad a la consulta
        $query = "SELECT dv.id_detalle, dv.cantidad, p.nombre_producto, t.nombre_tamano as tamano
                  FROM detalle_ventas dv
                  JOIN producto_tamano pt ON dv.id_producto_tamano = pt.id_producto_tamano
                  JOIN productos p ON pt.id_producto = p.id_producto
                  JOIN tamanos t ON pt.id_tamano = t.id_tamano
                  WHERE dv.id_venta = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_venta]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($detalles as &$d) {
            $queryT = "SELECT t.nombre_topping as nombre, dt.es_extra
                       FROM detalle_toppings dt
                       JOIN toppings t ON dt.id_topping = t.id_topping
                       WHERE dt.id_detalle = ?";
            $stmtT = $this->db->prepare($queryT);
            $stmtT->execute([$d['id_detalle']]);
            $d['toppings'] = $stmtT->fetchAll(PDO::FETCH_ASSOC);
        }

        return $detalles;
    }

    public function actualizarEstado($id_venta, $nuevo_estado) {
        $query = "UPDATE ventas SET estado_despacho = ? WHERE id_venta = ?";
        return $this->db->prepare($query)->execute([$nuevo_estado, $id_venta]);
    }
    public function getVentasPorRango($fechaInicio, $fechaFin) {
    // Buscamos ventas entre las 00:00:00 del inicio y las 23:59:59 del fin
    $query = "SELECT v.id_venta, v.fecha_venta, v.total, v.estado_despacho, u.username 
              FROM ventas v 
              JOIN usuarios u ON v.id_usuario = u.id_usuario
              WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
              ORDER BY v.fecha_venta DESC";
    
    $stmt = $this->db->prepare($query);
    $stmt->execute([$fechaInicio, $fechaFin]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ventas as &$v) {
        $v['detalles'] = $this->getDetallesConToppings($v['id_venta']);
    }
    return $ventas;
}
}