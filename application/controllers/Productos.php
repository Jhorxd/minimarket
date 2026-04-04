<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Verificar si el usuario está logueado
        if (!$this->session->userdata('id')) {
            redirect('login');
        }
        $this->load->model('Producto_model');
        $this->load->model('Categoria_model');
        $this->load->model('Almacen_model');
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
        $data['categorias'] = $this->Categoria_model->get_categorias();
        $data['almacenes']  = $this->Almacen_model->get_almacenes();
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('productos/nuevo', $data);
        $this->load->view('layouts/footer');
    }

    // Guardar el producto en la base de datos
    public function guardar() {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $json_variantes = $this->input->post('json_variantes');
        $variantes = $json_variantes ? json_decode($json_variantes, true) : [];
        $es_variable = !empty($variantes);

        $id_categoria = $this->input->post('id_categoria');
        $categoria_nombre = $this->input->post('categoria');

        // Si tenemos ID pero no el nombre (texto cacheado en la tabla), lo buscamos
        if ($id_categoria && empty($categoria_nombre)) {
            $cat = $this->Categoria_model->get_categoria($id_categoria);
            if ($cat) $categoria_nombre = $cat->nombre;
        }

        $data_comun = [
            'descripcion'   => $this->input->post('descripcion'),
            'categoria'     => $categoria_nombre,
            'id_categoria'  => $id_categoria ?: null,
            'id_almacen'    => $this->input->post('id_almacen') ?: null,
            'precio_compra' => $this->input->post('precio_compra') ?: 0,
            'stock_minimo'  => $this->input->post('stock_minimo') ?: 0,
            'id_sucursal'   => $id_sucursal,
            'version'       => time() 
        ];

        $ids_creados = [];
        $this->load->model('Stock_model');

        if ($es_variable && !empty($variantes)) {
            // Caso: Registro con Variantes (Creamos N productos independientes)
            foreach ($variantes as $v) {
                $nombre_full = $this->input->post('nombre') . ' (' . trim($v['talla'] . ' ' . $v['color'] . ' ' . $v['diseno']) . ')';
                
                $data_v = array_merge($data_comun, [
                    'codigo_barras' => $v['barcode'] ?: ($this->input->post('codigo_barras').'-'.uniqid()),
                    'nombre'        => $nombre_full,
                    'talla'         => $v['talla'],
                    'color'         => $v['color'],
                    'diseno'        => $v['diseno'],
                    'precio_venta'  => $v['precio'] ?: $this->input->post('precio_venta'),
                    'stock'         => 0
                ]);
                $id_v = $this->Producto_model->insertar($data_v);
                if ($id_v) {
                    $ids_creados[] = $id_v;
                    if (isset($v['stock']) && $v['stock'] > 0) {
                        $this->Stock_model->ajustar_stock($id_v, $id_sucursal, 'Entrada', $v['stock'], $v['motivo'] ?: 'Ajuste Inicial');
                    }
                }
            }
        } else {
            // Caso: Producto Estándar (1 solo producto)
            $data_std = array_merge($data_comun, [
                'codigo_barras' => $this->input->post('codigo_barras'),
                'nombre'        => $this->input->post('nombre'),
                'precio_venta'  => $this->input->post('precio_venta') ?: 0,
                'stock'         => 0
            ]);
            $id_p = $this->Producto_model->insertar($data_std);
            if ($id_p) {
                $ids_creados[] = $id_p;
                $cantidad_inicial = $this->input->post('stock_ajuste');
                if ($cantidad_inicial > 0) {
                    $this->Stock_model->ajustar_stock($id_p, $id_sucursal, 'Entrada', $cantidad_inicial, 'Ajuste Inicial');
                }
            }
        }

        // Manejo de Imagen para todos los productos creados en este lote
        if (!empty($_FILES['imagen']['name']) && !empty($ids_creados)) {
            $path = './uploads/productos/';
            if (!is_dir($path)) { mkdir($path, 0777, true); }
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            
            // Subimos la imagen para el primero y luego la copiamos para el resto
            $primer_id = $ids_creados[0];
            $nombre_archivo_base = $primer_id . "." . $extension;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $path . $nombre_archivo_base)) {
                $this->Producto_model->actualizar($primer_id, $id_sucursal, ['imagen' => $nombre_archivo_base]);
                
                for ($i = 1; $i < count($ids_creados); $i++) {
                    $nuevo_id = $ids_creados[$i];
                    $nombre_archivo_nuevo = $nuevo_id . "." . $extension;
                    copy($path . $nombre_archivo_base, $path . $nombre_archivo_nuevo);
                    $this->Producto_model->actualizar($nuevo_id, $id_sucursal, ['imagen' => $nombre_archivo_nuevo]);
                }
            }
        }

        if (!empty($ids_creados)) {
            $this->session->set_flashdata('success', 'Producto(s) registrado(s) correctamente');
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
            show_404();
        }

        // Cargar variantes si es un producto maestro
        $data['variantes'] = [];
        $data['categorias'] = $this->Categoria_model->get_categorias();
        $data['almacenes']  = $this->Almacen_model->get_almacenes();

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
    $this->load->model('Stock_model');
    
    $id_categoria = $this->input->post('id_categoria');
    $categoria_nombre = $this->input->post('categoria');
    
    if ($id_categoria && empty($categoria_nombre)) {
        $cat = $this->Categoria_model->get_categoria($id_categoria);
        if ($cat) $categoria_nombre = $cat->nombre;
    }

    $data = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $this->input->post('nombre'),
        'descripcion'   => $this->input->post('descripcion'),
        'categoria'     => $categoria_nombre,
        'id_categoria'  => $id_categoria ?: null,
        'id_almacen'    => $this->input->post('id_almacen') ?: null,
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'talla'         => $this->input->post('talla_producto'), // Usaremos estos nombres para no chocar con el generador si es necesario
        'color'         => $this->input->post('color_producto'),
        'diseno'        => $this->input->post('diseno_producto'),
        // El stock no se actualiza directamente aquí para mantener integridad del Kardex
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
            $data['imagen'] = $uploadData['file_name'];
            $data['version'] = time(); 
        }
    }

    if ($this->Producto_model->actualizar($id, $id_sucursal, $data)) {
        
        // --- 1. PROCESAR AJUSTE DE STOCK (KARDEX) ---
        $stock_ajuste = $this->input->post('stock_ajuste');
        $stock_motivo = $this->input->post('stock_motivo');

        if ($stock_ajuste !== null) {
            $p_actual = $this->Producto_model->get_producto($id, $id_sucursal);
            $dif = $stock_ajuste - $p_actual->stock;

            if ($dif != 0) {
                $tipo = ($dif > 0) ? 'Entrada' : 'Salida';
                $this->Stock_model->ajustar_stock($id, $id_sucursal, $tipo, abs($dif), $stock_motivo ?: 'Ajuste Manual en Edición');
            }
        }

        // --- 2. PROCESAR VARIANTES EN JSON ---
        $json_variantes = $this->input->post('json_variantes');
        if ($json_variantes) {
            $variantes = json_decode($json_variantes, true);
            foreach ($variantes as $v) {
                if (!empty($v['id'])) {
                    // ACTUALIZAR VARIANTE EXISTENTE
                    $v_data = [
                        'precio_venta' => $v['precio'],
                        'stock_minimo' => $this->input->post('stock_minimo')
                    ];
                    $this->Producto_model->actualizar($v['id'], $id_sucursal, $v_data);

                    // Procesar ajuste de stock para la variante si cambió
                    $p_v_actual = $this->Producto_model->get_producto($v['id'], $id_sucursal);
                    $dif_v = $v['stock'] - $p_v_actual->stock;
                    
                    if ($dif_v != 0) {
                        $tipo_v = ($dif_v > 0) ? 'Entrada' : 'Salida';
                        $this->Stock_model->ajustar_stock($v['id'], $id_sucursal, $tipo_v, abs($dif_v), $v['motivo'] ?: 'Ajuste de Variante');
                    }
                } else {
                    // INSERTAR NUEVO PRODUCTO GENERADO DURANTE LA EDICIÓN
                    $data_v = [
                        'id_sucursal'   => $id_sucursal,
                        'nombre'        => $data['nombre'] . ' (' . trim(($v['talla'] ?? '') . ' ' . ($v['color'] ?? '') . ' ' . ($v['diseno'] ?? '')) . ')',
                        'talla'         => $v['talla'] ?? '',
                        'color'         => $v['color'] ?? '',
                        'diseno'        => $v['diseno'] ?? '',
                        'codigo_barras' => $v['barcode'] ?: ($data['codigo_barras'] . '-' . uniqid()),
                        'precio_venta'  => $v['precio'] ?: $data['precio_venta'],
                        'precio_compra' => $data['precio_compra'],
                        'stock'         => 0,
                        'id_categoria'  => $data['id_categoria'],
                        'id_almacen'    => $data['id_almacen'],
                    ];
                    $new_id_v = $this->Producto_model->insertar($data_v);
                    
                    if ($v['stock'] > 0) {
                        $this->Stock_model->ajustar_stock($new_id_v, $id_sucursal, 'Entrada', $v['stock'], $v['motivo'] ?: 'Ajuste Inicial');
                    }
                }
            }
        }

        $this->session->set_flashdata('success', 'Producto y stock sincronizados correctamente');
    } else {
        $this->session->set_flashdata('error', 'Error al guardar cambios');
    }

    redirect('productos');
}
}