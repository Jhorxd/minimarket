<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login</title>

<link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

@keyframes fadeInUp{
0%{
opacity:0;
transform:translateY(40px);
}
100%{
opacity:1;
transform:translateY(0);
}
}

@keyframes float{
0%,100%{transform:translateY(0)}
50%{transform:translateY(-8px)}
}

.animate-login{
animation:fadeInUp .8s ease;
}

.animate-float{
animation:float 4s ease-in-out infinite;
}

/* Fondo animado */

.inventory-bg{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
overflow:hidden;
z-index:0;
pointer-events:none;
}

.inventory-bg i{
position:absolute;
color:#2563eb;
opacity:.40;
font-size:70px;
animation:floatInventory 20s linear infinite;
}

/* posiciones */

.inventory-bg i:nth-child(1){
left:5%;
top:80%;
font-size:50px;
animation-duration:25s;
}

.inventory-bg i:nth-child(2){
left:15%;
top:60%;
font-size:35px;
animation-duration:18s;
}

.inventory-bg i:nth-child(3){
left:30%;
top:75%;
animation-duration:22s;
}

.inventory-bg i:nth-child(4){
left:45%;
top:65%;
font-size:60px;
animation-duration:28s;
}

.inventory-bg i:nth-child(5){
left:60%;
top:85%;
font-size:45px;
animation-duration:24s;
}

.inventory-bg i:nth-child(6){
left:70%;
top:70%;
animation-duration:20s;
}

.inventory-bg i:nth-child(7){
left:85%;
top:80%;
font-size:55px;
animation-duration:26s;
}

.inventory-bg i:nth-child(8){
left:92%;
top:65%;
animation-duration:19s;
}

/* animación */

@keyframes floatInventory{

0%{
transform:translateY(0) rotate(0deg);
}

50%{
transform:translateY(-120px) rotate(180deg);
}

100%{
transform:translateY(-240px) rotate(360deg);
}

}


</style>

</head>

<body class="bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400 flex items-center justify-center min-h-screen">

<div class="inventory-bg">

<i class="fas fa-box"></i>
<i class="fas fa-barcode"></i>
<i class="fas fa-dolly"></i>
<i class="fas fa-truck"></i>
<i class="fas fa-warehouse"></i>
<i class="fas fa-cubes"></i>
<i class="fas fa-shopping-cart"></i>
<i class="fas fa-box-open"></i>

</div>


<div class="relative z-10 w-full max-w-md px-4 animate-login">


    <!-- LOGO -->
    <div class="text-center mb-8 animate-float">
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">
            <span class="text-blue-600">Sistema</span> Inventario
        </h1>
        <p class="text-slate-500 text-sm mt-2">Control inteligente de productos</p>
    </div>

    <!-- LOGIN CARD -->
    <div class="bg-white/90 backdrop-blur-md shadow-2xl rounded-2xl overflow-hidden border border-slate-200">

        <div class="p-8">

            <p class="text-center text-slate-700 mb-6 font-semibold text-lg">
                Iniciar sesión
            </p>

            <!-- MENSAJE DE ERROR -->
            <?php if($this->session->flashdata('login_error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Error:</strong> <?= $this->session->flashdata('login_error') ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login/ingresar') ?>" method="post" class="space-y-5">

                <!-- USUARIO -->
                <div class="relative group">
                    <input type="text" name="usuario" placeholder="Usuario" required
                    class="w-full border border-slate-300 rounded-xl px-4 py-3 pr-12
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                    transition-all duration-200">

                    <span class="absolute right-3 top-3 text-slate-400 group-focus-within:text-blue-500">
                        <i class="fas fa-user"></i>
                    </span>
                </div>

                <!-- PASSWORD -->
                <div class="relative group">
                    <input type="password" name="password" placeholder="Contraseña" required
                    class="w-full border border-slate-300 rounded-xl px-4 py-3 pr-12
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                    transition-all duration-200">

                    <span class="absolute right-3 top-3 text-slate-400 group-focus-within:text-blue-500">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>

                <!-- BOTON -->
                <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700
                text-white font-semibold py-3 rounded-xl
                flex items-center justify-center gap-2
                shadow-lg hover:shadow-xl
                transition-all duration-200
                active:scale-95">

                    <i class="fas fa-sign-in-alt"></i>
                    Ingresar
                </button>

            </form>

        </div>

    </div>

    <!-- FOOTER -->
    <p class="text-center text-xs text-slate-500 mt-6">
        © <?= date('Y') ?> Sistema Inventario
    </p>

</div>

<script src="<?= base_url() ?>plugins/jquery/jquery.min.js"></script>
<script src="<?= base_url() ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
