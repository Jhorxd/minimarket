<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Cargamos la base de datos por si no está en el autoload
        $this->load->database();
    }

    public function index() {
        // Verificación de seguridad: si no hay rol en la sesión, no está logueado
        if (!$this->session->userdata('rol')) {
            redirect('login'); 
        }

        $rol = $this->session->userdata('rol');
        $mes = date('m');
        $anio = date('Y');
        $data = array();

        // 1. Obtener datos según el rol
        if ($rol == 'admin') {
            $vista = 'dashboard_admin';
        } else {
            $data = $this->_get_data_bolivia($mes, $anio);
            $vista = 'dashboard_bolivia';
        }

        // 2. Cargar la vista (Los layouts se cargan dentro de la vista o aquí)
        // Si tus vistas ya tienen el header/sidebar por dentro, solo deja la carga de $vista
        $this->load->view($vista, $data);
    }

}