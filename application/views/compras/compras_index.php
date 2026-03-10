<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Compras
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Historial de Compras
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('compras/nueva') ?>"
                   class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-100">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Nueva Compra
                </a>
            </div>
        </header>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Compra</th>
                            <th class="px-4 py-3 font-bold">Fecha / Hora</th>
                            <th class="px-4 py-3 font-bold">Proveedor</th>
                            <th class="px-4 py-3 font-bold text-right">Total</th>
                            <th class="px-4 py-3 font-bold">Usuario</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($compras)): foreach ($compras as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-sm">
                                        #<?= str_pad($c->id, 6, '0', STR_PAD_LEFT); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-800 font-medium">
                                        <?= date('d/m/Y', strtotime($c->fecha_registro)); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= date('H:i:s', strtotime($c->fecha_registro)); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-800">
                                        <?= htmlspecialchars($c->proveedor_razon ?: $c->proveedor); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-black text-slate-900">
                                        S/ <?= number_format($c->total, 2); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700">
                                    <?= htmlspecialchars($c->usuario); ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= base_url('compras/ver_compras/'.$c->id); ?>"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold">
                                        <i class="fas fa-eye mr-1 text-xs"></i> Ver detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-400 italic text-sm">
                                    No hay compras registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
