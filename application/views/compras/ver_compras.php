<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Compras
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Detalle de Compra
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Compra #<?= str_pad($compra->id, 6, '0', STR_PAD_LEFT); ?> · Registrada el
                    <?= date('d/m/Y H:i', strtotime($compra->fecha_registro)); ?>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= base_url('compras/compras_index'); ?>"
                   class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                    Volver al listado
                </a>
            </div>
        </header>

        <!-- CABECERA: proveedor y totales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 lg:col-span-2">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                    Proveedor
                </h2>

                <div class="space-y-1 text-sm text-slate-700">
                    <div class="font-semibold">
                        <?= htmlspecialchars($compra->razon_social ?: $compra->proveedor); ?>
                    </div>
                    <?php if (!empty($compra->nro_documento)): ?>
                        <div class="text-xs text-slate-500">
                            <?= $compra->tipo_documento; ?>:
                            <?= htmlspecialchars($compra->nro_documento); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                    Resumen
                </h2>
                <div class="flex flex-col gap-1 text-sm text-slate-700">
                    <div class="flex justify-between">
                        <span class="text-xs text-slate-500">Total compra</span>
                        <span class="font-black text-slate-900">
                            S/ <?= number_format($compra->total, 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETALLE DE PRODUCTOS -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Productos de la compra
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Producto</th>
                            <th class="px-4 py-3 font-bold">Código</th>
                            <th class="px-4 py-3 font-bold text-right">Cantidad</th>
                            <th class="px-4 py-3 font-bold text-right">P. Compra</th>
                            <th class="px-4 py-3 font-bold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($detalles)): foreach ($detalles as $d): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2">
                                    <div class="text-sm font-semibold text-slate-800">
                                        <?= htmlspecialchars($d->nombre); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="text-[11px] font-mono text-slate-500">
                                        <?= htmlspecialchars($d->codigo_barras); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-slate-700">
                                    <?= number_format($d->cantidad, 2); ?>
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-slate-700">
                                    S/ <?= number_format($d->precio_compra, 2); ?>
                                </td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-slate-900">
                                    S/ <?= number_format($d->subtotal, 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-400 italic text-sm">
                                    No hay detalles registrados para esta compra.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
