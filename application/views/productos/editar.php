<?php 
    $foto_url = $p->imagen ? base_url('uploads/productos/'.$p->imagen.'?v='.$p->version) : null;
    
    // Ahora que los productos son independientes, el precio y código base vienen directamente de $p
    $base_price = (float)$p->precio_venta;
    $base_barcode = $p->codigo_barras;

    // Inicialización de atributos: Si no hay variantes, usamos los del producto mismo
    $tallas_init = !empty($p->talla) ? $p->talla : '';
    $colores_init = !empty($p->color) ? $p->color : '';
    $disenos_init = !empty($p->diseno) ? $p->diseno : '';

    // Limpiar el nombre base (quitar lo que está entre paréntesis) para edición limpia
    $nombre_base = preg_replace('/\s*\(.*?\)\s*$/', '', $p->nombre);
?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/@ericblade/quagga2@latest/dist/quagga.min.js"></script>

<script>
function productoForm(iniciales) {
    return {
        precioBase: iniciales.precio_venta || 0,
        stockActual: iniciales.stock || 0,
        descripcion: iniciales.descripcion || '',
        categoriaNombre: iniciales.categoria || '',
        categoriaId: iniciales.id_categoria || 0,
        inputTallas: iniciales.tallas_init || '',
        inputColores: iniciales.colores_init || '',
        inputDisenos: iniciales.disenos_init || '',
        nombreBase: iniciales.nombre_base || '',
        isStockModalOpen: false,
        currentVarianteIndex: null,
        modalCant: 0,
        modalMotivo: 'Ajuste Inicial',
        nombre: '',
        variantes: [],
        
        initData() {
            // Inicializar variantes con las que ya existen en la DB para no duplicar
            this.variantes = JSON.parse(JSON.stringify(iniciales.variantes_db));
            
            this.$watch('inputTallas', () => this.generarVariantes(true));
            this.$watch('inputColores', () => this.generarVariantes(true));
            this.$watch('inputDisenos', () => this.generarVariantes(true));
        },

        toggleTalla(t) {
            let tallas = this.inputTallas.split(',').map(s => s.trim()).filter(s => s !== '');
            if (tallas.includes(t)) {
                tallas = tallas.filter(s => s !== t);
            } else {
                tallas.push(t);
            }
            this.inputTallas = tallas.join(', ');
        },

        isTallaSelected(t) {
            return this.inputTallas.split(',').map(s => s.trim()).includes(t);
        },

        generarVariantes(manualChange = false) {
            const isCurrentProduct = (c) => {
                 return (c.talla || '') == (iniciales.id_actual ? (iniciales.talla_actual || '') : '') && 
                        (c.color || '') == (iniciales.id_actual ? (iniciales.color_actual || '') : '') && 
                        (c.diseno || '') == (iniciales.id_actual ? (iniciales.diseno_actual || '') : '');
            };
            const tallas = this.inputTallas.split(',').map(s => s.trim()).filter(s => s !== '');
            const colores = this.inputColores.split(',').map(s => s.trim()).filter(s => s !== '');
            const disenos = this.inputDisenos.split(',').map(s => s.trim()).filter(s => s !== '');
            
            // Si el producto es individual y estamos editándolo, no queremos crear variantes
            // a menos que el usuario realmente escriba algo nuevo que implique una lista.
            // Para productos únicos, simplemente guardaremos sus atributos.
            
            if (tallas.length <= 1 && colores.length <= 1 && disenos.length <= 1) {
                 // Si solo hay un valor de cada uno, los tratamos como atributos del producto actual, no como variantes.
                 if (manualChange) this.variantes = [];
                 return;
            }

            let combinations = [{}];
            if (tallas.length > 0) {
                let next = [];
                combinations.forEach(c => tallas.forEach(t => next.push({...c, talla: t})));
                combinations = next;
            }
            if (colores.length > 0) {
                let next = [];
                combinations.forEach(c => colores.forEach(co => next.push({...c, color: co})));
                combinations = next;
            }
            if (disenos.length > 0) {
                let next = [];
                combinations.forEach(c => disenos.forEach(d => next.push({...c, diseno: d})));
                combinations = next;
            }
            this.variantes = combinations.map(c => {
                const attrs = [c.talla, c.color, c.diseno].filter(Boolean).join(' ');
                const nombreFull = attrs ? `${this.nombreBase} (${attrs})` : this.nombreBase;

                // Prioridad 1: Buscar si esta combinación ya existe en la lista de variantes actual (editada)
                const existenteActual = this.variantes.find(v => 
                    (v.talla || '') == (c.talla || '') && 
                    (v.color || '') == (c.color || '') && 
                    (v.diseno || '') == (c.diseno || '')
                );

                // Prioridad 2: Buscar si existe un hermano (ya en DB) para esta combinación
                const hermanoEnDB = iniciales.variantes_db.find(h => 
                    (h.talla || '') == (c.talla || '') && 
                    (h.color || '') == (c.color || '') && 
                    (h.diseno || '') == (c.diseno || '')
                );

                if (existenteActual) {
                    existenteActual.nombre = nombreFull; 
                    return existenteActual;
                }

                return {
                    id: hermanoEnDB ? hermanoEnDB.id : (isCurrentProduct(c) ? iniciales.id_actual : null),
                    nombre: nombreFull,
                    talla: c.talla || '',
                    color: c.color || '',
                    diseno: c.diseno || '',
                    precio: hermanoEnDB ? hermanoEnDB.precio : (isCurrentProduct(c) ? this.precioBase : (this.precioBase || 0)),
                    stock: hermanoEnDB ? hermanoEnDB.stock : (isCurrentProduct(c) ? this.stockActual : 0),
                    barcode: hermanoEnDB ? hermanoEnDB.barcode : '',
                    motivo: ''
                };
            });
        },

        abrirModalStock() {
            this.currentVarianteIndex = null;
            this.modalCant = this.stockActual;
            this.modalMotivo = 'Ajuste Manual en Edición';
            this.isStockModalOpen = true;
        },

        abrirModalStockVariante(index) {
            this.currentVarianteIndex = index;
            this.modalCant = this.variantes[index].stock;
            this.modalMotivo = this.variantes[index].motivo || 'Ajuste Manual en Edición';
            this.isStockModalOpen = true;
        },

        confirmarStock() {
            if (this.modalMotivo === 'Compra') {
                this.modalMotivo = 'Ajuste Manual en Edición';
                alert('🚫 Las COMPRAS se registran en el módulo de Compras.\nAqí solo puedes hacer ajustes de inventario.');
                return;
            }
            if (this.currentVarianteIndex === null) {
                this.stockActual = parseInt(this.modalCant) || 0;
            } else {
                this.variantes[this.currentVarianteIndex].stock = parseInt(this.modalCant) || 0;
                this.variantes[this.currentVarianteIndex].motivo = this.modalMotivo;
            }
            this.isStockModalOpen = false;
        },

        submitForm() {
            const form = document.getElementById('formProducto');
            if (!form.reportValidity()) return;

            const btn = document.querySelector('[x-on\\:click="submitForm()"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Actualizando...'; }

            const inputStock = document.createElement('input');
            inputStock.type = 'hidden';
            inputStock.name = 'stock_ajuste';
            inputStock.value = parseInt(this.stockActual) || 0;
            form.appendChild(inputStock);

            const inputMotivo = document.createElement('input');
            inputMotivo.type = 'hidden';
            inputMotivo.name = 'stock_motivo';
            inputMotivo.value = this.modalMotivo || 'Ajuste Manual en Edición';
            form.appendChild(inputMotivo);

            // Campos para atributos individuales (talla_producto, color_producto, diseno_producto)
            const inputT = document.createElement('input');
            inputT.type = 'hidden';
            inputT.name = 'talla_producto';
            inputT.value = this.inputTallas;
            form.appendChild(inputT);

            const inputC = document.createElement('input');
            inputC.type = 'hidden';
            inputC.name = 'color_producto';
            inputC.value = this.inputColores;
            form.appendChild(inputC);

            const inputD = document.createElement('input');
            inputD.type = 'hidden';
            inputD.name = 'diseno_producto';
            inputD.value = this.inputDisenos;
            form.appendChild(inputD);

            if (this.variantes.length > 0) {
                const variantesParaEnviar = this.variantes.map(v => ({
                    ...v,
                    stock: parseInt(v.stock) || 0,
                    precio: parseFloat(v.precio) || 0
                }));
                const inputVar = document.createElement('input');
                inputVar.type = 'hidden';
                inputVar.name = 'json_variantes';
                inputVar.value = JSON.stringify(variantesParaEnviar);
                form.appendChild(inputVar);
            }

            form.submit();
        }
    }
}

