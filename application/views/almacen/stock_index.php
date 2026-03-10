<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Almacén
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Stock de Almacén
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Basado en productos, ventas, compras y movimientos de kardex.
                </p>
            </div>
        </header>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-info-circle text-emerald-500"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="text-xs text-slate-400 uppercase tracking-[0.2em] font-bold">
                    Productos en esta sucursal
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Producto</th>
                            <th class="px-4 py-3 font-bold">Código</th>
                            <th class="px-4 py-3 font-bold text-right">P. Venta</th>
                            <th class="px-4 py-3 font-bold text-center">Stock</th>
                            <th class="px-4 py-3 font-bold text-center">Mínimo</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($productos)): foreach ($productos as $p): ?>
                            <?php $critico = ($p->stock <= $p->stock_minimo); ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-sm">
                                        <?= htmlspecialchars($p->nombre); ?>
                                    </div>
                                    <?php if ($p->categoria): ?>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                            <?= htmlspecialchars($p->categoria); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-[11px] font-mono text-slate-500">
                                        <?= htmlspecialchars($p->codigo_barras); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-black text-slate-900">
                                        S/ <?= number_format($p->precio_venta, 2); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-3 py-1 rounded-lg text-xs font-black
                                        <?= $critico ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'; ?>">
                                        <?= number_format($p->stock, 2); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-[11px] text-slate-500">
                                        <?= number_format($p->stock_minimo, 0); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= base_url('almacen/ajustar/'.$p->id); ?>"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold">
                                        <i class="fas fa-sliders-h mr-1 text-xs"></i>
                                        Ajustar / Kardex
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-400 italic text-sm">
                                    No hay productos registrados en esta sucursal.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
