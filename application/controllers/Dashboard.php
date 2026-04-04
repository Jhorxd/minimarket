<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Cargamos la base de datos por si no está en el autoload
        $this->load->database();
    }

    /**
     * Dashboard financiero (admin): KPIs, gráficos y exportación.
     */
    public function financiero()
    {
        if (!$this->session->userdata('rol')) {
            redirect('login');
        }
        if ($this->session->userdata('rol') !== 'admin') {
            show_error('No autorizado', 403);
        }

        $this->load->model('Dashboard_model', 'dashboard_m');

        $desde_in = $this->input->get('fecha_desde');
        $hasta_in = $this->input->get('fecha_hasta');
        list($fecha_desde, $fecha_hasta) = $this->dashboard_m->normalizar_rango($desde_in, $hasta_in);

        $id_sucursal = (int) $this->session->userdata('id_sucursal');

        $total_ingresos = $this->dashboard_m->total_ingresos($id_sucursal, $fecha_desde, $fecha_hasta);
        $egresos_compras = $this->dashboard_m->total_egresos_compras($id_sucursal, $fecha_desde, $fecha_hasta);
        $egresos_gastos  = $this->dashboard_m->total_egresos_gastos_operativos($id_sucursal, $fecha_desde, $fecha_hasta);
        $total_egresos   = $egresos_compras + $egresos_gastos;
        $ganancia_neta   = $total_ingresos - $total_egresos;

        $serie_diaria = $this->dashboard_m->serie_diaria_ingresos_egresos($id_sucursal, $fecha_desde, $fecha_hasta);
        $egresos_cat  = $this->dashboard_m->egresos_por_categoria($id_sucursal, $fecha_desde, $fecha_hasta);
        $gastos_cat   = $this->dashboard_m->gastos_operativos_por_categoria($id_sucursal, $fecha_desde, $fecha_hasta);
        $serie_mensual = $this->dashboard_m->serie_mensual_ingresos_egresos($id_sucursal, $fecha_desde, $fecha_hasta);

        $cat_for_json = [];
        foreach ($egresos_cat as $row) {
            $cat_for_json[] = ['categoria' => 'Mercadería: ' . $row->categoria, 'total' => (float) $row->total];
        }
        foreach ($gastos_cat as $row) {
            $cat_for_json[] = ['categoria' => 'Operativo: ' . $row->categoria, 'total' => (float) $row->total];
        }

        $fd = DateTime::createFromFormat('Y-m-d', $fecha_desde);
        $fh = DateTime::createFromFormat('Y-m-d', $fecha_hasta);
        $dias_rango = ($fd && $fh) ? ((int) $fd->diff($fh)->format('%a') + 1) : 1;

        $data = [
            'titulo'              => 'Dashboard financiero',
            'fecha_desde'         => $fecha_desde,
            'fecha_hasta'         => $fecha_hasta,
            'dias_rango'          => $dias_rango,
            'total_ingresos'      => $total_ingresos,
            'egresos_compras'    => $egresos_compras,
            'egresos_gastos'     => $egresos_gastos,
            'total_egresos'       => $total_egresos,
            'ganancia_neta'       => $ganancia_neta,
            'json_serie_diaria'   => json_encode($serie_diaria),
            'json_egresos_categoria' => json_encode($cat_for_json),
            'json_serie_mensual'  => json_encode($serie_mensual),
        ];

        $this->load->view('dashboard/index', $data);
    }

    /**
     * Exporta reporte financiero como Excel (tabla HTML compatible con Excel).
     */
    public function export_excel()
    {
        if (!$this->session->userdata('rol') || $this->session->userdata('rol') !== 'admin') {
            show_error('No autorizado', 403);
        }

        $this->load->model('Dashboard_model', 'dashboard_m');
        $desde_in = $this->input->get('fecha_desde');
        $hasta_in = $this->input->get('fecha_hasta');
        list($fecha_desde, $fecha_hasta) = $this->dashboard_m->normalizar_rango($desde_in, $hasta_in);
        $id_sucursal = (int) $this->session->userdata('id_sucursal');

        $ing = $this->dashboard_m->total_ingresos($id_sucursal, $fecha_desde, $fecha_hasta);
        $ec  = $this->dashboard_m->total_egresos_compras($id_sucursal, $fecha_desde, $fecha_hasta);
        $eg  = $this->dashboard_m->total_egresos_gastos_operativos($id_sucursal, $fecha_desde, $fecha_hasta);
        $egr = $ec + $eg;
        $det = $this->dashboard_m->detalle_movimientos($id_sucursal, $fecha_desde, $fecha_hasta);

        $filename = 'dashboard_financiero_' . $fecha_desde . '_' . $fecha_hasta . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<table border="1">';
        echo '<tr><th colspan="2">Dashboard financiero</th></tr>';
        echo '<tr><td>Período</td><td>' . html_escape($fecha_desde) . ' al ' . html_escape($fecha_hasta) . '</td></tr>';
        echo '<tr><td>Total ingresos (ventas)</td><td>' . number_format($ing, 2, '.', '') . '</td></tr>';
        echo '<tr><td>Egresos mercadería (compras)</td><td>' . number_format($ec, 2, '.', '') . '</td></tr>';
        echo '<tr><td>Egresos operativos (gastos)</td><td>' . number_format($eg, 2, '.', '') . '</td></tr>';
        echo '<tr><td>Total egresos</td><td>' . number_format($egr, 2, '.', '') . '</td></tr>';
        echo '<tr><td>Ganancia neta</td><td>' . number_format($ing - $egr, 2, '.', '') . '</td></tr>';
        echo '</table><br>';

        echo '<table border="1">';
        echo '<tr><th colspan="4">Ventas en el período</th></tr>';
        echo '<tr><th>ID</th><th>Fecha</th><th>Total</th><th>Método pago</th></tr>';
        foreach ($det['ventas'] as $v) {
            echo '<tr>';
            echo '<td>' . (int) $v->id . '</td>';
            echo '<td>' . html_escape($v->fecha_registro) . '</td>';
            echo '<td>' . number_format((float) $v->total, 2, '.', '') . '</td>';
            echo '<td>' . html_escape($v->metodo_pago) . '</td>';
            echo '</tr>';
        }
        echo '</table><br>';

        echo '<table border="1">';
        echo '<tr><th colspan="4">Compras en el período</th></tr>';
        echo '<tr><th>ID</th><th>Fecha</th><th>Total</th><th>Proveedor (texto)</th></tr>';
        foreach ($det['compras'] as $c) {
            echo '<tr>';
            echo '<td>' . (int) $c->id . '</td>';
            echo '<td>' . html_escape($c->fecha_registro) . '</td>';
            echo '<td>' . number_format((float) $c->total, 2, '.', '') . '</td>';
            echo '<td>' . html_escape($c->proveedor) . '</td>';
            echo '</tr>';
        }
        echo '</table><br>';

        echo '<table border="1">';
        echo '<tr><th colspan="5">Gastos operativos en el período</th></tr>';
        echo '<tr><th>ID</th><th>Fecha</th><th>Categoría</th><th>Concepto</th><th>Monto</th></tr>';
        foreach ($det['gastos'] as $g) {
            echo '<tr>';
            echo '<td>' . (int) $g->id . '</td>';
            echo '<td>' . html_escape($g->fecha_gasto) . '</td>';
            echo '<td>' . html_escape($g->categoria) . '</td>';
            echo '<td>' . html_escape($g->concepto) . '</td>';
            echo '<td>' . number_format((float) $g->monto, 2, '.', '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    /**
     * Exporta reporte financiero en PDF (mPDF, mismo stack que tickets de venta).
     */
    public function export_pdf()
    {
        if (!$this->session->userdata('rol') || $this->session->userdata('rol') !== 'admin') {
            show_error('No autorizado', 403);
        }

        $this->load->model('Dashboard_model', 'dashboard_m');
        $desde_in = $this->input->get('fecha_desde');
        $hasta_in = $this->input->get('fecha_hasta');
        list($fecha_desde, $fecha_hasta) = $this->dashboard_m->normalizar_rango($desde_in, $hasta_in);
        $id_sucursal = (int) $this->session->userdata('id_sucursal');

        $ing = $this->dashboard_m->total_ingresos($id_sucursal, $fecha_desde, $fecha_hasta);
        $ec  = $this->dashboard_m->total_egresos_compras($id_sucursal, $fecha_desde, $fecha_hasta);
        $eg  = $this->dashboard_m->total_egresos_gastos_operativos($id_sucursal, $fecha_desde, $fecha_hasta);
        $egr = $ec + $eg;
        $net = $ing - $egr;
        $det = $this->dashboard_m->detalle_movimientos($id_sucursal, $fecha_desde, $fecha_hasta);

        $rows_v = '';
        foreach ($det['ventas'] as $v) {
            $rows_v .= '<tr>
                <td>' . (int) $v->id . '</td>
                <td>' . htmlspecialchars($v->fecha_registro, ENT_QUOTES, 'UTF-8') . '</td>
                <td style="text-align:right;">S/ ' . number_format((float) $v->total, 2) . '</td>
                <td>' . htmlspecialchars($v->metodo_pago, ENT_QUOTES, 'UTF-8') . '</td>
            </tr>';
        }
        if ($rows_v === '') {
            $rows_v = '<tr><td colspan="4" style="text-align:center;">Sin registros</td></tr>';
        }

        $rows_c = '';
        foreach ($det['compras'] as $c) {
            $rows_c .= '<tr>
                <td>' . (int) $c->id . '</td>
                <td>' . htmlspecialchars($c->fecha_registro, ENT_QUOTES, 'UTF-8') . '</td>
                <td style="text-align:right;">S/ ' . number_format((float) $c->total, 2) . '</td>
                <td>' . htmlspecialchars((string) $c->proveedor, ENT_QUOTES, 'UTF-8') . '</td>
            </tr>';
        }
        if ($rows_c === '') {
            $rows_c = '<tr><td colspan="4" style="text-align:center;">Sin registros</td></tr>';
        }

        $rows_g = '';
        foreach ($det['gastos'] as $g) {
            $rows_g .= '<tr>
                <td>' . (int) $g->id . '</td>
                <td>' . htmlspecialchars($g->fecha_gasto, ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($g->categoria, ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($g->concepto, ENT_QUOTES, 'UTF-8') . '</td>
                <td style="text-align:right;">S/ ' . number_format((float) $g->monto, 2) . '</td>
            </tr>';
        }
        if ($rows_g === '') {
            $rows_g = '<tr><td colspan="5" style="text-align:center;">Sin registros</td></tr>';
        }

        $html = '
        <style>
            body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
            h1 { font-size: 18px; margin-bottom: 4px; }
            .meta { color: #64748b; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
            th { background: #f1f5f9; text-align: left; }
            .kpi { margin-bottom: 16px; }
            .kpi td { border: none; padding: 4px 8px 4px 0; }
        </style>
        <h1>Dashboard financiero</h1>
        <div class="meta">Período: ' . htmlspecialchars($fecha_desde, ENT_QUOTES, 'UTF-8') . ' — ' . htmlspecialchars($fecha_hasta, ENT_QUOTES, 'UTF-8') . '</div>
        <table class="kpi">
            <tr><td><strong>Total ingresos (ventas)</strong></td><td>S/ ' . number_format($ing, 2) . '</td></tr>
            <tr><td><strong>Egresos mercadería (compras)</strong></td><td>S/ ' . number_format($ec, 2) . '</td></tr>
            <tr><td><strong>Egresos operativos (gastos)</strong></td><td>S/ ' . number_format($eg, 2) . '</td></tr>
            <tr><td><strong>Total egresos</strong></td><td>S/ ' . number_format($egr, 2) . '</td></tr>
            <tr><td><strong>Ganancia neta</strong></td><td>S/ ' . number_format($net, 2) . '</td></tr>
        </table>
        <h2 style="font-size:14px;">Ventas</h2>
        <table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Total</th><th>Método</th></tr></thead>
            <tbody>' . $rows_v . '</tbody>
        </table>
        <h2 style="font-size:14px;">Compras</h2>
        <table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Total</th><th>Proveedor</th></tr></thead>
            <tbody>' . $rows_c . '</tbody>
        </table>
        <h2 style="font-size:14px;">Gastos operativos</h2>
        <table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Categoría</th><th>Concepto</th><th>Monto</th></tr></thead>
            <tbody>' . $rows_g . '</tbody>
        </table>';

        $mpdf = new \Mpdf\Mpdf([
            'mode'         => 'utf-8',
            'format'       => 'A4',
            'margin_top'   => 12,
            'margin_bottom'=> 12,
            'margin_left'  => 12,
            'margin_right' => 12,
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output('dashboard_financiero_' . $fecha_desde . '_' . $fecha_hasta . '.pdf', 'I');
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