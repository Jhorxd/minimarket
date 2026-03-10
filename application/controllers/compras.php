<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compras extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Compra_model', 'compra_m');
    }

    // Listado de compras
    public function compras_index()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        $data['titulo']  = 'Compras';
        $data['compras'] = $this->compra_m->listar_compras($id_sucursal);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('compras/compras_index', $data);
        $this->load->view('layouts/footer');
    }

    // Form nueva compra
    public function nueva()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        $data['titulo']      = 'Nueva Compra';
        $data['proveedores'] = $this->compra_m->get_proveedores_activos();
        $data['productos']   = $this->compra_m->get_productos_sucursal($id_sucursal);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('compras/form_compras', $data);
        $this->load->view('layouts/footer');
    }

    // Guardar compra
    public function guardar()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $id_usuario  = $this->session->userdata('id');

        $id_proveedor    = (int)$this->input->post('id_proveedor');   // FK a proveedores.id_proveedor
        $proveedor_texto = $this->input->post('proveedor_texto');    // nombre impreso

        $ids_producto = $this->input->post('id_producto');   // array
        $cantidades   = $this->input->post('cantidad');      // array
        $precios      = $this->input->post('precio_compra'); // array

        $items = [];
        if (is_array($ids_producto)) {
            for ($i = 0; $i < count($ids_producto); $i++) {
                if (empty($ids_producto[$i])) continue;

                $items[] = [
                    'id_producto'   => (int)$ids_producto[$i],
                    'cantidad'      => (float)$cantidades[$i],
                    'precio_compra' => (float)$precios[$i],
                ];
            }
        }

        if (empty($items)) {
            $this->session->set_flashdata('msg', 'Debe agregar al menos un producto.');
            redirect('compras/nueva');
        }

        $id_compra = $this->compra_m->registrar_compra(
            $id_sucursal,
            $id_usuario,
            $id_proveedor ?: null,
            $proveedor_texto,
            $items
        );

        if ($id_compra) {
            $this->session->set_flashdata('msg', 'Compra registrada correctamente.');
        } else {
            $this->session->set_flashdata('msg', 'Error al registrar la compra.');
        }

        redirect('compras/compras_index');
    }

  public function ver_compras($id_compra)
{
    list($compra, $detalles) = $this->compra_m->get_compra_con_detalle($id_compra);
    if (!$compra) {
        show_404();
    }

    $data['titulo']   = 'Detalle de Compra';
    $data['compra']   = $compra;
    $data['detalles'] = $detalles;

    $this->load->view('layouts/header', $data);
    $this->load->view('layouts/sidebar');
    $this->load->view('compras/ver_compras', $data);
    $this->load->view('layouts/footer');
}

}
