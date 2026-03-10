<?php
class Producto_model extends CI_Model {

    public function get_productos_by_sucursal($id_sucursal) {
        return $this->db->get_where('productos', ['id_sucursal' => $id_sucursal])->result();
    }

    public function insertar($data) {
        return $this->db->insert('productos', $data);
    }

    public function get_producto($id, $id_sucursal) {
        return $this->db->get_where('productos', [
            'id' => $id, 
            'id_sucursal' => $id_sucursal
        ])->row();
    }

    public function eliminar($id, $id_sucursal) {
        return $this->db->delete('productos', [
            'id' => $id, 
            'id_sucursal' => $id_sucursal
        ]);
    }

    public function actualizar($id, $id_sucursal, $data) {
    $this->db->where('id', $id);
    $this->db->where('id_sucursal', $id_sucursal);
    return $this->db->update('productos', $data);
    }

    public function get_productos_pos($busqueda = '') {

        $this->db->select('id, codigo_barras, nombre, precio_venta, stock, imagen');

        $this->db->from('productos');


        if (!empty($busqueda)) {

            $this->db->group_start();

            $this->db->like('nombre', $busqueda);

            $this->db->or_like('codigo_barras', $busqueda);

            $this->db->group_end();

        }



        $query = $this->db->get();

        return $query->result_array();

    }
}