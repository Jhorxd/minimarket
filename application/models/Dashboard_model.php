<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Consultas agregadas para el dashboard financiero.
 *
 * Fuentes en BD:
 * - Ingresos: ventas.total / fecha_registro
 * - Egresos mercadería: compras.total / fecha_registro
 * - Egresos operativos: gastos.monto / fecha_gasto (tabla gastos + categorias_gasto)
 */
class Dashboard_model extends CI_Model {

    /**
     * Normaliza rango [desde, hasta] como fechas Y-m-d.
     *
     * @param string|null $desde
     * @param string|null $hasta
     * @return array{0:string,1:string} [desde, hasta]
     */
    public function normalizar_rango($desde, $hasta)
    {
        $hoy = new DateTime('today');
        if (!$hasta || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = $hoy->format('Y-m-d');
        }
        if (!$desde || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $dt = DateTime::createFromFormat('Y-m-d', $hasta) ?: $hoy;
            $desde = $dt->modify('first day of this month')->format('Y-m-d');
        }
        if ($desde > $hasta) {
            $tmp = $desde;
            $desde = $hasta;
            $hasta = $tmp;
        }
        return [$desde, $hasta];
    }

    /**
     * @param int $id_sucursal
     * @param string $desde Y-m-d
     * @param string $hasta Y-m-d
     */
    public function total_ingresos($id_sucursal, $desde, $hasta)
    {
        $sql = "
            SELECT COALESCE(SUM(v.total), 0) AS total
            FROM ventas v
            WHERE v.id_sucursal = ?
              AND v.estado = 'completada'
              AND v.fecha_registro >= ?
              AND v.fecha_registro <= ?
        ";
        $row = $this->db->query($sql, [
            (int) $id_sucursal,
            $desde . ' 00:00:00',
            $hasta . ' 23:59:59',
        ])->row();
        return (float) ($row ? $row->total : 0);
    }

    /**
     * @param int $id_sucursal
     * @param string $desde Y-m-d
     * @param string $hasta Y-m-d
     */
    public function total_egresos_compras($id_sucursal, $desde, $hasta)
    {
        $sql = "
            SELECT COALESCE(SUM(c.total), 0) AS total
            FROM compras c
            WHERE c.id_sucursal = ?
              AND c.fecha_registro >= ?
              AND c.fecha_registro <= ?
        ";
        $row = $this->db->query($sql, [
            (int) $id_sucursal,
            $desde . ' 00:00:00',
            $hasta . ' 23:59:59',
        ])->row();
        return (float) ($row ? $row->total : 0);
    }

    /**
     * Gastos operativos (no compras de inventario).
     *
     * @param int $id_sucursal
     * @param string $desde Y-m-d
     * @param string $hasta Y-m-d
     */
    public function total_egresos_gastos_operativos($id_sucursal, $desde, $hasta)
    {
        $sql = "
            SELECT COALESCE(SUM(g.monto), 0) AS total
            FROM gastos g
            WHERE g.id_sucursal = ?
              AND g.fecha_gasto >= ?
              AND g.fecha_gasto <= ?
        ";
        $row = $this->db->query($sql, [
            (int) $id_sucursal,
            $desde,
            $hasta,
        ])->row();
        return (float) ($row ? $row->total : 0);
    }

