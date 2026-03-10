<aside class="main-sidebar sidebar-dark-primary elevation-4">

<a href="#" class="brand-link">
<span class="brand-text font-weight-light">Inventario</span>
</a>
<style>
    <style>
/* Sidebar AdminLTE Responsive - Mantiene tu HTML original */
.main-sidebar {
    background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
    border-right: 1px solid #374151;
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.brand-link {
    background: linear-gradient(135deg, #007bff 0%, #007bff 100%);
    border-bottom: 1px solid #4b5563;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.brand-link:hover {
    background: linear-gradient(135deg, #007bff 0%, #007bff 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 300;
    letter-spacing: 0.05em;
}

.sidebar {
    padding: 1rem 0.75rem;
    height: calc(100vh - 70px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #6b7280 #1f2937;
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: #1f2937;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #6b7280;
    border-radius: 3px;
}

.nav-header {
    color: #a1a1aa;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.5rem 1rem;
    margin: 0.75rem 0;
    background: rgba(147, 51, 234, 0.1);
    border-radius: 0.5rem;
    border-left: 3px solid #e7e7e7;
}

.nav-link {
    color: #d1d5db;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: transparent;
    transition: all 0.2s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.nav-link:hover::before {
    background: linear-gradient(180deg, #3b82f6, #1e40af);
    width: 4px;
}

.nav-link.active {
    background: rgba(59, 130, 246, 0.2) !important;
    color: white !important;
    border-left: 4px solid #3b82f6;
}

.nav-icon {
    width: 1.25rem;
    margin-right: 0.75rem;
    opacity: 0.7;
    transition: all 0.2s ease;
    font-size: 1.1rem;
}

.nav-link:hover .nav-icon {
    opacity: 1;
    transform: scale(1.1);
}

p {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
}

/* RESPONSIVE - AdminLTE Style */
@media (max-width: 991px) {
    .main-sidebar {
        transform: translateX(-100%);
        position: fixed;
        top: 60px;
        z-index: 1050;
        height: calc(100vh - 60px);
    }
    
    .sidebar-open .main-sidebar {
        transform: translateX(0);
    }
}

@media (max-width: 767px) {
    .main-sidebar {
        width: 250px !important;
    }
}

/* Logout especial */
.nav-link[href*="login/cerrar"] {
    margin-top: 2rem;
    background: rgba(239, 68, 68, 0.2) !important;
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5 !important;
}

.nav-link[href*="login/cerrar"]:hover {
    background: rgba(239, 68, 68, 0.3) !important;
    color: white !important;
    border-color: #ef4444;
}

/* Módulo Perú/Bolivia colores */
.nav-header {
    background: rgba(147, 51, 234, 0.1);
    border-left-color: #5c85f6;
}

<?php if ($this->session->userdata('pais') == 'bolivia'): ?>
.nav-header {
    background: rgba(16, 185, 129, 0.1);
    border-left-color: #10b981;
}
<?php endif; ?>
</style>

</style>
<div class="sidebar">

<nav class="mt-2">
<ul class="nav nav-pills nav-sidebar flex-column">

<li class="nav-item">
<a href="<?= base_url('dashboard') ?>" class="nav-link">
<i class="nav-icon fas fa-home"></i>
<p>Dashboard</p>
</a>
</li>

<?php if ($this->session->userdata('rol') == 'admin'): ?>

    <li class="nav-item">
        <a href="<?= base_url('ventas/pos') ?>" class="nav-link">
            <i class="nav-icon fas fa-cash-register"></i>
            <p>Punto de Venta</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= base_url('productos') ?>" class="nav-link">
            <i class="nav-icon fas fa-file-invoice-dollar"></i> 
            <p>Productos</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="<?= base_url('caja') ?>" class="nav-link">
            <i class="nav-icon fas fa-cash-register"></i>
            <p>Control de Cajas</p>
        </a>
    </li>


<?php endif; ?>

<?php if ($this->session->userdata('rol') == 'vendedor'): ?>

    <li class="nav-item">
        <a href="<?= base_url('ventas_bolivia/nueva_cotizacion') ?>" class="nav-link">
            <i class="nav-icon fas fa-file-invoice-dollar"></i> 
            <p><small class="d-md-inline d-none">Nueva</small> Cotización</p>
        </a>
    </li>

<?php endif; ?>

<li class="nav-item mt-4">
    <a href="<?= base_url('login/cerrar') ?>" class="nav-link text-danger">
        <i class="nav-icon fas fa-sign-out-alt"></i>
        <p>Cerrar Sesión</p>
    </a>
</li>


</ul>
</nav>

</div>
</aside>