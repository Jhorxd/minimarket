<?php
// $proveedores, $productosIniciales (opcional)
?>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Compras
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Nueva Compra
                </h1>
            </div>
        </header>

        <form action="<?= base_url('compras/guardar'); ?>" method="post" id="form-compra" class="space-y-6">

            <!-- PROVEEDOR -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Datos del proveedor
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Proveedor (maestro)
                        </label>
                        <select name="id_proveedor"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p->id_proveedor; ?>">
                                    <?= htmlspecialchars($p->razon_social); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Proveedor (texto en documento)
                        </label>
                        <input type="text" name="proveedor_texto"
                               placeholder="Se imprimirá en el comprobante"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- BUSCADOR + CARRITO -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">

                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Productos de la compra
                </h2>

                <!-- Buscador de productos -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Buscar producto
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               id="busqueda-producto"
                               placeholder="Nombre o código de barras..."
                               class="w-full pl-9 pr-3 py-2.5 bg-slate-100 border-none rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Lista de resultados -->
                    <div id="lista-resultados"
                         class="mt-2 bg-white border border-slate-200 rounded-xl max-h-56 overflow-y-auto hidden">
                        <!-- se llena por JS -->
                    </div>
                </div>

                <!-- Tabla de ítems de compra -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[720px]">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                                <th class="px-4 py-3 font-bold">Producto</th>
                                <th class="px-4 py-3 font-bold">Cantidad</th>
                                <th class="px-4 py-3 font-bold">Precio compra</th>
                                <th class="px-4 py-3 font-bold text-right">Subtotal</th>
                                <th class="px-4 py-3 font-bold text-right">Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-items" class="divide-y divide-slate-100">
                            <!-- filas agregadas por JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="mt-4 flex justify-end">
                    <div class="text-right">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">
                            Total compra
                        </div>
                        <div id="total-compra" class="text-2xl font-black text-slate-900">
                            S/ 0.00
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <a href="<?= base_url('compras/compra_index'); ?>"
                   class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-100">
                    Guardar Compra
                </button>
            </div>

        </form>

    </div>
</div>

<script>
(function() {
    const inputBusqueda  = document.getElementById('busqueda-producto');
    const listaResultados = document.getElementById('lista-resultados');
    const tbodyItems     = document.getElementById('tbody-items');
    const totalCompraEl  = document.getElementById('total-compra');

    let timeout = null;

    // Buscar productos por AJAX (puedes cambiar la URL si usas otra)
    inputBusqueda.addEventListener('input', function() {
        const term = this.value.trim();
        clearTimeout(timeout);

        if (term.length < 2) {
            listaResultados.innerHTML = '';
            listaResultados.classList.add('hidden');
            return;
        }

        timeout = setTimeout(() => {
            const url = '<?= base_url('ventas/buscar_productos_ajax'); ?>?term=' + encodeURIComponent(term);
            fetch(url)
                .then(r => r.json())
                .then(data => renderResultados(data))
                .catch(() => {
                    listaResultados.innerHTML = '<div class="px-3 py-2 text-xs text-red-500">Error al buscar</div>';
                    listaResultados.classList.remove('hidden');
                });
        }, 300);
    });

    function renderResultados(productos) {
        if (!productos.length) {
            listaResultados.innerHTML = '<div class="px-3 py-2 text-xs text-slate-400">Sin resultados</div>';
            listaResultados.classList.remove('hidden');
            return;
        }

        listaResultados.innerHTML = '';
        productos.forEach(p => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'w-full text-left px-3 py-2 text-xs hover:bg-slate-50 flex justify-between items-center';
            row.innerHTML = `
                <div>
                    <div class="font-semibold text-slate-800">${p.nombre}</div>
                    <div class="text-[10px] text-slate-400">${p.codigo_barras || ''}</div>
                </div>
                <div class="text-[11px] font-bold text-blue-600">S/ ${parseFloat(p.precio_compra || p.precio_venta).toFixed(2)}</div>
            `;
            row.addEventListener('click', () => {
                agregarItem(p);
                listaResultados.classList.add('hidden');
                inputBusqueda.value = '';
            });
            listaResultados.appendChild(row);
        });

        listaResultados.classList.remove('hidden');
    }

    function agregarItem(p) {
        const tr = document.createElement('tr');
        const precio = parseFloat(p.precio_compra || p.precio_venta || 0).toFixed(2);

        tr.innerHTML = `
            <td class="px-4 py-2">
                <input type="hidden" name="id_producto[]" value="${p.id}">
                <div class="text-sm font-semibold text-slate-800">${p.nombre}</div>
                <div class="text-[10px] text-slate-400">${p.codigo_barras || ''}</div>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="cantidad[]" value="1" step="0.01" min="0.01"
                       class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm cantidad-input">
            </td>
            <td class="px-4 py-2">
                <input type="number" name="precio_compra[]" value="${precio}" step="0.01" min="0"
                       class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm precio-input">
            </td>
            <td class="px-4 py-2 text-right">
                <span class="text-sm text-slate-700 subtotal-item">S/ ${precio}</span>
            </td>
            <td class="px-4 py-2 text-right">
                <button type="button" class="text-slate-400 hover:text-red-600 text-xs btn-quitar">
                    <i class="fas fa-times-circle"></i>
                </button>
            </td>
        `;

        tbodyItems.appendChild(tr);
        recalcular();
    }

    // Delegación de eventos para inputs y botones de quitar
    tbodyItems.addEventListener('input', function(e) {
        if (!e.target.classList.contains('cantidad-input') &&
            !e.target.classList.contains('precio-input')) return;
        recalcular();
    });

    tbodyItems.addEventListener('click', function(e) {
        if (e.target.closest('.btn-quitar')) {
            e.target.closest('tr').remove();
            recalcular();
        }
    });

    function recalcular() {
        let total = 0;
        tbodyItems.querySelectorAll('tr').forEach(tr => {
            const cantidad = parseFloat(tr.querySelector('.cantidad-input').value) || 0;
            const precio   = parseFloat(tr.querySelector('.precio-input').value) || 0;
            const subtotal = cantidad * precio;
            tr.querySelector('.subtotal-item').textContent = 'S/ ' + subtotal.toFixed(2);
            total += subtotal;
        });
        totalCompraEl.textContent = 'S/ ' + total.toFixed(2);
    }
})();
</script>
