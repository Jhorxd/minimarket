<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($proveedores), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.razon_social.toLowerCase().includes(q) ||
                (i.nombre_comercial && i.nombre_comercial.toLowerCase().includes(q)) ||
                i.nro_documento.toLowerCase().includes(q) ||
                (i.telefono && i.telefono.includes(q)) ||
                (i.rubro && i.rubro.toLowerCase().includes(q))
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
                'Empresa/Razón Social': i.razon_social,
                'Nombre Comercial': i.nombre_comercial || '-',
                'RUC/Documento': i.nro_documento,
                'Teléfono': i.telefono || '-',
                'Correo': i.email || '-',
                'Rubro': i.rubro || '-'
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Proveedores');
            XLSX.writeFile(wb, 'Reporte_Proveedores_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            // Título Visual Premium
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59); // Slate-800
            doc.text('REPORTE DE PROVEEDORES', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.razon_social,
                i.nro_documento,
                i.telefono || '-',
                i.email || '-',
                i.rubro || '-'
            ]);

            doc.autoTable({
                startY: 35,
                head: [['Proveedor', 'Documento', 'Teléfono', 'Email', 'Rubro']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [30, 41, 59], // Slate-800 (#1e293b)
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Reporte_Proveedores_' + new Date().getTime() + '.pdf');
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Logística & Abastecimiento</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Proveedores</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Gestión de contactos y relaciones comerciales</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('proveedores/crear') ?>"
                   class="flex items-center px-5 py-3.5 bg-slate-900 hover:bg-black text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-slate-200 active:scale-95">
                    <i class="fas fa-truck-loading mr-3 text-sm"></i> Nuevo Proveedor
                </a>
            </div>
        </header>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-search text-lg"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por Razón Social, RUC, Nombre Comercial o Rubro..."
                    class="w-full pl-14 pr-8 py-3.5 bg-white border border-slate-200 rounded-2xl shadow-sm shadow-slate-100 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-700">
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

        <?php if ($this->session->flashdata('msg_error')): ?>
            <div class="mb-4 px-6 py-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 text-[10px] font-black uppercase tracking-widest flex items-center gap-3 shadow-sm anim-fade">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span><?= $this->session->flashdata('msg_error'); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-8 px-6 py-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-widest flex items-center gap-3 shadow-sm anim-fade">
                <i class="fas fa-check-double text-emerald-500 text-lg"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Listado de Proveedores -->
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Entidad Comercial</th>
                            <th class="px-6 py-5">Documento / RUC</th>
                            <th class="px-6 py-5">Contacto Directo</th>
                            <th class="px-6 py-5 text-center">Rubro</th>
                            <th class="px-8 py-5 text-right">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="p in pagedItems" :key="p.id_proveedor">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-inner border border-slate-100 group-hover:bg-slate-900 transition-all duration-300">
                                            <i class="fas fa-industry text-slate-300 group-hover:text-white transition-colors"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm tracking-tight" x-text="p.razon_social"></p>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5 italic" x-text="p.nombre_comercial || 'S. N. Comercial'"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="p.tipo_documento"></div>
                                    <div class="text-xs font-black text-slate-700 tracking-widest font-mono" x-text="p.nro_documento"></div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-phone-alt text-[9px] text-emerald-400"></i>
                                            <span class="text-xs font-black text-slate-700" x-text="p.telefono || 'Sin teléfono'"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-at text-[9px] text-blue-400"></i>
                                            <span class="text-[10px] font-medium text-slate-400 italic" x-text="p.email || 'Sin correo'"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200"
                                          x-text="p.rubro || 'General'">
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a :href="'<?= base_url('proveedores/editar/') ?>' + p.id_proveedor" 
                                           class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Editar">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a :href="'<?= base_url('proveedores/eliminar/') ?>' + p.id_proveedor" 
                                           onclick="return confirm('¿Eliminar proveedor?')" 
                                           class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Eliminar">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer y Paginación -->
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Socios Estratégicos: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> entidades
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
                                :class="p === page ? 'bg-slate-900 text-white border-slate-900 shadow-xl shadow-slate-200 scale-110' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="w-12 h-12 rounded-2xl border font-black text-[10px] transition-all flex items-center justify-center"
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
                    <i class="fas fa-truck text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin registros</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">No hay proveedores que coincidan con la búsqueda.</p>
            </div>
        </template>
    </div>
</div>