    /**
     * Serie diaria: ingresos y egresos por día (para gráfico comparativo).
     *
     * @return array<int,array{d:string,ingresos:float,egresos:float}>
     */
    public function serie_diaria_ingresos_egresos($id_sucursal, $desde, $hasta)
    {
        $inicio = $desde . ' 00:00:00';
        $fin    = $hasta . ' 23:59:59';

        $qV = "
            SELECT DATE(v.fecha_registro) AS d, SUM(v.total) AS ingresos
            FROM ventas v
            WHERE v.id_sucursal = ?
              AND v.estado = 'completada'
              AND v.fecha_registro >= ? AND v.fecha_registro <= ?
            GROUP BY DATE(v.fecha_registro)
        ";
        $qC = "
            SELECT DATE(c.fecha_registro) AS d, SUM(c.total) AS egresos
            FROM compras c
            WHERE c.id_sucursal = ?
              AND c.fecha_registro >= ? AND c.fecha_registro <= ?
            GROUP BY DATE(c.fecha_registro)
        ";
        $qG = "
            SELECT g.fecha_gasto AS d, SUM(g.monto) AS egresos_gasto
            FROM gastos g
            WHERE g.id_sucursal = ?
              AND g.fecha_gasto >= ? AND g.fecha_gasto <= ?
            GROUP BY g.fecha_gasto
        ";

        $ventas  = $this->db->query($qV, [(int) $id_sucursal, $inicio, $fin])->result();
        $compras = $this->db->query($qC, [(int) $id_sucursal, $inicio, $fin])->result();
        $gastosD = $this->db->query($qG, [(int) $id_sucursal, $desde, $hasta])->result();

        $map = [];
        foreach ($ventas as $r) {
            $map[$r->d] = ['d' => $r->d, 'ingresos' => (float) $r->ingresos, 'egresos' => 0.0];
        }
        foreach ($compras as $r) {
            if (!isset($map[$r->d])) {
                $map[$r->d] = ['d' => $r->d, 'ingresos' => 0.0, 'egresos' => 0.0];
            }
            $map[$r->d]['egresos'] += (float) $r->egresos;
        }
        foreach ($gastosD as $r) {
            if (!isset($map[$r->d])) {
                $map[$r->d] = ['d' => $r->d, 'ingresos' => 0.0, 'egresos' => 0.0];
            }
            $map[$r->d]['egresos'] += (float) $r->egresos_gasto;
        }
        ksort($map);

        // Una fila por cada día del rango (ceros si no hubo movimiento): el gráfico refleja el filtro completo.
        $dtDesde = DateTime::createFromFormat('Y-m-d', $desde);
        $dtHasta = DateTime::createFromFormat('Y-m-d', $hasta);
        if (!$dtDesde || !$dtHasta) {
            return array_values($map);
        }
        $numDias = (int) $dtDesde->diff($dtHasta)->format('%a') + 1;
        if ($numDias < 1 || $numDias > 400) {
            return array_values($map);
        }

        $filled = [];
        $cursor = clone $dtDesde;
        for ($i = 0; $i < $numDias; $i++) {
            $d = $cursor->format('Y-m-d');
            if (isset($map[$d])) {
                $filled[] = $map[$d];
            } else {
                $filled[] = ['d' => $d, 'ingresos' => 0.0, 'egresos' => 0.0];
            }
            $cursor->modify('+1 day');
        }
        return $filled;
    }

    /**
     * Egresos (compras) agrupados por categoría de producto — gráfico circular.
     *
     * @return array<int,object{categoria:string,total:float}>
     */
    public function egresos_por_categoria($id_sucursal, $desde, $hasta)
    {
        $sql = "
            SELECT
                COALESCE(cat.nombre, NULLIF(TRIM(p.categoria), ''), 'Sin categoría') AS categoria,
                SUM(cd.subtotal) AS total
            FROM compras c
            INNER JOIN compra_detalle cd ON cd.id_compra = c.id
            LEFT JOIN productos p ON p.id = cd.id_producto
            LEFT JOIN categorias cat ON cat.id = p.id_categoria
            WHERE c.id_sucursal = ?
              AND c.fecha_registro >= ?
              AND c.fecha_registro <= ?
            GROUP BY COALESCE(cat.nombre, NULLIF(TRIM(p.categoria), ''), 'Sin categoría')
            HAVING total > 0
            ORDER BY total DESC
        ";
        return $this->db->query($sql, [
            (int) $id_sucursal,
            $desde . ' 00:00:00',
            $hasta . ' 23:59:59',
        ])->result();
    }

    /**
     * Gastos operativos agrupados por categoría (gráfico circular).
     *
     * @return array<int,object{categoria:string,total:float}>
     */
    public function gastos_operativos_por_categoria($id_sucursal, $desde, $hasta)
    {
        $sql = "
            SELECT cg.nombre AS categoria, SUM(g.monto) AS total
            FROM gastos g
            INNER JOIN categorias_gasto cg ON cg.id = g.id_categoria_gasto
            WHERE g.id_sucursal = ?
              AND g.fecha_gasto >= ?
              AND g.fecha_gasto <= ?
            GROUP BY cg.id, cg.nombre
            HAVING total > 0
            ORDER BY total DESC
        ";
        return $this->db->query($sql, [
            (int) $id_sucursal,
            $desde,
            $hasta,
        ])->result();
    }

