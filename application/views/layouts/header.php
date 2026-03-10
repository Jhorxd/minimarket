<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema Inventario</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url() ?>plugins/fontawesome-free/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>dist/css/adminlte.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    <style>
        /* Tailwind config para AdminLTE */
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .sidebar-dark-primary .nav-link { transition: all 0.3s ease; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div class="wrapper">

<!-- Navbar SOLO en pantallas pequeñas/medias -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light d-lg-none">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <!-- Botón hamburguesa que abre/cierra el sidebar -->
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">Inicio</a>
        </li>
    </ul>

    <!-- Right navbar links (opcional) -->
    <ul class="navbar-nav ml-auto">
        <!-- aquí podrías poner usuario, notificaciones, etc. -->
    </ul>
</nav>

    <!-- /.navbar -->

    <!-- Main Sidebar Container (tu sidebar va aquí en sidebar.php) -->
