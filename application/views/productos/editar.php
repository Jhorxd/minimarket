<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/@ericblade/quagga2@latest/dist/quagga.min.js"></script>
<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">
    <div class="p-4 sm:p-6 lg:p-10 max-w-5xl mx-auto">
        
        <div class="mb-8">
            <a href="<?= base_url('productos') ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver al listado
            </a>
            <h1 class="text-3xl font-black text-slate-800">Editar Producto</h1>
            <p class="text-slate-500">Editando registro en: <span class="font-bold text-slate-700"><?= $this->session->userdata('sucursal_nombre') ?></span></p>
        </div>

<form action="<?= base_url('productos/actualizar/'.$p->id) ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">
    
    <div class="md:col-span-2 space-y-6">
        <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2" x-data="barcodeScanner('<?= $p->codigo_barras ?>')">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Código de Barras</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="codigo_barras" x-model="codigo" required autofocus 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-mono">
                            <template x-if="codigo">
                                <button @click="codigo = ''" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </template>
                        </div>
                        <button type="button" @click="startScanner()" 
                                class="md:hidden flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl active:scale-95 transition-all w-full sm:w-auto">
                            <i class="fas fa-camera text-lg"></i>
                        </button>
                    </div>
                    </div>

                <div class="flex flex-col gap-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Categoría</label>
                    <input type="text" name="categoria" value="<?= $p->categoria ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Nombre del Producto</label>
                <input type="text" name="nombre" value="<?= $p->nombre ?>" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Descripción</label>
                <textarea name="descripcion" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"><?= $p->descripcion ?></textarea>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm" 
            x-data="imagePreview('<?= $p->imagen ? base_url('uploads/productos/'.$p->imagen.'?v='.$p->version) : '' ?>')">
            
            <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 block text-center">
                Imagen del Producto
            </label>
            
            <div class="relative group">
                <input type="file" name="imagen" accept="image/*" capture="environment" 
                    class="hidden" x-ref="imageInput" @change="updatePreview">
                
                <div @click="$refs.imageInput.click()" 
                    class="w-full min-h-[350px] bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center overflow-hidden transition-all hover:border-blue-400 cursor-pointer relative">
                    
                    <template x-if="url">
                        <img :src="url" class="w-full h-[350px] object-contain p-2">
                    </template>

                    <template x-if="!url">
                        <div class="text-center p-4">
                            <i class="fas fa-cloud-upload-alt text-5xl text-slate-200 mb-3"></i>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Click para subir o capturar</p>
                        </div>
                    </template>
                </div>

                <template x-if="url">
                    <button type="button" @click="url = null; $refs.imageInput.value = ''" 
                            class="absolute -top-3 -right-3 bg-red-500 text-white w-9 h-9 rounded-full shadow-xl flex items-center justify-center hover:bg-red-600 transition-colors border-4 border-white">
                        <i class="fas fa-times"></i>
                    </button>
                </template>
            </div>
            
            <p class="text-[9px] text-slate-400 mt-3 text-center italic">
                Tip: Las fotos verticales de celular se ajustarán automáticamente.
            </p>
        </div>

        <div class="bg-slate-900 p-8 rounded-2xl shadow-xl shadow-slate-200 space-y-6">
            <h3 class="text-white font-bold text-sm uppercase tracking-widest border-b border-white/10 pb-4">Actualizar Inventario</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Precio Compra</label>
                    <input type="number" name="precio_compra" step="0.01" value="<?= $p->precio_compra ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase text-blue-400">Precio Venta</label>
                    <input type="number" name="precio_venta" step="0.01" value="<?= $p->precio_venta ?>" required class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all text-xl font-black">
                </div>
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Stock Actual</label>
                        <input type="number" name="stock" value="<?= $p->stock ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Stock Mínimo</label>
                        <input type="number" name="stock_minimo" value="<?= $p->stock_minimo ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all font-bold">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white rounded-xl font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-emerald-500/20">
                Actualizar Cambios
            </button>
        </div>
    </div>
</form>
    </div>
</div>
<script>

function imagePreview(initialUrl = null) {
    return {
        url: initialUrl,
        updatePreview(event) {
            const file = event.target.files[0];
            if (file) {
                // Si la URL actual es un "blob" (una foto recién tomada), la borramos de memoria
                if (this.url && this.url.startsWith('blob:')) {
                    URL.revokeObjectURL(this.url);
                }
                // Creamos la nueva previsualización
                this.url = URL.createObjectURL(file);
            }
        }
    }
}
function barcodeScanner(valorInicial = '') { // <-- Añadimos el parámetro aquí
    return {
        open: false,
        codigo: valorInicial, // <-- Asignamos el valor que viene de la BD
        startScanner() {
            this.open = true;
            this.$nextTick(() => {
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#interactive'),
                        constraints: {
                            width: 640,
                            height: 480,
                            facingMode: "environment"
                        },
                    },
                    decoder: {
                        readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader"]
                    }
                }, (err) => {
                    if (err) {
                        console.error(err);
                        alert("Error al iniciar cámara");
                        return;
                    }
                    Quagga.start();
                });

                Quagga.onDetected((data) => {
                    this.codigo = data.codeResult.code;
                    this.stopScanner();
                    if (navigator.vibrate) navigator.vibrate(100);
                });
            });
        },
        stopScanner() {
            // Verificamos si Quagga está activo antes de detenerlo para evitar errores
            if (Quagga) Quagga.stop();
            this.open = false;
            
            // Limpiar visor
            const visor = document.querySelector('#interactive');
            if (visor) {
                visor.innerHTML = '<div class="absolute inset-0 border-[30px] border-black/30 pointer-events-none"></div><div class="absolute inset-x-6 top-1/2 h-[2px] bg-blue-500 shadow-[0_0_15px_#3b82f6] animate-pulse"></div>';
            }
        }
    }
}
</script>

<style>
    /* Ajuste para que el video llene el contenedor */
    #interactive video, #interactive canvas {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    [x-cloak] { display: none !important; }
</style>
