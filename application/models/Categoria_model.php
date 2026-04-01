<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categoria_model extends CI_Model {

    // Listar todas las categorías activas (global, sin filtro de sucursal)
    public function get_categorias() {
        return $this->db
            ->where('activo', 1)
            ->order_by('nombre', 'ASC')
            ->get('categorias')
            ->result();
    }

    // Listar TODAS (incluyendo inactivas) para administración
    public function get_categorias_admin() {
        return $this->db
            ->order_by('nombre', 'ASC')
            ->get('categorias')
            ->result();
    }

    // Obtener una categoría por ID
    public function get_categoria($id) {
        return $this->db->get_where('categorias', ['id' => $id])->row();
    }

    // Insertar nueva categoría
    public function insertar($data) {
        if ($this->db->insert('categorias', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    // Actualizar categoría
    public function actualizar($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('categorias', $data);
    }

    // Eliminar permanentemente
    public function eliminar($id) {
        // Verificar si hay productos asignados a esta categoría
        $count = $this->db->where('id_categoria', $id)->count_all_results('productos');
        if ($count > 0) {
            // Si tiene productos, solo desactivar
            return $this->actualizar($id, ['activo' => 0]);
        }
        return $this->db->delete('categorias', ['id' => $id]);
    }

    // Verificar si el nombre ya existe (para validación única)
    public function nombre_existe($nombre, $excluir_id = null) {
        $this->db->where('LOWER(nombre)', strtolower(trim($nombre)));
        if ($excluir_id) {
            $this->db->where('id !=', $excluir_id);
        }
        return $this->db->count_all_results('categorias') > 0;
    }

    // Contar productos por categoría
    public function contar_productos($id_categoria) {
        return $this->db->where('id_categoria', $id_categoria)->count_all_results('productos');
    }
}
