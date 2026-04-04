<?php
class Producto_model extends CI_Model {

    public function get_productos_by_sucursal($id_sucursal) {
        $sql = "
            SELECT p.*, 
                   p.stock as stock_total,
                   TRIM(CONCAT_WS(' ', p.talla, p.color, p.diseno)) as variantes_detalle
            FROM productos p
            WHERE p.id_sucursal = ?
            ORDER BY p.nombre ASC
        ";
        return $this->db->query($sql, [$id_sucursal])->result();
    }



    public function insertar($data) {
        if ($this->db->insert('productos', $data)) {
            // Devuelve el último ID insertado en la base de datos
            return $this->db->insert_id();
        }
        return false;
    }

public function get_producto($id, $id_sucursal) {

    $this->db->select('
        id,
        nombre,
        descripcion,
        precio_venta,
        precio_compra,
        stock,
        stock_minimo,
        codigo_barras,
        imagen,
        version,
        id_categoria,
        categoria,
        id_almacen,
        talla,
        color,
        diseno
    ');

    $this->db->from('productos');
    $this->db->where('id', $id);
    $this->db->where('id_sucursal', $id_sucursal);

    return $this->db->get()->row();
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

        $this->db->select('id, id_categoria, codigo_barras, nombre, precio_venta, stock, stock_minimo, imagen, version, talla, color, diseno');

        $this->db->from('productos');
        // Quitamos el filtro de es_variable ya que todos los productos son vendibles ahora
        $this->db->where('id_sucursal', $this->session->userdata('id_sucursal'));


        if (!empty($busqueda)) {

            $this->db->group_start();

            $this->db->like('nombre', $busqueda);

            $this->db->or_like('codigo_barras', $busqueda);

            $this->db->group_end();

        }



        $this->db->limit(60);
        $query = $this->db->get();

        return $query->result_array();

    }

}