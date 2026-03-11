<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cliente_model extends CI_Model {

    private $table = 'clientes';

    public function get_all($solo_activos = true)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        if ($solo_activos) {
            $this->db->where('estado', 1);
        }
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get($this->table)->result();
    }


    public function get($id_cliente)
    {
        return $this->db->get_where($this->table, ['id_cliente' => $id_cliente])->row();
    }

    public function insert($data)
    {
        $data['fecha_registro'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id_cliente, $data)
    {
        $this->db->where('id_cliente', $id_cliente);
        return $this->db->update($this->table, $data);
    }

    public function delete_logico($id_cliente)
    {
        $this->db->where('id_cliente', $id_cliente);
        return $this->db->update($this->table, ['estado' => 0]);
    }
}
