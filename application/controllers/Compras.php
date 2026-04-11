<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compras extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Compra_model', 'compra_m');
    }

    // Listado de compras
    public function compras_index()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $fecha_desde = $this->input->get('fecha_desde') ?: date('Y-m-01');
        $fecha_hasta = $this->input->get('fecha_hasta') ?: date('Y-m-d');

        $data['titulo']      = 'Compras';
        $data['compras']     = $this->compra_m->listar_compras($id_sucursal, $fecha_desde, $fecha_hasta);
        $data['fecha_desde'] = $fecha_desde;
        $data['fecha_hasta'] = $fecha_hasta;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('compras/compras_index', $data);
        $this->load->view('layouts/footer');
    }

    // Form nueva compra
    public function nueva()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');

        $data['titulo']      = 'Nueva Compra';
        $data['proveedores'] = $this->compra_m->get_proveedores_activos();
        $data['productos']   = $this->compra_m->get_productos_sucursal($id_sucursal);

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('compras/form_compras', $data);
        $this->load->view('layouts/footer');
    }

    // Guardar compra (Nueva o Editar)
    public function guardar()
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $id_usuario  = $this->session->userdata('id');

        $id_compra       = $this->input->post('id_compra');           // Si viene, es edición de borrador
        $id_proveedor    = (int)$this->input->post('id_proveedor');
        $proveedor_texto = $this->input->post('proveedor_texto');
        $accion          = $this->input->post('accion') ?: 'ejecutar'; // 'borrador' o 'ejecutar'
        $estado          = ($accion === 'borrador') ? 'borrador' : 'completada';

        if ($id_proveedor && empty($proveedor_texto)) {
            $this->load->model('Proveedor_model');
            $prov = $this->Proveedor_model->get($id_proveedor);
            if ($prov) $proveedor_texto = $prov->razon_social;
        }

        $ids_producto = $this->input->post('id_producto');
        $cantidades   = $this->input->post('cantidad');
        $precios      = $this->input->post('precio_compra');

        $items = [];
        if (is_array($ids_producto)) {
            for ($i = 0; $i < count($ids_producto); $i++) {
                if (empty($ids_producto[$i])) continue;
                
                $prod_info = $this->db->select('talla, color, diseno')->from('productos')->where('id', (int)$ids_producto[$i])->get()->row();
                
                $items[] = [
                    'id_producto'   => (int)$ids_producto[$i],
                    'cantidad'      => (float)$cantidades[$i],
                    'precio_compra' => (float)$precios[$i],
                    'talla'         => $prod_info ? $prod_info->talla : null,
                    'color'         => $prod_info ? $prod_info->color : null,
                    'diseno'        => $prod_info ? $prod_info->diseno : null,
                ];
            }
        }

        if (empty($items)) {
            $this->session->set_flashdata('msg', 'Debe agregar al menos un producto.');
            redirect('compras/nueva');
        }

        if ($id_compra) {
            // Actualizar borrador existente
            $exito = $this->compra_m->actualizar_borrador($id_compra, $id_proveedor ?: null, $proveedor_texto, $items, $estado);
            $msg = ($estado === 'borrador') ? 'Borrador actualizado.' : 'Compra ejecutada y stock cargado.';
        } else {
            // Nueva compra (Borrador o Directa)
            $id_compra = $this->compra_m->registrar_compra($id_sucursal, $id_usuario, $id_proveedor ?: null, $proveedor_texto, $items, $estado);
            $exito = $id_compra ? true : false;
            $msg = ($estado === 'borrador') ? 'Borrador guardado correctamente.' : 'Compra registrada y stock cargado.';
        }

        if ($exito) {
            $this->session->set_flashdata('msg', $msg);
        } else {
            $this->session->set_flashdata('msg', 'Error al procesar la compra.');
        }

        redirect('compras/compras_index');
    }

    // Editar un borrador previo
    public function editar($id_compra)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        list($compra, $detalles) = $this->compra_m->get_compra_con_detalle($id_compra);

        if (!$compra || $compra->estado !== 'borrador') {
            $this->session->set_flashdata('msg', 'Solo se pueden editar compras en estado borrador.');
            redirect('compras/compras_index');
        }

        $data['titulo']      = 'Editar Borrador de Compra';
        $data['proveedores'] = $this->compra_m->get_proveedores_activos();
        $data['productos']   = $this->compra_m->get_productos_sucursal($id_sucursal);
        $data['compra']      = $compra;
        $data['detalles']    = $detalles;

        $this->load->view('layouts/header', $data);
        $this->load->view('layouts/sidebar');
        $this->load->view('compras/form_compras', $data);
        $this->load->view('layouts/footer');
    }

    // Ejecutar carga de stock desde el listado
    public function ejecutar($id_compra)
    {
        if ($this->compra_m->ejecutar_compra($id_compra)) {
            $this->session->set_flashdata('msg', 'Stock cargado correctamente. Compra completada.');
        } else {
            $this->session->set_flashdata('msg', 'Error al ejecutar la compra.');
        }
        redirect('compras/compras_index');
    }

  public function ver_compras($id_compra)
{
    list($compra, $detalles) = $this->compra_m->get_compra_con_detalle($id_compra);
    if (!$compra) {
        show_404();
    }

    $data['titulo']   = 'Detalle de Compra';
    $data['compra']   = $compra;
    $data['detalles'] = $detalles;

    $this->load->view('layouts/header', $data);
    $this->load->view('layouts/sidebar');
    $this->load->view('compras/ver_compras', $data);
    $this->load->view('layouts/footer');
}

