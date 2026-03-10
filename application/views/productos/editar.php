<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300">
    <div class="p-4 sm:p-6 lg:p-10 max-w-5xl mx-auto">
        
        <div class="mb-8">
            <a href="<?= base_url('productos') ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Volver al listado
            </a>
            <h1 class="text-3xl font-black text-slate-800">Editar Producto</h1>
            <p class="text-slate-500">Editando registro en: <span class="font-bold text-slate-700"><?= $this->session->userdata('sucursal_nombre') ?></span></p>
        </div>

        <form action="<?= base_url('productos/actualizar/'.$p->id) ?>" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Código de Barras</label>
                            <input type="text" name="codigo_barras" value="<?= $p->codigo_barras ?>" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Categoría</label>
                            <input type="text" name="categoria" value="<?= $p->categoria ?>" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Nombre del Producto</label>
                        <input type="text" name="nombre" value="<?= $p->nombre ?>" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all"><?= $p->descripcion ?></textarea>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-900 p-8 rounded-2xl shadow-xl shadow-slate-200 space-y-6">
                    <h3 class="text-white font-bold text-sm uppercase tracking-widest border-b border-white/10 pb-4">Actualizar Inventario</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Precio Compra</label>
                            <input type="number" name="precio_compra" step="0.01" value="<?= $p->precio_compra ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase text-blue-400">Precio Venta</label>
                            <input type="number" name="precio_venta" step="0.01" value="<?= $p->precio_venta ?>" required class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all text-xl font-black">
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Stock Actual</label>
                                <input type="number" name="stock" value="<?= $p->stock ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Stock Mínimo</label>
                                <input type="number" name="stock_minimo" value="<?= $p->stock_minimo ?>" class="w-full bg-white/10 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:bg-white/20 transition-all font-bold">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white rounded-xl font-black uppercase tracking-widest transition-all transform active:scale-95 shadow-lg shadow-emerald-500/20">
                        Actualizar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>