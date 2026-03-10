<?php
$id_proveedor    = isset($proveedor) ? $proveedor->id_proveedor    : '';
$razon_social    = isset($proveedor) ? $proveedor->razon_social    : '';
$nombre_comercial= isset($proveedor) ? $proveedor->nombre_comercial: '';
$tipo_documento  = isset($proveedor) ? $proveedor->tipo_documento  : 'RUC';
$nro_documento   = isset($proveedor) ? $proveedor->nro_documento   : '';
$telefono        = isset($proveedor) ? $proveedor->telefono        : '';
$email           = isset($proveedor) ? $proveedor->email           : '';
$direccion       = isset($proveedor) ? $proveedor->direccion       : '';
$rubro           = isset($proveedor) ? $proveedor->rubro           : '';
?>

<div class="md:ml-64 min-h-screen bg-slate-50 transition-all duration-300 pt-16 md:pt-0">

    <div class="p-4 sm:p-6 lg:p-10 w-full">

        <header class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div>
                <nav class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    Gestión de Proveedores
                </nav>
                <h1 class="text-3xl font-black text-slate-800">
                    <?= $id_proveedor ? 'Editar Proveedor' : 'Nuevo Proveedor'; ?>
                </h1>
            </div>
        </header>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
            <form action="<?= base_url('proveedores/guardar'); ?>" method="post" class="space-y-4">
                <input type="hidden" name="id_proveedor" value="<?= $id_proveedor; ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Razón social
                    </label>
                    <input type="text" name="razon_social" required
                           value="<?= htmlspecialchars($razon_social); ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Nombre comercial
                    </label>
                    <input type="text" name="nombre_comercial"
                           value="<?= htmlspecialchars($nombre_comercial); ?>"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                            Tipo documento
                        </label>
                        <select name="tipo_documento"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="RUC" <?= $tipo_documento === 'RUC' ? 'selected' : ''; ?>>RUC</option>
                            <option value="DNI" <?= $tipo_documento === 'DNI' ? 'selected' : ''; ?>>DNI</option>
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

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">
                        Rubro
                    </label>
                    <input type="text" name="rubro"
                           value="<?= htmlspecialchars($rubro); ?>"
                           placeholder="Abarrotes, bebidas, limpieza, etc."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="<?= base_url('proveedores/proveedor_index'); ?>"
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
