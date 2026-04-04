<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Gastos operativos (alquiler, servicios, etc.) — distintos de compras de mercadería.
 */
class Gastos extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        if ($this->session->userdata('rol') !== 'admin') {
            redirect('ventas/pos');
        }
        $this->load->model('Gasto_model', 'gasto_m');
    }

    public function index()
    {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $fecha_desde = $this->input->get('fecha_desde') ?: date('Y-m-01');
        $fecha_hasta = $this->input->get('fecha_hasta') ?: date('Y-m-d');

        $data['titulo']      = 'Gastos operativos';
        $data['gastos']      = $this->gasto_m->listar_por_sucursal($id_sucursal, $fecha_desde, $fecha_hasta);
        $data['categorias']  = $this->gasto_m->categorias_activas();
        $data['fecha_desde'] = $fecha_desde;
        $data['fecha_hasta'] = $fecha_hasta;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('gastos/index', $data);
        $this->load->view('layouts/footer');
    }

    public function guardar()
    {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        $id_usuario  = (int) $this->session->userdata('id');

        $id_cat   = (int) $this->input->post('id_categoria_gasto');
        $concepto = trim((string) $this->input->post('concepto'));
        $monto    = (float) $this->input->post('monto');
        $fecha    = trim((string) $this->input->post('fecha_gasto'));
        $obs      = trim((string) $this->input->post('observaciones'));

        if ($id_cat <= 0 || $concepto === '' || $monto <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $this->session->set_flashdata('error', 'Complete categoría, concepto, monto válido y fecha.');
            redirect('gastos');
        }

        $cat_ok = $this->db->get_where('categorias_gasto', ['id' => $id_cat, 'activo' => 1])->row();
        if (!$cat_ok) {
            $this->session->set_flashdata('error', 'Categoría no válida.');
            redirect('gastos');
        }

        $this->gasto_m->insertar([
            'id_sucursal'        => $id_sucursal,
            'id_usuario'         => $id_usuario,
            'id_categoria_gasto' => $id_cat,
            'concepto'           => $concepto,
            'monto'              => $monto,
            'fecha_gasto'        => $fecha,
            'observaciones'      => $obs !== '' ? $obs : null,
        ]);

        $this->session->set_flashdata('ok', 'Gasto registrado correctamente.');
        redirect('gastos');
    }

    public function eliminar($id)
    {
        $id_sucursal = (int) $this->session->userdata('id_sucursal');
        if ($this->gasto_m->eliminar((int) $id, $id_sucursal)) {
            $this->session->set_flashdata('ok', 'Gasto eliminado.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo eliminar el gasto.');
        }
        redirect('gastos');
    }
}
