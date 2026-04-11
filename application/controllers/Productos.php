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
        $id_sucursal = $this->session->userdata('id_sucursal');
        $data['categorias'] = $this->Categoria_model->get_categorias();
        $data['almacenes']  = $this->Almacen_model->get_almacenes();
        $data['next_barcode'] = $this->Producto_model->get_next_barcode_numeric($id_sucursal);
        
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
                $attrs = trim(($v['talla'] ?? '') . ' ' . ($v['color'] ?? '') . ' ' . ($v['diseno'] ?? ''));
                $nombre_base = $this->input->post('nombre');
                $nombre_full = !empty($attrs) ? "$nombre_base ($attrs)" : $nombre_base;
                
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

        // Obtener nombre base y buscar hermanos (variantes existentes)
        $nombre_base = preg_replace('/\s*\(.*?\)\s*$/', '', $data['p']->nombre);
        $data['hermanos'] = $this->Producto_model->get_hermanos($nombre_base, $id_sucursal);

        // Cargar auxiliares
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
    
    // 0. Obtener estado previo del producto para sincronización
    $p_inicial = $this->Producto_model->get_producto($id, $id_sucursal);
    if (!$p_inicial) {
        $this->session->set_flashdata('error', 'Producto no encontrado');
        redirect('productos');
    }
    // Extraer nombre base original (quitando lo que esté entre paréntesis)
    $old_base_name = preg_replace('/\s*\(.*?\)\s*$/', '', $p_inicial->nombre);

    // 1. Procesar datos del formulario
    $id_categoria = $this->input->post('id_categoria');
    $categoria_nombre = $this->input->post('categoria');
    if ($id_categoria && empty($categoria_nombre)) {
        $cat = $this->Categoria_model->get_categoria($id_categoria);
        if ($cat) $categoria_nombre = $cat->nombre;
    }

    $nombre_comercial = $this->input->post('nombre'); // Este es el nombre "Base" que quiere el usuario
    $descripcion = $this->input->post('descripcion');

    // 2. Determinar atributos específicos para el producto principal (el que se está editando directamente)
    $talla_main  = $p_inicial->talla;
    $color_main  = $p_inicial->color;
    $diseno_main = $p_inicial->diseno;
    
    // Si viene en el JSON de variantes, sincronizamos sus atributos internos
    $json_variantes = $this->input->post('json_variantes');
    if ($json_variantes) {
        $variantes_data = json_decode($json_variantes, true);
        if (is_array($variantes_data)) {
            foreach ($variantes_data as $vd) {
                if (!empty($vd['id']) && $vd['id'] == $id) {
                    $talla_main  = $vd['talla'] ?? $talla_main;
                    $color_main  = $vd['color'] ?? $color_main;
                    $diseno_main = $vd['diseno'] ?? $diseno_main;
                    break;
                }
            }
        }
    }

    // Nombre completo del producto principal
    $attrs_main = trim($talla_main . ' ' . $color_main . ' ' . $diseno_main);
    $nombre_full_main = !empty($attrs_main) ? "$nombre_comercial ($attrs_main)" : $nombre_comercial;

    $data_main = [
        'codigo_barras' => $this->input->post('codigo_barras'),
        'nombre'        => $nombre_full_main,
        'descripcion'   => $descripcion,
        'categoria'     => $categoria_nombre,
        'id_categoria'  => $id_categoria ?: null,
        'id_almacen'    => $this->input->post('id_almacen') ?: null,
        'precio_compra' => $this->input->post('precio_compra'),
        'precio_venta'  => $this->input->post('precio_venta'),
        'talla'         => $talla_main,
        'color'         => $color_main,
        'diseno'        => $diseno_main,
        'stock_minimo'  => $this->input->post('stock_minimo')
    ];

    // Manejo de Imagen
    if (!empty($_FILES['imagen']['name'])) {
        $path = './uploads/productos/';
        if (!is_dir($path)) { mkdir($path, 0777, true); }
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $id . "." . $extension; 
        $config = ['upload_path' => $path, 'file_name' => $nombre_archivo, 'allowed_types' => 'gif|jpg|png|jpeg|webp', 'overwrite' => TRUE, 'max_size' => '10240'];
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('imagen')) {
            $data_main['imagen'] = $this->upload->data('file_name');
            $data_main['version'] = time();
        }
    }

    // --- SINCRONIZACIÓN MASIVA (HERMANOS) ---
    // 1. Detectar cambios en atributos específicos (Talla, Color, Diseño) para propagación selectiva
    $cambios_attr = [];
    if ($talla_main  !== $p_inicial->talla)  $cambios_attr['talla']  = [$p_inicial->talla, $talla_main];
    if ($color_main  !== $p_inicial->color)  $cambios_attr['color']  = [$p_inicial->color, $color_main];
    if ($diseno_main !== $p_inicial->diseno) $cambios_attr['diseno'] = [$p_inicial->diseno, $diseno_main];

    $hermanos = $this->Producto_model->get_hermanos($old_base_name, $id_sucursal);

    // 2. Propagar cambios a los hermanos
    foreach ($hermanos as $h) {
        if ($h['id'] == $id) continue; // Saltamos el actual, se actualiza luego
        
        $h_data = [
            'descripcion'  => $descripcion,
            'categoria'    => $categoria_nombre,
            'id_categoria' => $id_categoria ?: null,
            'stock_minimo' => $data_main['stock_minimo']
        ];

        // Sincronizar atributos de variante si el hermano compartía el valor antiguo
        $nombre_recalculado = false;
        foreach ($cambios_attr as $campo => $vals) {
            if ($h[$campo] === $vals[0]) {
                $h_data[$campo] = $vals[1];
                $nombre_recalculado = true;
            }
        }

        // Si cambió el nombre base (comercial) o alguno de sus atributos internos, recalculamos el nombre full del hermano
        if ($nombre_recalculado || ($nombre_comercial !== $old_base_name)) {
            $h_t = $h_data['talla']  ?? $h['talla'];
            $h_c = $h_data['color']  ?? $h['color'];
            $h_d = $h_data['diseno'] ?? $h['diseno'];
            $h_attrs = trim($h_t . ' ' . $h_c . ' ' . $h_d);
            $h_data['nombre'] = !empty($h_attrs) ? "$nombre_comercial ($h_attrs)" : $nombre_comercial;
        }

        $this->Producto_model->actualizar($h['id'], $id_sucursal, $h_data);
    }

    // 3. Ejecutar actualización del producto principal
    if ($this->Producto_model->actualizar($id, $id_sucursal, $data_main)) {
        
        // Ajuste de Stock principal
        $stock_ajuste = $this->input->post('stock_ajuste');
        if ($stock_ajuste !== null) {
            $dif = $stock_ajuste - $p_inicial->stock;
            if ($dif != 0) {
                $this->Stock_model->ajustar_stock($id, $id_sucursal, ($dif > 0 ? 'Entrada' : 'Salida'), abs($dif), $this->input->post('stock_motivo') ?: 'Ajuste Manual en Edición');
            }
        }

        // 4. Procesar variantes en JSON
        if ($json_variantes) {
            $variantes_json = json_decode($json_variantes, true);
            if (is_array($variantes_json)) {
                // Obtener todos los hermanos para reconciliación (incluyendo los recién actualizados por sincronización masiva)
                $hermanos_db = $this->Producto_model->get_hermanos($nombre_comercial, $id_sucursal);
                $ids_usados = [$id]; // El principal ya está usado

                // FASE 1: Procesar los que tienen ID explícito
                foreach ($variantes_json as &$v) {
                    if ($v['id'] == $id) { $v['_procesado'] = true; continue; }
                    if (!empty($v['id'])) {
                        $v_attrs = trim(($v['talla'] ?? '') . ' ' . ($v['color'] ?? '') . ' ' . ($v['diseno'] ?? ''));
                        $v_data = [
                            'nombre'       => !empty($v_attrs) ? "$nombre_comercial ($v_attrs)" : $nombre_comercial,
                            'descripcion'  => $descripcion,
                            'talla'        => $v['talla'] ?? '',
                            'color'        => $v['color'] ?? '',
                            'diseno'       => $v['diseno'] ?? '',
                            'precio_venta' => $v['precio'],
                            'stock_minimo' => $data_main['stock_minimo'],
                            'id_categoria' => $data_main['id_categoria'],
                            'categoria'    => $data_main['categoria']
                        ];
                        $this->Producto_model->actualizar($v['id'], $id_sucursal, $v_data);
                        $ids_usados[] = $v['id'];
                        $v['_procesado'] = true;
                    }
                }

                // FASE 2: Procesar los que NO tienen ID (intentar búsqueda exacta o reconciliación)
                foreach ($variantes_json as &$v) {
                    if (!empty($v['_procesado'])) continue;

                    $v_attrs = trim(($v['talla'] ?? '') . ' ' . ($v['color'] ?? '') . ' ' . ($v['diseno'] ?? ''));
                    $v_data = [
                        'nombre'       => !empty($v_attrs) ? "$nombre_comercial ($v_attrs)" : $nombre_comercial,
                        'descripcion'  => $descripcion,
                        'talla'        => $v['talla'] ?? '',
                        'color'        => $v['color'] ?? '',
                        'diseno'       => $v['diseno'] ?? '',
                        'precio_venta' => $v['precio'],
                        'stock_minimo' => $data_main['stock_minimo'],
                        'id_categoria' => $data_main['id_categoria'],
                        'categoria'    => $data_main['categoria']
                    ];

                    // A. Intentar búsqueda exacta de atributos (por si ya existía pero el JSON no traía ID)
                    $existente = $this->Producto_model->get_producto_por_atributos($id_sucursal, $v['talla']??'', $v['color']??'', $v['diseno']??'', $nombre_comercial);
                    
                    if ($existente) {
                        $this->Producto_model->actualizar($existente->id, $id_sucursal, $v_data);
                        $ids_usados[] = $existente->id;
                    } else {
                        // B. RECONCILIACIÓN: Buscar un hermano "huérfano" que coincida en talla (Heurística de renombre)
                        $reconciliado = null;
                        foreach ($hermanos_db as $h) {
                            if (!in_array($h['id'], $ids_usados) && $h['talla'] === ($v['talla'] ?? '')) {
                                $reconciliado = $h;
                                break;
                            }
                        }

                        if ($reconciliado) {
                            $this->Producto_model->actualizar($reconciliado['id'], $id_sucursal, $v_data);
                            $ids_usados[] = $reconciliado['id'];
                        } else {
                            // C. INSERTAR NUEVO (Si no se encontró ni por atributos ni por reconciliación)
                            $v_data['id_sucursal']   = $id_sucursal;
                            $v_data['precio_compra'] = $data_main['precio_compra'];
                            $v_data['stock']         = 0;
                            $v_data['id_almacen']    = $data_main['id_almacen'];
                            $v_data['codigo_barras'] = !empty($v['barcode']) ? $v['barcode'] : ($data_main['codigo_barras'] . '-' . uniqid());
                            
                            $target_id = $this->Producto_model->insertar($v_data);
                            if ($target_id && !empty($data_main['imagen'])) {
                                $this->Producto_model->actualizar($target_id, $id_sucursal, ['imagen' => $data_main['imagen'], 'version' => time()]);
                            }
                        }
                    }

                    // Ajuste de stock para la variante (sea nueva, reconciliada o encontrada por atributos)
                    $actual_target_id = !empty($v['id']) ? $v['id'] : (!empty($existente->id) ? $existente->id : (!empty($reconciliado['id']) ? $reconciliado['id'] : ($target_id ?? null)));
                    
                    if ($actual_target_id) {
                        $p_v = $this->Producto_model->get_producto($actual_target_id, $id_sucursal);
                        if ($p_v) {
                            $dif_v = ($v['stock'] ?? 0) - $p_v->stock;
                            if ($dif_v != 0) {
                                $this->Stock_model->ajustar_stock($actual_target_id, $id_sucursal, ($dif_v > 0 ? 'Entrada' : 'Salida'), abs($dif_v), (!empty($v['motivo']) ? $v['motivo'] : 'Ajuste de Variante en Edición'));
                            }
                        }
                    }
                }
            }
        }
        $this->session->set_flashdata('success', 'Producto y variantes sincronizados correctamente');
    } else {
        $this->session->set_flashdata('error', 'Error al guardar cambios');
    }
    redirect('productos');
}

}