<?php
class Login extends CI_Controller {

    public function index(){
        $this->load->view('login');
    }

    public function ingresar(){
        $usuario = $this->input->post('usuario');
        $password = md5($this->input->post('password'));

        // Hacemos un JOIN para traer los datos de la sucursal de una vez
        $this->db->select('u.*, s.nombre as sucursal_nombre');
        $this->db->from('usuarios u');
        $this->db->join('sucursales s', 'u.id_sucursal = s.id');
        $this->db->where('u.usuario', $usuario);
        $this->db->where('u.password', $password);
        $this->db->where('u.estado', 1); // Solo usuarios activos

        $query = $this->db->get();

        if($query->num_rows() > 0){
            $user = $query->row();

            // Ahora guardamos TODO lo necesario en la sesión
            $this->session->set_userdata([
                'id'              => $user->id,
                'usuario'         => $user->usuario,
                'nombre_completo' => $user->nombre,
                'rol'             => $user->rol,
                'id_sucursal'     => $user->id_sucursal,     // ID para las consultas SQL
                'sucursal_nombre' => $user->sucursal_nombre  // Nombre para mostrar en la interfaz
            ]);

            redirect('dashboard');

        } else {
            $this->session->set_flashdata('login_error', 'Usuario o contraseña incorrectos');
            redirect('login');
        }
    }

    public function cerrar() {
        $this->session->sess_destroy();
        redirect('login');
    }
}