<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($compras), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 12,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.id.toString().includes(q) ||
                (i.proveedor_razon && i.proveedor_razon.toLowerCase().includes(q)) ||
                (i.proveedor && i.proveedor.toLowerCase().includes(q)) ||
                i.usuario.toLowerCase().includes(q)
            );
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
            this.page = 1;
        },
        exportExcel() {
            const data = this.filteredItems.map(i => ({
                'ID': i.id,
                'Fecha Registro': i.fecha_registro,
                'Proveedor': i.proveedor_razon || i.proveedor || 'Sin detallar',
                'Registrado Por': i.usuario,
                'Total': parseFloat(i.total)
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Compras');
            XLSX.writeFile(wb, 'Reporte_Compras_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE COMPRAS E INVENTARIO', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.id,
                i.fecha_registro,
                i.proveedor_razon || i.proveedor || 'Sin detallar',
                i.usuario,
                'S/ ' + parseFloat(i.total).toFixed(2)
            ]);

            doc.autoTable({
                startY: 35,
                head: [['ID', 'Fecha de Operación', 'Origen / Proveedor', 'Registrado Por', 'Inversión Bruta']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [30, 41, 59], // Slate-800
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Reporte_Compras_' + new Date().getTime() + '.pdf');
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Abastecimiento & Gastos</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Historial de Compras</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Registro de ingresos de mercadería y costos operativos</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('compras/nueva') ?>"
                   class="flex items-center px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                    <i class="fas fa-file-invoice-dollar mr-3 text-sm"></i> Registrar Nueva Compra
                </a>
            </div>
        </header>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">
                    <i class="fas fa-search text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por #Compra, Proveedor o Usuario..."
                    class="w-full pl-14 pr-8 py-3.5 bg-white border border-slate-200 rounded-2xl shadow-sm focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center gap-2">
                <button @click="resetFilters()" 
                    class="h-full px-5 py-3.5 bg-white border border-slate-200 text-slate-500 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-eraser text-xs"></i> Limpiar
                </button>
                <button @click="exportExcel()"
                    class="h-full px-5 py-3.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-excel text-xs"></i> Excel
                </button>
                <button @click="exportPDF()"
                    class="h-full px-5 py-3.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl font-black uppercase tracking-widest text-[9px] hover:bg-rose-600 hover:text-white transition-all flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-pdf text-xs"></i> PDF
                </button>
            </div>
        </div>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-8 px-6 py-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-widest flex items-center gap-3 shadow-sm anim-fade">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Listado de Compras -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Referencia / ID</th>
                            <th class="px-6 py-5">Fecha de Operación</th>
                            <th class="px-6 py-5">Origen / Proveedor</th>
                            <th class="px-6 py-5 text-right">Inversión Bruta</th>
                            <th class="px-6 py-5 text-center">Registrado Por</th>
                            <th class="px-8 py-5 text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="c in pagedItems" :key="c.id">
                            <tr class="hover:bg-emerald-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-inner border border-slate-100 group-hover:bg-emerald-600 transition-all duration-300">
                                            <i class="fas fa-file-invoice text-slate-300 group-hover:text-white transition-colors text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm tracking-tight" x-text="'#' + c.id.toString().padStart(6, '0')"></p>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 italic">Folio Interno</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[11px] font-black text-slate-700" x-text="c.fecha_registro.split(' ')[0]"></div>
                                    <div class="text-[9px] text-slate-400 font-bold uppercase mt-0.5 tracking-tighter" x-text="c.fecha_registro.split(' ')[1]"></div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-emerald-400 rounded-full"></div>
                                        <span class="text-xs font-black text-slate-800 uppercase max-w-[180px] truncate" 
                                              :title="c.proveedor_razon || c.proveedor"
                                              x-text="c.proveedor_razon || c.proveedor || 'Sin detallar'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-black text-slate-900 leading-none">S/ <span x-text="parseFloat(c.total).toFixed(2)"></span></div>
                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Importe Total</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200"
                                          x-text="c.usuario">
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a :href="'<?= base_url('compras/ver_compras/') ?>' + c.id"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-[0.1em] hover:bg-emerald-700 transition-all shadow-md active:scale-95">
                                        <i class="fas fa-eye text-[10px] text-emerald-400"></i>
                                        Detalle
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
                    Registros: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> compras
                </div>
                
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
                                x-show="Math.abs(p - page) <= 2 || p === 1 || p === totalPages"
                                :class="p === page ? 'bg-emerald-600 text-white border-emerald-600 shadow-xl shadow-emerald-200 scale-110' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
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

        <!-- Empty State -->
        <template x-if="filteredItems.length === 0">
            <div class="py-24 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-file-invoice text-slate-300 text-4xl opacity-30"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin coincidencias</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">No hay facturas de compra que coincidan con la búsqueda.</p>
            </div>
        </template>
    </div>
</div>
