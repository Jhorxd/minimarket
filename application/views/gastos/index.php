<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hoy    = new DateTimeImmutable('today');
$urlGastos = site_url('gastos/index');
$preset = function ($desde, $hasta) use ($urlGastos) {
    return $urlGastos . '?' . http_build_query(['fecha_desde' => $desde, 'fecha_hasta' => $hasta]);
};
$q7  = $preset($hoy->sub(new DateInterval('P6D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$q30 = $preset($hoy->sub(new DateInterval('P29D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$iniMes = $hoy->format('Y-m-01');
$qMes = $preset($iniMes, $hoy->format('Y-m-d'));

$gastos_json = [];
foreach ($gastos as $g) {
    $gastos_json[] = [
        'id'               => (int) $g->id,
        'concepto'         => $g->concepto,
        'monto'            => (float) $g->monto,
        'fecha_gasto'      => $g->fecha_gasto,
        'categoria_nombre' => $g->categoria_nombre,
        'usuario_nombre'   => $g->usuario_nombre,
        'observaciones'    => $g->observaciones,
    ];
}
?>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0"
     x-data="{
        items: <?= htmlspecialchars(json_encode($gastos_json), ENT_QUOTES, 'UTF-8') ?>,
        search: '',
        page: 1,
        perPage: 10,
        get filteredItems() {
            if (this.search === '') return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i =>
                i.concepto.toLowerCase().includes(q) ||
                i.categoria_nombre.toLowerCase().includes(q) ||
                (i.observaciones && i.observaciones.toLowerCase().includes(q))
            );
        },
        get pagedItems() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredItems.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
        },
        nextPage() { if (this.page < this.totalPages) this.page++; },
        prevPage() { if (this.page > 1) this.page--; },
        resetPage() { this.page = 1; }
     }">

    <div class="p-4 sm:p-6 lg:p-8 w-full max-w-7xl mx-auto">

        <header class="flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 leading-none">Administración</nav>
                <h1 class="text-2xl font-black text-slate-800 tracking-tighter">Gastos operativos</h1>
                <p class="text-slate-500 text-sm mt-2">Registre egresos que no son compras de mercadería (alquiler, servicios, nómina, etc.). Las compras a proveedor siguen en <strong>Compras e Inventario</strong>.</p>
            </div>
        </header>

        <!-- Filtros de Fecha -->
        <form method="get" action="<?= site_url('gastos/index') ?>"
              class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm mb-8">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Atajos</span>
                <a href="<?= html_escape($q7) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Últimos 7 días</a>
                <a href="<?= html_escape($q30) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Últimos 30 días</a>
                <a href="<?= html_escape($qMes) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Mes en curso</a>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Desde</label>
                    <input type="date" name="fecha_desde" value="<?= html_escape($fecha_desde) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-rose-500 outline-none transition-shadow">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= html_escape($fecha_hasta) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-rose-500 outline-none transition-shadow">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest bg-rose-600 text-white hover:bg-rose-700 shadow-lg shadow-rose-600/20 transition-colors">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
            </div>
        </form>

        <?php if ($this->session->flashdata('ok')): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm font-medium">
                <?= html_escape($this->session->flashdata('ok')) ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm font-medium">
                <?= html_escape($this->session->flashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 mb-10">
            <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-rose-500 rounded-full"></span> Registrar gasto
                </h2>
                <form action="<?= site_url('gastos/guardar') ?>" method="post" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Categoría</label>
                        <select name="id_categoria_gasto" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Seleccione…</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int) $c->id ?>"><?= html_escape($c->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Concepto</label>
                        <input type="text" name="concepto" required maxlength="255" placeholder="Ej. Pago luz abril"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Monto (S/)</label>
                            <input type="number" name="monto" required step="0.01" min="0.01"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Fecha</label>
                            <input type="date" name="fecha_gasto" required value="<?= date('Y-m-d') ?>"
                                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Observaciones (opcional)</label>
                        <textarea name="observaciones" rows="2" maxlength="500"
                                  class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-slate-900 text-white font-bold text-sm hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">
                        Guardar gasto
                    </button>
                </form>
            </div>

            <div class="xl:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h2 class="font-bold text-slate-800">Historial</h2>
                    <input type="search" x-model="search" @input="resetPage()" placeholder="Buscar…"
                           class="rounded-xl border border-slate-200 px-4 py-2 text-sm w-full sm:w-64 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-3 font-bold">Fecha</th>
                                <th class="px-6 py-3 font-bold">Categoría</th>
                                <th class="px-6 py-3 font-bold">Concepto</th>
                                <th class="px-6 py-3 font-bold text-right">Monto</th>
                                <th class="px-6 py-3 font-bold text-center w-24">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="row in pagedItems" :key="row.id">
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap" x-text="row.fecha_gasto"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700" x-text="row.categoria_nombre"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-800">
                                        <div class="font-semibold" x-text="row.concepto"></div>
                                        <div class="text-xs text-slate-400 mt-0.5" x-show="row.observaciones" x-text="row.observaciones"></div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900" x-text="'S/ ' + Number(row.monto).toFixed(2)"></td>
                                    <td class="px-6 py-4 text-center">
                                        <a :href="'<?= site_url('gastos/eliminar/') ?>' + row.id"
                                           onclick="return confirm('¿Eliminar este gasto?');"
                                           class="text-red-600 hover:text-red-800 text-xs font-bold uppercase">Eliminar</a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p class="px-6 py-8 text-center text-slate-400 text-sm" x-show="filteredItems.length === 0">No hay gastos registrados.</p>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500" x-show="filteredItems.length > perPage">
                    <button type="button" @click="prevPage()" class="font-bold text-blue-600 disabled:opacity-30" :disabled="page <= 1">Anterior</button>
                    <span>Página <span x-text="page"></span> de <span x-text="totalPages"></span></span>
                    <button type="button" @click="nextPage()" class="font-bold text-blue-600 disabled:opacity-30" :disabled="page >= totalPages">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>
