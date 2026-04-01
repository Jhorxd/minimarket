<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Almacenes extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        // Solo admin puede gestionar almacenes
        if ($this->session->userdata('rol') != 'admin') {
            redirect('ventas/pos');
        }
        $this->load->model('Almacen_model');
    }

    // Vista principal de gestión de almacenes
    public function index() {
        $data['almacenes'] = $this->Almacen_model->get_almacenes_admin();

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('almacenes/index', $data);
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

        if ($this->Almacen_model->nombre_existe($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un almacén con ese nombre']);
            return;
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'activo'      => 1,
        ];

        $id = $this->Almacen_model->insertar($data);

        if ($id) {
            $alm = $this->Almacen_model->get_almacen($id);
            echo json_encode(['success' => true, 'message' => 'Almacén creado', 'almacen' => $alm]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
    }

    // POST: Actualizar almacén existente (responde JSON)
    public function actualizar($id) {
        $this->output->set_content_type('application/json');

        $nombre = trim($this->input->post('nombre'));

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        if ($this->Almacen_model->nombre_existe($nombre, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro almacén con ese nombre']);
            return;
        }

        $data = [
            'nombre'      => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'activo'      => (int)$this->input->post('activo'),
        ];

        if ($this->Almacen_model->actualizar($id, $data)) {
            $alm = $this->Almacen_model->get_almacen($id);
            echo json_encode(['success' => true, 'message' => 'Almacén actualizado', 'almacen' => $alm]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    // GET: Eliminar / desactivar almacén (responde JSON)
    public function eliminar($id) {
        $this->output->set_content_type('application/json');

        $alm = $this->Almacen_model->get_almacen($id);
        if (!$alm) {
            echo json_encode(['success' => false, 'message' => 'Almacén no encontrado']);
            return;
        }

        if ($this->Almacen_model->eliminar($id)) {
            echo json_encode(['success' => true, 'message' => 'Almacén eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    // GET: Retorta JSON de almacenes activos (para selección en productos)
    public function listar_json() {
        $this->output->set_content_type('application/json');
        $almacenes = $this->Almacen_model->get_almacenes();
        echo json_encode($almacenes);
    }
}
