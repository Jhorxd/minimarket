<!-- Librería reactiva Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($categorias), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            return this.items.filter(i => 
                i.nombre.toLowerCase().includes(this.search.toLowerCase()) ||
                (i.descripcion && i.descripcion.toLowerCase().includes(this.search.toLowerCase()))
            );
        },
        get pagedItems() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredItems.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.ceil(this.filteredItems.length / this.perPage);
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
                'Nombre': i.nombre,
                'Descripción': i.descripcion || '-',
                'Color': i.color,
                'Icono': i.icono
            }));
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Categorias');
            XLSX.writeFile(wb, 'Reporte_Categorias_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Título Branded
            doc.setFontSize(22);
            doc.setTextColor(30, 41, 59);
            doc.text('REPORTE DE CATEGORÍAS', 14, 20);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 28);

            const tableData = this.filteredItems.map(i => [
                i.nombre,
                i.descripcion || '-'
            ]);

            doc.autoTable({
                startY: 35,
                head: [['Categoría', 'Descripción']],
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

            doc.save('Reporte_Categorias_' + new Date().getTime() + '.pdf');
        }
     }">
    
    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

        <!-- Header -->
        <header class="flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Clasificación de Productos</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Categorías</h1>
                <p class="text-slate-400 text-xs mt-2 font-medium italic">Organiza tu inventario por familias y grupos</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="abrirModalNueva()"
                   class="flex items-center px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-[10px] transition-all shadow-xl shadow-blue-500/20 active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i> Nueva Categoría
                </button>
            </div>
        </header>

        <div id="alertBox" class="hidden mb-6"></div>

        <!-- Barra de Herramientas (Búsqueda + Exportación) -->
        <div class="flex flex-col lg:flex-row items-stretch gap-3 mb-6">
            <!-- Buscador -->
            <div class="flex-grow relative group">
                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                    <i class="fas fa-search text-base"></i>
                </div>
                <input type="text" x-model="search" @input="resetPage()" placeholder="Buscar por nombre o descripción..."
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

        <!-- Tabla Reactiva -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                            <th class="px-6 py-4">Visual / Categoría</th>
                            <th class="px-6 py-4 hidden md:table-cell">Descripción</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <template x-for="cat in pagedItems" :key="cat.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm"
                                            :style="'background:' + cat.color + '22; border: 1.5px solid ' + cat.color + '44;'">
                                            <i :class="'fas ' + cat.icono + ' text-base'"
                                                :style="'color:' + cat.color"></i>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 text-sm" x-text="cat.nombre"></p>
                                            <span class="text-[10px] font-black uppercase tracking-tighter px-2 py-0.5 rounded-lg border"
                                                  :style="'color:' + cat.color + '; background:' + cat.color + '10; border-color:' + cat.color + '20'"
                                                  x-text="cat.color"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs font-medium hidden md:table-cell" 
                                    x-text="cat.descripcion || '—'"></td>
                                <td class="px-6 py-4 text-center">
                                    <template x-if="cat.activo == 1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Activa
                                        </span>
                                    </template>
                                    <template x-if="cat.activo == 0">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-200">
                                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span> Inactiva
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="abrirModalEditar(cat)"
                                            class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Editar">
                                            <i class="fas fa-pen text-sm"></i>
                                        </button>
                                        <button @click="eliminarCategoria(cat.id, cat.nombre)"
                                            class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Mostrando <span class="text-slate-800" x-text="pagedItems.length"></span> de <span class="text-slate-800" x-text="filteredItems.length"></span> categorías
                </div>
                
                <div class="flex items-center gap-2" x-show="totalPages > 1">
                    <button @click="prevPage()" :disabled="page === 1"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    
                    <div class="flex flex-wrap items-center justify-center gap-1">
                        <template x-for="p in totalPages" :key="p">
                            <button @click="page = p"
                                :class="p === page ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="w-10 h-10 rounded-xl border font-black text-xs transition-all flex items-center justify-center"
                                x-text="p">
                            </button>
                        </template>
                    </div>

                    <button @click="nextPage()" :disabled="page === totalPages"
                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredItems.length === 0">
            <div class="py-20 text-center space-y-4">
                <div class="w-20 h-20 bg-slate-100 rounded-[2rem] flex items-center justify-center mx-auto">
                    <i class="fas fa-search text-slate-300 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-slate-800 font-black uppercase tracking-widest text-xs">Sin resultados</h3>
                    <p class="text-slate-400 text-xs mt-1">No encontramos categorías que coincidan con tu búsqueda.</p>
                </div>
            </div>
        </template>

    </div>
</div>

