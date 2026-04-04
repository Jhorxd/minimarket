<!-- Alpine.js for data handling -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0"
     x-data="{ 
        items: <?= htmlspecialchars(json_encode($kardex), ENT_QUOTES, 'UTF-8') ?>,
        productName: '<?= addslashes($producto->nombre) ?>',
        get totalEntradas() {
            return this.items.filter(i => i.tipo_movimiento === 'Entrada').reduce((sum, i) => sum + parseInt(i.cantidad), 0);
        },
        get totalSalidas() {
            return this.items.filter(i => i.tipo_movimiento === 'Salida').reduce((sum, i) => sum + parseInt(i.cantidad), 0);
        },
        exportExcel() {
            if (this.items.length === 0) return;
            const data = this.items.map(i => ({
                'Fecha': i.fecha,
                'Tipo': i.tipo_movimiento,
                'Motivo': i.motivo,
                'Doc. Ref.': i.documento_ref,
                'Tercero': i.tercero_nombre || '-',
                'Cantidad': parseInt(i.cantidad),
                'Stock Resultante': parseInt(i.stock_resultante)
            }));
            
            data.push({'Fecha': '---', 'Tipo': '---', 'Motivo': '---', 'Doc. Ref.': '---', 'Tercero': '---', 'Cantidad': '---', 'Stock Resultante': '---'});
            data.push({'Fecha': 'TOTAL ENTRADAS', 'Tipo': '', 'Motivo': '', 'Doc. Ref.': '', 'Tercero': '', 'Cantidad': this.totalEntradas, 'Stock Resultante': ''});
            data.push({'Fecha': 'TOTAL SALIDAS', 'Tipo': '', 'Motivo': '', 'Doc. Ref.': '', 'Tercero': '', 'Cantidad': this.totalSalidas, 'Stock Resultante': ''});

            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Kardex');
            XLSX.writeFile(wb, 'Kardex_' + this.productName.replace(/ /g, '_') + '_' + new Date().toLocaleDateString().replace(/\//g, '-') + '.xlsx');
        },
        exportPDF() {
            if (this.items.length === 0) return;
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            doc.setFontSize(20);
            doc.setTextColor(30, 41, 59);
            doc.text('KARDEX DE MOVIMIENTOS', 14, 20);
            
            doc.setFontSize(12);
            doc.text('Producto: ' + this.productName, 14, 28);
            
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('Generado el: ' + new Date().toLocaleString(), 14, 34);

            const tableData = this.items.map(i => [
                i.fecha,
                i.tipo_movimiento,
                i.motivo,
                i.documento_ref,
                i.tercero_nombre || '-',
                parseInt(i.cantidad),
                parseInt(i.stock_resultante)
            ]);

            tableData.push(['', '', '', '', 'TOTAL ENTRADAS', this.totalEntradas, '']);
            tableData.push(['', '', '', '', 'TOTAL SALIDAS', this.totalSalidas, '']);

            doc.autoTable({
                startY: 40,
                head: [['Fecha', 'Tipo', 'Motivo', 'Doc. Ref.', 'Tercero', 'Cant.', 'Stock Res.']],
                body: tableData,
                theme: 'striped',
                headStyles: { 
                    fillColor: [30, 41, 59],
                    textColor: 255,
                    fontSize: 10,
                    fontStyle: 'bold'
                },
                styles: { fontSize: 9, cellPadding: 3 },
                alternateRowStyles: { fillColor: [248, 250, 252] }
            });

            doc.save('Kardex_' + this.productName.replace(/ /g, '_') + '.pdf');
        }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">
                    Gestión de Almacén
                </nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">
                    Ajustar Stock
                </h1>
                <p class="mt-2 text-xs text-slate-400 font-medium italic">
                    <?= htmlspecialchars($producto->nombre); ?> · Stock actual: <span class="font-black text-slate-600"><?= (int)$producto->stock; ?></span>
                </p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Formulario de ajuste -->
            <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-4">
                    Nuevo ajuste
                </h2>

                <form action="<?= base_url('almacen/guardar_ajuste'); ?>" method="post" class="space-y-4" x-data="{ motivo: 'Ajuste' }">
                    <input type="hidden" name="id_producto" value="<?= $producto->id; ?>">

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Tipo de movimiento
                        </label>
                        <select name="tipo_movimiento"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Entrada">Entrada (+)</option>
                            <option value="Salida">Salida (-)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Cantidad
                        </label>
                        <input type="number" step="0.01" min="0.01" name="cantidad" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Motivo
                        </label>
                        <select name="motivo" x-model="motivo"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Ajuste">Ajuste</option>
                            <option value="Compra">Compra</option>
                            <option value="Venta">Venta</option>
                            <option value="Traslado">Traslado</option>
                        </select>
                    </div>

                    <div x-show="motivo === 'Ajuste'" x-transition>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Descripción del ajuste (Opcional)
                        </label>
                        <input type="text" name="descripcion_ajuste" placeholder="Ej. Producto mermado, conteo manual..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="<?= base_url('almacen/stock_index'); ?>"
                           class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-100">
                            Guardar ajuste
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden h-fit">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                        Historial de movimientos (Kardex)
                    </h2>
                    
                    <div class="flex items-center gap-2" x-show="items.length > 0">
                        <button @click="exportExcel()" 
                            class="px-3 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg font-black uppercase tracking-widest text-[8px] hover:bg-emerald-600 hover:text-white transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-excel text-[10px]"></i> Excel
                        </button>
                        <button @click="exportPDF()" 
                            class="px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg font-black uppercase tracking-widest text-[8px] hover:bg-rose-600 hover:text-white transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-pdf text-[10px]"></i> PDF
                        </button>
                    </div>
                </div>

             <div class="overflow-x-auto max-h-[420px]">
    <table class="w-full text-left border-collapse min-w-[540px]">
        <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                <th class="px-4 py-3 font-bold">Fecha</th>
                <th class="px-4 py-3 font-bold">Tipo</th>
                <th class="px-4 py-3 font-bold">Motivo</th>
                <th class="px-4 py-3 font-bold">Doc. Ref.</th>
                <th class="px-4 py-3 font-bold">Cliente/Proveedor</th>
                <th class="px-4 py-3 font-bold text-right">Cantidad</th>
                <th class="px-4 py-3 font-bold text-right">Stock resultante</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!empty($kardex)): foreach ($kardex as $m): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="text-xs text-slate-800 font-medium">
                            <?= date('d/m/Y', strtotime($m->fecha)); ?>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <?= date('H:i:s', strtotime($m->fecha)); ?>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            <?= $m->tipo_movimiento === 'Entrada'
                                ? 'bg-emerald-50 text-emerald-600'
                                : 'bg-red-50 text-red-600'; ?>">
                            <?= $m->tipo_movimiento; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-700">
                        <?= $m->motivo; ?>
                    </td>

                    <!-- Doc. Ref. NV-000004 / NC-000001 -->
                    <td class="px-4 py-3 text-xs text-slate-700 font-mono">
                        <?= htmlspecialchars($m->documento_ref); ?>
                    </td>

                    <!-- Cliente / Proveedor -->
                    <td class="px-4 py-3 text-xs text-slate-700">
                        <?= htmlspecialchars($m->tercero_nombre); ?>
                    </td>

                    <td class="px-4 py-3 text-right text-xs text-slate-800">
                        <?= (int) $m->cantidad; ?>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-slate-800">
                        <?= (int) $m->stock_resultante; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400 italic text-sm">
                        Aún no hay movimientos registrados en el kardex de este producto.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200" x-show="items.length > 0">
            <tr>
                <td colspan="5" class="px-4 py-2 text-right text-xs text-slate-500 uppercase tracking-widest border-b border-slate-100">Total Entradas:</td>
                <td class="px-4 py-2 text-right text-xs text-emerald-600 border-b border-slate-100" x-text="totalEntradas"></td>
                <td class="border-b border-slate-100"></td>
            </tr>
            <tr>
                <td colspan="5" class="px-4 py-2 text-right text-xs text-slate-500 uppercase tracking-widest">Total Salidas:</td>
                <td class="px-4 py-2 text-right text-xs text-red-600" x-text="totalSalidas"></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

            </div>

        </div>

    </div>
</div>
