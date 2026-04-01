<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categorias extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        // Solo admin puede gestionar categorías
        if ($this->session->userdata('rol') != 'admin') {
            redirect('ventas/pos');
        }
        $this->load->model('Categoria_model');
    }

    // Vista principal de gestión de categorías
    public function index() {
        $data['categorias'] = $this->Categoria_model->get_categorias_admin();

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('categorias/index', $data);
        $this->load->view('layouts/footer');
    }

    // POST: Guardar nueva categoría (responde JSON)
    public function guardar() {
        $this->output->set_content_type('application/json');

        $nombre = trim($this->input->post('nombre'));

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        if ($this->Categoria_model->nombre_existe($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
            return;
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'color'       => $this->input->post('color') ?: '#3b82f6',
            'icono'       => $this->input->post('icono') ?: 'fa-tag',
            'activo'      => 1,
        ];

        $id = $this->Categoria_model->insertar($data);

        if ($id) {
            $cat = $this->Categoria_model->get_categoria($id);
            echo json_encode(['success' => true, 'message' => 'Categoría creada', 'categoria' => $cat]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
    }

    // POST: Actualizar categoría existente (responde JSON)
    public function actualizar($id) {
        $this->output->set_content_type('application/json');

        $nombre = trim($this->input->post('nombre'));

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        if ($this->Categoria_model->nombre_existe($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otra categoría con ese nombre']);
            return;
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'color'       => $this->input->post('color') ?: '#3b82f6',
            'icono'       => $this->input->post('icono') ?: 'fa-tag',
            'activo'      => (int)$this->input->post('activo'),
        ];

        if ($this->Categoria_model->actualizar($id, $data)) {
            $cat = $this->Categoria_model->get_categoria($id);
            echo json_encode(['success' => true, 'message' => 'Categoría actualizada', 'categoria' => $cat]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // GET: Eliminar / desactivar categoría (responde JSON)
    public function eliminar($id) {
        $this->output->set_content_type('application/json');

        $cat = $this->Categoria_model->get_categoria($id);
        if (!$cat) {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            return;
        }

        $count = $this->Categoria_model->contar_productos($id);

        if ($this->Categoria_model->eliminar($id)) {
            $msg = $count > 0
                ? "Categoría desactivada (tiene {$count} productos asignados)"
                : 'Categoría eliminada correctamente';
            echo json_encode(['success' => true, 'message' => $msg, 'desactivada' => ($count > 0)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    // GET: Retorna JSON de categorías activas (para modal selector en productos)
    public function listar_json() {
        $this->output->set_content_type('application/json');
        $categorias = $this->Categoria_model->get_categorias();
        echo json_encode($categorias);
    }
}
