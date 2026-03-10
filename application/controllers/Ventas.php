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


    public function ticket($id_venta)
    {
        $venta = $this->db->query("
            SELECT v.*, u.nombre as cajero
            FROM ventas v
            JOIN usuarios u ON u.id = v.id_usuario
            WHERE v.id = ?
        ", [$id_venta])->row();

        if (!$venta) show_404();

        $detalles = $this->db->query("
            SELECT vd.*, p.nombre, p.codigo_barras
            FROM venta_detalles vd
            JOIN productos p ON p.id = vd.id_producto
            WHERE vd.id_venta = ?
        ", [$id_venta])->result();

        $sucursal = $this->db->query(
            "SELECT * FROM sucursales WHERE id = ?",
            [$venta->id_sucursal]
        )->row();

        $html = $this->_html_ticket($venta, $detalles, $sucursal);

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => [80, 200],
            'margin_top'    => 4,
            'margin_bottom' => 4,
            'margin_left'   => 4,
            'margin_right'  => 4,
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output('ticket_' . $id_venta . '.pdf', 'I');
    }

    private function _html_ticket($venta, $detalles, $sucursal)
    {
        $items_html = '';
        foreach ($detalles as $d) {
            $items_html .= '
            <tr>
                <td style="padding:2px 0; font-size:10px;">' . htmlspecialchars($d->nombre) . '</td>
                <td style="text-align:center; font-size:10px;">' . $d->cantidad . '</td>
                <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->precio_unitario, 2) . '</td>
                <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->subtotal, 2) . '</td>
            </tr>';
        }

        $vuelto_html = '';
        if ($venta->metodo_pago === 'efectivo') {
            $vuelto_html = '
            <tr>
                <td colspan="2" style="font-size:10px;">Recibido:</td>
                <td colspan="2" style="text-align:right; font-size:10px;">S/ ' . number_format($venta->monto_recibido, 2) . '</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:10px;">Vuelto:</td>
                <td colspan="2" style="text-align:right; font-size:10px;">S/ ' . number_format($venta->vuelto, 2) . '</td>
            </tr>';
        }

        $nombre_sucursal = $sucursal ? htmlspecialchars($sucursal->nombre) : 'Sucursal';

        return '
        <style>
            body { font-family: monospace; font-size: 11px; color: #000; }
            .center { text-align: center; }
            .bold { font-weight: bold; }
            .line { border-top: 1px dashed #000; margin: 4px 0; }
            table { width: 100%; border-collapse: collapse; }
        </style>

        <div class="center bold" style="font-size:14px;">' . $nombre_sucursal . '</div>
        <div class="center" style="font-size:10px;">Boleta de Venta</div>
        <div class="line"></div>

        <table>
            <tr>
                <td style="font-size:10px;">Ticket N°:</td>
                <td style="text-align:right; font-size:10px; font-weight:bold;">#' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . '</td>
            </tr>
            <tr>
                <td style="font-size:10px;">Fecha:</td>
                <td style="text-align:right; font-size:10px;">' . date('d/m/Y H:i', strtotime($venta->fecha_registro)) . '</td>
            </tr>
            <tr>
                <td style="font-size:10px;">Cajero:</td>
                <td style="text-align:right; font-size:10px;">' . htmlspecialchars($venta->cajero) . '</td>
            </tr>
        </table>

        <div class="line"></div>

        <table>
            <thead>
                <tr style="border-bottom: 1px dashed #000;">
                    <th style="text-align:left; font-size:10px; padding-bottom:2px;">Producto</th>
                    <th style="text-align:center; font-size:10px;">Cant.</th>
                    <th style="text-align:right; font-size:10px;">P.Unit</th>
                    <th style="text-align:right; font-size:10px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>' . $items_html . '</tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td colspan="2" style="font-size:13px; font-weight:bold;">TOTAL:</td>
                <td colspan="2" style="text-align:right; font-size:13px; font-weight:bold;">S/ ' . number_format($venta->total, 2) . '</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:10px;">Método pago:</td>
                <td colspan="2" style="text-align:right; font-size:10px; text-transform:uppercase;">' . $venta->metodo_pago . '</td>
            </tr>
            ' . $vuelto_html . '
        </table>

        <div class="line"></div>
        <div class="center" style="font-size:10px; margin-top:6px;">¡Gracias por su compra!</div>
        <div class="center" style="font-size:9px;">Conserve su comprobante</div>
        ';
    }


    public function venta_index()
{
    $id_sucursal = $this->session->userdata('id_sucursal');

    // Traemos las ventas con info básica
    $ventas = $this->db->query("
        SELECT v.id,
               v.fecha_registro,
               v.total,
               v.metodo_pago,
               u.nombre   AS cajero
        FROM ventas v
        JOIN usuarios u ON u.id = v.id_usuario
        WHERE v.id_sucursal = ?
        ORDER BY v.fecha_registro DESC
    ", [$id_sucursal])->result();

    $data['ventas'] = $ventas;
    $data['titulo'] = 'Historial de Ventas';

    $this->load->view('layouts/header', $data);
    $this->load->view('layouts/sidebar');
    $this->load->view('ventas/venta_index', $data);
    $this->load->view('layouts/footer');
}

    
}