function imagePreview(initialUrl = null) {
    return {
        url: initialUrl,
        updatePreview(event) {
            const file = event.target.files[0];
            if (file) {
                this.url = URL.createObjectURL(file);
            }
        }
    }
}

function barcodeScanner(valorInicial = '') {
    return {
        codigo: valorInicial,
        startScanner() { alert("Escáner..."); }
    }
}


function abrirSelectorCategoria(el, dispatchFn) {
    const modal = document.getElementById('modalSelectorCat');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('buscarCatModal').value = '';
    filtrarCatModal('');
    setTimeout(() => document.getElementById('buscarCatModal').focus(), 100);
}

function cerrarSelectorCategoria() {
    const modal = document.getElementById('modalSelectorCat');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function filtrarCatModal(q) {
    document.querySelectorAll('.cat-item').forEach(btn => {
        btn.style.display = btn.dataset.search.includes(q.toLowerCase()) ? '' : 'none';
    });
}

function seleccionarCategoria(id, nombre) {
    window.dispatchEvent(new CustomEvent('categoria-seleccionada', {
        detail: { id: id, nombre: nombre }
    }));
    cerrarSelectorCategoria();
}
</script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0" 
        x-data='productoForm({
            precio_venta: <?= (float)$p->precio_venta ?>,
            stock: <?= (int)$p->stock ?>,
            nombre_base: <?= json_encode($nombre_base) ?>,
            id_actual: <?= (int)$p->id ?>,
            talla_actual: <?= json_encode($p->talla ?: "") ?>,
            color_actual: <?= json_encode($p->color ?: "") ?>,
            diseno_actual: <?= json_encode($p->diseno ?: "") ?>,

            descripcion: <?= json_encode($p->descripcion ?? "") ?>,
            id_categoria: <?= (int)($p->id_categoria ?? 0) ?>,
            categoria: <?= json_encode($p->categoria ?? "") ?>,

            tallas_init: <?= json_encode($tallas_init ?? "") ?>,
            colores_init: <?= json_encode($colores_init ?? "") ?>,
            disenos_init: <?= json_encode($disenos_init ?? "") ?>,

            variantes_db: <?= json_encode($hermanos ?? []) ?>,
            variantes: []
        })'
        x-init="initData()"
        >
    
    <div class="p-4 sm:p-6 lg:p-10 max-w-6xl mx-auto">
        
        <!-- Navbar Superior de Acción -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
            <div>
                <a href="<?= base_url('productos') ?>" class="inline-flex items-center text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest mb-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al listado
                </a>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Editar Producto</h1>
                <p class="text-slate-400 text-sm mt-1">
                    ID: #<?= $p->id ?> · <span class="font-bold text-slate-600"><?= $this->session->userdata('sucursal_nombre') ?></span>
                </p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" @click="submitForm()" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/25 active:scale-95 text-sm">
                    <i class="fas fa-sync-alt mr-2"></i> Guardar Cambios
                </button>
            </div>
        </div>

        <form id="formProducto" action="<?= base_url('productos/actualizar/'.$p->id) ?>" method="POST" enctype="multipart/form-data" 
              class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8 space-y-6">
                <!-- Card: Identificación -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 pb-4 flex items-center">
                        <i class="fas fa-tag mr-2 text-blue-500"></i> Datos del Producto
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2" x-data="barcodeScanner('<?= $base_barcode ?>')">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Código de Barras</label>
                            <input type="text" name="codigo_barras" x-model="codigo" required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-mono text-sm font-bold">
                        </div>

            <div class="flex flex-col gap-2">
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">
                    Categoría
                </label>

                <!-- Valores que se enviarán al backend -->
                <input type="hidden" name="categoria" :value="categoriaNombre">
                <input type="hidden" name="id_categoria" :value="categoriaId">

                <!-- Input visible -->
                <input 
                    type="text"
                    x-model="categoriaNombre"
                    readonly
                    @click="abrirSelectorCategoria($el, $dispatch)"
                    placeholder="Seleccionar categoría"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer font-bold text-sm"
                >

                <!-- Evento cuando el modal selecciona -->
                <div 
                    x-on:categoria-seleccionada.window="
                        categoriaNombre = $event.detail.nombre;
                        categoriaId = $event.detail.id;
                        if(!nombreBase) nombreBase = categoriaNombre;
                    ">
                </div>
            </div>
        </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Nombre Comercial</label>
                        <input type="text" name="nombre" x-model="nombreBase" required @input="generarVariantes(true)"
                            class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-xl outline-none font-black text-slate-800 text-base">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-8 flex flex-col gap-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Descripción</label>
                            <textarea name="descripcion" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm font-medium"><?= htmlspecialchars($p->descripcion) ?></textarea>
                        </div>
                        <div class="md:col-span-4 flex flex-col gap-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Almacén</label>
                            <select name="id_almacen" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-bold text-sm">
                                <?php foreach($almacenes as $alm): ?>
                                    <option value="<?= $alm->id ?>" <?= ($p->id_almacen == $alm->id) ? 'selected' : '' ?>><?= htmlspecialchars($alm->nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card: Precios e Inventario -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 pb-4 flex items-center">
                        <i class="fas fa-coins mr-2 text-amber-500"></i> Precios e Inventario
                    </h3>

                    <!-- Inputs de Generación de Variantes (Igual que en Nuevo) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tallas</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                <template x-for="t in ['S','M','L','XL','XXL']" :key="t">
                                    <button type="button" @click="toggleTalla(t)"
                                        :class="isTallaSelected(t) ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200 scale-105' : 'bg-white text-slate-400 border-slate-200 hover:border-blue-400 hover:text-blue-500'"
                                        class="w-11 h-11 flex items-center justify-center rounded-xl border text-[11px] font-black transition-all active:scale-90"
                                        x-text="t">
                                    </button>
                                </template>
                                <!-- Input invisible pero vinculado para compatibilidad -->
                                <input type="hidden" x-model="inputTallas">
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Colores</label>
                            <input type="text" x-model="inputColores" placeholder="Rojo, Azul" 
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-blue-400 font-bold text-sm">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Diseños</label>
                            <input type="text" x-model="inputDisenos" placeholder="Burbuja, Dragón" 
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-blue-400 font-bold text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Precio Venta (Base)</label>
                            <input type="number" name="precio_venta" x-model="precioBase" step="0.01" 
                                class="w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl font-black text-blue-800 text-lg">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precio Compra</label>
                            <input type="number" name="precio_compra" value="<?= $p->precio_compra ?>" step="0.01"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Alerta Mínima</label>
                            <input type="number" name="stock_minimo" value="<?= $p->stock_minimo ?>"
                                class="w-full px-4 py-3 bg-rose-50 border border-rose-100 rounded-xl font-black">
                        </div>
                    </div>


                    <!-- Stock Maestro (Solo si no tiene variantes) -->
                    <div x-show="variantes.length === 0" class="pt-4 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Stock Actual</p>
                                <p class="text-sm font-black text-slate-800"><span x-text="stockActual"><?= $p->stock ?></span> Unidades</p>
                            </div>
                        </div>
                        <button type="button" @click="abrirModalStock()"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-black uppercase text-[10px] shadow-lg shadow-emerald-500/20 active:scale-95">
                            <i class="fas fa-plus-circle mr-1"></i> Ajustar Saldo
                        </button>
                    </div>

                    <!-- Tabla de Variantes -->
                    <div class="overflow-hidden border border-slate-100 rounded-2xl" x-show="variantes.length > 0">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 font-black text-slate-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-4 py-3">Variante</th>
                                    <th class="px-4 py-3 text-right">Precio</th>
                                    <th class="px-4 py-3 text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="(v, index) in variantes" :key="index">
                                    <tr>
                                        <td class="px-4 py-3 font-black text-slate-700" x-text="v.nombre"></td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model="v.precio" step="0.01" class="w-20 px-2 py-1 bg-blue-50 border border-blue-100 rounded-lg text-right font-bold">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="abrirModalStockVariante(index)"
                                                class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg font-black border border-emerald-100">
                                                <span x-text="v.stock"></span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Fotografía -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" x-data="imagePreview('<?= $foto_url ?>')">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-center">FOTOGRAFÍA</h3>
                    <div class="relative flex flex-col items-center">
                        <input type="file" name="imagen" accept="image/*" class="hidden" x-ref="imageInput" @change="updatePreview">
                        <div @click="$refs.imageInput.click()" class="w-full aspect-square bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl flex items-center justify-center overflow-hidden cursor-pointer hover:bg-blue-50">
                            <template x-if="url"><img :src="url" class="w-full h-full object-cover"></template>
                            <template x-if="!url"><i class="fas fa-camera text-slate-300 text-2xl"></i></template>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


<!-- ======= MODAL DE AJUSTE ======= -->
<div x-show="isStockModalOpen" class="fixed inset-0 z-[1001] flex items-center justify-center p-4 transition-all" x-cloak>
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" @click="isStockModalOpen = false"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up">
        <div class="p-8 border-b border-slate-50 text-center">
            <h3 class="text-xl font-black text-slate-800 tracking-tight">Ajustar Saldo</h3>
            <p class="text-slate-400 text-xs mt-2 uppercase font-bold">Registro en Kardex</p>
        </div>
        <div class="p-8 space-y-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nuevo Stock</label>
                <input type="number" x-model="modalCant" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-2xl font-black text-slate-800 text-center outline-none focus:border-emerald-500">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Motivo del ajuste</label>
                <select x-model="modalMotivo" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 outline-none focus:border-emerald-500">
                    <option value="Ajuste Manual en Edición">Ajuste por Inventario</option>
                    <option value="Donacion">Donación / Salida</option>
                    <option value="Devolucion">Devolución</option>
                </select>
            </div>
            <button type="button" @click="confirmarStock()" class="w-full py-5 bg-emerald-600 text-white rounded-2xl font-black uppercase text-xs shadow-xl active:scale-95 transition-all">
                Actualizar Stock
            </button>
        </div>
    </div>
</div>

    </div>
</div>

<!-- ======= MODAL SELECTOR DE CATEGORÍA ======= -->
<div id="modalSelectorCat" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="cerrarSelectorCategoria()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-slide-up">
        <!-- Header -->
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Elegir Categoría</h3>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Catálogo compartido</p>
            </div>
            <button onclick="cerrarSelectorCategoria()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Buscador -->
        <div class="px-6 py-4 bg-slate-50/50">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input type="text" id="buscarCatModal" placeholder="Filtrar por nombre..."
                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl outline-none focus:border-blue-400 transition-all text-sm font-bold shadow-sm"
                    oninput="filtrarCatModal(this.value)" autocomplete="off">
            </div>
        </div>

        <!-- Lista -->
        <div class="overflow-y-auto max-h-[350px] px-3 py-2" id="listaCatModal">
            <?php foreach ($categorias as $c): ?>
            <button type="button"
                class="cat-item w-full flex items-center justify-between px-4 py-4 rounded-2xl hover:bg-blue-50/50 text-left transition-all group mb-1"
                data-search="<?= strtolower(htmlspecialchars($c->nombre)) ?>"
                onclick="seleccionarCategoria(<?= $c->id ?>, '<?= htmlspecialchars($c->nombre, ENT_QUOTES) ?>')">
                
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm"
                        style="background:<?= $c->color ?>; color: white;">
                        <i class="fas <?= $c->icono ?> text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-black text-slate-800 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($c->nombre) ?></span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">ID: #<?= $c->id ?></span>
                    </div>
                </div>
                
                <i class="fas fa-chevron-right text-slate-200 group-hover:text-blue-400 transition-all"></i>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<style>
    [x-cloak] { display: none !important; }
    @keyframes slide-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>
