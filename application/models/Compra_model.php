<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Compra_model extends CI_Model {

    // Listar compras con proveedor y usuario
    public function listar_compras($id_sucursal, $desde = null, $hasta = null)
    {
        $where = "WHERE c.id_sucursal = ?";
        $params = [$id_sucursal];

        if ($desde && $hasta) {
            $where .= " AND DATE(c.fecha_registro) BETWEEN ? AND ?";
            $params[] = $desde;
            $params[] = $hasta;
        }

        return $this->db->query("
            SELECT  c.id,
                    c.fecha_registro,
                    c.total,
                    c.proveedor,
                    c.estado,
                    pr.razon_social   AS proveedor_razon,
                    u.nombre          AS usuario
            FROM compras c
            LEFT JOIN proveedores pr
                ON pr.id_proveedor = c.id_proveedor
            JOIN usuarios u
                ON u.id = c.id_usuario
            $where
            ORDER BY c.fecha_registro DESC
        ", $params)->result();
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

    public function registrar_compra($id_sucursal, $id_usuario, $id_proveedor, $proveedor_texto, $items, $estado = 'completada')
    {
        $this->db->trans_start();

        $total = 0;
        foreach ($items as $it) {
            $total += $it['cantidad'] * $it['precio_compra'];
        }

        $id_compra = $this->db->insert('compras', [
            'id_sucursal'    => $id_sucursal,
            'id_usuario'     => $id_usuario,
            'id_proveedor'   => $id_proveedor ?: null,
            'proveedor'      => $proveedor_texto,
            'total'          => $total,
            'estado'         => $estado,
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]) ? $this->db->insert_id() : false;

        if (!$id_compra) {
            $this->db->trans_rollback();
            return false;
        }

        foreach ($items as $it) {
            $cantidad      = (float) $it['cantidad'];
            $precio_compra = (float) $it['precio_compra'];
            
            $this->db->insert('compra_detalle', [
                'id_compra'     => $id_compra,
                'id_producto'   => (int)$it['id_producto'],
                'cantidad'      => $cantidad,
                'precio_compra' => $precio_compra,
                'subtotal'      => $cantidad * $precio_compra,
                'talla'         => $it['talla'] ?? null,
                'color'         => $it['color'] ?? null,
                'diseno'        => $it['diseno'] ?? null,
            ]);

            // Solo actualizar stock si NO es borrador
            if ($estado === 'completada') {
                $this->_procesar_stock_item($id_compra, $id_sucursal, (int)$it['id_producto'], $cantidad);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id_compra : false;
    }

    /**
     * Actualiza un borrador existente reemplazando sus items
     */
    public function actualizar_borrador($id_compra, $id_proveedor, $proveedor_texto, $items, $estado = 'borrador')
    {
        $this->db->trans_start();

        $total = 0;
        foreach ($items as $it) { $total += $it['cantidad'] * $it['precio_compra']; }

        // 1) Actualizar cabecera
        $this->db->where('id', $id_compra);
        $this->db->update('compras', [
            'id_proveedor'   => $id_proveedor ?: null,
            'proveedor'      => $proveedor_texto,
            'total'          => $total,
            'estado'         => $estado
        ]);

        // 2) Reemplazar detalle (Borrar y re-insertar items)
        $this->db->delete('compra_detalle', ['id_compra' => $id_compra]);
        
        foreach ($items as $it) {
            $cantidad      = (float) $it['cantidad'];
            $precio_compra = (float) $it['precio_compra'];
            $id_producto   = (int)$it['id_producto'];

            $this->db->insert('compra_detalle', [
                'id_compra'     => $id_compra,
                'id_producto'   => $id_producto,
                'cantidad'      => $cantidad,
                'precio_compra' => $precio_compra,
                'subtotal'      => $cantidad * $precio_compra,
                'talla'         => $it['talla'] ?? null,
                'color'         => $it['color'] ?? null,
                'diseno'        => $it['diseno'] ?? null,
            ]);

            // Si al actualizar pasamos a 'completada' (botón ejecutar desde form), procesamos stock
            if ($estado === 'completada') {
                $this->_procesar_stock_item($id_compra, $this->session->userdata('id_sucursal'), $id_producto, $cantidad);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Ejecuta una compra que estaba en estado borrador (Carga stock y kardex)
     */
    public function ejecutar_compra($id_compra)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        list($compra, $detalles) = $this->get_compra_con_detalle($id_compra);

        if (!$compra || $compra->estado !== 'borrador') return false;

        $this->db->trans_start();

        foreach ($detalles as $det) {
            $this->_procesar_stock_item($id_compra, $id_sucursal, $det->id_producto, $det->cantidad);
        }

        $this->db->where('id', $id_compra);
        $this->db->update('compras', ['estado' => 'completada']);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Helper privado para centralizar la actualización de stock y Kardex
     */
    private function _procesar_stock_item($id_compra, $id_sucursal, $id_producto, $cantidad)
    {
        // 1) Incrementar stock
        $this->db->query("UPDATE productos SET stock = stock + ? WHERE id = ? AND id_sucursal = ?", 
            [$cantidad, $id_producto, $id_sucursal]);

        // 2) Obtener stock resultante para el historial
        $producto = $this->db->get_where('productos', ['id' => $id_producto, 'id_sucursal' => $id_sucursal])->row();

        // 3) Registrar en Kardex
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

    public function anular_compra($id_compra, $motivo)
    {
        $id_sucursal = $this->session->userdata('id_sucursal');
        $compra = $this->db->get_where('compras', ['id' => $id_compra, 'id_sucursal' => $id_sucursal])->row();
        
        if (!$compra || $compra->estado === 'anulada') {
            return false;
        }

        $this->db->trans_start();

        // 1) Actualizar cabecera
        $this->db->where('id', $id_compra);
        $this->db->update('compras', [
            'estado' => 'anulada',
            'motivo_anulacion' => $motivo
        ]);

        // 2) Revertir stock (Salida) del detalle
        $detalles = $this->db->get_where('compra_detalle', ['id_compra' => $id_compra])->result();
        
        foreach ($detalles as $det) {
            // Restar stock (Salida)
            $this->db->query("
                UPDATE productos 
                SET stock = stock - ? 
                WHERE id = ? AND id_sucursal = ?
            ", [$det->cantidad, $det->id_producto, $id_sucursal]);

            // Stock resultante
            $producto = $this->db->get_where('productos', [
                'id' => $det->id_producto, 
                'id_sucursal' => $id_sucursal
            ])->row();

            // Kardex (Salida por Anulación)
            $this->db->insert('kardex', [
                'id_sucursal'      => $id_sucursal,
                'id_producto'      => $det->id_producto,
                'tipo_movimiento'  => 'Salida',
                'motivo'           => 'Ajuste', // Podría ser 'Anulacion' si fuera soportado
                'doc_tipo'         => 'Compra',
                'doc_id'           => $id_compra,
                'cantidad'         => $det->cantidad,
                'stock_resultante' => $producto ? $producto->stock : 0,
                'fecha'            => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
