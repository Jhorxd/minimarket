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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Tailwind config para AdminLTE */
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .sidebar-dark-primary .nav-link { transition: all 0.3s ease; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div class="wrapper">

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-dark navbar-primary shadow-lg">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">Dashboard</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        
        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user-circle text-xl"></i>
                <span class="d-none d-md-inline ml-2">
                    <?= $this->session->userdata('nombre') ?? 'Usuario' ?>
                </span>
                <?php if ($this->session->userdata('pais') == 'peru'): ?>
                    <span class="badge badge-success ml-1">PE</span>
                <?php elseif ($this->session->userdata('pais') == 'bolivia'): ?>
                    <span class="badge badge-info ml-1">BO</span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-lg border-0">
                <div class="dropdown-item text-center py-4 bg-primary text-white rounded-top">
                    <i class="fas fa-user-circle fa-3x mb-2"></i>
                    <h5><?= $this->session->userdata('nombre') ?? 'Usuario' ?></h5>
                    <small class="text-primary-100">
                        <?= strtoupper($this->session->userdata('pais') ?? '') ?>
                    </small>
                </div>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('perfil') ?>" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Mi Perfil
                </a>
                <a href="<?= base_url('configuracion') ?>" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i> Configuración
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('login/cerrar') ?>" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </li>

        <!-- Fullscreen -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container (tu sidebar va aquí) -->
