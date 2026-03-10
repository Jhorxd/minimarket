<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Verificar si el usuario está logueado
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        $this->load->model('Producto_model'); // Asegúrate de crear este modelo
    }

    // Listado de productos de LA SUCURSAL actual
    public function index() {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $data['productos'] = $this->Producto_model->get_productos_by_sucursal($id_sucursal);
        
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/index', $data);
        $this->load->view('layouts/footer');
    }

    // Vista del formulario de nuevo producto
    public function nuevo() {
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/nuevo');
        $this->load->view('layouts/footer');
    }

    // Guardar el producto en la base de datos
    public function guardar() {
        // Recibir datos del formulario
        $data = [
            'codigo_barras' => $this->input->post('codigo_barras'),
            'nombre'        => $this->input->post('nombre'),
            'descripcion'   => $this->input->post('descripcion'),
            'categoria'     => $this->input->post('categoria'),
            'precio_compra' => $this->input->post('precio_compra'),
            'precio_venta'  => $this->input->post('precio_venta'),
            'stock'         => $this->input->post('stock'),
            'stock_minimo'  => $this->input->post('stock_minimo'),
            'id_sucursal'   => $this->session->userdata('id_sucursal') // <--- LA CLAVE
        ];

        if ($this->Producto_model->insertar($data)) {
            $this->session->set_flashdata('success', 'Producto registrado correctamente');
        } else {
            $this->session->set_flashdata('error', 'Error al registrar el producto');
        }

        redirect('productos');
    }

    // Editar producto (solo si pertenece a su sucursal)
    public function editar($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $data['p'] = $this->Producto_model->get_producto($id, $id_sucursal);

        if (!$data['p']) {
            show_404(); // No permitir editar productos de otras sucursales
        }

        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/editar', $data);
        $this->load->view('layouts/footer');
    }

    public function eliminar($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $this->Producto_model->eliminar($id, $id_sucursal);
        redirect('productos');
    }
    
    public function actualizar($id) {
        $id_sucursal = $this->session->userdata('id_sucursal');
        
        $data = [
            'codigo_barras' => $this->input->post('codigo_barras'),
            'nombre'        => $this->input->post('nombre'),
            'descripcion'   => $this->input->post('descripcion'),
            'categoria'     => $this->input->post('categoria'),
            'precio_compra' => $this->input->post('precio_compra'),
            'precio_venta'  => $this->input->post('precio_venta'),
            'stock'         => $this->input->post('stock'),
            'stock_minimo'  => $this->input->post('stock_minimo')
        ];

        // El modelo debe asegurar que el producto pertenezca a la sucursal
        $this->Producto_model->actualizar($id, $id_sucursal, $data);
        
        $this->session->set_flashdata('success', 'Producto actualizado');
        redirect('productos');
    }
}