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
    // 1. Recibir datos básicos
    $id_sucursal = $this->session->userdata('id_sucursal'); // Lo guardamos en una variable para reusarlo
    
    $data = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $this->input->post('nombre'),
        'descripcion'   => $this->input->post('descripcion'),
        'categoria'     => $this->input->post('categoria'),
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'stock'         => $this->input->post('stock'),
        'stock_minimo'  => $this->input->post('stock_minimo'),
        'id_sucursal'   => $id_sucursal
    ];

    // 2. Insertar el producto y obtener el ID generado
    $id_producto = $this->Producto_model->insertar($data);

    if ($id_producto) {
        // 3. Verificar si se subió una imagen
        if (!empty($_FILES['imagen']['name'])) {
            
            // Creamos la carpeta si no existe
            $path = './uploads/productos/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            // Configuración de subida
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $id_producto . "." . $extension; 

            $config['upload_path']   = $path;
            $config['file_name']     = $nombre_archivo;
            $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
            $config['overwrite']     = TRUE;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('imagen')) {
                // 4. ACTUALIZACIÓN CORREGIDA: Pasamos los 3 argumentos que pide tu modelo
                // Argumento 1: ID del producto
                // Argumento 2: ID de la sucursal
                // Argumento 3: Array con los datos (el nombre de la imagen)
                $this->Producto_model->actualizar($id_producto, $id_sucursal, ['imagen' => $nombre_archivo]);
            } else {
                $error_upload = $this->upload->display_errors();
                $this->session->set_flashdata('warning', 'Producto guardado, pero la imagen falló: ' . $error_upload);
            }
        }

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
    
    // 1. Datos básicos del formulario
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

    // 2. Lógica para la imagen (solo si se selecciona una nueva)
    if (!empty($_FILES['imagen']['name'])) {
        
        $path = './uploads/productos/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $id . "." . $extension; // Mantenemos el mismo ID como nombre

        $config['upload_path']   = $path;
        $config['file_name']     = $nombre_archivo;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['overwrite']     = TRUE; // Sobrescribe la imagen anterior

        $this->load->library('upload', $config);

            if ($this->upload->do_upload('imagen')) {
                $data['imagen'] = $nombre_archivo;
                // Forzamos un cambio de versión para romper el caché solo de este producto
                $data['version'] = time(); 
            } else {
            // Opcional: Manejar error de subida
            $error = $this->upload->display_errors();
            $this->session->set_flashdata('warning', 'Producto actualizado, pero la imagen no: ' . $error);
        }
    }

    // 3. Ejecutar la actualización con los 3 parámetros que pide tu modelo
    if ($this->Producto_model->actualizar($id, $id_sucursal, $data)) {
        $this->session->set_flashdata('success', 'Producto actualizado correctamente');
    } else {
        $this->session->set_flashdata('error', 'Error al actualizar el producto');
    }
    
    redirect('productos');
}
}