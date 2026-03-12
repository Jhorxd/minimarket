<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Almacen extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // aquí podrías validar sesión / rol
        $this->load->model('Stock_model', 'stock_m');
    }

    // Listado principal de stock
    public function stock_index()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        $data['titulo']    = 'Stock de Almacén';
        $data['productos'] = $this->stock_m->get_stock_sucursal($id_sucursal);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('almacen/stock_index', $data);
        $this->load->view('layouts/footer');
    }

    // Form de ajuste para un producto
    public function ajustar($id_producto)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        $producto = $this->stock_m->get_producto($id_producto, $id_sucursal);
        if (!$producto) {
            show_404();
        }

        $data['titulo']   = 'Ajustar Stock';
        $data['producto'] = $producto;
        $data['kardex']   = $this->stock_m->get_kardex_producto($id_producto, $id_sucursal);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('almacen/ajustar_stock', $data);
        $this->load->view('layouts/footer');
    }

    // Guardar ajuste
    public function guardar_ajuste()
    {
        $id_sucursal  = $this->session->userdata('id_sucursal');
        $id_producto  = (int) $this->input->post('id_producto');
        $tipo         = $this->input->post('tipo_movimiento'); // Entrada / Salida
        $cantidad     = (float) $this->input->post('cantidad');
        $motivo       = $this->input->post(  'motivo') ?: 'Ajuste';

        if ($cantidad <= 0 || !in_array($tipo, ['Entrada', 'Salida'])) {
            $this->session->set_flashdata('msg', 'Datos de ajuste inválidos');
            redirect('almacen/ajustar/'.$id_producto);
        }

        $ok = $this->stock_m->ajustar_stock($id_producto, $id_sucursal, $tipo, $cantidad, $motivo);

        $this->session->set_flashdata(
            'msg',
            $ok ? 'Stock ajustado correctamente' : 'Error al ajustar el stock'
        );

        redirect('almacen/stock_index');
    }
}
