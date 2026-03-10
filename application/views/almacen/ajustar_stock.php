<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Almacén
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Ajustar Stock
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    <?= htmlspecialchars($producto->nombre); ?> · Stock actual: <?= number_format($producto->stock, 2); ?>
                </p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Formulario de ajuste -->
            <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-4">
                    Nuevo ajuste
                </h2>

                <form action="<?= base_url('almacen/guardar_ajuste'); ?>" method="post" class="space-y-4">
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
                        <select name="motivo"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Ajuste">Ajuste</option>
                            <option value="Compra">Compra</option>
                            <option value="Venta">Venta</option>
                            <option value="Traslado">Traslado</option>
                        </select>
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

            <!-- Historial de kardex -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                        Historial de movimientos (Kardex)
                    </h2>
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
                        <?= number_format($m->cantidad, 2); ?>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-slate-800">
                        <?= number_format($m->stock_resultante, 2); ?>
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
    </table>
</div>

            </div>

        </div>

    </div>
</div>
