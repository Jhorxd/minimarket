<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($productos), ENT_QUOTES, 'UTF-8') ?>,
        search: '', 
        filterCat: '', 
        filterAlm: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            const q = this.search.toLowerCase();
            return this.items.filter(i => {
                const matchSearch = q === '' || 
                                   i.nombre.toLowerCase().includes(q) || 
                                   i.codigo_barras.toLowerCase().includes(q) ||
                                   (i.categoria_nombre && i.categoria_nombre.toLowerCase().includes(q)) ||
                                   (i.almacen_nombre && i.almacen_nombre.toLowerCase().includes(q));
                const matchCat = this.filterCat === '' || i.id_categoria == this.filterCat;
                const matchAlm = this.filterAlm === '' || i.id_almacen == this.filterAlm;
                return matchSearch && matchCat && matchAlm;
            });
        },
        get pagedItems() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredItems.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
        },
        nextPage() { if(this.page < this.totalPages) this.page++; },
        prevPage() { if(this.page > 1) this.page--; },
        resetPage() { this.page = 1; },
        resetFilters() { 
            this.search = '';
            this.filterCat = '';
            this.filterAlm = '';
            this.page = 1;
        },
        exportExcel() {
            const data = this.filteredItems.map(i => ({
                'Código': i.codigo_barras,
                'Producto': i.nombre,
                'Categoría': i.categoria_nombre || '-',
                'Almacén': i.almacen_nombre || '-',
                'Stock': i.stock
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Stock');
            XLSX.writeFile(wb, 'Reporte_Stock_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE STOCK E INVENTARIO', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.codigo_barras,
                i.nombre,
                i.categoria_nombre || '-',
                i.almacen_nombre || '-',
                i.stock
            ]);

            doc.autoTable({
                startY: 35,
                head: [['Código', 'Producto', 'Categoría', 'Almacén', 'Stock']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [59, 130, 246], // Blue-500
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Reporte_Stock_' + new Date().getTime() + '.pdf');
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Control de Inventario</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Stock de Almacén</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Monitoreo en tiempo real de existencias y ubicaciones</p>
            </div>
            
            <!-- Botones de Acción Superiores -->
            <div class="flex items-center gap-2">
                <button @click="resetFilters()" 
                    class="px-5 py-3.5 bg-white border border-slate-200 text-slate-500 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-eraser text-xs"></i> Limpiar
                </button>
                <button @click="exportExcel()"
                    class="px-5 py-3.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-excel text-xs"></i> Excel
                </button>
                <button @click="exportPDF()"
                    class="px-5 py-3.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-rose-600 hover:text-white transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-pdf text-xs"></i> PDF
                </button>
            </div>
        </header>

        <!-- Alertas -->
        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-8 px-6 py-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-widest flex items-center gap-3 shadow-sm anim-fade">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Panel de Control y Filtros -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden mb-10">
            
            <div class="px-8 py-7 border-b border-slate-100 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Filtros de Búsqueda Avanzada</div>
                    <div class="text-[10px] font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-full">Sucursal: <?= $this->session->userdata('sucursal_nombre') ?></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Buscador -->
                    <div class="md:col-span-2 relative group">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" x-model="search" @input="resetPage()" placeholder="Nombre o código de barras del producto..."
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-xs font-bold focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all text-slate-700">
                    </div>

                    <!-- Filtro Categoría -->
                    <div class="relative group">
                        <select x-model="filterCat" @change="resetPage()" class="w-full pl-5 pr-10 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all text-slate-600 appearance-none">
                            <option value="">Todas las Categorías</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                    </div>

                    <!-- Filtro Almacén -->
                    <div class="relative group">
                        <select x-model="filterAlm" @change="resetPage()" class="w-full pl-5 pr-10 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all text-slate-600 appearance-none">
                            <option value="">Todos los Almacenes</option>
                            <?php foreach($almacenes as $alm): ?>
                                <option value="<?= $alm->id ?>"><?= htmlspecialchars($alm->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- Tabla Reactiva -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Visual / Producto</th>
                            <th class="px-6 py-5">Categoría</th>
                            <th class="px-6 py-5">Ubicación Actual</th>
                            <th class="px-6 py-5 text-right">Inversión Unit.</th>
                            <th class="px-6 py-5 text-center">Nivel de Stock</th>
                            <th class="px-6 py-5 text-center">Alertas</th>
                            <th class="px-8 py-5 text-right">Mantenimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="p in pagedItems" :key="p.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-inner border border-slate-100 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-box text-slate-200 text-lg group-hover:text-blue-500 transition-colors"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm tracking-tight" x-text="p.nombre"></p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[9px] font-black font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100" x-text="p.codigo_barras"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <template x-if="p.categoria_nombre">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full" :style="'background:' + p.categoria_color"></div>
                                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest" x-text="p.categoria_nombre"></span>
                                        </div>
                                    </template>
                                    <template x-if="!p.categoria_nombre">
                                        <span class="text-[10px] text-slate-300 italic uppercase font-black">Sin definir</span>
                                    </template>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <i class="fas fa-warehouse text-[10px] text-slate-300"></i>
                                        <span class="text-[10px] font-black uppercase tracking-tight" x-text="p.almacen_nombre || 'Depósito Gral.'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-xs font-black text-slate-900 leading-none">S/ <span x-text="parseFloat(p.precio_venta).toFixed(2)"></span></div>
                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">Venta Directa</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span :class="parseFloat(p.stock) <= Math.max(2, parseFloat(p.stock_minimo) || 0) ? 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-100/50' : 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-100/30'"
                                              class="px-5 py-1.5 rounded-xl text-xs font-black border shadow-lg"
                                              x-text="parseFloat(p.stock).toFixed(0)">
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <template x-if="parseFloat(p.stock) <= Math.max(2, parseFloat(p.stock_minimo) || 0)">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-full text-[9px] font-black uppercase tracking-[0.1em] animate-pulse">
                                            <i class="fas fa-exclamation-triangle text-[10px]"></i> Crítico
                                        </span>
                                    </template>
                                    <template x-if="parseFloat(p.stock) > Math.max(2, parseFloat(p.stock_minimo) || 0)">
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Normal</span>
                                    </template>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a :href="'<?= base_url('almacen/ajustar/') ?>' + p.id"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 transition-all transform active:scale-95 group/btn">
                                        <i class="fas fa-sliders-h text-slate-400 group-hover/btn:rotate-180 transition-transform duration-500"></i>
                                        Ajustes
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer y Paginación -->
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Auditoría: registrados <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> productos
                </div>
                
                <div class="flex items-center gap-3" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
                                x-show="Math.abs(p - page) <= 2 || p === 1 || p === totalPages"
                                :class="p === page ? 'bg-blue-600 text-white border-blue-600 shadow-xl shadow-blue-200 scale-110' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="w-12 h-12 rounded-2xl border font-black text-xs transition-all flex items-center justify-center"
                                x-text="p">
                            </button>
                        </template>
                    </div>

                    <button @click="nextPage()" :disabled="page === totalPages"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- Empty State -->
        <template x-if="filteredItems.length === 0">
            <div class="py-24 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-boxes text-slate-300 text-4xl opacity-30"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin registros de stock</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">No hay productos que coincidan con los filtros aplicados.</p>
            </div>
        </template>
    </div>
</div>
