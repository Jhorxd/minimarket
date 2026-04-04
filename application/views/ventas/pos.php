<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script> <!-- [web:14] -->
<script src="https://unpkg.com/@ericblade/quagga2@latest/dist/quagga.min.js"></script>

<div class="content-wrapper bg-slate-100 !pt-0 !mt-0">
    <div class="min-h-screen px-2 pb-2 lg:px-4 lg:pb-4 pt-16 lg:pt-4" x-data="posSystem()">
        <div class="flex flex-col lg:flex-row gap-4 h-full lg:h-[calc(100vh-30px)]">
            
            <!-- Productos → en móvil va abajo (order-2), en PC va primero (lg:order-1) -->
            <div class="w-full lg:w-8/12 flex flex-col gap-3 order-2 lg:order-1">
                
                <!-- Buscador + botón escanear -->
                <div class="bg-white rounded-xl shadow-sm p-3 border border-slate-200">
                    <div class="relative flex items-center gap-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="text" 
                               x-ref="inputBusqueda"
                               class="block w-full pl-10 pr-3 py-2.5 border-none bg-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 transition-all text-slate-700"
                               placeholder="Buscar por nombre o código..." 
                               x-model="busqueda" 
                               @input="onInputBusqueda()"
                               @keydown.enter="codigoBarrasDirecto()">

                        <!-- Botón solo para móviles -->
                        <button x-show="isMobileDevice"
                                @click="abrirScannerMovil()"
                                class="shrink-0 px-3 py-2 rounded-lg bg-emerald-500 text-white text-xs font-bold flex items-center gap-1">
                            <i class="fas fa-camera"></i>
                            ESCANEAR
                        </button>
                    </div>
                </div>

                <!-- Listado de productos -->
                <div class="flex-1 overflow-y-auto pr-1 custom-scrollbar max-h-[420px] lg:max-h-none">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 pb-6">
                        <template x-for="prod in productosFiltrados" :key="prod.id">
                            <div @click="agregarItem(prod)" 
                                 class="group bg-white rounded-xl p-3 border border-slate-200 hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer relative overflow-hidden flex flex-col h-full">
                                
                                <div class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[10px] font-bold z-10"
                                     :class="parseFloat(prod.stock) > umbralStockMin(prod) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                    <span x-text="'Stock: ' + parseInt(prod.stock)"></span>
                                </div>

                                <div class="h-28 mb-2 flex items-center justify-center bg-slate-50 rounded-lg overflow-hidden">
                                    <img :src="prod.imagen 
                                            ? '<?= base_url('uploads/productos/') ?>' + prod.imagen + '?v=' + (prod.version || '1') 
                                            : 'https://placehold.co/200x200?text=Sin+Imagen'" 
                                        class="max-h-full object-contain group-hover:scale-110 transition-transform">
                                </div>

                                <div class="flex flex-col flex-1">
                                    <h3 class="text-[11px] font-black text-slate-700 uppercase leading-tight mb-1 flex-1" x-text="prod.nombre"></h3>
                                    <template x-if="prod.talla || prod.color">
                                        <div class="flex gap-1 mb-2">
                                            <template x-if="prod.talla">
                                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200" x-text="'T:' + prod.talla"></span>
                                            </template>
                                            <template x-if="prod.color">
                                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200" x-text="'C:' + prod.color"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-base font-black text-blue-600" x-text="'S/ ' + parseFloat(prod.precio_venta).toFixed(2)"></span>
                                        <div class="bg-blue-50 text-blue-600 p-1.5 rounded-md group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                            <i class="fas fa-plus text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Carrito → en móvil va arriba (order-1), en PC va segundo (lg:order-2) -->
            <div class="w-full lg:w-4/12 flex flex-col order-1 lg:order-2">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 flex flex-col h-full overflow-hidden">
                    
                    <!-- Header carrito -->
                    <div class="p-3 border-b border-slate-100 bg-slate-50 flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <h2 class="text-sm font-black text-slate-800 uppercase tracking-tighter">Venta Actual</h2>
                            <button @click="carrito = []; calcularTotal()" class="text-slate-400 hover:text-red-500 text-xs font-bold">Limpiar</button>
                        </div>
                        
                        <!-- Client Selector -->
                        <div class="flex flex-col gap-1 relative" @click.away="showDropdownClientes = false">
                            <label class="text-[10px] font-bold text-slate-500 uppercase flex justify-between">
                                Cliente:
                                <button @click="modalNuevoCliente = true" class="text-blue-500 hover:text-blue-600 focus:outline-none"><i class="fas fa-plus"></i> Nuevo</button>
                            </label>
                            
                            <input type="text" x-model="searchCliente" @focus="showDropdownClientes = true; fetchClientes()" @input="onInputClienteSearch()" placeholder="Buscar cliente..."
                                   class="w-full bg-white border border-slate-200 text-sm rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500 font-medium text-slate-700 outline-none">
                            
                            <div x-show="showDropdownClientes" style="display: none;"
                                 class="absolute top-full left-0 w-full mt-1 bg-white border border-slate-200 shadow-xl rounded-lg z-50 max-h-48 overflow-y-auto">
                                <template x-for="cli in clientesFiltrados" :key="cli.id_cliente">
                                    <div @click="seleccionarCliente(cli)" 
                                         class="px-3 py-2 text-sm text-slate-700 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0"
                                         :class="idCliente == cli.id_cliente ? 'bg-blue-50 font-bold' : ''">
                                        <div x-text="cli.nombre"></div>
                                        <div x-show="cli.nro_documento" class="text-[10px] text-slate-400" x-text="cli.tipo_documento + ': ' + cli.nro_documento"></div>
                                    </div>
                                </template>
                                <div x-show="clientesFiltrados.length === 0" class="px-3 py-2 text-sm text-slate-500 italic">
                                    No se encontraron clientes
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items del carrito -->
                    <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                        <template x-for="(item, index) in carrito" :key="index">
                            <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100 relative group">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-800 uppercase truncate">
                                        <span x-text="item.nombre"></span>
                                        <template x-if="item.talla || item.color">
                                            <span class="ml-1 text-[8px] text-slate-400 font-bold" x-text="(item.talla ? 'T:'+item.talla : '') + (item.color ? ' C:'+item.color : '')"></span>
                                        </template>
                                        <span x-show="item.es_promo" class="ml-1 text-[8px] bg-amber-100 text-amber-600 px-1 py-0.5 rounded font-black">Promo</span>
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[11px] text-blue-600 font-black">S/</span>
                                        <input type="number" 
                                               step="0.01"
                                               class="w-20 text-[11px] text-blue-600 font-black border-none focus:ring-1 focus:ring-blue-300 p-0 bg-transparent rounded hover:bg-white transition-colors"
                                               x-model="item.precio" 
                                               @input="calcularTotal()">
                                    </div>
                                </div>
                                
                                <div class="flex items-center bg-white rounded-lg border border-slate-200 shrink-0">
                                    <button @click="restarCant(index)" class="w-6 h-6 flex items-center justify-center hover:bg-slate-100 text-slate-500 text-xs">-</button>
                                    <input type="number" class="w-8 text-center border-none focus:ring-0 text-xs font-bold p-0 bg-transparent" x-model="item.cantidad" @input="calcularTotal()">
                                    <button @click="sumarCant(index)" class="w-6 h-6 flex items-center justify-center hover:bg-slate-100 text-slate-500 text-xs">+</button>
                                </div>

                                <button @click="eliminarItem(index)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </template>

                        <!-- Estado vacío -->
                        <div x-show="carrito.length === 0" class="flex flex-col items-center justify-center py-10 text-slate-300">
                            <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                            <p class="text-xs font-bold uppercase">Carrito vacío</p>
                        </div>
                    </div>

                    <!-- Footer: método de pago + total + botón -->
                    <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                        <!-- Método de pago -->
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Método de Pago</p>
                            <div class="grid grid-cols-3 gap-1.5">
                                <template x-for="metodo in ['efectivo','tarjeta','yape','plin','transferencia']" :key="metodo">
                                    <button @click="metodoPago = metodo; montoRecibido = ''"
                                            :class="metodoPago === metodo 
                                                ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-100' 
                                                : 'bg-white text-slate-500 border-slate-200 hover:border-blue-300'"
                                            class="py-2 px-1 rounded-xl border text-[9px] font-black uppercase tracking-wider transition-all">
                                        <span x-text="metodo"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Monto recibido (solo efectivo) -->
                        <div x-show="metodoPago === 'efectivo'" x-transition>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Monto Recibido</p>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-black text-slate-400">S/</span>
                                <input type="number" step="0.01" placeholder="0.00"
                                       x-model="montoRecibido"
                                       class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-800 font-black text-sm outline-none focus:border-blue-500 transition-all">
                            </div>
                            <!-- Vuelto -->
                            <div x-show="montoRecibido >= totalVenta && montoRecibido > 0" x-transition
                                 class="mt-2 flex justify-between items-center bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2">
                                <span class="text-[10px] font-black text-emerald-600 uppercase">Vuelto</span>
                                <span class="text-sm font-black text-emerald-600" x-text="'S/ ' + vuelto"></span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-slate-800 font-black tracking-tighter text-xl">TOTAL</span>
                            <span class="text-blue-600 font-black text-xl" x-text="'S/ ' + totalVenta"></span>
                        </div>
                        
                        <button @click="procesarVenta()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95 text-sm">
                            <i class="fas fa-check-circle"></i>
                            FINALIZAR VENTA (F9)
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <div x-show="mostrarScanner"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/90 flex items-center justify-center z-[100] p-4">
        
            <div class="bg-white rounded-2xl p-4 w-full max-w-sm overflow-hidden shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-tighter">Escáner de Barras</h3>
                    <button @click="cerrarScannerMovil()" class="text-slate-400 hover:text-red-500">
                        <i class="fas fa-times-circle text-xl"></i>
                    </button>
                </div>

                <div class="relative bg-black rounded-xl overflow-hidden aspect-square border-4 border-slate-100">
                    <div id="reader" class="w-full h-full object-cover"></div>
                    
                    <div class="absolute inset-0 border-[40px] border-black/40 pointer-events-none"></div>
                    <div class="absolute inset-x-8 top-1/2 -translate-y-1/2 h-[2px] bg-red-500 shadow-[0_0_8px_red] animate-pulse"></div>
                    <div class="absolute inset-8 border-2 border-emerald-400 rounded-lg pointer-events-none"></div>
                </div>

                <p class="text-[10px] font-bold text-slate-500 mt-4 text-center uppercase tracking-widest">
                    Alinea el código con la línea roja
                </p>
                
                <button @click="cerrarScannerMovil()"
                        class="mt-4 w-full bg-slate-800 text-white text-xs font-bold py-3 rounded-xl active:scale-95 transition-transform">
                    CANCELAR
                </button>
            </div>
        </div>


