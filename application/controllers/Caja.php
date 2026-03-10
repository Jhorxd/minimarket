<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Caja extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('id')) redirect('login');
        $this->load->model('Caja_model');
    }

    public function index() {
        $id_sucursal = $this->session->userdata('id_sucursal');
        
        $data['cajas'] = $this->Caja_model->get_historial_cajas($id_sucursal);
        $data['caja_activa'] = $this->Caja_model->get_caja_abierta($this->session->userdata('id'), $id_sucursal);
        
        // Traer usuarios de la sucursal para el modal
        $this->db->where('id_sucursal', $id_sucursal);
        $this->db->where('estado', 1);
        $data['usuarios_sucursal'] = $this->db->get('usuarios')->result();

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('caja/index', $data);
        $this->load->view('layouts/footer');
    }

    public function abrir() {
        // Vista del formulario de apertura
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('caja/apertura');
        $this->load->view('layouts/footer');
    }

    public function guardar_apertura() {
        $data = [
            'id_sucursal'    => $this->session->userdata('id_sucursal'),
            'id_usuario'     => $this->session->userdata('id'),
            'monto_apertura' => $this->input->post('monto_apertura'),
            'fecha_apertura' => date('Y-m-d H:i:s'),
            'estado'         => 'Abierta'
        ];

        $id = $this->Caja_model->insertar($data);
        $this->session->set_userdata('id_caja', $id);
        redirect('caja');
    }

    public function cerrar($id) {
        // Aquí luego sumaremos las ventas reales para comparar
        $monto_cierre = $this->input->post('monto_cierre') ?? 0; 
        
        $data = [
            'monto_cierre' => $monto_cierre,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'estado'       => 'Cerrada'
        ];

        $this->Caja_model->actualizar($id, $data);
        $this->session->unset_userdata('id_caja');
        redirect('caja');
    }
}