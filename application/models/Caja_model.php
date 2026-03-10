<?php
class Caja_model extends CI_Model {

    public function get_historial_cajas($id_sucursal) {
        $this->db->select('c.*, u.nombre as cajero');
        $this->db->from('cajas c');
        $this->db->join('usuarios u', 'c.id_usuario = u.id');
        $this->db->where('c.id_sucursal', $id_sucursal);
        $this->db->order_by('c.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_caja_abierta($id_usuario, $id_sucursal) {
        return $this->db->get_where('cajas', [
            'id_usuario'  => $id_usuario,
            'id_sucursal' => $id_sucursal,
            'estado'      => 'Abierta'
        ])->row();
    }

    public function insertar($data) {
        $this->db->insert('cajas', $data);
        return $this->db->insert_id();
    }

    public function actualizar($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('cajas', $data);
    }
}