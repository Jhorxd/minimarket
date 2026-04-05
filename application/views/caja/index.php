<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($cajas), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        openModal: false, 
        openModalCierre: false,
        activeCajaId: <?= isset($caja_activa->id) ? $caja_activa->id : 'null' ?>,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.cajero.toLowerCase().includes(q) ||
                i.estado.toLowerCase().includes(q)
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
                'Cajero': i.cajero,
                'Apertura': i.fecha_apertura,
                'Cierre': i.fecha_cierre || 'Abierta',
                'Monto Apertura': i.monto_apertura,
                'Monto Cierre': i.monto_cierre || '-',
                'Estado': i.estado
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Cajas');
            XLSX.writeFile(wb, 'Reporte_Cajas_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE TURNOS Y CAJAS', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.id,
                i.cajero,
                i.fecha_apertura,
                i.fecha_cierre || 'Abierta',
                'S/ ' + i.monto_apertura,
                i.estado
            ]);

            doc.autoTable({
                startY: 35,
                head: [['ID', 'Cajero', 'Apertura', 'Cierre', 'Monto Ap.', 'Estado']],
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

            doc.save('Reporte_Cajas_' + new Date().getTime() + '.pdf');
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
        
        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-center sm:items-end mb-8 gap-6">
            <div class="w-full sm:w-auto">
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Finanzas & Control</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Control de Cajas</h1>
                <p class="text-slate-400 text-xs mt-1.5 font-medium italic">Historial de aperturas y cierres operativos</p>
            </div>

            <?php if(!$caja_activa): ?>
            <button @click="openModal = true" 
                    class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black shadow-xl shadow-blue-500/20 transition-all flex items-center justify-center transform active:scale-95 uppercase text-[10px] tracking-widest">
                <i class="fas fa-unlock-alt mr-3 text-sm"></i> 
                Aperturar Nueva Caja
            </button>
            <?php else: ?>
            <div class="w-full sm:w-auto px-6 py-3.5 bg-emerald-50 text-emerald-700 rounded-xl font-black border border-emerald-100 flex items-center justify-center shadow-sm uppercase text-[10px] tracking-widest">
                <i class="fas fa-check-circle mr-3 text-base"></i> 
                Caja Activa en Sucursal
            </div>
            <?php endif; ?>
        </header>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-search text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por cajero o estado..."
                    class="w-full pl-14 pr-8 py-3.5 bg-white border border-slate-200 rounded-2xl shadow-sm focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-700">
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

        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Cajero / Operador</th>
                            <th class="px-6 py-5 text-center">Apertura</th>
                            <th class="px-6 py-5 text-center">Fondo Inicial</th>
                            <th class="px-6 py-5 text-center">Monto Cierre</th>
                            <th class="px-6 py-5 text-center">Estado</th>
                            <th class="px-8 py-5 text-right">Control</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="c in pagedItems" :key="c.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-slate-100 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-user-circle text-slate-300 text-xl group-hover:text-blue-500 transition-colors"></i>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-800 text-sm tracking-tight" x-text="c.cajero"></div>
                                            <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-0.5"><?= $this->session->userdata('sucursal_nombre') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center text-xs text-slate-500 font-black italic" x-text="c.fecha_apertura"></td>
                                <td class="px-6 py-5 text-center font-black text-slate-700 text-sm">
                                    <span class="text-slate-300 mr-1 italic">S/</span><span x-text="parseFloat(c.monto_apertura).toFixed(2)"></span>
                                </td>
                                <td class="px-6 py-5 text-center font-black text-slate-700 text-sm">
                                    <span class="text-slate-300 mr-1 italic">S/</span><span x-text="parseFloat(c.monto_cierre || 0).toFixed(2)"></span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <template x-if="c.estado == 'Abierta'">
                                        <span class="inline-flex items-center px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest animate-pulse border border-emerald-100">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span> Abierta
                                        </span>
                                    </template>
                                    <template x-if="c.estado == 'Cerrada'">
                                        <span class="px-4 py-1.5 bg-slate-100 text-slate-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200">
                                            Finalizada
                                        </span>
                                    </template>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template x-if="c.estado == 'Abierta' && c.id_usuario == '<?= $this->session->userdata('id') ?>'">
                                            <button @click="openModalCierre = true" 
                                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-md active:scale-90">
                                                <i class="fas fa-power-off"></i>
                                                Cerrar Turno
                                            </button>
                                        </template>
                                        <template x-if="c.estado == 'Cerrada'">
                                            <button class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-blue-600 hover:bg-white rounded-xl transition-all shadow-sm border border-transparent hover:border-slate-100">
                                                <i class="fas fa-file-invoice-dollar text-sm"></i>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row flex-wrap items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Registros operativos: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> turnos
                </div>
                
                <div class="flex items-center gap-3" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
                                :class="p === page ? 'bg-slate-900 text-white border-slate-900 shadow-xl shadow-slate-200 scale-110' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
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

        <template x-if="filteredItems.length === 0">
            <div class="py-24 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-cash-register text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin registros de caja</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium">No hay turnos registrados en este historial todavía.</p>
            </div>
        </template>
    </div>

    <!-- ======= MODAL APERTURA ======= -->
    <div x-show="openModal" 
        class="fixed inset-0 z-[1001] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" 
        x-cloak x-transition>
        
        <div @click.away="openModal = false" 
            class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-100 transform transition-all duration-300">
            
            <div class="bg-blue-600 p-8 text-center text-white relative">
                <i class="fas fa-unlock-alt text-4xl mb-4 opacity-50"></i>
                <h3 class="text-xl font-black uppercase tracking-tighter">Aperturar Turno</h3>
                <p class="text-blue-200 text-[10px] uppercase font-bold tracking-widest mt-1"><?= $this->session->userdata('sucursal_nombre') ?></p>
            </div>
            
            <form action="<?= base_url('caja/guardar_apertura') ?>" method="POST" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Cajero de Turno</label>
                    <select name="id_usuario" required class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-black text-slate-800 text-sm outline-none focus:border-blue-500 transition-all text-center">
                        <?php foreach($usuarios_sucursal as $u): ?>
                            <option value="<?= $u->id ?>" <?= ($u->id == $this->session->userdata('id')) ? 'selected' : '' ?>>
                                <?= $u->nombre ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-center block">Fondo de Apertura</label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-slate-300 group-focus-within:text-blue-500 text-xl transition-colors">S/</span>
                        <input type="number" name="monto_apertura" step="0.01" required placeholder="0.00"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 border-2 border-transparent focus:border-blue-500 rounded-[1.5rem] transition-all text-3xl font-black text-slate-800 outline-none text-center shadow-inner">
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95">
                        Iniciar Turno Ahora
                    </button>
                    <button type="button" @click="openModal = false" class="w-full py-3 text-slate-400 font-black uppercase text-[10px] tracking-widest hover:text-slate-600 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======= MODAL CIERRE ======= -->
    <div x-show="openModalCierre" 
        class="fixed inset-0 z-[1001] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-xl" 
        x-cloak x-transition>
        
        <div @click.away="openModalCierre = false" 
            class="bg-white w-full max-w-sm rounded-[3rem] shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="bg-slate-900 p-8 text-center text-white">
                <div class="w-16 h-16 bg-rose-600 rounded-[1.5rem] flex items-center justify-center mx-auto mb-4 shadow-lg shadow-rose-900/50">
                    <i class="fas fa-power-off text-2xl"></i>
                </div>
                <h3 class="text-xl font-black uppercase tracking-tighter italic">Cerrar Operación</h3>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1 opacity-80 italic">Balance de Caja Diario</p>
            </div>
            
            <form action="<?= base_url('caja/cerrar/'.$caja_activa->id) ?>" method="POST" class="p-8 space-y-6">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-dashed border-slate-200 text-center">
                        <span class="text-[9px] font-black text-slate-400 uppercase block tracking-widest mb-1">Apertura</span>
                        <span class="text-sm font-black text-slate-700">S/ <?= number_format($caja_activa->monto_apertura ?? 0, 2) ?></span>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-center">
                        <span class="text-[9px] font-black text-emerald-500 uppercase block tracking-widest mb-1">Ventas</span>
                        <span class="text-sm font-black text-emerald-800">S/ <?= number_format($caja_activa->ventas_totales ?? 0, 2) ?></span>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest text-center block">Total Neto en Sistema</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-slate-400 text-xl italic">S/</span>
                        <input type="number" 
                            name="monto_cierre" 
                            value="<?= number_format(($caja_activa->monto_apertura ?? 0) + ($caja_activa->ventas_totales ?? 0), 2, '.', '') ?>" 
                            readonly
                            class="w-full pl-14 pr-6 py-6 bg-slate-100 border-2 border-slate-200 rounded-[2rem] outline-none text-4xl font-black text-slate-500 text-center cursor-not-allowed shadow-inner">
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-[1.5rem] font-black uppercase text-xs tracking-[0.2em] shadow-xl hover:bg-rose-600 transition-all transform active:scale-95">
                        Confirmar Cierre de Turno
                    </button>
                    <button type="button" @click="openModalCierre = false" class="w-full py-2 text-slate-400 font-bold uppercase text-[10px] hover:text-slate-600 transition-all tracking-widest">
                        Revisar Movimientos
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
    [x-cloak] { display: none !important; }
    input::placeholder { color: #cbd5e1; font-weight: 500; }
</style>