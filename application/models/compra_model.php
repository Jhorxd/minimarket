<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compra_model extends CI_Model {

    // Listar compras con proveedor y usuario
    public function listar_compras($id_sucursal)
    {
        return $this->db->query("
            SELECT  c.id,
                    c.fecha_registro,
                    c.total,
                    c.proveedor,
                    pr.razon_social   AS proveedor_razon,
                    u.nombre          AS usuario
            FROM compras c
            LEFT JOIN proveedores pr
                ON pr.id_proveedor = c.id_proveedor
            JOIN usuarios u
                ON u.id = c.id_usuario
            WHERE c.id_sucursal = ?
            ORDER BY c.fecha_registro DESC
        ", [$id_sucursal])->result();
    }

    // Proveedores activos (estado=1)
    public function get_proveedores_activos()
    {
        return $this->db->order_by('razon_social', 'ASC')
                        ->get_where('proveedores', ['estado' => 1])
                        ->result();
    }

    // Productos de la sucursal
    public function get_productos_sucursal($id_sucursal)
    {
        return $this->db->order_by('nombre', 'ASC')
                        ->get_where('productos', ['id_sucursal' => $id_sucursal])
                        ->result();
    }

    // Cabecera + detalle de una compra
    public function get_compra_con_detalle($id_compra)
    {
        $compra = $this->db->query("
            SELECT  c.*,
                    pr.razon_social,
                    pr.nro_documento,
                    pr.tipo_documento
            FROM compras c
            LEFT JOIN proveedores pr
                ON pr.id_proveedor = c.id_proveedor
            WHERE c.id = ?
        ", [$id_compra])->row();

        $detalles = $this->db->query("
            SELECT  cd.*,
                    p.nombre,
                    p.codigo_barras
            FROM compra_detalle cd
            JOIN productos p
                ON p.id = cd.id_producto
            WHERE cd.id_compra = ?
        ", [$id_compra])->result();

        return [$compra, $detalles];
    }

    /**
     * Registrar compra:
     * - Inserta en compras y compra_detalle
     * - Actualiza productos.stock (Entrada)
     * - Registra movimiento en kardex (Entrada / Compra)
     *
     * $items = [
     *   ['id_producto' => 1, 'cantidad' => 10.5, 'precio_compra' => 2.50],
     *   ...
     * ]
     */
    public function registrar_compra($id_sucursal, $id_usuario, $id_proveedor, $proveedor_texto, $items)
    {
        $this->db->trans_start();

        // Total de la compra
        $total = 0;
        foreach ($items as $it) {
            $total += $it['cantidad'] * $it['precio_compra'];
        }

        // 1) Cabecera
        $this->db->insert('compras', [
            'id_sucursal'    => $id_sucursal,
            'id_usuario'     => $id_usuario,
            'id_proveedor'   => $id_proveedor ?: null,
            'proveedor'      => $proveedor_texto,
            'total'          => $total,
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);

        $id_compra = $this->db->insert_id();

        // 2) Detalle + stock + kardex
        foreach ($items as $it) {
            $id_producto   = (int)   $it['id_producto'];
            $cantidad      = (float) $it['cantidad'];
            $precio_compra = (float) $it['precio_compra'];
            $subtotal      = $cantidad * $precio_compra;

            // Detalle
            $this->db->insert('compra_detalle', [
                'id_compra'     => $id_compra,
                'id_producto'   => $id_producto,
                'cantidad'      => $cantidad,
                'precio_compra' => $precio_compra,
                'subtotal'      => $subtotal,
            ]);

            // Actualizar stock (Entrada)
            $this->db->query("
                UPDATE productos
                SET stock = stock + ?
                WHERE id = ? AND id_sucursal = ?
            ", [$cantidad, $id_producto, $id_sucursal]);

            // Stock resultante
            $producto = $this->db->get_where('productos', [
                'id'          => $id_producto,
                'id_sucursal' => $id_sucursal
            ])->row();

            // Kardex
          $this->db->insert('kardex', [
                'id_sucursal'      => $id_sucursal,
                'id_producto'      => $id_producto,
                'tipo_movimiento'  => 'Entrada',
                'motivo'           => 'Compra',
                'doc_tipo'         => 'Compra',
                'doc_id'           => $id_compra,
                'cantidad'         => $cantidad,
                'stock_resultante' => $producto ? $producto->stock : 0,
                'fecha'            => date('Y-m-d H:i:s'),
            ]);

        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $id_compra : false;
    }
}