    /**
     * Totales mensuales dentro del rango (evolución mensual).
     *
     * @return array<string,array{ingresos:float,egresos:float}> clave YYYY-MM
     */
    public function serie_mensual_ingresos_egresos($id_sucursal, $desde, $hasta)
    {
        $inicio = $desde . ' 00:00:00';
        $fin    = $hasta . ' 23:59:59';

        $qV = "
            SELECT DATE_FORMAT(v.fecha_registro, '%Y-%m') AS ym, SUM(v.total) AS t
            FROM ventas v
            WHERE v.id_sucursal = ?
              AND v.estado = 'completada'
              AND v.fecha_registro >= ? AND v.fecha_registro <= ?
            GROUP BY ym
        ";
        $qC = "
            SELECT DATE_FORMAT(c.fecha_registro, '%Y-%m') AS ym, SUM(c.total) AS t
            FROM compras c
            WHERE c.id_sucursal = ?
              AND c.fecha_registro >= ? AND c.fecha_registro <= ?
            GROUP BY ym
        ";
        $qG = "
            SELECT DATE_FORMAT(g.fecha_gasto, '%Y-%m') AS ym, SUM(g.monto) AS t
            FROM gastos g
            WHERE g.id_sucursal = ?
              AND g.fecha_gasto >= ? AND g.fecha_gasto <= ?
            GROUP BY ym
        ";

        $mv = $this->db->query($qV, [(int) $id_sucursal, $inicio, $fin])->result();
        $mc = $this->db->query($qC, [(int) $id_sucursal, $inicio, $fin])->result();
        $mg = $this->db->query($qG, [(int) $id_sucursal, $desde, $hasta])->result();

        $merged = [];
        foreach ($mv as $r) {
            $merged[$r->ym] = ['ingresos' => (float) $r->t, 'egresos' => 0.0];
        }
        foreach ($mc as $r) {
            if (!isset($merged[$r->ym])) {
                $merged[$r->ym] = ['ingresos' => 0.0, 'egresos' => 0.0];
            }
            $merged[$r->ym]['egresos'] += (float) $r->t;
        }
        foreach ($mg as $r) {
            if (!isset($merged[$r->ym])) {
                $merged[$r->ym] = ['ingresos' => 0.0, 'egresos' => 0.0];
            }
            $merged[$r->ym]['egresos'] += (float) $r->t;
        }
        ksort($merged);
        return $merged;
    }

    /**
     * Detalle tabular para exportaciones (opcional).
     *
     * @return array{ventas:array,compras:array,gastos:array}
     */
    public function detalle_movimientos($id_sucursal, $desde, $hasta)
    {
        $inicio = $desde . ' 00:00:00';
        $fin    = $hasta . ' 23:59:59';

        $ventas = $this->db->query("
            SELECT v.id, v.fecha_registro, v.total, v.metodo_pago
            FROM ventas v
            WHERE v.id_sucursal = ?
              AND v.estado = 'completada'
              AND v.fecha_registro >= ? AND v.fecha_registro <= ?
            ORDER BY v.fecha_registro
        ", [(int) $id_sucursal, $inicio, $fin])->result();

        $compras = $this->db->query("
            SELECT c.id, c.fecha_registro, c.total, c.proveedor
            FROM compras c
            WHERE c.id_sucursal = ?
              AND c.fecha_registro >= ? AND c.fecha_registro <= ?
            ORDER BY c.fecha_registro
        ", [(int) $id_sucursal, $inicio, $fin])->result();

        $gastos = $this->db->query("
            SELECT g.id, g.fecha_gasto, g.concepto, g.monto, g.observaciones, cg.nombre AS categoria
            FROM gastos g
            INNER JOIN categorias_gasto cg ON cg.id = g.id_categoria_gasto
            WHERE g.id_sucursal = ?
              AND g.fecha_gasto >= ? AND g.fecha_gasto <= ?
            ORDER BY g.fecha_gasto, g.id
        ", [(int) $id_sucursal, $desde, $hasta])->result();

        return ['ventas' => $ventas, 'compras' => $compras, 'gastos' => $gastos];
    }
}
