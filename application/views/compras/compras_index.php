<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<?php
$hoy    = new DateTimeImmutable('today');
$urlCompras = site_url('compras/compras_index');
$preset = function ($desde, $hasta) use ($urlCompras) {
    return $urlCompras . '?' . http_build_query(['fecha_desde' => $desde, 'fecha_hasta' => $hasta]);
};
$q7  = $preset($hoy->sub(new DateInterval('P6D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$q30 = $preset($hoy->sub(new DateInterval('P29D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$iniMes = $hoy->format('Y-m-01');
$qMes = $preset($iniMes, $hoy->format('Y-m-d'));
?>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($compras), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
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
                '#' + i.id.toString().padStart(6, '0'),
                i.fecha_registro,
                i.proveedor_razon || i.proveedor || 'Sin detallar',
                i.estado.toUpperCase(),
                'S/ ' + parseFloat(i.total).toFixed(2)
            ]);

            tableData.push(['', '', 'TOTAL ACUMULADO:', '', 'S/ ' + this.totalGeneral.toFixed(2)]);

            doc.autoTable({
                startY: 35,
                head: [['ID', 'Fecha de Operación', 'Origen / Proveedor', 'Estado', 'Monto']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [30, 41, 59], 
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                didParseCell: function (data) {
                    if (data.row.index === tableData.length - 1) {
                        data.cell.styles.fontStyle = 'bold';
                        data.cell.styles.textColor = [16, 185, 129];
                    }
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Reporte_Compras_' + new Date().getTime() + '.pdf');
        },
        get totalGeneral() {
            return this.filteredItems
                .filter(i => i.estado === 'completada')
                .reduce((sum, item) => sum + parseFloat(item.total), 0);
        },
        anularCompra(id) {
            Swal.fire({
                title: '¿Anular compra #'+id+'?',
                text: 'Esto restará el stock ingresado y registrará una salida en Kardex.',
                input: 'text',
                inputPlaceholder: 'Motivo de la anulación...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar',
                preConfirm: (motivo) => {
                    if (!motivo) Swal.showValidationMessage('El motivo es obligatorio');
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?= base_url('compras/anular_compra') ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id_compra: id, motivo: result.value})
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Anulada', 'La compra fue anulada y el stock revertido.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Error desconocido', 'error');
                        }
                    });
                }
            });
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Abastecimiento & Gastos</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Compras e Inventario</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Registro de ingresos de mercadería y costos operativos</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('compras/nueva') ?>"
                   class="flex items-center px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                    <i class="fas fa-file-invoice-dollar mr-3 text-sm"></i> Registrar Nueva Compra
                </a>
            </div>
        </header>

        <!-- Filtros de Fecha -->
        <form method="get" action="<?= site_url('compras/compras_index') ?>"
              class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm mb-8">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Atajos</span>
                <a href="<?= html_escape($q7) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Últimos 7 días</a>
                <a href="<?= html_escape($q30) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Últimos 30 días</a>
                <a href="<?= html_escape($qMes) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Mes en curso</a>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Desde</label>
                    <input type="date" name="fecha_desde" value="<?= html_escape($fecha_desde) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none transition-shadow">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= html_escape($fecha_hasta) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500 outline-none transition-shadow">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition-colors">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
            </div>
        </form>

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
                            <tr :class="c.estado === 'anulada' ? 'bg-rose-50/50 hover:bg-rose-100/50' : 'hover:bg-emerald-50/30'" class="transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-inner border border-slate-100 group-hover:bg-emerald-600 transition-all duration-300">
                                            <i class="fas fa-file-invoice text-slate-300 group-hover:text-white transition-colors text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm tracking-tight" :class="c.estado === 'anulada' ? 'line-through text-red-500' : ''" x-text="'#' + c.id.toString().padStart(6, '0')"></p>
                                            <span class="text-[9px] font-black uppercase tracking-widest mt-0.5 italic" :class="c.estado === 'anulada' ? 'text-rose-400' : 'text-slate-400'" x-text="c.estado"></span>
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
                                <td class="px-8 py-5 text-right font-sans">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'<?= base_url('compras/ver_compras/') ?>' + c.id"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-[0.1em] hover:bg-blue-700 transition-all shadow-md active:scale-95">
                                            <i class="fas fa-eye text-[10px] text-blue-400"></i>
                                            Ver
                                        </a>
                                        <a :href="'<?= base_url('compras/ticket_pdf/') ?>' + c.id" target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md shadow-blue-500/20 transition-all transform active:scale-95">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <button x-show="c.estado === 'completada'" @click="anularCompra(c.id)" 
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all transform active:scale-95">
                                            <i class="fas fa-times"></i> Anular
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <!-- Fila de Totales -->
                    <tfoot class="border-t-2 border-slate-100">
                        <tr class="bg-slate-50/50">
                            <td colspan="3" class="px-8 py-6 text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Inversión Total Acumulada:</span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="text-base font-black text-emerald-600">S/ <span x-text="totalGeneral.toFixed(2)"></span></div>
                                <div class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">(Solo Completadas)</div>
                            </td>
                            <td colspan="2" class="px-8 py-6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row flex-wrap items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Registros: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> compras
                </div>
                
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex flex-wrap items-center justify-center gap-2">
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
