<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Almacen_model extends CI_Model {

    // Listar todos los almacenes activos (global)
    public function get_almacenes() {
        return $this->db
            ->where('activo', 1)
            ->order_by('nombre', 'ASC')
            ->get('almacenes')
            ->result();
    }

    // Listar TODAS (incluyendo inactivas) para administración
    public function get_almacenes_admin() {
        return $this->db
            ->order_by('nombre', 'ASC')
            ->get('almacenes')
            ->result();
    }

    // Obtener un almacén por ID
    public function get_almacen($id) {
        return $this->db->get_where('almacenes', ['id' => $id])->row();
    }

    // Insertar nuevo almacén
    public function insertar($data) {
        if ($this->db->insert('almacenes', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    // Actualizar almacén
    public function actualizar($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('almacenes', $data);
    }

    // Eliminar permanentemente
    public function eliminar($id) {
        // Verificar si hay productos asignados a este almacén
        $count = $this->db->where('id_almacen', $id)->count_all_results('productos');
        if ($count > 0) {
            // Si tiene productos, solo desactivar
            return $this->actualizar($id, ['activo' => 0]);
        }
        return $this->db->delete('almacenes', ['id' => $id]);
    }

    // Verificar si el nombre ya existe (para validación única)
    public function nombre_existe($nombre, $excluir_id = null) {
        $this->db->where('LOWER(nombre)', strtolower(trim($nombre)));
        if ($excluir_id) {
            $this->db->where('id !=', $excluir_id);
        }
        return $this->db->count_all_results('almacenes') > 0;
    }
}
