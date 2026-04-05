<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hoy    = new DateTimeImmutable('today');
$urlFin = site_url('dashboard/financiero');
$preset = function ($desde, $hasta) use ($urlFin) {
    return $urlFin . '?' . http_build_query(['fecha_desde' => $desde, 'fecha_hasta' => $hasta]);
};
$q7  = $preset($hoy->sub(new DateInterval('P6D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$q30 = $preset($hoy->sub(new DateInterval('P29D'))->format('Y-m-d'), $hoy->format('Y-m-d'));
$iniMes = $hoy->format('Y-m-01');
$qMes = $preset($iniMes, $hoy->format('Y-m-d'));
?>
<?php $this->load->view('layouts/header', isset($titulo) ? ['titulo' => $titulo] : []); ?>
<?php $this->load->view('layouts/sidebar'); ?>

<div class="lg:ml-[250px] min-h-screen bg-slate-50 transition-all duration-300 pt-16 lg:pt-0">
    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8 border-b border-slate-200 pb-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Finanzas</nav>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Dashboard financiero</h1>
                <p class="text-sm text-slate-500 mt-1">Ingresos por ventas; egresos por compras de mercadería y por <a href="<?= site_url('gastos') ?>" class="text-blue-600 font-semibold hover:underline">gastos operativos</a>.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a id="btnExportExcel" href="<?= site_url('dashboard/export_excel?' . http_build_query(['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta])) ?>"
                   onclick="var btn=this; btn.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Generando...'; btn.classList.add('opacity-70', 'pointer-events-none'); setTimeout(function(){ btn.innerHTML='<i class=\'fas fa-file-excel\'></i> Excel'; btn.classList.remove('opacity-70', 'pointer-events-none'); }, 3000);"
                   class="inline-flex min-w-[100px] justify-center items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-lg shadow-emerald-600/25 hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="<?= site_url('dashboard/export_pdf?' . http_build_query(['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta])) ?>"
                   target="_blank"
                   class="inline-flex min-w-[100px] justify-center items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-red-600 text-white shadow-lg shadow-red-600/25 hover:bg-red-700 transition-colors">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </header>

        <form id="formFiltroFinanciero" method="get" action="<?= site_url('dashboard/financiero') ?>"
              class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm mb-8"
              onsubmit="var b=this.querySelector('button[type=submit]'); if(b){ b.disabled=true; b.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Cargando…'; }">
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
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Hasta</label>
                    <input type="date" name="fecha_hasta" value="<?= html_escape($fecha_hasta) ?>"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-colors disabled:opacity-70">
                        <i class="fas fa-filter"></i> Aplicar
                    </button>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">
                <strong><?= (int) $dias_rango ?></strong> día(s) en el rango · Los gráficos diarios incluyen días sin movimiento en cero.
            </p>

        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm border-t-4 border-t-emerald-500">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total ingresos</p>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">S/ <?= number_format($total_ingresos, 2) ?></p>
                        <p class="text-xs text-slate-500 mt-2"><code class="text-slate-600 bg-slate-100 px-1 rounded">ventas</code></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-arrow-trend-up text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm border-t-4 border-t-orange-500">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Egresos mercadería</p>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">S/ <?= number_format($egresos_compras, 2) ?></p>
                        <p class="text-xs text-slate-500 mt-2"><code class="text-slate-600 bg-slate-100 px-1 rounded">compras</code></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
                        <i class="fas fa-shopping-basket text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm border-t-4 border-t-rose-500">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Egresos operativos</p>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">S/ <?= number_format($egresos_gastos, 2) ?></p>
                        <p class="text-xs text-slate-500 mt-2"><code class="text-slate-600 bg-slate-100 px-1 rounded">gastos</code></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm border-t-4 border-t-blue-500">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ganancia neta</p>
                        <p class="text-2xl sm:text-3xl font-black <?= $ganancia_neta >= 0 ? 'text-slate-900' : 'text-rose-600' ?> mt-1">S/ <?= number_format($ganancia_neta, 2) ?></p>
                        <p class="text-xs text-slate-500 mt-2">Ingresos − compras − gastos</p>
                        <p class="text-[10px] text-slate-400 mt-1">Total egresos: S/ <?= number_format($total_egresos, 2) ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fas fa-scale-balanced text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                        Ingresos vs egresos (por día)
                    </h2>
                    <p class="text-[11px] text-slate-400"><?= (int) $dias_rango ?> días · <?= (int) $dias_rango > 14 ? 'Vista en líneas' : 'Vista en barras' ?></p>
                </div>
                <div class="p-4 sm:p-6 h-80 min-h-[320px]">
                    <canvas id="chartBarDiario"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-violet-600 rounded-full"></span>
                        Distribución de egresos por categoría
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Prefijos <strong>Mercadería</strong> (compras) y <strong>Operativo</strong> (gastos).</p>
                </div>
                <div class="p-4 sm:p-6 h-80 flex items-center justify-center">
                    <canvas id="chartPieCategorias"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-cyan-600 rounded-full"></span>
                    Evolución mensual en el período
                </h2>
            </div>
            <div class="p-4 sm:p-6 h-80">
                <canvas id="chartLineMensual"></canvas>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const palette = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'];

    const serieDiaria = <?= $json_serie_diaria ?>;
    const diasRango = <?= (int) $dias_rango ?>;
    const labelsDia = serieDiaria.map(function (r) { return r.d; });
    const ingDia = serieDiaria.map(function (r) { return r.ingresos; });
    const egrDia = serieDiaria.map(function (r) { return r.egresos; });

    const usarLineas = diasRango > 14;
    const chartTipo = usarLineas ? 'line' : 'bar';
    const dsIngresos = usarLineas
        ? {
            label: 'Ingresos (ventas)',
            data: ingDia,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.12)',
            fill: true,
            tension: 0.25,
            pointRadius: diasRango > 45 ? 0 : 3,
            pointHoverRadius: 5,
            borderWidth: 2
        }
        : {
            label: 'Ingresos (ventas)',
            data: ingDia,
            backgroundColor: 'rgba(16, 185, 129, 0.75)',
            borderRadius: 6,
            maxBarThickness: 14
        };
    const dsEgresos = usarLineas
        ? {
            label: 'Egresos (compras + gastos)',
            data: egrDia,
            borderColor: '#f43f5e',
            backgroundColor: 'rgba(244, 63, 94, 0.08)',
            fill: true,
            tension: 0.25,
            pointRadius: diasRango > 45 ? 0 : 3,
            pointHoverRadius: 5,
            borderWidth: 2
        }
        : {
            label: 'Egresos (compras + gastos)',
            data: egrDia,
            backgroundColor: 'rgba(244, 63, 94, 0.75)',
            borderRadius: 6,
            maxBarThickness: 14
        };

    new Chart(document.getElementById('chartBarDiario'), {
        type: chartTipo,
        data: {
            labels: labelsDia,
            datasets: [dsIngresos, dsEgresos]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const v = ctx.raw != null ? ctx.raw : 0;
                            return ctx.dataset.label + ': S/ ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: usarLineas ? 0 : 45,
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: usarLineas ? 12 : 20
                    },
                    grid: { display: usarLineas }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (v) {
                            return Number(v).toLocaleString('es-PE', { maximumFractionDigits: 0 });
                        }
                    }
                }
            }
        }
    });

    const catData = <?= $json_egresos_categoria ?>;
    const catLabels = catData.map(function (r) { return r.categoria; });
    const catTotals = catData.map(function (r) { return parseFloat(r.total); });
    const bg = catLabels.map(function (_, i) { return palette[i % palette.length]; });

    new Chart(document.getElementById('chartPieCategorias'), {
        type: 'doughnut',
        data: {
            labels: catLabels.length ? catLabels : ['Sin datos'],
            datasets: [{
                data: catTotals.length ? catTotals : [1],
                backgroundColor: catTotals.length ? bg : ['#e2e8f0'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const v = ctx.raw || 0;
                            return ctx.label + ': S/ ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }
        }
    });

    const mensual = <?= $json_serie_mensual ?>;
    const labelsMes = Object.keys(mensual);
    const ingMes = labelsMes.map(function (k) { return mensual[k].ingresos; });
    const egrMes = labelsMes.map(function (k) { return mensual[k].egresos; });

    new Chart(document.getElementById('chartLineMensual'), {
        type: 'line',
        data: {
            labels: labelsMes,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingMes,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Egresos',
                    data: egrMes,
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.08)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
})();
</script>

<?php $this->load->view('layouts/footer'); ?>
