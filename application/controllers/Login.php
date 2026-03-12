<?php
class Login extends CI_Controller {

    public function index(){
        $this->load->view('login');
    }

    public function ingresar(){
        $usuario  = $this->input->post('usuario');
        $password = md5($this->input->post('password'));

        // Logs básicos de entrada
        log_message('debug', 'Login intento. Usuario enviado: ' . $usuario);
        log_message('debug', 'Password (md5) generado: ' . $password);

        // Armamos la consulta
        $this->db->select('u.*, s.nombre as sucursal_nombre');
        $this->db->from('usuarios u');
        $this->db->join('sucursales s', 'u.id_sucursal = s.id');
        $this->db->where('u.usuario', $usuario);
        $this->db->where('u.password', $password);
        $this->db->where('u.estado', 1); // Solo usuarios activos

        $query = $this->db->get();

        // Log de la consulta ejecutada y resultado
        log_message('debug', 'Login SQL: ' . $this->db->last_query());
        log_message('debug', 'Login filas encontradas: ' . $query->num_rows());

        if($query->num_rows() > 0){
            $user = $query->row();

            log_message('info', 'Login correcto para usuario ID: ' . $user->id);

            // Ahora guardamos TODO lo necesario en la sesión
            $this->session->set_userdata([
                'id'              => $user->id,
                'usuario'         => $user->usuario,
                'nombre_completo' => $user->nombre,
                'rol'             => $user->rol,
                'id_sucursal'     => $user->id_sucursal,
                'sucursal_nombre' => $user->sucursal_nombre
            ]);

            redirect('ventas/pos');

        } else {
            log_message('error', 'Login fallido para usuario: ' . $usuario);

            $this->session->set_flashdata('login_error', 'Usuario o contraseña incorrectos');
            redirect('login');
        }
    }

    public function cerrar() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
