<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Clientes
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Lista de Clientes
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Administra tus clientes frecuentes para ventas y créditos.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('clientes/crear') ?>"
                   class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-100">
                    <i class="fas fa-user-plus mr-2"></i> Nuevo Cliente
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
                            <th class="px-4 py-3 font-bold">Cliente</th>
                            <th class="px-4 py-3 font-bold">Documento</th>
                            <th class="px-4 py-3 font-bold">Contacto</th>
                            <th class="px-4 py-3 font-bold">Dirección</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($clientes)): foreach ($clientes as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-sm">
                                        <?= htmlspecialchars($c->nombre); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-700">
                                        <?= $c->tipo_documento; ?>: <?= htmlspecialchars($c->nro_documento); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-700">
                                        <?= htmlspecialchars($c->telefono); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= htmlspecialchars($c->email); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-slate-700">
                                        <?= htmlspecialchars($c->direccion); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="<?= base_url('clientes/editar/'.$c->id_cliente); ?>"
                                           class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a href="<?= base_url('clientes/eliminar/'.$c->id_cliente); ?>"
                                           onclick="return confirm('¿Eliminar cliente?');"
                                           class="p-1.5 text-slate-400 hover:text-red-600 transition-colors">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-400 italic text-sm">
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
