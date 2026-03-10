<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">
        
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Gestión de Inventario</nav>
                <h1 class="text-3xl font-black text-slate-800">Productos en Sucursal</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= base_url('productos/nuevo') ?>" class="flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue-100">
                    <i class="fas fa-plus mr-2"></i> Nuevo Producto
                </a>
            </div>
        </header>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Código / Producto</th>
                            <th class="px-4 py-3 font-bold text-center">Categoría</th>
                            <th class="px-4 py-3 font-bold text-right">Precio Venta</th>
                            <th class="px-4 py-3 font-bold text-center">Stock</th>
                            <th class="px-4 py-3 font-bold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(!empty($productos)): foreach($productos as $p): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800 text-sm"><?= $p->nombre ?></div>
                                <div class="text-[10px] text-slate-400 font-mono tracking-wider"><?= $p->codigo_barras ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase">
                                    <?= $p->categoria ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-black text-slate-900">S/ <?= number_format($p->precio_venta, 2) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php $color_stock = ($p->stock <= $p->stock_minimo) ? 'text-red-600 bg-red-50' : 'text-emerald-600 bg-emerald-50'; ?>
                                <span class="px-3 py-1 rounded-lg text-xs font-black <?= $color_stock ?>">
                                    <?= number_format($p->stock, 0) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="<?= base_url('productos/editar/'.$p->id) ?>" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="<?= base_url('productos/eliminar/'.$p->id) ?>" onclick="return confirm('¿Eliminar producto?')" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400 italic text-sm">
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