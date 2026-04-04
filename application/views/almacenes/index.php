<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($almacenes), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => 
                i.nombre.toLowerCase().includes(q) ||
                (i.descripcion && i.descripcion.toLowerCase().includes(q))
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
        prevPage() { if(this.page > 1) this.page--; },        resetPage() { this.page = 1; },
        resetFilters() { 
            this.search = '';
            this.page = 1;
        },
        exportExcel() {
            const data = this.filteredItems.map(i => ({
                'Nombre': i.nombre,
                'Ubicación': i.ubicacion || '-',
                'Descripción': i.descripcion || '-'
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Almacenes');
            XLSX.writeFile(wb, 'Reporte_Almacenes_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE ALMACENES', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.nombre,
                i.descripcion || '-'
            ]);

            doc.autoTable({
                startY: 35,
                head: [['Almacén', 'Descripción']],
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

            doc.save('Reporte_Almacenes_' + new Date().getTime() + '.pdf');
        }
     }">
    
    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Infraestructura & Logística</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Almacenes</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Control global de puntos de almacenamiento</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="abrirModalNueva()"
                   class="flex items-center px-5 py-3.5 bg-slate-900 hover:bg-black text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-slate-200 active:scale-95">
                    <i class="fas fa-plus-circle text-sm mr-2"></i> Nuevo Almacén
                </button>
            </div>
        </header>

        <div id="alertBox" class="hidden mb-6"></div>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-warehouse text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por nombre o descripción..."
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
        </div>/div>

        <!-- Tabla -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5">Nombre / Ubicación</th>
                            <th class="px-8 py-5 hidden md:table-cell">Descripción Detallada</th>
                            <th class="px-8 py-5 text-center">Estado Operativo</th>
                            <th class="px-8 py-5 text-right">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="alm in pagedItems" :key="alm.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-slate-100 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-warehouse text-blue-500 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm tracking-tight" x-text="alm.nombre"></p>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ID Punto: #<span x-text="alm.id"></span></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-slate-400 text-xs font-semibold italic hidden md:table-cell" 
                                    x-text="alm.description || alm.descripcion || 'Sin descripción adicional'"></td>
                                <td class="px-8 py-5 text-center">
                                    <template x-if="alm.activo == 1">
                                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Disponible
                                        </span>
                                    </template>
                                    <template x-if="alm.activo == 0">
                                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-slate-100 text-slate-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200 opacity-60">
                                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span> Inactivo
                                        </span>
                                    </template>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="abrirModalEditar(alm)"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Editar">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button @click="eliminarAlmacen(alm.id, alm.nombre)"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-white rounded-xl transition-all shadow-sm group-hover:shadow-md border border-transparent hover:border-slate-100" title="Eliminar">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer y Paginación -->
            <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                    Registrados: <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> almacenes
                </div>
                
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
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
                    <i class="fas fa-warehouse text-slate-300 text-4xl"></i>
                </div>
                <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin almacenes</h3>
                <p class="text-slate-400 text-xs mt-2 font-medium">No hay resultados para tu búsqueda actual.</p>
            </div>
        </template>

    </div>
</div>

<!-- ====================== MODAL FORMULARIO ====================== -->
<div id="modalAlmacen" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="cerrarModal()"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden transform transition-all border border-white/20">
        <!-- Header -->
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                    <i class="fas fa-warehouse text-xl"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-lg font-black text-slate-800 tracking-tight">Nuevo Almacén</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Control de inventario</p>
                </div>
            </div>
            <button onclick="cerrarModal()" class="w-10 h-10 rounded-xl bg-white text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all shadow-sm border border-slate-100">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="formAlmacen" class="p-8 space-y-6">
            <input type="hidden" id="editId" value="">

            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre Identificador *</label>
                <input type="text" id="inputNombre" required maxlength="100"
                    placeholder="Ej: Almacén Principal, Estante A1..."
                    class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-800 text-sm">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción corta</label>
                <textarea id="inputDescripcion" rows="2" maxlength="255"
                    placeholder="Ubicación física o detalles relevantes..."
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all text-xs font-medium"></textarea>
            </div>

            <div id="campoActivo" class="hidden space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado Operativo</label>
                <select id="inputActivo" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all font-bold text-sm">
                    <option value="1">✅ Activo para uso</option>
                    <option value="0">❌ Fuera de servicio</option>
                </select>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" id="btnGuardar"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] transition-all shadow-xl shadow-blue-500/20 active:scale-95">
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';

function showAlert(msg, tipo = 'success') {
    const box = document.getElementById('alertBox');
    const colors = {
        success: 'bg-emerald-50 border-emerald-200 text-emerald-800',
        error:   'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-amber-50 border-amber-200 text-amber-800'
    };
    box.className = `flex items-center gap-3 px-6 py-4 rounded-2xl border text-[10px] font-black uppercase tracking-widest shadow-sm mb-6 ${colors[tipo]}`;
    box.innerHTML = `<i class="fas fa-info-circle text-sm"></i> ${msg}`;
    box.classList.remove('hidden');
    setTimeout(() => box.classList.add('hidden'), 4000);
}

function abrirModal() {
    const modal = document.getElementById('modalAlmacen');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('inputNombre').focus(), 100);
}

function cerrarModal() {
    const modal = document.getElementById('modalAlmacen');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('formAlmacen').reset();
    document.getElementById('editId').value = '';
    document.getElementById('campoActivo').classList.add('hidden');
}

function abrirModalNueva() {
    document.getElementById('modalTitle').textContent = 'Nuevo Almacén';
    document.getElementById('editId').value = '';
    document.getElementById('campoActivo').classList.add('hidden');
    abrirModal();
}

function abrirModalEditar(alm) {
    document.getElementById('modalTitle').textContent = 'Editar Almacén';
    document.getElementById('editId').value = alm.id;
    document.getElementById('inputNombre').value = alm.nombre;
    document.getElementById('inputDescripcion').value = alm.descripcion || '';
    document.getElementById('inputActivo').value = alm.activo;
    document.getElementById('campoActivo').classList.remove('hidden');
    abrirModal();
}

document.getElementById('formAlmacen').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';

    const formData = new FormData();
    formData.append('nombre', document.getElementById('inputNombre').value.trim());
    formData.append('descripcion', document.getElementById('inputDescripcion').value.trim());
    if (id) formData.append('activo', document.getElementById('inputActivo').value);

    const url = id ? `${BASE_URL}almacenes/actualizar/${id}` : `${BASE_URL}almacenes/guardar`;

    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showAlert(res.message, 'success');
                cerrarModal();
                setTimeout(() => location.reload(), 800);
            } else showAlert(res.message, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Guardar Registro';
        });
});

function eliminarAlmacen(id, nombre) {
    if (!confirm(`¿Eliminar el almacén "${nombre}"?`)) return;
    fetch(`${BASE_URL}almacenes/eliminar/${id}`)
        .then(r => r.json())
        .then(res => {
            showAlert(res.message, res.success ? 'success' : 'error');
            if (res.success) setTimeout(() => location.reload(), 1000);
        });
}
</script>

<style>
    @keyframes slide-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    #modalAlmacen.flex .relative { animation: slide-up 0.3s ease-out; }
</style>
