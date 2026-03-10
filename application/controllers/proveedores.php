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

        if (empty($post['id'])) {
            $this->proveedor_m->insert($data);
            $this->session->set_flashdata('msg', 'Proveedor creado correctamente');
        } else {
            $this->proveedor_m->update($post['id'], $data);
            $this->session->set_flashdata('msg', 'Proveedor actualizado correctamente');
        }

        redirect('proveedores/proveedor_index');
    }


    public function eliminar($id_proveedor)
    {
        $this->proveedor_m->delete_logico($id_proveedor);
        $this->session->set_flashdata('msg', 'Proveedor eliminado correctamente');
        redirect('proveedores/proveedor_index');
    }
}
