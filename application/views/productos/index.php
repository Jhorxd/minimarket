<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
function catalogoProductos() {
    return {
        items: <?= json_encode($productos) ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.nombre.toLowerCase().includes(q) ||
                i.codigo_barras.toLowerCase().includes(q) ||
                (i.categoria && i.categoria.toLowerCase().includes(q))
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
                'Código': i.codigo_barras,
                'Producto': i.nombre + (i.variantes_detalle ? ' [' + i.variantes_detalle + ']' : ''),
                'Categoría': i.categoria || '-',
                'Precio Venta': i.precio_venta,
                'Stock': i.stock,
                'Stock Mín': i.stock_minimo
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Productos');
            XLSX.writeFile(wb, 'Reporte_Productos_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE PRODUCTOS', 14, 20);
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);
            const tableData = this.filteredItems.map(i => [
                i.codigo_barras,
                i.nombre + (i.variantes_detalle ? '\n(' + i.variantes_detalle + ')' : ''),
                i.categoria || '-',
                'S/ ' + parseFloat(i.precio_venta).toFixed(2),
                i.stock
            ]);
            doc.autoTable({
                startY: 35,
                head: [['Código', 'Producto', 'Categoría', 'Precio Venta', 'Stock']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [59, 130, 246], 
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });
            doc.save('Reporte_Productos_' + new Date().getTime() + '.pdf');
        },
        async confirmarEliminacion(p) {
            try {
                const response = await fetch('<?= base_url("productos/verificar_historial/") ?>' + p.id);
                const data = await response.json();
                
                let warningHtml = '<p class="text-sm text-slate-500 font-medium mt-2">¿Estás seguro de que deseas eliminar este producto?</p>';
                
                if (data.tiene_historial) {
                    warningHtml += `
                        <div class="mt-4 p-4 bg-rose-50 border border-rose-100 rounded-2xl text-left italic">
                            <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-1 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Advertencia de Kardex
                            </p>
                            <p class="text-[11px] text-rose-500 font-bold leading-relaxed">
                                Este producto tiene <b>${data.movimientos} movimientos</b> en el historial (Kardex). Al eliminarlo, estos registros dejarán de ser visibles en los reportes de inventario.
                            </p>
                        </div>
                    `;
                }

                const result = await Swal.fire({
                    title: '<span class="text-slate-800 font-black tracking-tight">Eliminar Producto</span>',
                    html: warningHtml,
                    icon: data.tiene_historial ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'SÍ, ELIMINAR',
                    cancelButtonText: 'CANCELAR',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    customClass: {
                        popup: 'rounded-[2rem] border-none shadow-2xl',
                        confirmButton: 'rounded-2xl px-6 py-3 font-black text-[10px] tracking-widest uppercase',
                        cancelButton: 'rounded-2xl px-6 py-3 font-black text-[10px] tracking-widest uppercase'
                    }
                });

                if (result.isConfirmed) {
                    window.location.href = '<?= base_url("productos/eliminar/") ?>' + p.id;
                }
            } catch (error) {
                console.error('Error al verificar historial:', error);
                if (confirm('¿Eliminar producto?')) {
                    window.location.href = '<?= base_url("productos/eliminar/") ?>' + p.id;
                }
            }
        }
    }
}
</script>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0"
     x-data="catalogoProductos()">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Catálogo Maestro</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Gestión de Productos</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Administración central de artículos y existencias</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('productos/nuevo') ?>" class="flex items-center px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-blue-500/20 active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i> Nuevo Artículo
                </a>
            </div>
        </header>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-search text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por nombre, categoría o código..."
                    class="w-full pl-14 pr-8 py-3.5 bg-white border border-slate-200 rounded-2xl shadow-sm shadow-slate-100 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-700">
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-wrap items-center gap-2 mt-2 lg:mt-0">
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

        <!-- Tabla de Productos -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-6 py-5">Identificación / Producto</th>
                            <th class="px-6 py-5 text-center">Categoría</th>
                            <th class="px-6 py-5 text-right">Precio Venta</th>
                            <th class="px-6 py-5 text-center">Stock Disponible</th>
                            <th class="px-6 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="p in pagedItems" :key="p.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center overflow-hidden border border-slate-200">
                                            <template x-if="p.imagen">
                                                <img :src="'<?= base_url('uploads/productos/') ?>' + p.imagen" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!p.imagen">
                                                <i class="fas fa-box text-slate-300 text-lg"></i>
                                            </template>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-black text-slate-800 text-sm leading-tight" x-text="p.nombre"></p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <p class="text-[10px] text-slate-400 font-black font-mono tracking-widest uppercase" x-text="p.codigo_barras"></p>
                                                
                                                <!-- Detalle de Atributos Concatenados -->
                                                <div class="flex items-center gap-2 text-[9px] font-bold text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-100" 
                                                     x-show="p.variantes_detalle">
                                                    <i class="fas fa-layer-group text-[7px] text-slate-300"></i>
                                                    <span x-text="p.variantes_detalle"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-200"
                                          x-text="p.categoria || 'Sin Categoría'">
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="text-sm font-black text-slate-900">S/ <span x-text="parseFloat(p.precio_venta).toFixed(2)"></span></span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span :class="parseFloat(p.stock) <= Math.max(2, parseFloat(p.stock_minimo) || 0) ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                                              class="px-4 py-1 rounded-xl text-xs font-black border"
                                              x-text="parseFloat(p.stock).toFixed(0)">
                                        </span>
                                        <template x-if="parseFloat(p.stock) <= Math.max(2, parseFloat(p.stock_minimo) || 0)">
                                            <span class="text-[8px] font-black text-rose-400 uppercase tracking-tighter">Stock Crítico</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a :href="'<?= base_url('productos/editar/') ?>' + p.id" 
                                           class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Editar">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a href="javascript:void(0)" 
                                           @click.prevent="confirmarEliminacion(p)" 
                                           class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Eliminar">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row flex-wrap items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Mostrando <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> ítems
                </div>
                
                <div class="flex items-center gap-3" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex flex-wrap items-center justify-center gap-2">
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

        <!-- Empty State -->
        <template x-if="filteredItems.length === 0">
            <div class="py-24 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-box-open text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin coincidencias</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium">Intenta con otros términos de búsqueda.</p>
            </div>
        </template>

    </div>
</div>