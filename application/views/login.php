<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-md">
    
    <!-- LOGO -->
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-slate-800">
            <b>Sistema</b> Inventario
        </h1>
    </div>

    <!-- LOGIN CARD -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="p-6">
            <p class="text-center text-slate-700 mb-6 font-medium">Iniciar sesión</p>

                <!-- MENSAJE DE ERROR -->
            <?php if($this->session->flashdata('login_error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong>Error:</strong> <?= $this->session->flashdata('login_error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login/ingresar') ?>" method="post" class="space-y-4">

                <!-- USUARIO -->
                <div class="relative">
                    <input type="text" name="usuario" placeholder="Usuario" required
                           class="w-full border border-slate-300 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <span class="absolute right-3 top-3 text-slate-400">
                        <i class="fas fa-user"></i>
                    </span>
                </div>

                <!-- CONTRASEÑA -->
                <div class="relative">
                    <input type="password" name="password" placeholder="Contraseña" required
                           class="w-full border border-slate-300 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <span class="absolute right-3 top-3 text-slate-400">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>

                <!-- BOTÓN -->
                <div>
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Ingresar
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<!-- SCRIPTS -->
<script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
<script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>