<!-- ====================== MODAL FORMULARIO ====================== -->
<div id="modalCategoria" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="cerrarModal()"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-white/20">
        <!-- Header -->
        <div class="p-8 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div id="modalIconPreview" class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-all duration-500">
                    <i id="modalIconPreviewI" class="fas fa-tag text-2xl"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-xl font-black text-slate-800 tracking-tight">Nueva Categoría</h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ajustes visuales y datos</p>
                </div>
            </div>
            <button onclick="cerrarModal()" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="formCategoria" class="p-8 space-y-6">
            <input type="hidden" id="editId" value="">

            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre de la Categoría</label>
                <input type="text" id="inputNombre" required maxlength="100"
                    placeholder="Ej: Abarrotes, Limpieza..."
                    class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition-all font-bold text-slate-800">
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción corta</label>
                <input type="text" id="inputDescripcion" maxlength="255"
                    placeholder="Detalles sobre este grupo..."
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-blue-400 transition-all text-sm font-medium">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Color Identificador</label>
                    <div class="flex items-center gap-3 bg-slate-50 p-2 rounded-2xl border border-slate-200">
                        <input type="color" id="inputColor" value="#3b82f6"
                            class="w-10 h-10 rounded-xl border-none cursor-pointer bg-white shadow-sm p-1"
                            oninput="actualizarPreview()">
                        <span id="colorHex" class="text-xs font-black text-slate-500 font-mono">#3B82F6</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ícono Visual</label>
                    <select id="inputIcono" class="w-full px-3 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-400 transition-all text-sm font-bold"
                        onchange="actualizarPreview()">
                        <option value="fa-tag">🏷️ Etiqueta</option>
                        <option value="fa-apple-alt">🍎 Frutas</option>
                        <option value="fa-bread-slice">🍞 Panadería</option>
                        <option value="fa-drumstick-bite">🍗 Carnes</option>
                        <option value="fa-cheese">🧀 Lácteos</option>
                        <option value="fa-wine-bottle">🍷 Bebidas</option>
                        <option value="fa-box-open">📦 Abarrotes</option>
                        <option value="fa-spray-can">🧴 Limpieza</option>
                        <option value="fa-star">⭐ Destacado</option>
                    </select>
                </div>
            </div>

            <div id="campoActivo" class="hidden space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado de Operación</label>
                <select id="inputActivo" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none transition-all font-bold">
                    <option value="1">✅ Activa</option>
                    <option value="0">❌ Inactiva</option>
                </select>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" id="btnGuardar"
                    class="flex-[2] py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-xs transition-all shadow-xl shadow-blue-500/20 active:scale-95">
                    Guardar Categoría
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
    box.className = `flex items-center gap-3 px-6 py-4 rounded-2xl border text-xs font-black uppercase tracking-widest shadow-sm mb-6 ${colors[tipo]}`;
    box.innerHTML = `<i class="fas fa-info-circle"></i> ${msg}`;
    box.classList.remove('hidden');
    setTimeout(() => box.classList.add('hidden'), 4000);
}

function abrirModal() {
    const modal = document.getElementById('modalCategoria');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function cerrarModal() {
    const modal = document.getElementById('modalCategoria');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('formCategoria').reset();
    document.getElementById('editId').value = '';
    document.getElementById('campoActivo').classList.add('hidden');
}

function abrirModalNueva() {
    document.getElementById('modalTitle').textContent = 'Nueva Categoría';
    document.getElementById('editId').value = '';
    document.getElementById('campoActivo').classList.add('hidden');
    abrirModal();
    actualizarPreview();
}

function abrirModalEditar(cat) {
    document.getElementById('modalTitle').textContent = 'Editar Categoría';
    document.getElementById('editId').value = cat.id;
    document.getElementById('inputNombre').value = cat.nombre;
    document.getElementById('inputDescripcion').value = cat.descripcion || '';
    document.getElementById('inputColor').value = cat.color || '#3b82f6';
    document.getElementById('inputIcono').value = cat.icono || 'fa-tag';
    document.getElementById('inputActivo').value = cat.activo;
    document.getElementById('campoActivo').classList.remove('hidden');
    abrirModal();
    actualizarPreview();
}

function actualizarPreview() {
    const color = document.getElementById('inputColor').value;
    const icono = document.getElementById('inputIcono').value;
    document.getElementById('colorHex').textContent = color.toUpperCase();
    const prev = document.getElementById('modalIconPreview');
    const prevI = document.getElementById('modalIconPreviewI');
    prev.style.background = color;
    prevI.style.color = 'white';
    prevI.className = `fas ${icono} text-2xl`;
    prev.style.boxShadow = `0 10px 25px -5px ${color}66`;
}

document.getElementById('formCategoria').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('nombre', document.getElementById('inputNombre').value.trim());
    formData.append('descripcion', document.getElementById('inputDescripcion').value.trim());
    formData.append('color', document.getElementById('inputColor').value);
    formData.append('icono', document.getElementById('inputIcono').value);
    if (id) formData.append('activo', document.getElementById('inputActivo').value);

    const url = id ? `${BASE_URL}categorias/actualizar/${id}` : `${BASE_URL}categorias/guardar`;

    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showAlert(res.message, 'success');
                cerrarModal();
                setTimeout(() => location.reload(), 1000);
            } else showAlert(res.message, 'error');
        })
        .finally(() => btn.disabled = false);
});

function eliminarCategoria(id, nombre) {
    if (!confirm(`¿Eliminar la categoría "${nombre}"?`)) return;
    fetch(`${BASE_URL}categorias/eliminar/${id}`)
        .then(r => r.json())
        .then(res => {
            showAlert(res.message, res.success ? 'success' : 'error');
            if (res.success) setTimeout(() => location.reload(), 1000);
        });
}
</script>
