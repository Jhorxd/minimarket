<?php
class Login extends CI_Controller {

    public function index(){
        $this->load->view('login');
    }

    public function ingresar(){
        $usuario = $this->input->post('usuario');
        $password = md5($this->input->post('password'));

        $query = $this->db->get_where('usuarios', [
            'usuario' => $usuario,
            'password' => $password
        ]);

        if($query->num_rows() > 0){
            $user = $query->row();

            $this->session->set_userdata([
                'id' => $user->id,
                'usuario' => $user->usuario,
                'rol' => $user->rol
            ]);

            redirect('dashboard');

        } else {
            // Guardar mensaje de error en flashdata
            $this->session->set_flashdata('login_error', 'Usuario o contraseña incorrectos');
            redirect('login');
        }
    }

    public function cerrar() {
        $this->session->sess_destroy();
        redirect('login');
    }
}