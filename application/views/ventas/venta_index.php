<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Ventas
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Historial de Ventas
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Revisa las ventas realizadas y descarga el comprobante en PDF.
                </p>
            </div>
        </header>

        <!-- Alertas -->
        <?php if ($this->session->flashdata('msg')): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span><?= $this->session->flashdata('msg'); ?></span>
            </div>
        <?php endif; ?>

        <!-- Card tabla -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="text-xs text-slate-400 uppercase tracking-[0.2em] font-bold">
                    Últimas Ventas
                </div>
                <!-- Aquí luego puedes poner filtros o buscador -->
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Ticket</th>
                            <th class="px-4 py-3 font-bold">Fecha / Hora</th>
                            <th class="px-4 py-3 font-bold">Cajero</th>
                            <th class="px-4 py-3 font-bold text-right">Total</th>
                            <th class="px-4 py-3 font-bold text-center">Método</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($ventas)): foreach ($ventas as $v): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <!-- Ticket -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[11px] font-black text-slate-500">
                                            VT
                                        </span>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 text-sm leading-tight">
                                                #<?= str_pad($v->id, 6, '0', STR_PAD_LEFT); ?>
                                            </span>
                                            <span class="text-[10px] text-slate-400 uppercase tracking-widest">
                                                ID Interno
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Fecha -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-xs text-slate-800 font-medium">
                                        <?= date('d/m/Y', strtotime($v->fecha_registro)); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= date('H:i:s', strtotime($v->fecha_registro)); ?>
                                    </div>
                                </td>

                                <!-- Cajero -->
                                <td class="px-4 py-3 align-middle">
                                    <div class="text-xs font-medium text-slate-800">
                                        <?= htmlspecialchars($v->cajero); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                        Cajero
                                    </div>
                                </td>

                                <!-- Total -->
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="text-sm font-black text-slate-900">
                                        S/ <?= number_format($v->total, 2); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Importe total
                                    </div>
                                </td>

                                <!-- Método -->
                                <td class="px-4 py-3 text-center align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        <?= $v->metodo_pago === 'efectivo'
                                            ? 'bg-emerald-50 text-emerald-600'
                                            : 'bg-slate-100 text-slate-600'; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                            <?= $v->metodo_pago === 'efectivo'
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-400'; ?>"></span>
                                        <?= htmlspecialchars($v->metodo_pago); ?>
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-3 text-right align-middle">
                                    <div class="flex justify-end gap-2">
                                        <a href="<?= base_url('ventas/ticket/'.$v->id); ?>"
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold shadow-sm shadow-blue-100">
                                            <i class="fas fa-file-pdf mr-1 text-xs"></i>
                                            Ver PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                            <i class="fas fa-receipt text-slate-300 text-lg"></i>
                                        </div>
                                        <p class="text-sm text-slate-500 font-medium">
                                            No hay ventas registradas.
                                        </p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            Cuando realices ventas desde el POS, aparecerán listadas aquí.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
