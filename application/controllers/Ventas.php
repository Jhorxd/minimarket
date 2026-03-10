<?php
class Ventas extends CI_Controller {

    public function pos() {
        // Carga la interfaz del POS
        $this->load->view('layouts/header');
        $this->load->view('layouts/sidebar');
        $this->load->view('ventas/pos');
        $this->load->view('layouts/footer');
    }

    // Endpoint para el buscador en tiempo real
    public function buscar_productos_ajax() {
        $buscar = $this->input->get('term'); // El término que viene del JS
        $this->load->model('Producto_model');
        
        $productos = $this->Producto_model->get_productos_pos($buscar);
        
        // Retornamos JSON para que el JS lo procese
        echo json_encode($productos);
    }

    public function guardar()
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['carrito'])) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Datos inválidos']));
    }

    $carrito     = $input['carrito'];
    $total       = $input['total'];
    $metodo_pago = $input['metodo_pago'];
    $monto_rec   = $input['monto_recibido'];
    $vuelto      = $input['vuelto'];

    $id_sucursal = $this->session->userdata('id_sucursal');
    $id_usuario  = $this->session->userdata('id');

    // Verificar caja activa
    $caja = $this->db->get_where('cajas', [
        'id_sucursal' => $id_sucursal,
        'estado'      => 'Abierta'
    ])->row();

    if (!$caja) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'No hay caja activa en esta sucursal']));
    }

    $this->db->trans_start();

    // 1. Insertar venta
    $this->db->insert('ventas', [
        'id_sucursal'    => $id_sucursal,
        'id_usuario'     => $id_usuario,
        'id_caja'        => $caja->id,
        'total'          => $total,
        'metodo_pago'    => $metodo_pago,
        'monto_recibido' => $monto_rec,
        'vuelto'         => $vuelto,
        'fecha_registro' => date('Y-m-d H:i:s')
    ]);

    $id_venta = $this->db->insert_id();

    // 2. Detalle + stock + kardex por cada producto
    foreach ($carrito as $item) {
        $id_producto = $item['id'];
        $cantidad    = $item['cantidad'];
        $precio      = $item['precio'];

        // Insertar detalle (columna precio_unitario)
        $this->db->insert('venta_detalles', [
            'id_venta'       => $id_venta,
            'id_producto'    => $id_producto,
            'cantidad'       => $cantidad,
            'precio_unitario' => $precio,
            'subtotal'       => $cantidad * $precio
        ]);

        // Descontar stock y obtener stock resultante
        $this->db->query(
            "UPDATE productos SET stock = stock - ? WHERE id = ? AND id_sucursal = ?",
            [$cantidad, $id_producto, $id_sucursal]
        );

        $stock_actual = $this->db->query(
            "SELECT stock FROM productos WHERE id = ? AND id_sucursal = ?",
            [$id_producto, $id_sucursal]
        )->row()->stock;

        // Insertar movimiento en kardex
        $this->db->insert('kardex', [
            'id_sucursal'     => $id_sucursal,
            'id_producto'     => $id_producto,
            'tipo_movimiento' => 'salida',
            'motivo'          => 'Venta #' . $id_venta,
            'cantidad'        => $cantidad,
            'stock_resultante' => $stock_actual,
            'fecha'           => date('Y-m-d H:i:s')
        ]);
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === false) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => 'Error al guardar la venta']));
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['success' => true, 'id_venta' => $id_venta]));
}

}