<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/@ericblade/quagga2@latest/dist/quagga.min.js"></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">
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
                <button type="submit" form="formProducto" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-500/25 active:scale-95 text-sm">
                    <i class="fas fa-save mr-2"></i> Guardar Producto
                </button>
            </div>
        </div>

        <form id="formProducto" action="<?= base_url('productos/guardar') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- SECCIÓN IZQUIERDA: Identificación y Detalles (Col 8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Card: Identificación -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 pb-4 flex items-center">
                        <i class="fas fa-tag mr-2 text-blue-500"></i> Identificación del Producto
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Código de Barras -->
                        <div class="flex flex-col gap-2" x-data="barcodeScanner()">
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
                            <div x-on:categoria-seleccionada.window="catNombre = $event.detail.nombre; catId = $event.detail.id"></div>
                        </div>
                    </div>

                    <!-- Nombre del Producto -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-1">Nombre Comercial</label>
                        <input type="text" name="nombre" required placeholder="Ej: Arroz Costeño de 1kg..."
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

                <!-- Card: Inventario y Costos -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 pb-4 flex items-center">
                        <i class="fas fa-coins mr-2 text-amber-500"></i> Control de Stock y Precios
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Precio Compra</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">S/</span>
                                <input type="number" name="precio_compra" step="0.01" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all font-bold text-slate-700 text-sm">
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Precio Venta</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 text-xs font-bold">S/</span>
                                <input type="number" name="precio_venta" step="0.01" required placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 bg-blue-50 border border-blue-200 rounded-xl outline-none focus:border-blue-400 transition-all font-black text-blue-800 text-lg">
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Stock Inicial</label>
                            <input type="number" name="stock" value="0"
                                class="w-full px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl outline-none focus:border-emerald-400 transition-all font-black text-emerald-800">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Alerta Mínima</label>
                            <input type="number" name="stock_minimo" value="5"
                                class="w-full px-4 py-3 bg-rose-50 border border-rose-100 rounded-xl outline-none focus:border-rose-400 transition-all font-black text-rose-800">
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
</div>

<!-- ======= MODALES Y LOGICA ======= -->

<script>
function imagePreview() {
    return {
        url: null,
        updatePreview(event) {
            const file = event.target.files[0];
            if (file) {
                this.url = URL.createObjectURL(file);
            }
        }
    }
}

function barcodeScanner() {
    return {
        codigo: '',
        startScanner() {
            // Lógica similar al componente previo para Quagga
            alert("Iniciando escáner...");
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
    input::placeholder, textarea::placeholder { font-weight: 500; font-size: 0.8rem; color: #cbd5e1; }
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

<style>
    @keyframes slide-up {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>