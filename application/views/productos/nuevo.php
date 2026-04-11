<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/@ericblade/quagga2@latest/dist/quagga.min.js"></script>

<script>
function productoForm() {
    return {
        precioBase: '',
        inputTallas: '',
        inputColores: '',
        inputDisenos: '',
        stockInicial: 0,
        motivoStock: 'Ajuste Inicial',
        isStockModalOpen: false,
        currentVarianteIndex: null,
        modalCant: 0,
        modalMotivo: 'Ajuste Inicial',
        nombre: '',
        variantes: [],
        init() {
            this.$watch('inputTallas', () => this.generarVariantes());
            this.$watch('inputColores', () => this.generarVariantes());
            this.$watch('inputDisenos', () => this.generarVariantes());
            this.$watch('precioBase', (val) => {
                this.variantes.forEach(v => v.precio = val);
            });
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
        generarVariantes() {
            const tallas = this.inputTallas.split(',').map(s => s.trim()).filter(s => s !== '');
            const colores = this.inputColores.split(',').map(s => s.trim()).filter(s => s !== '');
            const disenos = this.inputDisenos.split(',').map(s => s.trim()).filter(s => s !== '');
            if (tallas.length === 0 && colores.length === 0 && disenos.length === 0) {
                this.variantes = [];
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
            this.variantes = combinations.map((c, i) => {
                const attrs = [c.talla, c.color, c.diseno].filter(Boolean).join(' ');
                return {
                    talla: c.talla || '',
                    color: c.color || '',
                    diseno: c.diseno || '',
                    nombre: attrs ? `${this.nombre} (${attrs})` : this.nombre,
                    precio: this.precioBase || 0,
                    barcode: '',
                    stock: 0,
                    motivo: 'Ajuste Inicial'
                };
            });
        },
        abrirModalStock() {
            this.currentVarianteIndex = null;
            this.modalCant = this.stockInicial;
            this.modalMotivo = this.motivoStock;
            this.isStockModalOpen = true;
        },
        abrirModalStockVariante(index) {
            this.currentVarianteIndex = index;
            this.modalCant = this.variantes[index].stock;
            this.modalMotivo = this.variantes[index].motivo;
            this.isStockModalOpen = true;
        },
        confirmarStock() {
            if (this.modalMotivo === 'Compra') {
                // Notificación clara y reseteo del motivo a una opción válida
                this.modalMotivo = 'Ajuste Inicial';
                alert('🚫 Las COMPRAS se registran en el módulo de Compras.\nAquí solo puedes hacer ajustes de inventario inicial.');
                return;
            }
            if (this.currentVarianteIndex === null) {
                // Stock simple (producto sin variantes)
                this.stockInicial = parseInt(this.modalCant) || 0;
                this.motivoStock  = this.modalMotivo;
            } else {
                // Stock de una variante específica — convertir a entero
                this.variantes[this.currentVarianteIndex].stock  = parseInt(this.modalCant) || 0;
                this.variantes[this.currentVarianteIndex].motivo = this.modalMotivo;
            }
            this.isStockModalOpen = false;
        },
        submitForm() {
            const form = document.getElementById('formProducto');
            if (!form.reportValidity()) return;

            // Validación mandatoría de Color y Diseño
            if (!this.inputColores.trim()) {
                Swal.fire({
                    title: '<span class="text-slate-800">Color Faltante</span>',
                    html: '<p class="text-sm font-medium text-slate-500">Por favor, ingresa al menos un <b>COLOR</b> para el producto.<br><span class="text-xs text-slate-400">(Ej: Rojo, Azul, No aplica)</span></p>',
                    icon: 'warning',
                    confirmButtonText: 'ENTENDIDO',
                    confirmButtonColor: '#3b82f6',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-2xl px-8 py-3 font-black text-xs tracking-widest' }
                });
                return;
            }
            if (!this.inputDisenos.trim()) {
                Swal.fire({
                    title: '<span class="text-slate-800">Diseño Faltante</span>',
                    html: '<p class="text-sm font-medium text-slate-500">Por favor, ingresa al menos un <b>DISEÑO</b> para el producto.<br><span class="text-xs text-slate-400">(Ej: Logo, Estampado, Liso, No aplica)</span></p>',
                    icon: 'warning',
                    confirmButtonText: 'ENTENDIDO',
                    confirmButtonColor: '#3b82f6',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-2xl px-8 py-3 font-black text-xs tracking-widest' }
                });
                return;
            }

            const btn = document.querySelector('[x-on\\:click="submitForm()"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...'; }

            // FIX: Sincronizar precio_venta manualmente (evita bug de Alpine defer)
            const precioInput = form.querySelector('[name="precio_venta"]');
            if (precioInput) precioInput.value = this.precioBase;

            const isVariable = this.variantes.length > 0;

            // Campo hidden para saber si es variable
        

            if (isVariable) {
                // Enviamos todas las variantes con stock como entero
                const variantesParaEnviar = this.variantes.map(v => ({
                    ...v,
                    stock: parseInt(v.stock) || 0,
                    precio: parseFloat(v.precio) || 0
                }));
                const input  = document.createElement('input');
                input.type   = 'hidden';
                input.name   = 'json_variantes';
                input.value  = JSON.stringify(variantesParaEnviar);
                form.appendChild(input);
            } else {
                const inputStock  = document.createElement('input');
                inputStock.type   = 'hidden';
                inputStock.name   = 'stock_ajuste';
                inputStock.value  = parseInt(this.stockInicial) || 0;
                form.appendChild(inputStock);

                const inputMotivo  = document.createElement('input');
                inputMotivo.type   = 'hidden';
                inputMotivo.name   = 'stock_motivo';
                inputMotivo.value  = this.motivoStock;
                form.appendChild(inputMotivo);
            }
            form.submit();
        }
    }
}
function imagePreview() {
    return {
        url: null,
        updatePreview(event) {
            const file = event.target.files[0];
            if (file) { this.url = URL.createObjectURL(file); }
        }
    }
}
function barcodeScanner(inicial = '') {
    return {
        codigo: inicial,
        startScanner() { alert("Iniciando escáner..."); }
    }
}
</script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0" x-data="productoForm()">
    <div class="p-4 sm:p-6 lg:p-10 max-w-6xl mx-auto">
        
        <!-- Navbar Superior de Acción -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 gap-4">
            <div>
                <a href="<?= base_url('productos') ?>" class="inline-flex items-center text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest mb-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al listado
                </a>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Nuevo Producto</h1>
                <p class="text-slate-400 text-sm mt-1">
                    Registrando en <span class="font-bold text-slate-600"><?= $this->session->userdata('sucursal_nombre') ?></span>
                </p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" @click="submitForm()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-500/25 active:scale-95 text-sm">
                    <i class="fas fa-save mr-2"></i> Guardar Producto
                </button>
            </div>
        </div>

        <form id="formProducto" action="<?= base_url('productos/guardar') ?>" method="POST" enctype="multipart/form-data" 
              class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- SECCIÓN IZQUIERDA: Identificación y Detalles (Col 8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Card: Identificación -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 pb-4 flex items-center">
                        <i class="fas fa-tag mr-2 text-blue-500"></i> Identificación del Producto
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Código de Barras -->
                        <div class="flex flex-col gap-2" x-data="barcodeScanner('<?= $next_barcode ?>')">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Código de Barras</label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" name="codigo_barras" x-model="codigo" required autofocus 
                                        placeholder="Escanear o escribir..."
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-mono text-sm">
                                    <template x-if="codigo">
                                        <button @click="codigo = ''" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                                            <i class="fas fa-times-circle text-xs"></i>
                                        </button>
                                    </template>
                                </div>
                                <button type="button" @click="startScanner()" 
                                        class="flex items-center justify-center px-4 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 rounded-xl transition-all border border-slate-200 shadow-sm" title="Escanear con cámara">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Categoría con Modal -->
                        <div class="flex flex-col gap-2" x-data="{ catNombre: '', catId: '' }">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Categoría</label>
                            <input type="hidden" name="categoria" :value="catNombre">
                            <input type="hidden" name="id_categoria" :value="catId">
                            <div x-on:categoria-seleccionada.window="catNombre = $event.detail.nombre; catId = $event.detail.id; if(!nombre) nombre = catNombre"></div>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" :value="catNombre" readonly
                                        placeholder="Seleccionar..."
                                        @click="abrirSelectorCategoria($el, $dispatch)"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer hover:border-blue-400 transition-all text-sm font-bold"
                                        :class="catNombre ? 'text-slate-800' : 'text-slate-400'">
                                    <template x-if="catNombre">
                                        <i class="fas fa-check-circle absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500 text-xs"></i>
                                    </template>
                                </div>
                                <button type="button" @click="abrirSelectorCategoria(null, $dispatch)"
                                    class="px-4 py-3 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 rounded-xl transition-all border border-slate-200">
                                    <i class="fas fa-search text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Nombre del Producto -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Nombre Comercial</label>
                        <input type="text" name="nombre" x-model="nombre" required placeholder="Ej: Arroz Costeño de 1kg..." @input="generarVariantes()"
                            class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-800 text-base">
                    </div>

                    <!-- Descripción y Almacén -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-8 flex flex-col gap-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Descripción / Notas Adicionales</label>
                            <textarea name="descripcion" rows="2" placeholder="Especificaciones, marca o detalles..."
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all text-sm"></textarea>
                        </div>
                        <div class="md:col-span-4 flex flex-col gap-2">
                            <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Ubicación / Almacén</label>
                            <select name="id_almacen" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-4 focus:ring-blue-500/5 focus:border-blue-400 transition-all font-bold text-slate-700 text-sm">
                                <?php foreach($almacenes as $alm): ?>
                                    <option value="<?= $alm->id ?>" <?= $alm->nombre == 'Almacen 01' ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($alm->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card: Inventario y Precios -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center">
                            <i class="fas fa-coins mr-2 text-amber-500"></i> Inventario y Atributos
                        </h3>
                    </div>

                    <!-- Generador de Variantes (Siempre Visible) -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                            <div class="space-y-2">
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
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Colores</label>
                                <input type="text" x-model="inputColores" placeholder="Rojo, Azul, Negro..."
                                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all text-xs font-bold shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Diseños</label>
                                <input type="text" x-model="inputDisenos" placeholder="Logo, Estampado..."
                                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all text-xs font-bold shadow-sm">
                            </div>
                        </div>

                        <!-- Sección de Configuración de Stock/Precio -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Precio Venta (Base)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 text-xs font-bold">S/</span>
                                    <input type="number" name="precio_venta" x-model="precioBase" step="0.01" 
                                        class="w-full pl-8 pr-4 py-3 bg-blue-50 border border-blue-200 rounded-xl outline-none focus:border-blue-400 transition-all font-black text-blue-800 text-lg">
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precio Compra</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">S/</span>
                                    <input type="number" name="precio_compra" step="0.01" placeholder="0.00"
                                        class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all font-bold text-slate-700 text-sm">
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Alerta Mínima</label>
                                <input type="number" name="stock_minimo" value="2"
                                    class="w-full px-4 py-3 bg-rose-50 border border-rose-100 rounded-xl outline-none focus:border-rose-400 transition-all font-black text-rose-800">
                            </div>
                        </div>

                        <div x-show="variantes.length === 0" class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Stock Inicial</p>
                                    <p class="text-sm font-black text-slate-800"><span x-text="stockInicial">0</span> Unidades</p>
                                </div>
                            </div>
                            <button type="button" @click="abrirModalStock()"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-black uppercase tracking-widest text-[10px] transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                                <i class="fas fa-plus-circle mr-1"></i> Ajustar Stock
                            </button>
                        </div>

                        <!-- Tabla de Variantes (Si existen) -->
                        <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm" x-show="variantes.length > 0">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-4 py-3">Variante</th>
                                        <th class="px-4 py-3 text-right">Precio</th>
                                        <th class="px-4 py-3 text-center">Stock</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs">
                                    <template x-for="(v, index) in variantes" :key="index">
                                        <tr class="hover:bg-blue-50/20">
                                            <td class="px-4 py-3 font-black text-slate-700" x-text="v.nombre"></td>
                                            <td class="px-4 py-3 text-right">
                                                <input type="number" x-model="v.precio" step="0.01" class="w-20 px-2 py-1 bg-blue-50 border border-blue-100 rounded-lg text-right font-bold text-blue-700">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" @click="abrirModalStockVariante(index)"
                                                    class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg font-black hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100">
                                                    <span x-text="v.stock">0</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN DERECHA: Imagen y Publicación (Col 4) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Card: Fotografía -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" x-data="imagePreview()">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-center">FOTOGRAFÍA</h3>
                    
                    <div class="relative flex flex-col items-center">
                        <input type="file" name="imagen" accept="image/*" capture="environment" class="hidden" x-ref="imageInput" @change="updatePreview">
                        
                        <div @click="$refs.imageInput.click()" 
                            class="w-full aspect-square max-w-[240px] bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center overflow-hidden transition-all hover:bg-blue-50 hover:border-blue-300 cursor-pointer group relative">
                            
                            <template x-if="url">
                                <img :src="url" class="w-full h-full object-cover">
                            </template>

                            <template x-if="!url">
                                <div class="text-center p-6 space-y-2">
                                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mx-auto shadow-sm group-hover:scale-110 transition-transform">
                                        <i class="fas fa-camera text-blue-500 text-xl"></i>
                                    </div>
                                    <p class="text-[11px] font-black text-slate-400 uppercase leading-tight tracking-tighter">Tomar Foto o<br>Cargar Archivo</p>
                                </div>
                            </template>

                            <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/5 transition-colors"></div>
                        </div>

                        <template x-if="url">
                            <button type="button" @click="url = null; $refs.imageInput.value = ''" 
                                class="mt-4 flex items-center gap-2 text-[10px] font-black text-rose-500 uppercase tracking-widest hover:text-rose-700 transition-colors">
                                <i class="fas fa-trash-alt"></i> Eliminar Foto
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Botón Secundario Guardar (Mobile) -->
                <div class="lg:hidden">
                    <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-[0.2em] shadow-xl shadow-blue-500/25 active:scale-95 transition-all">
                        Finalizar Registro
                    </button>
                </div>

                <!-- Tip de Ayuda -->
                <div class="p-5 rounded-2xl bg-slate-800 text-white space-y-3">
                    <div class="flex items-center gap-2 text-blue-400 text-xs font-black uppercase tracking-widest">
                        <i class="fas fa-lightbulb"></i> Tip de Gestión
                    </div>
                    <p class="text-[11px] text-slate-300 leading-relaxed font-medium">
                        Asegúrate de asignar un <span class="text-white font-bold">Stock Mínimo</span> adecuado para que el sistema te avise cuando sea momento de reponer el producto.
                    </p>
                </div>
            </div>
        </form>
    </div>


<style>
    [x-cloak] { display: none !important; }
    @keyframes slide-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>

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

<script>
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

<!-- ======= MODAL DE AJUSTE DE STOCK INICIAL (KARDEX) ======= -->
<div x-show="isStockModalOpen" class="fixed inset-0 z-[1001] flex items-center justify-center p-4" x-cloak>
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" @click="isStockModalOpen = false"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden animate-slide-up">
        <!-- Header -->
        <div class="p-8 border-b border-slate-50 text-center">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-boxes text-2xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 tracking-tight">Stock Inicial</h3>
        </div>

        <div class="p-8 space-y-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cantidad</label>
                <input type="number" x-model="modalCant" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-2xl font-black text-slate-800 text-center outline-none focus:border-emerald-500">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Motivo</label>
                <select x-model="modalMotivo" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 outline-none focus:border-emerald-500">
                    <option value="Ajuste Inicial">Ajuste por Inventario Inicial</option>
                    <option value="Donacion">Donación / Regalo</option>
                    <option value="Compra">Compra (No usar aquí)</option>
                </select>
            </div>

            <button type="button" @click="confirmarStock()"
                class="w-full py-5 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-emerald-200 hover:bg-emerald-700 transition-all active:scale-95">
                Confirmar registro
            </button>
        </div>
    </div>
</div>

    </div>
</div>

<style>
    @keyframes slide-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
    .animate-slide-up { animation: slide-up 0.3s ease-out; }
    .animate-fade-in { animation: fade-in 0.4s ease-out; }
    [x-cloak] { display: none !important; }
    input::placeholder, textarea::placeholder { font-weight: 500; font-size: 0.8rem; color: #cbd5e1; }
</style>