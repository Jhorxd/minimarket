<style>
    [x-cloak] { display: none !important; }
    
    /* Scrollbar minimalista para la tabla en móviles */
    .custom-scrollbar::-webkit-scrollbar { height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    /* Ajuste de enfoque para inputs */
    input:focus { outline: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="md:ml-64 min-h-screen bg-slate-50 px-4 pb-4 pt-16 md:px-8 md:pb-8 md:pt-8 lg:px-8 lg:pb-4 lg:pt-4" 
     x-data="{ openModal: false, openModalCierre: false }">

    
    <header class="flex flex-col sm:flex-row justify-between items-center sm:items-end mb-8 gap-6 text-center sm:text-left">
        <div class="w-full sm:w-auto">
            <nav class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 md:mb-2">Finanzas</nav>
            <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tighter">Control de Cajas</h1>
        </div>

        <?php if(!$caja_activa): ?>
        <button @click="openModal = true" 
                class="w-full sm:w-auto px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-xl shadow-blue-100 transition-all flex items-center justify-center transform active:scale-95">
            <i class="fas fa-unlock-alt mr-2 text-sm"></i> 
            <span class="uppercase text-xs tracking-wider">Aperturar Nueva Caja</span>
        </button>
        <?php else: ?>
        <div class="w-full sm:w-auto px-8 py-4 bg-emerald-100 text-emerald-700 rounded-2xl font-bold border border-emerald-200 flex items-center justify-center shadow-sm">
            <i class="fas fa-check-circle mr-2"></i> 
            <span class="uppercase text-xs tracking-wider">Caja Activa en Sucursal</span>
        </div>
        <?php endif; ?>
    </header>

    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-tighter border-b border-slate-100">
                        <th class="px-6 py-3">Cajero / Sucursal</th>
                        <th class="px-4 py-3 text-center">Fecha Apertura</th>
                        <th class="px-4 py-3 text-center">Monto Inicial</th>
                        <th class="px-4 py-3 text-center">Monto Final</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($cajas as $c): ?>
                    <tr class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-3">
                            <div class="font-bold text-slate-800 text-sm group-hover:text-blue-600 transition-colors"><?= $c->cajero ?></div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight"><?= $this->session->userdata('sucursal_nombre') ?></div>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-slate-500 font-mono">
                            <div class="bg-slate-100 rounded-lg py-1 px-2 inline-block italic">
                                <?= date('d/m/Y H:i', strtotime($c->fecha_apertura)) ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-black text-slate-700 text-sm">
                            <span class="text-slate-300 mr-1 text-xs">S/</span><?= number_format($c->monto_apertura, 2) ?>
                        </td>
                        <td class="px-4 py-3 text-center font-black text-slate-700 text-sm">
                            <span class="text-slate-300 mr-1 text-xs">S/</span><?= number_format($c->monto_cierre ?? 0, 2) ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($c->estado == 'Abierta'): ?>
                                <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest animate-pulse border border-emerald-200">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span> Abierta
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-slate-100 text-slate-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200">
                                    Cerrada
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <?php if($c->estado == 'Abierta' && $c->id_usuario == $this->session->userdata('id')): ?>
                                <button @click="openModalCierre = true" 
                                        type="button"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all shadow-sm active:scale-90">
                                    <i class="fas fa-power-off"></i>
                                    Cerrar Caja
                                </button>
                            <?php elseif($c->estado == 'Cerrada'): ?>
                                <button class="p-2 text-slate-300 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div x-show="openModal" 
        class="fixed inset-0 z-[1001] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
        x-cloak x-transition>
        
        <div @click.away="openModal = false" 
            class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl overflow-hidden border border-white/20">
            
            <div class="bg-slate-900 p-5 text-center text-white relative">
                <h3 class="text-lg font-black italic uppercase tracking-tighter">Aperturar Caja</h3>
                <p class="text-slate-400 text-[9px] uppercase font-bold tracking-widest"><?= $this->session->userdata('sucursal_nombre') ?></p>
            </div>
            
            <form action="<?= base_url('caja/guardar_apertura') ?>" method="POST" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Cajero</label>
                    <select name="id_usuario" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-700 text-sm outline-none focus:border-blue-500 transition-all">
                        <?php foreach($usuarios_sucursal as $u): ?>
                            <option value="<?= $u->id ?>" <?= ($u->id == $this->session->userdata('id')) ? 'selected' : '' ?>>
                                <?= $u->nombre ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Fondo Inicial</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 group-focus-within:text-blue-500 text-lg transition-colors">S/</span>
                        <input type="number" name="monto_apertura" step="0.01" required placeholder="0.00"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all text-2xl font-black text-slate-800 outline-none">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="openModal = false" class="flex-1 py-3 bg-slate-100 text-slate-500 rounded-xl font-bold uppercase text-[10px] hover:bg-slate-200 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-[2] py-3 bg-blue-600 text-white rounded-xl font-black uppercase text-[10px] shadow-md shadow-blue-200 hover:bg-blue-700 transition-all">
                        Abrir Caja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="openModalCierre" 
        class="fixed inset-0 z-[1001] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" 
        x-cloak x-transition>
        
        <div @click.away="openModalCierre = false" 
            class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="bg-red-600 p-5 text-center text-white">
                <h3 class="text-lg font-black italic uppercase tracking-tighter">Finalizar Turno</h3>
                <p class="text-red-100 text-[9px] font-bold uppercase tracking-widest opacity-80">Cierre Operativo</p>
            </div>
            
            <form action="<?= base_url('caja/cerrar/'.$caja_activa->id) ?>" method="POST" class="p-6 space-y-4">
                
                <div class="bg-slate-50 p-3 rounded-xl border border-dashed border-slate-200 text-center">
                    <span class="text-[9px] font-black text-slate-400 uppercase block tracking-widest">Iniciaste con</span>
                    <span class="text-lg font-black text-slate-700 font-mono">S/ <?= number_format($caja_activa->monto_apertura ?? 0, 2) ?></span>
                </div>

                <div class="space-y-2">
                    <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest text-center block">Total en Sistema</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-400 text-lg">S/</span>
                        <input type="number" 
                            name="monto_cierre" 
                            value="<?= number_format(($caja_activa->monto_apertura ?? 0) + ($caja_activa->ventas_totales ?? 0), 2, '.', '') ?>" 
                            readonly
                            class="w-full pl-12 pr-4 py-4 bg-slate-100 border-2 border-slate-200 rounded-2xl outline-none text-3xl font-black text-slate-500 text-center cursor-not-allowed">
                    </div>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-xl font-black uppercase text-[10px] tracking-widest shadow-lg hover:bg-red-600 transition-all transform active:scale-95">
                        Confirmar y Cerrar
                    </button>
                    <button type="button" @click="openModalCierre = false" class="w-full py-2 text-slate-400 font-bold uppercase text-[9px] hover:text-slate-600 transition-all">
                        Volver
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>