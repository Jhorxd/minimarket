<?php
// $proveedores, $productosIniciales (opcional)
?>

<div x-data="compraUI()" class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Compras
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    <?= isset($compra) ? 'Editar Borrador' : 'Nueva Compra' ?>
                </h1>
            </div>
        </header>

        <form action="<?= base_url('compras/guardar'); ?>" method="post" id="form-compra" class="space-y-6">

            <!-- PROVEEDOR -->
            <?php if (isset($compra)): ?>
                <input type="hidden" name="id_compra" value="<?= $compra->id ?>">
            <?php endif; ?>
            <input type="hidden" name="accion" id="input-accion" value="ejecutar">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Datos del proveedor
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative" @click.away="showDropdownProv = false">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 flex justify-between">
                            Proveedor
                            <button type="button" @click="modalNuevoProv = true" class="text-blue-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-plus"></i> Nuevo</button>
                        </label>
                        <input type="hidden" name="id_proveedor" :value="idProveedor">
                        <input type="text" x-model="searchProv" @focus="showDropdownProv = true; fetchProv()" @input="onInputProv()" placeholder="Buscar proveedor por razón social o RUC..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        
                        <div x-show="showDropdownProv" style="display: none;"
                             class="absolute top-full left-0 w-full mt-1 bg-white border border-slate-200 shadow-xl rounded-lg z-50 max-h-48 overflow-y-auto">
                            <template x-for="p in proveedoresResult" :key="p.id_proveedor">
                                <div @click="seleccionarProv(p)" 
                                     class="px-3 py-2 text-sm text-slate-700 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0"
                                     :class="idProveedor == p.id_proveedor ? 'bg-blue-50 font-bold' : ''">
                                    <div x-text="p.razon_social"></div>
                                    <div x-show="p.nro_documento" class="text-[10px] text-slate-400" x-text="(p.tipo_documento || 'DOC') + ': ' + p.nro_documento"></div>
                                </div>
                            </template>
                            <div x-show="proveedoresResult.length === 0" class="px-3 py-2 text-sm text-slate-500 italic">
                                No se encontraron proveedores
                            </div>
                        </div>
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

                <a href="<?= base_url('compras/compras_index'); ?>"
                   class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-sm font-bold hover:bg-slate-50 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <button type="submit" @click="document.getElementById('input-accion').value='borrador'"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold shadow-lg shadow-slate-200 transition-all active:scale-95">
                    <i class="fas fa-save mr-2 text-slate-400"></i> Guardar Borrador
                </button>
                <button type="submit" @click="document.getElementById('input-accion').value='ejecutar'"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-lg shadow-emerald-200 transition-all active:scale-95">
                    <i class="fas fa-check-double mr-2"></i> Ejecutar e Ingresar Stock
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Modal Nuevo Proveedor -->
<div x-show="modalNuevoProv" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto w-full h-full">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalNuevoProv" class="fixed inset-0 bg-slate-900 bg-opacity-75" @click="modalNuevoProv = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div x-show="modalNuevoProv" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                <h3 class="text-lg leading-6 font-black text-slate-800">Agregar Nuevo Proveedor</h3>
            </div>
            <div class="px-4 py-5 sm:p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        <span x-text="nuevoProv.tipo_documento === 'DNI' ? 'Nombre' : 'Razón Social'"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="nuevoProv.razon_social" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Doc.</label>
                        <select x-model="nuevoProv.tipo_documento" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            <option value="RUC">RUC</option>
                            <option value="DNI">DNI</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Número</label>
                        <input type="text" x-model="nuevoProv.nro_documento" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                <button type="button" @click="guardarNuevoProv()" class="w-full inline-flex justify-center rounded-xl px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm font-bold">
                    Guardar
                </button>
                <button type="button" @click="modalNuevoProv = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-bold">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('compraUI', () => ({
        idProveedor: '',
        searchProv: '',
        showDropdownProv: false,
        proveedoresResult: [],
        _timeoutProv: null,
        
        modalNuevoProv: false,
        nuevoProv: {razon_social: '', tipo_documento: 'RUC', nro_documento: ''},

        init() {
            this.fetchProv(); // cargar iniciales
            <?php if (isset($compra)): ?>
                this.idProveedor = '<?= $compra->id_proveedor ?>';
                this.searchProv = '<?= htmlspecialchars($compra->proveedor ?: $compra->razon_social ?: '') ?>';
            <?php endif; ?>
        },
        
        onInputProv() {
            clearTimeout(this._timeoutProv);
            this._timeoutProv = setTimeout(() => {
                this.fetchProv();
            }, 300);
        },
        
        fetchProv() {
            fetch('<?= base_url('proveedores/buscar_ajax') ?>?term=' + encodeURIComponent(this.searchProv))
                .then(r => r.json())
                .then(data => { this.proveedoresResult = data; });
        },
        
        seleccionarProv(p) {
            this.idProveedor = p.id_proveedor;
            this.searchProv = p.razon_social;
            this.showDropdownProv = false;
        },
        
        guardarNuevoProv() {
            if(!this.nuevoProv.razon_social) {
                return Swal.fire('Error', 'La razón social es obligatoria', 'error');
            }
            fetch('<?= base_url('proveedores/guardar_ajax') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(this.nuevoProv)
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    this.seleccionarProv(data.proveedor);
                    this.modalNuevoProv = false;
                    this.nuevoProv = {razon_social: '', tipo_documento: 'RUC', nro_documento: ''};
                    Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Proveedor agregado', showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    }));
});
</script>

<script>
(function() {
    const inputBusqueda  = document.getElementById('busqueda-producto');
    const listaResultados = document.getElementById('lista-resultados');
    const tbodyItems     = document.getElementById('tbody-items');
    const totalCompraEl  = document.getElementById('total-compra');

    // Cargar items si estamos en edición
    const itemsIniciales = <?= isset($detalles) ? json_encode($detalles) : '[]' ?>;
    itemsIniciales.forEach(it => {
        agregarItem({
            id: it.id_producto,
            nombre: it.nombre,
            codigo_barras: it.codigo_barras,
            precio_compra: it.precio_compra
        }, it.cantidad);
    });

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

    function agregarItem(p, cantidadPredeterminada = 1) {
        // Verificar si el producto ya existe en la lista
        const filasActuales = tbodyItems.querySelectorAll('tr');
        for (let tr of filasActuales) {
            const idInput = tr.querySelector('input[name="id_producto[]"]');
            if (idInput && idInput.value == p.id) {
                // Existe, incrementar cantidad
                const cantInput = tr.querySelector('.cantidad-input');
                let cant = parseFloat(cantInput.value) || 0;
                cantInput.value = cant + cantidadPredeterminada;
                
                recalcular();
                return;
            }
        }

        const tr = document.createElement('tr');
        const precio = parseFloat(p.precio_compra || p.precio_venta || 0).toFixed(2);
        const subtotal = (parseFloat(precio) * cantidadPredeterminada).toFixed(2);

        tr.innerHTML = `
            <td class="px-4 py-2">
                <input type="hidden" name="id_producto[]" value="${p.id}">
                <div class="text-sm font-semibold text-slate-800">${p.nombre}</div>
                <div class="text-[10px] text-slate-400">${p.codigo_barras || ''}</div>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="cantidad[]" value="${Math.round(cantidadPredeterminada)}" step="1" min="1"
                       class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm cantidad-input"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </td>
            <td class="px-4 py-2">
                <input type="number" name="precio_compra[]" value="${precio}" step="0.01" min="0"
                       class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-sm precio-input">
            </td>
            <td class="px-4 py-2 text-right">
                <span class="text-sm text-slate-700 subtotal-item">S/ ${subtotal}</span>
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
