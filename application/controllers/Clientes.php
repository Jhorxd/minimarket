<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        // valida sesión/rol si lo necesitas
        $this->load->model('Cliente_model', 'cliente_m');
    }

    public function cliente_index()
    {
        $data['titulo']   = 'Clientes';
        $data['clientes'] = $this->cliente_m->get_all();

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('clientes/cliente_index', $data);
        $this->load->view('layouts/footer');
    }

    public function crear()
    {
        $data['titulo'] = 'Nuevo Cliente';

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('clientes/form_cliente', $data);
        $this->load->view('layouts/footer');
    }

    public function editar($id_cliente)
    {
        $cliente = $this->cliente_m->get($id_cliente);
        if (!$cliente) {
            show_404();
        }

        $data['titulo']  = 'Editar Cliente';
        $data['cliente'] = $cliente;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('clientes/form_cliente', $data);
        $this->load->view('layouts/footer');
    }

    public function guardar()
    {
        $post        = $this->input->post();
         $id_sucursal = $this->session->userdata('id_sucursal'); // de la sesión

       $data = [
        'nombre'         => $post['nombre'],
        'tipo_documento' => $post['tipo_documento'],
        'nro_documento'  => $post['nro_documento'],
        'telefono'       => $post['telefono'],
        'email'          => $post['email'],
        'direccion'      => $post['direccion'],
        'id_sucursal'    => $id_sucursal,
    ];

        if (!empty($post['nro_documento'])) {
            if ($this->cliente_m->existe_documento($post['nro_documento'], $post['id_cliente'] ?? null)) {
                $this->session->set_flashdata('msg_error', 'El documento ' . $post['nro_documento'] . ' ya se encuentra registrado para otro cliente.');
                redirect('clientes/cliente_index');
                return;
            }
        }

        if (empty($post['id_cliente'])) {
        $this->cliente_m->insert($data);
        $this->session->set_flashdata('msg', 'Cliente creado correctamente');
    } else {
        $this->cliente_m->update($post['id_cliente'], $data);
        $this->session->set_flashdata('msg', 'Cliente actualizado correctamente');
    }

        redirect('clientes/cliente_index');
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
            'nombre'         => $json['nombre'] ?? '',
            'tipo_documento' => $json['tipo_documento'] ?? '',
            'nro_documento'  => $json['nro_documento'] ?? '',
            'telefono'       => $json['telefono'] ?? '',
            'email'          => $json['email'] ?? '',
            'direccion'      => $json['direccion'] ?? '',
            'id_sucursal'    => $id_sucursal,
            'estado'         => 1
        ];

        if (empty($data['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }
        
        if (!empty($data['nro_documento'])) {
            if ($this->cliente_m->existe_documento($data['nro_documento'])) {
                echo json_encode(['success' => false, 'message' => 'El documento ' . $data['nro_documento'] . ' ya existe en clientes.']);
                return;
            }
        }

        $id = $this->cliente_m->insert($data);

        if ($id) {
            $data['id_cliente'] = $id;
            echo json_encode(['success' => true, 'cliente' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar cliente']);
        }
    }

    public function eliminar($id_cliente)
    {
        $this->cliente_m->delete_logico($id_cliente);
        $this->session->set_flashdata('msg', 'Cliente eliminado correctamente');
        redirect('clientes/cliente_index');
    }
}
