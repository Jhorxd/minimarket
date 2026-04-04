<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Módulo de Ventas
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    Detalle de Venta
                </h1>
                <p class="mt-1 text-xs text-slate-400 flex items-center gap-2">
                    Ticket #<?= str_pad($venta->id, 6, '0', STR_PAD_LEFT); ?> · 
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest border <?= ($venta->estado === 'anulada') ? 'bg-red-50 text-red-500 border-red-100' : 'bg-blue-50 text-blue-500 border-blue-100' ?>">
                        <?= $venta->estado; ?>
                    </span>
                    · <?= date('d/m/Y H:i', strtotime($venta->fecha_registro)); ?>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($venta->estado === 'completada'): ?>
                    <button onclick="anularVentaDetalle(<?= $venta->id ?>)"
                        class="px-4 py-2 rounded-xl border border-rose-200 bg-white text-rose-600 text-sm font-semibold hover:bg-rose-600 hover:text-white transition-all flex items-center gap-2">
                        <i class="fas fa-times"></i> Anular Venta
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('ventas/ticket/' . $venta->id); ?>" target="_blank"
                   class="px-4 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-600 text-sm font-semibold hover:bg-blue-600 hover:text-white transition-all flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> Ticket PDF
                </a>
                <a href="<?= base_url('ventas/venta_index'); ?>"
                   class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                    Volver al historial
                </a>
            </div>
        </header>

        <!-- CABECERA: cliente y totales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 lg:col-span-2">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                    Información del Cliente y Cajero
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Cliente:</span>
                        <div class="font-semibold text-slate-700">
                            <?= htmlspecialchars($venta->cliente_nombre ?: 'Público General'); ?>
                        </div>
                        <?php if (!empty($venta->nro_documento)): ?>
                            <div class="text-[11px] text-slate-500">
                                <?= $venta->tipo_documento; ?>: <?= htmlspecialchars($venta->nro_documento); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Atendido por:</span>
                        <div class="font-semibold text-slate-700">
                            <?= htmlspecialchars($venta->cajero); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                    Resumen de Pago
                </h2>
                <div class="flex flex-col gap-2 text-sm text-slate-700">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Método de pago:</span>
                        <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-black uppercase"><?= $venta->metodo_pago; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Total Venta:</span>
                        <span class="font-black <?= ($venta->estado === 'anulada') ? 'text-red-500 line-through' : 'text-blue-600' ?>">
                            S/ <?= number_format($venta->total, 2); ?>
                        </span>
                    </div>
                    
                    <?php if ($venta->metodo_pago === 'efectivo'): ?>
                        <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-[10px] text-slate-400">Recibido:</span>
                            <span class="text-xs font-bold text-slate-600">S/ <?= number_format($venta->monto_recibido, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-slate-400">Vuelto:</span>
                            <span class="text-xs font-bold text-emerald-600">S/ <?= number_format($venta->vuelto, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($venta->estado === 'anulada'): ?>
                        <div class="mt-2 p-2 bg-red-50 border border-red-100 rounded-lg text-[10px] text-red-600 uppercase font-black tracking-widest">
                            <i class="fas fa-info-circle mr-1"></i> Motivo: <?= htmlspecialchars($venta->motivo_anulacion); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- DETALLE DE PRODUCTOS -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Productos de la venta
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 font-bold">Producto</th>
                            <th class="px-4 py-3 font-bold text-center">Variante</th>
                            <th class="px-4 py-3 font-bold text-right">Cantidad</th>
                            <th class="px-4 py-3 font-bold text-right">P. Unitario</th>
                            <th class="px-4 py-3 font-bold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($detalles)): foreach ($detalles as $d): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-800">
                                        <?= htmlspecialchars($d->nombre); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono"><?= $d->codigo_barras; ?></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (!empty($d->talla) || !empty($d->color) || !empty($d->diseno)): ?>
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded text-[9px] font-bold uppercase">
                                            <?= trim($d->talla . ' ' . $d->color . ' ' . $d->diseno); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">
                                    <?= number_format($d->cantidad, 2); ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">
                                    S/ <?= number_format($d->precio_unitario, 2); ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">
                                    S/ <?= number_format($d->subtotal, 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function anularVentaDetalle(id) {
    Swal.fire({
        title: '¿Anular esta venta?',
        text: 'Se devolverá el stock y se ajustará el Kardex.',
        input: 'text',
        inputPlaceholder: 'Ingresa el motivo...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        preConfirm: (motivo) => {
            if (!motivo) Swal.showValidationMessage('El motivo es obligatorio');
            return motivo;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= base_url('ventas/anular_venta') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id_venta: id, motivo: result.value})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Anulada', 'Venta anulada con éxito.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Error', 'error');
                }
            });
        }
    });
}
</script>
