<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedores extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // aquí podrías validar sesión / rol
        $this->load->model('Proveedor_model', 'proveedor_m');
    }

    public function proveedor_index()
    {
        $data['titulo']      = 'Proveedores';
        $data['proveedores'] = $this->proveedor_m->get_all();

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('proveedores/proveedor_index', $data);
        $this->load->view('layouts/footer');
    }

    public function crear()
    {
        $data['titulo'] = 'Nuevo Proveedor';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('proveedores/form_proveedor', $data);
        $this->load->view('layouts/footer');
    }

    public function editar($id_proveedor)
    {
        $proveedor = $this->proveedor_m->get($id_proveedor);
        if (!$proveedor) {
            show_404();
        }

        $data['titulo']    = 'Editar Proveedor';
        $data['proveedor'] = $proveedor;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('proveedores/form_proveedor', $data);
        $this->load->view('layouts/footer');
    }

    public function guardar()
    {
        $post        = $this->input->post();
        $id_sucursal = $this->session->userdata('id_sucursal');

        $data = [
            'razon_social'     => $post['razon_social'],
            'nombre_comercial' => $post['nombre_comercial'],
            'tipo_documento'   => $post['tipo_documento'],
            'nro_documento'    => $post['nro_documento'],
            'telefono'         => $post['telefono'],
            'email'            => $post['email'],
            'direccion'        => $post['direccion'],
            'rubro'            => $post['rubro'],
            'id_sucursal'      => $id_sucursal,
        ];

        if (!empty($post['nro_documento'])) {
            if ($this->proveedor_m->existe_documento($post['nro_documento'], $post['id'] ?? null)) {
                $this->session->set_flashdata('msg_error', 'El documento ' . $post['nro_documento'] . ' ya se encuentra registrado para otro proveedor.');
                redirect('proveedores/proveedor_index');
                return;
            }
        }

        if (empty($post['id'])) {
            $this->proveedor_m->insert($data);
            $this->session->set_flashdata('msg', 'Proveedor creado correctamente');
        } else {
            $this->proveedor_m->update($post['id'], $data);
            $this->session->set_flashdata('msg', 'Proveedor actualizado correctamente');
        }

        redirect('proveedores/proveedor_index');
    }

    public function buscar_ajax() {
        $buscar = $this->input->get('term');
        $proveedores = $this->proveedor_m->get_proveedores_ajax($buscar);
        echo json_encode($proveedores);
    }

    public function guardar_ajax()
    {
        $this->output->set_content_type('application/json');
        
        $json = json_decode($this->input->raw_input_stream, true);
        if (!$json) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        $id_sucursal = $this->session->userdata('id_sucursal');
        
        $data = [
            'razon_social'     => $json['razon_social'] ?? '',
            'tipo_documento'   => $json['tipo_documento'] ?? '',
            'nro_documento'    => $json['nro_documento'] ?? '',
            'telefono'         => $json['telefono'] ?? '',
            'email'            => $json['email'] ?? '',
            'direccion'        => $json['direccion'] ?? '',
            'id_sucursal'      => $id_sucursal,
            'estado'         => 1
        ];

        if (empty($data['razon_social'])) {
            echo json_encode(['success' => false, 'message' => 'La Razón Social/Nombre es obligatoria']);
            return;
        }
        
        if (!empty($data['nro_documento'])) {
            if ($this->proveedor_m->existe_documento($data['nro_documento'])) {
                echo json_encode(['success' => false, 'message' => 'El documento ' . $data['nro_documento'] . ' ya existe en proveedores.']);
                return;
            }
        }

        $id = $this->proveedor_m->insert($data);

        if ($id) {
            $data['id_proveedor'] = $id;
            echo json_encode(['success' => true, 'proveedor' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar proveedor']);
        }
    }


    public function eliminar($id_proveedor)
    {
        $this->proveedor_m->delete_logico($id_proveedor);
        $this->session->set_flashdata('msg', 'Proveedor eliminado correctamente');
        redirect('proveedores/proveedor_index');
    }
}