<!-- Modal Nuevo Cliente -->
<div x-show="modalNuevoCliente" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalNuevoCliente" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" @click="modalNuevoCliente = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="modalNuevoCliente" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100">
                <h3 class="text-lg leading-6 font-black text-slate-800" id="modal-title">Agregar Nuevo Cliente</h3>
            </div>
            <div class="px-4 py-5 sm:p-6 space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        <span x-text="nuevoCliente.tipo_documento === 'DNI' ? 'Nombre Completo' : 'Razón Social'"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="nuevoCliente.nombre" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border" placeholder="Ej. Juan Pérez">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Doc.</label>
                        <select x-model="nuevoCliente.tipo_documento" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="CE">C.Extranjería</option>
                            <option value="PASAPORTE">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Número</label>
                        <input type="text" x-model="nuevoCliente.nro_documento" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Teléfono</label>
                    <input type="text" x-model="nuevoCliente.telefono" class="w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 p-2 border">
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                <button type="button" @click="guardarNuevoCliente()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm font-bold">
                    Guardar Cliente
                </button>
                <button type="button" @click="modalNuevoCliente = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-bold">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

    </div>
</div>



<style>
    .content-header { display: none !important; }
    @media (min-width: 992px) {
        .content-wrapper { margin-left: 250px !important; padding-top: 0 !important; }
    }
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    /* Quitar flechas del input number para estética */
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

