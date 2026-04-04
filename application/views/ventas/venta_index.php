<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<?php
$hoy    = new DateTimeImmutable('today');
$urlVentas = site_url('ventas/venta_index');
$preset = function ($desde, $hasta) use ($urlVentas) {
    return $urlVentas . '?' . http_build_query(['fecha_desde' => $desde, 'fecha_hasta' => $hasta]);
};
$q7  = $preset($hoy->sub(new DateInterval('P6D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$q30 = $preset($hoy->sub(new DateInterval('P29D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$iniMes = $hoy->format('Y-m-01');
$qMes = $preset($iniMes, $hoy->format('Y-m-d'));
?>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($ventas), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.id.toString().includes(q) ||
                i.cajero.toLowerCase().includes(q) ||
                i.metodo_pago.toLowerCase().includes(q)
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
        get totalGeneral() {
            return this.filteredItems
                .filter(i => i.estado === 'completada')
                .reduce((sum, item) => sum + parseFloat(item.total), 0);
        },
        exportExcel() {
            const data = this.filteredItems.map(i => ({
                'ID': i.id,
                'Fecha Registro': i.fecha_registro,
                'Responsable': i.cajero,
                'Cliente': i.cliente || 'General',
                'Estado': i.estado,
                'Total': parseFloat(i.total),
                'Método Pago': i.metodo_pago
            }));

            // Fila de Totales al final
            data.push({
                'ID': '',
                'Fecha Registro': '',
                'Responsable': '',
                'Cliente': '',
                'Estado': 'TOTAL ACUMULADO',
                'Total': this.totalGeneral,
                'Método Pago': ''
            });

            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Ventas');
            XLSX.writeFile(wb, 'Reporte_Ventas_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE VENTAS REALIZADAS', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                '#' + i.id.toString().padStart(6, '0'),
                i.fecha_registro,
                i.cajero,
                i.estado.toUpperCase(),
                'S/ ' + parseFloat(i.total).toFixed(2),
                i.metodo_pago
            ]);

            // Agregar fila de total acumulado
            tableData.push([
                '', '', '', 
                'TOTAL ACUMULADO:', 
                'S/ ' + this.totalGeneral.toFixed(2), 
                ''
            ]);

            doc.autoTable({
                startY: 35,
                head: [['Ticket', 'Fecha y Registro', 'Responsable', 'Estado', 'Monto', 'Pago']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [59, 130, 246],
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                didParseCell: function (data) {
                    // Resaltar fila de totales
                    if (data.row.index === tableData.length - 1) {
                        data.cell.styles.fontStyle = 'bold';
                        data.cell.styles.fillColor = [241, 245, 249];
                        data.cell.styles.textColor = [37, 99, 235];
                    }
                    // Resaltar ventas anuladas (índice 3 es columna Estado)
                    if (data.section === 'body' && data.column.index === 3 && data.cell.raw === 'ANULADA') {
                        data.row.cells[4].styles.textColor = [220, 38, 38]; // Color rojo al monto
                        data.row.cells[4].styles.fontStyle = 'bold';
                    }
                },
                styles: { fontSize: 8, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Reporte_Ventas_' + new Date().getTime() + '.pdf');
        },
        anularVenta(id) {
            Swal.fire({
                title: '¿Anular venta #'+id+'?',
                text: 'Debes ingresar un motivo para la anulación.',
                input: 'text',
                inputPlaceholder: 'Ej. Error de cobro',
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
                    fetch('<?= base_url('ventas/anular_venta') ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({id_venta: id, motivo: result.value})
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Anulada', 'La venta fue anulada y el stock revertido.', 'success').then(() => location.reload());
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
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Módulo de Transacciones</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Ventas Realizadas</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Gestión de comprobantes y auditoría de ingresos</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('ventas/pos') ?>"
                   class="flex items-center px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-blue-500/20 active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i> Nueva Venta (POS)
                </a>
            </div>
        </header>

        <!-- Filtros de Fecha -->
        <form method="get" action="<?= site_url('ventas/venta_index') ?>"
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
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-shadow">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= html_escape($fecha_hasta) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none transition-shadow">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-colors">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
            </div>
        </form>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-search text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por ticket, cajero o medio de pago..."
                    class="w-full pl-14 pr-8 py-3.5 bg-white border border-slate-200 rounded-2xl shadow-sm focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-700">
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

        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Ticket / Auditoría</th>
                            <th class="px-6 py-5">Fecha y Registro</th>
                            <th class="px-6 py-5">Responsable</th>
                            <th class="px-6 py-5 text-right">Inversión Total</th>
                            <th class="px-6 py-5 text-center">Forma de Pago</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="v in pagedItems" :key="v.id">
                            <tr :class="v.estado === 'anulada' ? 'bg-rose-50/80 hover:bg-rose-100/80' : 'hover:bg-blue-50/30'" class="transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div :class="v.estado === 'anulada' ? 'bg-red-50 border-red-200' : 'bg-white border-slate-100'" class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm border group-hover:scale-110 transition-transform">
                                            <span class="text-[10px] font-black" :class="v.estado === 'anulada' ? 'text-red-400' : 'text-slate-400'">VT</span>
                                        </div>
                                        <div>
                                            <p class="font-black text-sm tracking-tight" :class="v.estado === 'anulada' ? 'text-red-600 line-through' : 'text-slate-800'" x-text="'#' + v.id.toString().padStart(6, '0')"></p>
                                            <span class="text-[9px] font-black uppercase tracking-widest mt-0.5" :class="v.estado === 'anulada' ? 'text-red-500' : 'text-slate-400'" x-text="v.estado"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-xs font-black text-slate-700" x-text="v.fecha_registro.split(' ')[0]"></div>
                                    <div class="text-[10px] text-slate-400 font-medium italic" x-text="v.fecha_registro.split(' ')[1]"></div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" :class="v.estado === 'anulada' ? 'bg-red-400' : 'bg-blue-400'"></div>
                                        <span class="text-xs font-black text-slate-700 uppercase" x-text="v.cajero"></span>
                                    </div>
                                    <div class="text-[9px] text-slate-400 uppercase mt-1" x-text="'Cliente: ' + (v.cliente || 'General')"></div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-sm font-black text-slate-900">S/ <span x-text="parseFloat(v.total).toFixed(2)"></span></div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Monto Final</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span :class="v.metodo_pago === 'efectivo' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-blue-50 text-blue-600 border-blue-100'"
                                          class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border"
                                          x-text="v.metodo_pago">
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right font-sans">
                                    <div class="flex items-center justify-end gap-2">
                                        <a :href="'<?= base_url('ventas/ver_venta/') ?>' + v.id"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-[0.1em] hover:bg-blue-700 transition-all shadow-md active:scale-95">
                                            <i class="fas fa-eye text-[10px] text-blue-400"></i>
                                            Ver
                                        </a>
                                        <a :href="'<?= base_url('ventas/ticket/') ?>' + v.id" target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md shadow-blue-500/20 transition-all transform active:scale-95">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <button x-show="v.estado === 'completada'" @click="anularVenta(v.id)" 
                                                class="inline-flex items-center gap-2 px-3 py-2 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all transform active:scale-95">
                                            <i class="fas fa-times"></i> Anular
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <!-- Fila de Totales -->
                    <tfoot class="border-t-2 border-slate-200">
                        <tr class="bg-slate-50/80">
                            <td colspan="3" class="px-8 py-6 text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Acumulado del Periodo:</span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="text-base font-black text-blue-600">S/ <span x-text="totalGeneral.toFixed(2)"></span></div>
                                <div class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">(Solo Completadas)</div>
                            </td>
                            <td colspan="2" class="px-8 py-6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer y Paginación -->
            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Registros: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> ventas
                </div>
                
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
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
                    <i class="fas fa-receipt text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin coincidencias</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium">Prueba buscando por número de ticket o cajero.</p>
            </div>
        </template>

    </div>
</div>
