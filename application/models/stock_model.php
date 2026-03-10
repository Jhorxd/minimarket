<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model {

    // Listado de productos con stock por sucursal
    public function get_stock_sucursal($id_sucursal)
    {
        return $this->db->query("
            SELECT p.id,
                   p.codigo_barras,
                   p.nombre,
                   p.categoria,
                   p.precio_compra,
                   p.precio_venta,
                   p.stock,
                   p.stock_minimo
            FROM productos p
            WHERE p.id_sucursal = ?
            ORDER BY p.nombre ASC
        ", [$id_sucursal])->result();
    }

    public function get_producto($id_producto, $id_sucursal)
    {
        return $this->db->get_where('productos', [
            'id'          => $id_producto,
            'id_sucursal' => $id_sucursal
        ])->row();
    }

    /**
     * Ajuste manual de stock (Entrada/Salida) con registro en kardex
     * $tipo_movimiento: 'Entrada' o 'Salida'
     */
    public function ajustar_stock($id_producto, $id_sucursal, $tipo_movimiento, $cantidad, $motivo = 'Ajuste')
    {
        $this->db->trans_start();

        // Signo de la cantidad
        $delta = ($tipo_movimiento === 'Entrada') ? $cantidad : -$cantidad;

        // Actualizar stock en productos
        $this->db->query("
            UPDATE productos
            SET stock = stock + ?
            WHERE id = ? AND id_sucursal = ?
        ", [$delta, $id_producto, $id_sucursal]);

        // Obtener stock resultante
        $producto = $this->db->get_where('productos', [
            'id'          => $id_producto,
            'id_sucursal' => $id_sucursal
        ])->row();

        // Registrar en kardex
        $this->db->insert('kardex', [
            'id_sucursal'      => $id_sucursal,
            'id_producto'      => $id_producto,
            'tipo_movimiento'  => $tipo_movimiento,         // 'Entrada' o 'Salida'
            'motivo'           => $motivo,                  // 'Ajuste','Venta','Compra','Traslado'
            'cantidad'         => $cantidad,
            'stock_resultante' => $producto ? $producto->stock : 0,
            'fecha'            => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    // Historial de kardex por producto
public function get_kardex_producto($id_producto, $id_sucursal)
{
    // Nota: LEFT JOIN a ventas y compras, según doc_tipo/doc_id
    return $this->db->query("
        SELECT  k.*,
                -- Código interno de referencia
                CASE 
                    WHEN k.doc_tipo = 'Venta'  THEN CONCAT('NV-', LPAD(v.id, 6, '0'))
                    WHEN k.doc_tipo = 'Compra' THEN CONCAT('NC-', LPAD(co.id, 6, '0'))
                    ELSE ''
                END AS documento_ref,
                -- Nombre del cliente o proveedor
                CASE 
                    WHEN k.doc_tipo = 'Venta' 
                        THEN IFNULL(cli.nombre, 'Cliente Mostrador')
                    WHEN k.doc_tipo = 'Compra' 
                        THEN IFNULL(prov.razon_social, k.motivo)
                    ELSE k.motivo
                END AS tercero_nombre
        FROM kardex k
        LEFT JOIN ventas v
               ON v.id_cliente = k.doc_id AND k.doc_tipo = 'Venta'
        LEFT JOIN clientes cli
               ON cli.id_cliente = v.id_cliente
        LEFT JOIN compras co
               ON co.id_proveedor = k.doc_id AND k.doc_tipo = 'Compra'
        LEFT JOIN proveedores prov
               ON prov.id_proveedor = co.id_proveedor
        WHERE k.id_producto = ?
          AND k.id_sucursal = ?
        ORDER BY k.fecha DESC, k.id DESC
    ", [$id_producto, $id_sucursal])->result();
}

}