[x-cloak] {
    display: none !important;
}

</style>

<script>
function posSystem() {
    return {
        busqueda: '',
        listaProductos: [],
        carrito: [],
        totalVenta: '0.00',
        isMobile: window.innerWidth < 1024,
        metodoPago: 'efectivo',
        montoRecibido: '',
        _debounceTimer: null,

        isMobileDevice: /(android|iphone|ipad|mobile)/i.test(navigator.userAgent),
        mostrarScanner: false,
        html5QrCode: null,

        promociones: <?= json_encode($promociones ?? []) ?>,
        clientes: <?= json_encode($clientes ?? []) ?>,
        idCliente: 1,

        searchCliente: '',
        showDropdownClientes: false,
        modalNuevoCliente: false,
        nuevoCliente: {nombre: '', tipo_documento: 'DNI', nro_documento: '', telefono: ''},

        init() {
            let c = this.clientes.find(x => x.id_cliente == this.idCliente);
            if(c) this.searchCliente = c.nombre;
        },

        _debounceTimerCliente: null,
        onInputClienteSearch() {
            clearTimeout(this._debounceTimerCliente);
            this._debounceTimerCliente = setTimeout(() => {
                this.fetchClientes();
            }, 300);
        },

        fetchClientes() {
            // Solo buscar si el input cambió de texto real. Si es un click (focus), traer top 30
            const url = `<?= base_url('ventas/buscar_clientes_ajax') ?>?term=${encodeURIComponent(this.searchCliente)}`;
            fetch(url)
                .then(res => res.json())
                .then(data => { 
                    // Asegurarnos que el cliente seleccionado no se borre de la lista visual si existe
                    // Aunque en AJAX no importa tanto
                    this.clientes = data; 
                });
        },

        get clientesFiltrados() {
            // Filtrado local es opcional porque ya se filtra en backend, pero lo dejamos por consistencia de UI
            if(this.searchCliente.trim() === '') {
                // If search is empty or just matches the selected client exactly, show all
                let c = this.clientes.find(x => x.id_cliente == this.idCliente);
                if (c && this.searchCliente === c.nombre) return this.clientes;
            }
            return this.clientes;
        },

        seleccionarCliente(cli) {
            this.idCliente = cli.id_cliente;
            this.searchCliente = cli.nombre;
            this.showDropdownClientes = false;
        },

        guardarNuevoCliente() {
            if(!this.nuevoCliente.nombre) {
                Swal.fire('Error', 'El nombre del cliente es obligatorio', 'error');
                return;
            }
            fetch('<?= base_url('clientes/guardar_ajax') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(this.nuevoCliente)
            }).then(r => r.json()).then(data => {
                if(data.success) {
                    this.clientes.push(data.cliente);
                    this.seleccionarCliente(data.cliente);
                    this.modalNuevoCliente = false;
                    this.nuevoCliente = {nombre: '', tipo_documento: 'DNI', nro_documento: '', telefono: ''};
                    Swal.fire({
                        toast: true, position: 'top-end',
                        icon: 'success', title: 'Cliente agregado',
                        showConfirmButton: false, timer: 1500
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            }).catch(e => {
                Swal.fire('Error', 'No se pudo conectar', 'error');
            });
        },

        get productosFiltrados() {
            const limite = this.isMobile ? 8 : 40;
            return this.listaProductos.slice(0, limite);
        },

        get vuelto() {
            const recibido = parseFloat(this.montoRecibido) || 0;
            const total = parseFloat(this.totalVenta) || 0;
            return recibido >= total ? (recibido - total).toFixed(2) : '0.00';
        },

        /**
         * Alerta de stock bajo: el umbral nunca es menor a 2 unidades (aunque el mínimo del producto sea 1).
         * Verde si stock > umbral; rojo si stock <= umbral.
         */
        umbralStockMin(prod) {
            const min = parseFloat(prod.stock_minimo);
            const base = (!isNaN(min) && min >= 0) ? min : 0;
            return Math.max(2, base);
        },

        init() {
            console.log("🚀 Sistema POS Iniciado");
            this.fetchProductos();

            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 1024;
            });

            window.addEventListener('keydown', (e) => {
                // 1. Ejecutar venta con F9
                if (e.key === 'F9') {
                    e.preventDefault();
                    this.procesarVenta();
                }

                // 2. Control inteligente del Foco
                // Obtenemos qué elemento tiene el foco actualmente
                const elementoActivo = document.activeElement.tagName;

                // Solo enfocamos el buscador si:
                // - Es una tecla de carácter (e.key.length === 1)
                // - NO estamos ya escribiendo en un INPUT, TEXTAREA o SELECT
                if (e.key.length === 1 && 
                    elementoActivo !== 'INPUT' && 
                    elementoActivo !== 'TEXTAREA' && 
                    elementoActivo !== 'SELECT') {
                    
                    this.$refs.inputBusqueda.focus();
                }
            });
        },

        onInputBusqueda() {
            clearTimeout(this._debounceTimer);
            this._debounceTimer = setTimeout(() => {
                this.fetchProductos();
            }, 300);
        },

        fetchProductos() {
            const url = `<?= base_url('ventas/buscar_productos_ajax') ?>?term=${encodeURIComponent(this.busqueda)}`;
            fetch(url)
                .then(res => res.json())
                .then(data => { this.listaProductos = data; })
                .catch(err => console.error("❌ Error:", err));
        },

        codigoBarrasDirecto() {
            if (!this.busqueda.trim()) return;

            const url = `<?= base_url('ventas/buscar_productos_ajax') ?>?term=${encodeURIComponent(this.busqueda)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    this.listaProductos = data;

                    if (data.length === 1) {
                        this.agregarItem(data[0]);
                        this.busqueda = '';
                        this.fetchProductos();
                    } else if (data.length === 0) {
                        Swal.fire({
                            title: '❌ No encontrado',
                            text: `El código "${this.busqueda}" no está registrado`,
                            icon: 'warning',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        this.busqueda = '';
                    }
                })
                .catch(err => console.error("❌ Error scanner:", err));
        },

abrirScannerMovil() {
    this.mostrarScanner = true;
    // Variable de control interna para esta sesión de escaneo
    let escaneoFinalizado = false;

    this.$nextTick(() => {
        Quagga.init({
            inputStream: {
                type: "LiveStream",
                target: document.querySelector("#reader"),
                constraints: {
                    width: { min: 640 },
                    height: { min: 480 },
                    facingMode: "environment"
                },
                area: { top: "15%", right: "10%", left: "10%", bottom: "15%" },
            },
            decoder: {
                // Eliminamos formatos raros que causan falsos positivos
                readers: ["code_128_reader", "ean_reader", "ean_8_reader"]
            },
            locate: true
        }, (err) => {
            if (err) {
                Swal.fire('Error', 'No se pudo iniciar la cámara', 'error');
                this.mostrarScanner = false;
                return;
            }
            Quagga.start();
        });

        Quagga.onDetected((result) => {
            // 1. Si ya procesamos uno, ignoramos el resto
            if (escaneoFinalizado) return;

            // 2. Extraer el código
            const code = result.codeResult.code;

            // 3. VALIDACIÓN CRÍTICA: Que no esté vacío y tenga longitud mínima
            // La mayoría de códigos de barras tienen al menos 4-5 dígitos
            if (code && code.trim() !== "" && code.length >= 4) {
                
                // 4. Marcamos como finalizado para detener el bucle
                escaneoFinalizado = true;
                
                // Feedback visual y sonoro
                if (navigator.vibrate) navigator.vibrate(100);
                this.reproducirBeep(); // Si quieres añadir el sonido

                // 5. Ejecutar lógica de negocio
                this.busqueda = code;
                
                // Detenemos la cámara antes de buscar para liberar recursos
                Quagga.stop();
                
                this.codigoBarrasDirecto();
                this.cerrarScannerMovil();
            }
        });
    });
},

// Función auxiliar para emitir un pitido de confirmación
reproducirBeep() {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioCtx.destination);

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // Nota La (A5)
    gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);

    oscillator.start();
    oscillator.stop(audioCtx.currentTime + 0.1);
},

cerrarScannerMovil() {
    this.mostrarScanner = false;
    if (typeof Quagga !== 'undefined') {
        Quagga.stop();
        // Limpiamos el contenido del reader para apagar la cámara físicamente
        const reader = document.querySelector("#reader");
        if (reader) reader.innerHTML = "";
    }
},



        agregarItem(p) {
            if (parseFloat(p.stock) <= 0) {
                return Swal.fire('Sin Stock', 'No hay unidades disponibles', 'warning');
            }
            const existe = this.carrito.find(item => item.id === p.id);
            if (existe) {
                if (existe.cantidad < parseFloat(p.stock)) {
                    existe.cantidad++;
                } else {
                    Swal.fire('Límite de Stock', 'No puedes agregar más del stock disponible', 'error');
                }
            } else {
                this.carrito.push({
                    id: p.id,
                    id_categoria: p.id_categoria, // Añadido para agrupar promos
                    nombre: p.nombre,
                    talla: p.talla,
                    color: p.color,
                    precio: p.precio_venta,
                    cantidad: 1,
                    stock: p.stock
                });
            }
            this.calcularTotal();
        },

        sumarCant(index) {
            if (this.carrito[index].cantidad < parseFloat(this.carrito[index].stock)) {
                this.carrito[index].cantidad++;
                this.calcularTotal();
            }
        },

        restarCant(index) {
            if (this.carrito[index].cantidad > 1) {
                this.carrito[index].cantidad--;
                this.calcularTotal();
            }
        },

        eliminarItem(index) {
            this.carrito.splice(index, 1);
            this.calcularTotal();
        },

        calcularTotal() {
            let total = 0;
            // Clonamos para procesar
            let items = this.carrito.map(item => ({...item}));
            this.carrito.forEach(i => i.es_promo = false); // reset visual flag

            if(this.promociones && this.promociones.length > 0) {
                this.promociones.forEach(promo => {
                    if (promo.id_categoria) {
                        let idCat = promo.id_categoria;
                        let cantReq = parseInt(promo.cantidad_requerida);
                        let pCombo = parseFloat(promo.precio_combo);

                        let cantTotal = items.filter(i => i.id_categoria == idCat).reduce((sum, i) => sum + parseInt(i.cantidad), 0);

                        if (cantTotal >= cantReq) {
                            let paquetes = Math.floor(cantTotal / cantReq);
                            let cantEnPromo = paquetes * cantReq;
                            let pUnitPromedio = (paquetes * pCombo) / cantEnPromo;
                            
                            let aDescontar = cantEnPromo;
                            
                            // Marcar en UI
                            this.carrito.filter(i => i.id_categoria == idCat).forEach(realItem => {
                                let localItem = items.find(j => j.id == realItem.id);
                                if(localItem && localItem.cantidad > 0 && aDescontar > 0) {
                                    let tomar = Math.min(localItem.cantidad, aDescontar);
                                    total += tomar * pUnitPromedio;
                                    localItem.cantidad -= tomar;
                                    aDescontar -= tomar;
                                    realItem.es_promo = true; // Flag para UI
                                }
                            });
                        }
                    }
                });
            }

            // Sumar el resto
            items.forEach(it => {
                if(it.cantidad > 0) {
                    total += it.cantidad * parseFloat(it.precio);
                }
            });

            this.totalVenta = total.toFixed(2);
        },

        procesarVenta() {
            if (this.carrito.length === 0) return Swal.fire('Carrito Vacío', 'Agrega productos', 'error');

            if (this.metodoPago === 'efectivo' && (!this.montoRecibido || parseFloat(this.montoRecibido) < parseFloat(this.totalVenta))) {
                return Swal.fire('Monto insuficiente', 'Ingresa el monto recibido', 'warning');
            }

            const resumenPago = this.metodoPago === 'efectivo'
                ? `<br><span style="font-size:12px; color:#64748b;">Recibido: S/ ${parseFloat(this.montoRecibido).toFixed(2)} · Vuelto: S/ ${this.vuelto}</span>`
                : `<br><span style="font-size:12px; color:#64748b;">Pago con: ${this.metodoPago}</span>`;

            Swal.fire({
                title: '¿Confirmar Venta?',
                html: `Total a cobrar: <b>S/ ${this.totalVenta}</b>${resumenPago}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Sí, generar ticket'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= base_url('ventas/guardar') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            carrito: this.carrito,
                            total: this.totalVenta,
                            metodo_pago: this.metodoPago,
                            monto_recibido: this.metodoPago === 'efectivo' ? this.montoRecibido : this.totalVenta,
                            vuelto: this.metodoPago === 'efectivo' ? this.vuelto : '0.00',
                            id_cliente: this.idCliente
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.carrito = [];
                            this.totalVenta = '0.00';
                            this.metodoPago = 'efectivo';
                            this.montoRecibido = '';
                            this.fetchProductos();

                            const ticketUrl = `<?= base_url('ventas/ticket/') ?>${data.id_venta}`;

                            Swal.fire({
                                title: `✅ Ticket #${String(data.id_venta).padStart(6, '0')}`,
                                html: `
                                    <iframe src="${ticketUrl}" 
                                            style="width:100%; height:420px; border:none; border-radius:8px;"
                                            id="iframe-ticket">
                                    </iframe>
                                    <div style="margin-top:12px; display:flex; gap:8px; justify-content:center;">
                                        <button onclick="document.getElementById('iframe-ticket').contentWindow.print()"
                                                style="display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:10px; font-weight:700; font-size:12px; cursor:pointer;">
                                            🖨️ Imprimir
                                        </button>
                                        <a href="${ticketUrl}" target="_blank"
                                           style="display:inline-flex; align-items:center; gap:6px; padding:10px 20px; background:#f1f5f9; color:#475569; border-radius:10px; font-weight:700; text-decoration:none;">
                                            ↗ Abrir en pestaña
                                        </a>
                                    </div>
                                `,
                                showConfirmButton: false,
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar',
                                cancelButtonColor: '#94a3b8',
                                width: 420,
                                padding: '1.5rem'
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo registrar la venta', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Falló la conexión con el servidor', 'error'));
                }
            });
        }
    }
}
</script>
