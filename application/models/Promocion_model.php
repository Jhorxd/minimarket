<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promocion_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Obtener las promociones activas
    public function get_activas() {
        $this->db->where('activa', 1);
        return $this->db->get('promociones')->result();
    }

    /**
     * Aplica la lógica de promociones a un carrito agrupado por categoría.
     * Retorna los items divididos en unidades y promociones.
     */
    public function aplicar_promociones($carrito) {
        $promociones = $this->get_activas();
        $items_procesados = [];
        
        $carrito_con_categorias = [];
        foreach ($carrito as $item) {
            $producto = $this->db->select('id_categoria, talla, color, diseno')->from('productos')->where('id', $item['id'])->get()->row();
            if ($producto) {
                $carrito_con_categorias[] = [
                    'id_producto' => $item['id'],
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_base' => $item['precio'],
                    'id_categoria' => $producto->id_categoria,
                    'talla' => $producto->talla,
                    'color' => $producto->color,
                    'diseno' => $producto->diseno
                ];
            }
        }

        $items_restantes = $carrito_con_categorias;
        
        foreach ($promociones as $promo) {
            // 1. Promociones por CATEGORIA
            if (!empty($promo->id_categoria)) {
                $id_cat = $promo->id_categoria;
                $cant_req = (int) $promo->cantidad_requerida;
                
                $cantidad_total_cat = 0;
                foreach ($items_restantes as $item) {
                    if ($item['id_categoria'] == $id_cat) {
                        $cantidad_total_cat += $item['cantidad'];
                    }
                }
                
                if ($cantidad_total_cat >= $cant_req) {
                    $paquetes = floor($cantidad_total_cat / $cant_req);
                    $cant_en_promo = $paquetes * $cant_req;
                    $monto_promo_unitario = ($paquetes * $promo->precio_combo) / $cant_en_promo;
                    
                    $cant_a_descontar = $cant_en_promo;
                    foreach ($items_restantes as $idx => $item) {
                        if ($item['id_categoria'] == $id_cat && $item['cantidad'] > 0 && $cant_a_descontar > 0) {
                            $tomar = min($item['cantidad'], $cant_a_descontar);
                            
                            $items_procesados[] = [
                                'id_producto' => $item['id_producto'],
                                'nombre' => $item['nombre'] . ' (Promo)',
                                'cantidad' => $tomar,
                                'precio_unitario' => $monto_promo_unitario,
                                'subtotal' => $tomar * $monto_promo_unitario,
                                'tipo_venta' => 'promocion',
                                'talla' => $item['talla'],
                                'color' => $item['color'],
                                'diseno' => $item['diseno']
                            ];
                            
                            $items_restantes[$idx]['cantidad'] -= $tomar;
                            $cant_a_descontar -= $tomar;
                        }
                    }
                }
            }
            
            // 2. Promociones por PRODUCTO
            if (!empty($promo->id_producto)) {
                $id_prod = $promo->id_producto;
                $cant_req = (int) $promo->cantidad_requerida;
                foreach ($items_restantes as $idx => $item) {
                    if ($item['id_producto'] == $id_prod && $item['cantidad'] >= $cant_req) {
                        $paquetes = floor($item['cantidad'] / $cant_req);
                        $cant_en_promo = $paquetes * $cant_req;
                        $monto_promo_unitario = ($paquetes * $promo->precio_combo) / $cant_en_promo;
                        
                        $items_procesados[] = [
                            'id_producto' => $item['id_producto'],
                            'nombre' => $item['nombre'] . ' (Promo)',
                            'cantidad' => $cant_en_promo,
                            'precio_unitario' => $monto_promo_unitario,
                            'subtotal' => $cant_en_promo * $monto_promo_unitario,
                            'tipo_venta' => 'promocion',
                            'talla' => $item['talla'],
                            'color' => $item['color'],
                            'diseno' => $item['diseno']
                        ];
                        
                        $items_restantes[$idx]['cantidad'] -= $cant_en_promo;
                    }
                }
            }
        }
        
        // 3. Agregar los ítems que NO entraron en promoción
        foreach ($items_restantes as $item) {
            if ($item['cantidad'] > 0) {
                $items_procesados[] = [
                    'id_producto' => $item['id_producto'],
                    'nombre' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_base'],
                    'subtotal' => $item['cantidad'] * $item['precio_base'],
                    'tipo_venta' => 'unidad',
                    'talla' => $item['talla'],
                    'color' => $item['color'],
                    'diseno' => $item['diseno']
                ];
            }
        }
        
        return $items_procesados;
    }
}
