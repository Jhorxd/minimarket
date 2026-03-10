<?php
$id_cliente    = isset($cliente) ? $cliente->id_cliente    : '';
$nombre        = isset($cliente) ? $cliente->nombre        : '';
$tipo_documento= isset($cliente) ? $cliente->tipo_documento: 'DNI';
$nro_documento = isset($cliente) ? $cliente->nro_documento : '';
$telefono      = isset($cliente) ? $cliente->telefono      : '';
$email         = isset($cliente) ? $cliente->email         : '';
$direccion     = isset($cliente) ? $cliente->direccion     : '';

?>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Clientes
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    <?= $id_cliente ? 'Editar Cliente' : 'Nuevo Cliente'; ?>
                </h1>
            </div>
        </header>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
            <form action="<?= base_url('clientes/guardar'); ?>" method="post" class="space-y-4">
                <input type="hidden" name="id_cliente" value="<?= $id_cliente; ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Nombre / Razón social
                    </label>
                    <input type="text" name="nombre" required
                           value="<?= htmlspecialchars($nombre); ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Tipo documento
                        </label>
                        <select name="tipo_documento"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="DNI"  <?= $tipo_documento === 'DNI'  ? 'selected' : ''; ?>>DNI</option>
                            <option value="RUC"  <?= $tipo_documento === 'RUC'  ? 'selected' : ''; ?>>RUC</option>
                            <option value="OTRO" <?= $tipo_documento === 'OTRO' ? 'selected' : ''; ?>>OTRO</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            N° documento
                        </label>
                        <input type="text" name="nro_documento"
                               value="<?= htmlspecialchars($nro_documento); ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Teléfono
                        </label>
                        <input type="text" name="telefono"
                               value="<?= htmlspecialchars($telefono); ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Email
                        </label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($email); ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Dirección
                    </label>
                    <input type="text" name="direccion"
                           value="<?= htmlspecialchars($direccion); ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="<?= base_url('clientes/cliente_index'); ?>"
                       class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-100">
                        Guardar
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
