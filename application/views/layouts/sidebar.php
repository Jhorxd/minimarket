<?php 
    $segment1 = $this->uri->segment(1); 
    $segment2 = $this->uri->segment(2);
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('dashboard') ?>" class="brand-link border-0 flex items-center px-4 py-4 transition-all duration-300">
        <div class="bg-blue-600 p-2 rounded-lg mr-3 shadow-lg shadow-blue-500/30 flex items-center justify-center" style="width: 35px; height: 35px;">
            <i class="fas fa-boxes text-white text-sm"></i>
        </div>
        <span class="brand-text font-bold tracking-tight text-white uppercase text-sm">SISTEMA DE GESTIÓN</span>
    </a>

    <style>
        /* Contenedor Principal */
        .main-sidebar {
            background: #0f172a !important; /* Slate 900 Moderno */
            border-right: 1px solid rgba(255,255,255,0.05) !important;
        }

        /* Forzar Alineación Horizontal (Flexbox) */
        .nav-sidebar .nav-item .nav-link {
            display: flex !important;
            align-items: center !important;
            flex-direction: row !important; /* Asegura que sea horizontal */
            padding: 0.6rem 1rem !important;
            margin: 0.25rem 0.75rem !important;
            border-radius: 10px !important;
            transition: all 0.25s ease !important;
            white-space: nowrap; /* Evita que el texto salte de línea */
        }

        /* Estilo del Icono */
        .nav-link i {
            width: 28px !important;
            min-width: 28px !important; /* Evita que se encoja */
            margin-right: 12px !important;
            font-size: 1rem !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            color: #94a3b8; /* Slate 400 */
        }

        /* Estilo del Texto */
        .nav-link p {
            margin: 0 !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            letter-spacing: 0.02em;
        }

        /* Efecto Hover */
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            transform: translateX(4px);
        }

        .nav-link:hover i {
            color: #3b82f6 !important;
        }

        /* Item Activo */
        .nav-link.active {
            background: #3b82f6 !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-link.active i {
            color: white !important;
        }

        /* Encabezados de Sección */
        .nav-header {
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            color: #475569 !important; /* Slate 600 */
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            padding: 1.5rem 1.2rem 0.5rem !important;
            background: transparent !important;
        }

        /* Logout Especial */
        .nav-logout {
            margin-top: 2rem !important;
            background: rgba(239, 68, 68, 0.1) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
        }

        .nav-logout:hover {
            background: #ef4444 !important;
            color: white !important;
            border-color: #ef4444 !important;
        }

        /* Scrollbar Fina */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <li class="nav-header">PRINCIPAL</li>
                
                <li class="nav-item">
                    <a href="<?= base_url('ventas/pos') ?>" class="nav-link <?= ($segment1 == 'ventas' && $segment2 == 'pos') ? 'active' : '' ?>">
                        <i class="fas fa-cash-register"></i>
                        <p>Punto de Venta</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('clientes/cliente_index') ?>" class="nav-link <?= ($segment1 == 'clientes') ? 'active' : '' ?>">
                        <i class="fas fa-user-friends"></i>
                        <p>Clientes</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('proveedores/proveedor_index') ?>" class="nav-link <?= ($segment1 == 'proveedores') ? 'active' : '' ?>">
                        <i class="fas fa-truck-loading"></i>
                        <p>Proveedores</p>
                    </a>
                </li>

                <?php if ($this->session->userdata('rol') == 'admin'): ?>
                <li class="nav-header">ADMINISTRACIÓN</li>

                <li class="nav-item">
                    <a href="<?= base_url('ventas/venta_index') ?>" class="nav-link <?= ($segment1 == 'ventas' && $segment2 == 'venta_index') ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i>
                        <p>Ventas Realizadas</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('dashboard/financiero') ?>" class="nav-link <?= ($segment1 == 'dashboard' && $segment2 == 'financiero') ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie"></i>
                        <p>Dashboard Financiero</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('compras/compras_index') ?>" class="nav-link <?= ($segment1 == 'compras') ? 'active' : '' ?>">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Compras e Inventario</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('gastos') ?>" class="nav-link <?= ($segment1 == 'gastos') ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <p>Gastos operativos</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('almacen/stock_index') ?>" class="nav-link <?= ($segment1 == 'almacen') ? 'active' : '' ?>">
                        <i class="fas fa-layer-group"></i>
                        <p>Stock Almacén</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('productos') ?>" class="nav-link <?= ($segment1 == 'productos') ? 'active' : '' ?>">
                        <i class="fas fa-tags"></i> 
                        <p>Gestión Productos</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('caja') ?>" class="nav-link <?= ($segment1 == 'caja') ? 'active' : '' ?>">
                        <i class="fas fa-wallet"></i>
                        <p>Control de Cajas</p>
                    </a>
                </li>

                <li class="nav-header">MÁS</li>

                <li class="nav-item">
                    <a href="<?= base_url('categorias') ?>" class="nav-link <?= ($segment1 == 'categorias') ? 'active' : '' ?>">
                        <i class="fas fa-sitemap"></i>
                        <p>Categorías</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('almacenes') ?>" class="nav-link <?= ($segment1 == 'almacenes') ? 'active' : '' ?>">
                        <i class="fas fa-warehouse"></i>
                        <p>Almacenes</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($this->session->userdata('rol') == 'vendedor'): ?>
                <li class="nav-header">COTIZACIONES</li>
                <li class="nav-item">
                    <a href="<?= base_url('ventas_bolivia/nueva_cotizacion') ?>" class="nav-link <?= ($segment1 == 'ventas_bolivia') ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice"></i> 
                        <p>Nueva Cotización</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?= base_url('login/cerrar') ?>" class="nav-link nav-logout">
                        <i class="fas fa-power-off"></i>
                        <p>Cerrar Sesión</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>