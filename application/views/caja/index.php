<style>
    [x-cloak] { display: none !important; }
</style>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<div class="md:ml-64 min-h-screen bg-slate-50 p-6 lg:p-10" x-data="{ openModal: false, openModalCierre: false }">
    
    <header class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Finanzas</nav>
            <h1 class="text-3xl font-black text-slate-800">Control de Cajas</h1>
        </div>

        <?php if(!$caja_activa): ?>
        <button @click="openModal = true" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-100 transition-all">
            <i class="fas fa-unlock-alt mr-2"></i> Aperturar Nueva Caja
        </button>
        <?php else: ?>
        <div class="px-6 py-3 bg-emerald-100 text-emerald-700 rounded-xl font-bold border border-emerald-200">
            <i class="fas fa-check-circle mr-2"></i> Tienes una caja abierta
        </div>
        <?php endif; ?>
    </header>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-tighter border-b border-slate-100">
                    <th class="px-6 py-4">Cajero</th>
                    <th class="px-6 py-4 text-center">Apertura</th>
                    <th class="px-6 py-4 text-center text-nowrap">Monto Inicial</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($cajas as $c): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800"><?= $c->cajero ?></div>
                        <div class="text-[10px] text-slate-400 uppercase"><?= $this->session->userdata('sucursal_nombre') ?></div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500 font-mono">
                        <?= date('d/m/Y H:i', strtotime($c->fecha_apertura)) ?>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-slate-700">
                        S/ <?= number_format($c->monto_apertura, 2) ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if($c->estado == 'Abierta'): ?>
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest animate-pulse">Abierta</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-slate-100 text-slate-400 rounded-full text-[10px] font-black uppercase tracking-widest">Cerrada</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if($c->estado == 'Abierta' && $c->id_usuario == $this->session->userdata('id')): ?>
                            <button @click="openModalCierre = true" 
                                    type="button"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-full text-[10px] font-black uppercase tracking-tighter hover:bg-red-600 hover:text-white transition-all shadow-sm active:scale-95">
                                <i class="fas fa-lock text-[12px]"></i>
                                Cerrar Caja
                            </button>
                        <?php elseif($c->estado == 'Cerrada'): ?>
                            <button class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                <i class="fas fa-print"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="openModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 text-center text-white">
                <h3 class="text-xl font-black italic uppercase tracking-tighter">Aperturar Caja</h3>
                <p class="text-slate-400 text-xs mt-1 uppercase font-bold"><?= $this->session->userdata('sucursal_nombre') ?></p>
            </div>
            
            <form action="<?= base_url('caja/guardar_apertura') ?>" method="POST" class="p-8 space-y-5">
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Asignar Cajero</label>
                    <select name="id_usuario" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-700">
                        <?php foreach($usuarios_sucursal as $u): ?>
                            <option value="<?= $u->id ?>" <?= ($u->id == $this->session->userdata('id')) ? 'selected' : '' ?>>
                                <?= $u->nombre ?> (<?= ucfirst($u->rol) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Monto Inicial (Efectivo)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300">S/</span>
                        <input type="number" name="monto_apertura" step="0.01" required placeholder="0.00"
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-2xl font-black text-slate-800">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="openModal = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold uppercase text-xs hover:bg-slate-200 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black uppercase text-xs shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
                        Confirmar Apertura
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div x-show="openModalCierre" 
        class="fixed inset-0 z-[1000] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" 
        x-cloak 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">
        
        <div @click.away="openModalCierre = false" 
            class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-100">
            
            <div class="bg-red-600 p-8 text-center text-white relative">
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-white rounded-2xl shadow-lg flex items-center justify-center text-red-600">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </div>
                <h3 class="text-2xl font-black italic uppercase tracking-tighter">Finalizar Turno</h3>
                <p class="text-red-100 text-xs font-bold uppercase tracking-widest mt-1 opacity-80">Cierre de Caja Operativo</p>
            </div>
            
            <form action="<?= base_url('caja/cerrar/'.$caja_activa->id) ?>" method="POST" class="p-10 pt-12 space-y-6">
                
                <div class="bg-slate-50 p-4 rounded-2xl border border-dashed border-slate-200 mb-4 text-center">
                    <span class="text-[10px] font-black text-slate-400 uppercase block mb-1 tracking-widest">Iniciaste con</span>
                    <span class="text-xl font-black text-slate-700 font-mono">S/ <?= number_format($caja_activa->monto_apertura ?? 0, 2) ?></span>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Total Calculado por Sistema</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-400">S/</span>
                        <input type="number" 
                            name="monto_cierre" 
                            id="monto_cierre"
                            value="<?= number_format(($caja_activa->monto_apertura ?? 0) + ($caja_activa->ventas_totales ?? 0), 2, '.', '') ?>" 
                            readonly
                            class="w-full pl-12 pr-6 py-5 bg-slate-100 border-2 border-slate-200 rounded-3xl cursor-not-allowed outline-none text-3xl font-black text-slate-500"
                            placeholder="0.00">
                    </div>
                    <p class="text-[9px] text-blue-500 font-bold text-center px-4 leading-relaxed uppercase tracking-tighter">
                        <i class="fas fa-info-circle mr-1"></i> Este monto se calcula automáticamente sumando el fondo inicial y las ventas.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 pt-4">
                    <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-3xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-red-600 transition-all transform active:scale-95">
                        Confirmar Arqueo y Cerrar
                    </button>
                    <button type="button" @click="openModalCierre = false" class="w-full py-3 text-slate-400 font-bold uppercase text-[10px] tracking-widest hover:text-slate-600 transition-all">
                        Seguir Vendiendo
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>