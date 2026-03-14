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
    $id_sucursal = $this->session->userdata('id_sucursal');
    
    $data = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $this->input->post('nombre'),
        'descripcion'   => $this->input->post('descripcion'),
        'categoria'     => $this->input->post('categoria'),
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'stock'         => $this->input->post('stock'),
        'stock_minimo'  => $this->input->post('stock_minimo'),
        'id_sucursal'   => $id_sucursal,
        'version'       => time() 
    ];

    // 1. Insertar primero para obtener el ID
    $id_producto = $this->Producto_model->insertar($data);

    if ($id_producto) {
        if (!empty($_FILES['imagen']['name'])) {
            
            $path = './uploads/productos/';
            if (!is_dir($path)) { mkdir($path, 0777, true); }

            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $id_producto . "." . $extension; 

            $config['upload_path']   = $path;
            $config['file_name']     = $nombre_archivo;
            $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
            $config['overwrite']     = TRUE;
            $config['max_size']      = '10240'; 

            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('imagen')) {
                $uploadData = $this->upload->data();
                $full_path = $uploadData['full_path'];

                $this->load->library('image_lib');

                // --- 2. CORREGIR ROTACIÓN SEGÚN EXIF ---
                $exif = @exif_read_data($full_path);
                if($exif && isset($exif['Orientation'])) {
                    $ort = $exif['Orientation'];
                    $degrees = 0;

                    if($ort == 6) $degrees = 270; // 90 grados a la derecha
                    if($ort == 8) $degrees = 90;  // 90 grados a la izquierda
                    if($ort == 3) $degrees = 180; // Invertido

                    if($degrees != 0) {
                        $config_r['image_library'] = 'gd2';
                        $config_r['source_image']  = $full_path;
                        $config_r['rotation_angle'] = $degrees;
                        $this->image_lib->initialize($config_r);
                        $this->image_lib->rotate();
                        $this->image_lib->clear();
                    }
                }

                // --- 3. COMPRESIÓN Y REDIMENSIÓN ---
                $config_img['image_library']  = 'gd2';
                $config_img['source_image']   = $full_path;
                $config_img['maintain_ratio'] = TRUE;
                $config_img['width']          = 800;
                $config_img['height']         = 800;
                $config_img['quality']        = '60%'; 

                $this->image_lib->initialize($config_img);
                $this->image_lib->resize();
                $this->image_lib->clear();

                // Actualizamos el registro con el nombre final de la imagen y versión real
                $this->Producto_model->actualizar($id_producto, $id_sucursal, [
                    'imagen' => $uploadData['file_name'],
                    'version' => time()
                ]);
            } else {
                $error_upload = $this->upload->display_errors('', '');
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

    if (!empty($_FILES['imagen']['name'])) {
        $path = './uploads/productos/';
        if (!is_dir($path)) { mkdir($path, 0777, true); }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $id . "." . $extension; 

        $config['upload_path']   = $path;
        $config['file_name']     = $nombre_archivo;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['overwrite']     = TRUE;
        $config['max_size']      = '10240'; 

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('imagen')) {
            $uploadData = $this->upload->data();
            $full_path = $uploadData['full_path'];

            // --- CORRECCIÓN DE ROTACIÓN MANUAL ---
            $this->load->library('image_lib');
            
            // Leemos los datos EXIF para ver si la foto está rotada
            $exif = @exif_read_data($full_path);
            if($exif && isset($exif['Orientation'])) {
                $ort = $exif['Orientation'];
                $degrees = 0;

                if($ort == 6) $degrees = 270; // Rotar 90 a la derecha
                if($ort == 8) $degrees = 90;  // Rotar 90 a la izquierda
                if($ort == 3) $degrees = 180; // Rotar 180

                if($degrees != 0) {
                    $config_r['image_library'] = 'gd2';
                    $config_r['source_image']  = $full_path;
                    $config_r['rotation_angle'] = $degrees;
                    $this->image_lib->initialize($config_r);
                    $this->image_lib->rotate();
                    $this->image_lib->clear();
                }
            }

            // --- COMPRESIÓN Y REDIMENSIÓN ---
            $config_img['image_library']  = 'gd2';
            $config_img['source_image']   = $full_path;
            $config_img['maintain_ratio'] = TRUE;
            $config_img['width']          = 800;
            $config_img['height']         = 800;
            $config_img['quality']        = '60%'; 

            $this->image_lib->initialize($config_img);
            $this->image_lib->resize();
            $this->image_lib->clear();

            $data['imagen'] = $uploadData['file_name'];
            $data['version'] = time(); 
        } else {
            $this->session->set_flashdata('error', 'Error al subir imagen: ' . $this->upload->display_errors('', ''));
        }
    }

    if ($this->Producto_model->actualizar($id, $id_sucursal, $data)) {
        $this->session->set_flashdata('success', 'Producto actualizado correctamente');
    } else {
        $this->session->set_flashdata('error', 'Error al guardar en la base de datos');
    }

    redirect('productos');
}
}