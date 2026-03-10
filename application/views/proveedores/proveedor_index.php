<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Proveedores
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Lista de Proveedores
                </h1>
                <p class="mt-1 text-xs text-slate-400">
                    Mantén actualizada la información de tus proveedores.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('proveedores/crear') ?>"
                   class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-100">
                    <i class="fas fa-truck-loading mr-2"></i> Nuevo Proveedor
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
                            <th class="px-4 py-3 font-bold">Proveedor</th>
                            <th class="px-4 py-3 font-bold">Documento</th>
                            <th class="px-4 py-3 font-bold">Contacto</th>
                            <th class="px-4 py-3 font-bold">Rubro</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($proveedores)): foreach ($proveedores as $p): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-sm">
                                        <?= htmlspecialchars($p->razon_social); ?>
                                    </div>
                                    <?php if ($p->nombre_comercial): ?>
                                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                            <?= htmlspecialchars($p->nombre_comercial); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-700">
                                        <?= $p->tipo_documento; ?>: <?= htmlspecialchars($p->nro_documento); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-700">
                                        <?= htmlspecialchars($p->telefono); ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= htmlspecialchars($p->email); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase">
                                        <?= htmlspecialchars($p->rubro); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="<?= base_url('proveedores/editar/'.$p->id_proveedor); ?>"
                                           class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a href="<?= base_url('proveedores/eliminar/'.$p->id_proveedor); ?>"
                                           onclick="return confirm('¿Eliminar proveedor?');"
                                           class="p-1.5 text-slate-400 hover:text-red-600 transition-colors">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-400 italic text-sm">
                                    No hay proveedores registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
