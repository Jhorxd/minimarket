<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gasto_model extends CI_Model {

    public function listar_por_sucursal($id_sucursal, $desde = null, $hasta = null)
    {
        $where = "WHERE g.id_sucursal = ?";
        $params = [(int) $id_sucursal];

        if ($desde && $hasta) {
            $where .= " AND g.fecha_gasto BETWEEN ? AND ?";
            $params[] = $desde;
            $params[] = $hasta;
        }

        $sql = "
            SELECT g.id, g.concepto, g.monto, g.fecha_gasto, g.observaciones, g.created_at,
                   cg.nombre AS categoria_nombre,
                   u.nombre AS usuario_nombre
            FROM gastos g
            INNER JOIN categorias_gasto cg ON cg.id = g.id_categoria_gasto
            INNER JOIN usuarios u ON u.id = g.id_usuario
            $where
            ORDER BY g.fecha_gasto DESC, g.id DESC
        ";
        return $this->db->query($sql, $params)->result();
    }

    public function categorias_activas()
    {
        return $this->db
            ->where('activo', 1)
            ->order_by('nombre', 'ASC')
            ->get('categorias_gasto')
            ->result();
    }

    public function insertar(array $data)
    {
        $this->db->insert('gastos', $data);
        return $this->db->insert_id();
    }

    /**
     * @param int $id
     * @param int $id_sucursal
     * @return object|null
     */
    public function get_por_sucursal($id, $id_sucursal)
    {
        return $this->db->get_where('gastos', [
            'id'          => (int) $id,
            'id_sucursal' => (int) $id_sucursal,
        ])->row();
    }

    public function eliminar($id, $id_sucursal)
    {
        $this->db->where('id', (int) $id);
        $this->db->where('id_sucursal', (int) $id_sucursal);
        $this->db->delete('gastos');
        return $this->db->affected_rows() > 0;
    }
}
