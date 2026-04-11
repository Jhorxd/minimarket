<?php
class Producto_model extends CI_Model {

    public function get_productos_by_sucursal($id_sucursal) {
        $sql = "
            SELECT p.*, 
                   p.stock as stock_total,
                   TRIM(CONCAT_WS(' ', p.talla, p.color, p.diseno)) as variantes_detalle
            FROM productos p
            WHERE p.id_sucursal = ? AND p.estado = 1
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
    $this->db->where('estado', 1);

    return $this->db->get()->row();
}

    public function eliminar($id, $id_sucursal) {
        $this->db->where('id', $id);
        $this->db->where('id_sucursal', $id_sucursal);
        return $this->db->update('productos', ['estado' => 0]);
    }

    public function actualizar($id, $id_sucursal, $data) {
    $this->db->where('id', $id);
    $this->db->where('id_sucursal', $id_sucursal);
    return $this->db->update('productos', $data);
    }

    public function get_productos_pos($busqueda = '') {

        $this->db->select('id, id_categoria, codigo_barras, nombre, precio_venta, precio_compra, stock, stock_minimo, imagen, version, talla, color, diseno');

        $this->db->from('productos');
        // Quitamos el filtro de es_variable ya que todos los productos son vendibles ahora
        $this->db->where('id_sucursal', $this->session->userdata('id_sucursal'));
        $this->db->where('estado', 1);


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

    /**
     * Obtener productos que compartan el mismo nombre base en una sucursal
     */
    public function get_hermanos($nombre_base, $id_sucursal) {
        $this->db->select('id, nombre, talla, color, diseno, precio_venta as precio, stock, codigo_barras as barcode');
        $this->db->from('productos');
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('estado', 1);
        $this->db->group_start();
        $this->db->where('nombre', $nombre_base);
        $this->db->or_like('nombre', $nombre_base . ' (', 'after');
        $this->db->group_end();
        $query = $this->db->get();
        return $query->result_array();
    }
    /**
     * Busca un producto por sus atributos específicos para evitar duplicados
     */
    public function get_producto_por_atributos($id_sucursal, $talla, $color, $diseno, $nombre_base) {
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('talla', $talla);
        $this->db->where('color', $color);
        $this->db->where('diseno', $diseno);
        $this->db->where('estado', 1);
        
        $this->db->group_start();
        $this->db->where('nombre', $nombre_base);
        $this->db->or_like('nombre', $nombre_base . ' (', 'after');
        $this->db->group_end();
        
        return $this->db->get('productos')->row();
    }

    /**
     * Actualiza atributos comunes de todos los productos que comparten un nombre base
     */
    public function actualizar_por_nombre_base($id_sucursal, $old_base_name, $data) {
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->group_start();
        $this->db->where('nombre', $old_base_name);
        $this->db->or_like('nombre', $old_base_name . ' (', 'after');
        $this->db->group_end();
        return $this->db->update('productos', $data);
    }
}