public function ticket_pdf($id_compra)
{
    list($compra, $detalles) = $this->compra_m->get_compra_con_detalle($id_compra);
    if (!$compra) {
        show_404();
    }

    $sucursal = $this->db->query(
        "SELECT * FROM sucursales WHERE id = ?",
        [$compra->id_sucursal]
    )->row();
    $nombre_sucursal = $sucursal ? htmlspecialchars($sucursal->nombre) : 'Sucursal';

    $items_html = '';
    foreach ($detalles as $d) {
        $nombre_completo = $d->nombre;
        if (!empty($d->talla) || !empty($d->color) || !empty($d->diseno)) {
            $nombre_completo .= ' (' . trim($d->talla . ' ' . $d->color . ' ' . $d->diseno) . ')';
        }

        $items_html .= '
        <tr>
            <td style="padding:2px 0; font-size:10px;">' . htmlspecialchars($nombre_completo) . '</td>
            <td style="text-align:center; font-size:10px;">' . number_format($d->cantidad, 2) . '</td>
            <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->precio_compra, 2) . '</td>
            <td style="text-align:right; font-size:10px;">S/ ' . number_format($d->subtotal, 2) . '</td>
        </tr>';
    }

    $fecha = date('d/m/Y H:i', strtotime($compra->fecha_registro));
    $proveedor = htmlspecialchars($compra->razon_social ?: $compra->proveedor ?: 'Sin proveedor');

    $html = '
    <style>
        body { font-family: monospace; font-size: 11px; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
    </style>

    <div class="center bold" style="font-size:14px;">' . $nombre_sucursal . '</div>
    <div class="center" style="font-size:10px;">Comprobante de Ingreso / Compra</div>
    <div class="line"></div>

    <table>
        <tr>
            <td style="font-size:10px;">Ticket N°:</td>
            <td style="text-align:right; font-size:10px; font-weight:bold;">#' . str_pad($compra->id, 6, '0', STR_PAD_LEFT) . '</td>
        </tr>
        <tr>
            <td style="font-size:10px;">Fecha:</td>
            <td style="text-align:right; font-size:10px;">' . $fecha . '</td>
        </tr>
        <tr>
            <td style="font-size:10px;">Proveedor:</td>
            <td style="text-align:right; font-size:10px;">' . $proveedor . '</td>
        </tr>
        <tr>
            <td style="font-size:10px;">Usuario:</td>
            <td style="text-align:right; font-size:10px;">' . htmlspecialchars($compra->nombre_usuario ?? 'Admin') . '</td>
        </tr>
    </table>
    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th style="font-size:10px; text-align:left; border-bottom:1px solid #000;">Prod/Desc</th>
                <th style="font-size:10px; text-align:center; border-bottom:1px solid #000;">Cant</th>
                <th style="font-size:10px; text-align:right; border-bottom:1px solid #000;">P.U.</th>
                <th style="font-size:10px; text-align:right; border-bottom:1px solid #000;">Total</th>
            </tr>
        </thead>
        <tbody>
            ' . $items_html . '
        </tbody>
    </table>
    <div class="line"></div>
    <table>
        <tr>
            <td colspan="3" class="bold" style="font-size:12px;">Total Compra:</td>
            <td class="bold" style="text-align:right; font-size:12px;">S/ ' . number_format($compra->total, 2) . '</td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="center" style="font-size:10px; margin-top:6px;">
        *** Copia interna de almacén ***
    </div>';

    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => [80, 200],
        'margin_top'    => 4,
        'margin_bottom' => 4,
        'margin_left'   => 4,
        'margin_right'  => 4,
    ]);

    $mpdf->WriteHTML($html);
    $mpdf->Output('compra_ticket_' . $id_compra . '.pdf', 'I');
}

    public function anular_compra()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_compra = $input['id_compra'] ?? null;
        $motivo = $input['motivo'] ?? 'Error de registro';

        if (!$id_compra) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'ID inválido']));
        }

        if ($this->compra_m->anular_compra($id_compra, $motivo)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
        } else {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'No se pudo anular la compra o ya está anulada']));
        }
    }
}
