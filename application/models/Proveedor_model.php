<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedor_model extends CI_Model {

    private $table = 'proveedores';

    public function get_all($solo_activos = true)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        if ($solo_activos) {
            $this->db->where('estado', 1);
        }
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->order_by('razon_social', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_proveedores_ajax($busqueda = '')
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $this->db->where('estado', 1);
        $this->db->where('id_sucursal', $id_sucursal);
        
        if (!empty($busqueda)) {
            $this->db->group_start();
            $this->db->like('razon_social', $busqueda);
            $this->db->or_like('nro_documento', $busqueda);
            $this->db->group_end();
        }
        
        $this->db->order_by('razon_social', 'ASC');
        $this->db->limit(30);
        return $this->db->get($this->table)->result();
    }


    public function get($id_proveedor)
    {
        return $this->db->get_where($this->table, ['id_proveedor' => $id_proveedor])->row();
    }

    public function insert($data)
    {
        $data['fecha_registro'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id_proveedor, $data)
    {
        $this->db->where('id_proveedor', $id_proveedor);
        return $this->db->update($this->table, $data);
    }

    public function delete_logico($id_proveedor)
    {
        $this->db->where('id_proveedor', $id_proveedor);
        return $this->db->update($this->table, ['estado' => 0]);
    }
    
    public function existe_documento($nro_documento, $id_proveedor_excluido = null) {
        if (empty(trim($nro_documento))) return false;
        
        $id_sucursal = $this->session->userdata('id_sucursal');
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('nro_documento', trim($nro_documento));
        if ($id_proveedor_excluido) {
            $this->db->where('id_proveedor !=', $id_proveedor_excluido);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }
}
