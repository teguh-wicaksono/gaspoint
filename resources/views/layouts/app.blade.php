<!DOCTYPE html>
<html lang="id" dir="ltr">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title') @yield('title') - @endif Rumah Gas & Galon</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Preload Fonts -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- CoreUI CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --sidebar-bg: linear-gradient(180deg, #2eb85c 0%, #1f8a47 100%);
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --sidebar-active: rgba(255, 255, 255, 0.2);
            --primary-color: #2eb85c;
            --secondary-color: #1f8a47;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        /* Sidebar Styling */
        #sidebar.sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            width: 16rem;
            background: var(--sidebar-bg);
            transition: width var(--transition-speed) ease, transform var(--transition-speed) ease;
        }

        #sidebar .sidebar-nav {
            min-height: 0;
            overflow-y: auto;
        }

        .wrapper {
            width: calc(100% - 16rem);
            margin-left: 16rem;
            transition: width var(--transition-speed) ease, margin-left var(--transition-speed) ease;
        }
        
        .sidebar-brand {
            display: flex !important;
            align-items: center;
            min-height: 4.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand-full {
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }
        
        .sidebar-brand-narrow {
            display: none;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar .nav-icon {
            width: 1.25rem;
            text-align: center;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            transition: all 0.2s ease;
            border-radius: 0.375rem;
            margin: 0.125rem 0.5rem;
            padding: 0.75rem 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: var(--sidebar-hover);
            transform: translateX(3px);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: var(--sidebar-active);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-title {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.5rem 1rem 0.5rem;
        }
        
        .sidebar .nav-group-items {
            padding-left: 0.5rem;
        }

        .sidebar-overlay {
            display: none;
            padding: 0;
            border: 0;
            background: rgba(16, 24, 32, 0.48);
            cursor: pointer;
        }

        body.sidebar-minimized #sidebar {
            width: 4.75rem;
        }

        body.sidebar-minimized .wrapper {
            width: calc(100% - 4.75rem);
            margin-left: 4.75rem;
        }

        body.sidebar-minimized .sidebar-brand {
            justify-content: center;
            padding: 1rem 0.5rem;
        }

        body.sidebar-minimized .sidebar-brand-full,
        body.sidebar-minimized .sidebar .nav-label,
        body.sidebar-minimized .sidebar .nav-title,
        body.sidebar-minimized .sidebar .badge,
        body.sidebar-minimized .sidebar .nav-group-items {
            display: none !important;
        }

        body.sidebar-minimized .sidebar-brand-narrow {
            display: block;
        }

        body.sidebar-minimized .sidebar .nav-link {
            justify-content: center;
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }

        body.sidebar-minimized .sidebar .nav-icon {
            margin-right: 0 !important;
        }

        body.sidebar-minimized .sidebar .nav-group-toggle::after {
            display: none;
        }

        .sidebar-toggler {
            flex: 0 0 auto;
            position: static;
            width: 100%;
            min-height: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            color: rgba(255, 255, 255, 0.8);
            background: transparent;
        }

        .sidebar-toggler:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Badge Styling */
        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
        }
        
        /* Main Content */
        .wrapper {
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            z-index: 1020;
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background: transparent;
            padding: 0;
        }
        
        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            font-size: 0.875rem;
        }
        
        /* Loading Indicator */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar.sidebar {
                width: min(17.5rem, 86vw);
                transform: translateX(-100%);
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }

            body.sidebar-mobile-open #sidebar.sidebar {
                transform: translateX(0);
            }

            .wrapper,
            body.sidebar-minimized .wrapper {
                width: 100%;
                margin-left: 0;
            }

            body.sidebar-minimized #sidebar.sidebar {
                width: min(17.5rem, 86vw);
            }

            body.sidebar-minimized .sidebar-brand-full,
            body.sidebar-minimized .sidebar .nav-label,
            body.sidebar-minimized .sidebar .nav-title,
            body.sidebar-minimized .sidebar .badge {
                display: initial !important;
            }

            body.sidebar-minimized .sidebar-brand-full {
                display: block !important;
            }

            body.sidebar-minimized .sidebar-brand-narrow {
                display: none;
            }

            body.sidebar-minimized .sidebar .nav-group-items {
                display: block !important;
            }

            body.sidebar-minimized .sidebar .nav-link {
                justify-content: flex-start;
                padding: 0.75rem 1rem;
            }

            body.sidebar-minimized .sidebar .nav-icon {
                margin-right: 0.5rem !important;
            }

            body.sidebar-minimized .sidebar .nav-group-toggle::after {
                display: block;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                z-index: 1025;
                background: rgba(16, 24, 32, 0.48);
            }

            body.sidebar-mobile-open .sidebar-overlay {
                display: block;
            }

            body.sidebar-mobile-open {
                overflow: hidden;
            }
            
            .sidebar-brand-full {
                font-size: 1rem;
            }
            
            .header {
                position: sticky;
                top: 0;
            }
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .header, .footer, .breadcrumb {
                display: none !important;
            }
            
            .body {
                margin: 0;
                padding: 0;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <!-- Sidebar -->
    @php
        $sidebarGradient = match(Auth::user()->role ?? 'admin') {
            'pembeli' => 'linear-gradient(180deg, #2980b9 0%, #1a5276 100%)',
            'kurir' => 'linear-gradient(180deg, #e67e22 0%, #a04c00 100%)',
            default => 'linear-gradient(180deg, #2eb85c 0%, #1f8a47 100%)',
        };
    @endphp
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar" style="background: {{ $sidebarGradient }};">
        <div class="sidebar-brand">
            <div class="sidebar-brand-full px-3 text-white fw-bold">
                <i class="fas fa-fire me-2"></i> Rumah Gas & Galon
            </div>
            <div class="sidebar-brand-narrow text-white fw-bold">R&G</div>
        </div>

        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ Request::routeIs('dashboard') ? 'active' : '' }}"
                   href="{{ route('dashboard') }}">
                    <i class="nav-icon fas fa-tachometer-alt me-2"></i><span class="nav-label">Dashboard</span>
                </a>
            </li>

            @if(Auth::user()->role === 'admin')
            <li class="nav-title">Inventory</li>

            <!-- Barang -->
            <li class="nav-item">
                <a class="nav-link {{ Request::routeIs('barang.*') ? 'active' : '' }}"
                   href="{{ route('barang.index') }}">
                    <i class="nav-icon fas fa-box me-2"></i><span class="nav-label">Barang</span>
                </a>
            </li>

            <!-- Kelola Stok -->
            <li class="nav-group {{ Request::routeIs('stok.*') ? 'show' : '' }}">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-warehouse me-2"></i><span class="nav-label">Kelola Stok</span>
                    @php
                        $stokCount = \App\Models\StokMasuk::count() + \App\Models\StokKeluar::count();
                    @endphp
                    @if($stokCount > 0)
                        <span class="badge bg-info ms-auto">{{ $stokCount }}</span>
                    @endif
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('stok.masuk.*') ? 'active' : '' }}"
                           href="{{ route('stok.masuk.index') }}">
                            <i class="nav-icon fas fa-arrow-down me-2"></i><span class="nav-label">Stok Masuk</span>
                            @php
                                $stokMasukCount = \App\Models\StokMasuk::count();
                            @endphp
                            @if($stokMasukCount > 0)
                                <span class="badge bg-success ms-auto">{{ $stokMasukCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('stok.keluar.*') ? 'active' : '' }}"
                           href="{{ route('stok.keluar.index') }}">
                            <i class="nav-icon fas fa-arrow-up me-2"></i><span class="nav-label">Stok Keluar</span>
                            @php
                                $stokKeluarCount = \App\Models\StokKeluar::count();
                            @endphp
                            @if($stokKeluarCount > 0)
                                <span class="badge bg-danger ms-auto">{{ $stokKeluarCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <div class="nav-link">
                            <i class="nav-icon fas fa-gas-pump me-2"></i><span class="nav-label">Stok Gas</span>
                            @php
                                $stokGas = \App\Models\Barang::where('jenis', 'gas')->sum('stok');
                            @endphp
                            <span class="badge {{ $stokGas < 10 ? 'bg-danger' : 'bg-warning' }} ms-auto">
                                {{ $stokGas }}
                            </span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="nav-link">
                            <i class="nav-icon fas fa-water me-2"></i><span class="nav-label">Stok Galon</span>
                            @php
                                $stokGalon = \App\Models\Barang::where('jenis', 'galon')->sum('stok');
                            @endphp
                            <span class="badge {{ $stokGalon < 10 ? 'bg-danger' : 'bg-primary' }} ms-auto">
                                {{ $stokGalon }}
                            </span>
                        </div>
                    </li>
                </ul>
            </li>
            @endif

            @if(Auth::user()->role === 'pembeli')
            <li class="nav-title">Belanja</li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-store me-2"></i><span class="nav-label">Katalog Produk</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-shopping-cart me-2"></i><span class="nav-label">Keranjang</span>
                    <span class="badge bg-secondary ms-auto">0</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-receipt me-2"></i><span class="nav-label">Pesanan Saya</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-history me-2"></i><span class="nav-label">Riwayat Transaksi</span>
                </a>
            </li>
            @endif

            @if(Auth::user()->role === 'kurir')
            <li class="nav-title">Pengantaran</li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-tasks me-2"></i><span class="nav-label">Tugas Antar</span>
                    <span class="badge bg-secondary ms-auto">0</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-truck me-2"></i><span class="nav-label">Sedang Diantar</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-map-marked-alt me-2"></i><span class="nav-label">Rute Pengantaran</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" onclick="return false;" style="cursor: default; opacity: 0.6;">
                    <i class="nav-icon fas fa-clipboard-check me-2"></i><span class="nav-label">Riwayat Antar</span>
                </a>
            </li>
            @endif

        </ul>

        <button class="sidebar-toggler" type="button" onclick="toggleSidebar()" aria-label="Minimize navigation" title="Minimize navigation">
            <i class="fas fa-angle-left"></i>
        </button>
    </div>
    <button class="sidebar-overlay" type="button" onclick="closeMobileSidebar()" aria-label="Close navigation"></button>

    <!-- Main Wrapper -->
    <div class="wrapper d-flex flex-column min-vh-100">
        <header class="header header-sticky">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button class="header-toggler me-3" type="button"
                            onclick="toggleSidebar()"
                            aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- User Dropdown -->
                    @auth
                    <div class="dropdown ms-auto">
                        <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" 
                                type="button" 
                                data-coreui-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            {{ Auth::user()->name }}
                            @php
                                $roleBadge = match(Auth::user()->role) {
                                    'admin' => 'bg-success',
                                    'pembeli' => 'bg-primary',
                                    'kurir' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $roleBadge }} ms-2" style="font-size: .65rem;">{{ ucfirst(Auth::user()->role) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endauth
                </div>
            </div>

            <!-- Breadcrumb -->
            <div class="container-fluid mt-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        @yield('breadcrumb-items', '<li class="breadcrumb-item active">' . (isset($breadcrumb) ? $breadcrumb : 'Dashboard') . '</li>')
                    </ol>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                <!-- Flash Messages -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-circle me-2"></i>Periksa kembali data:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer mt-auto py-3">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <strong>Gas & Galon</strong> &copy; {{ date('Y') }}
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            <i class="fas fa-code me-1"></i> Powered by Laravel v{{ Illuminate\Foundation\Application::VERSION }}
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- CoreUI JS -->
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/js/coreui.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        const sidebarMobileQuery = window.matchMedia('(max-width: 768px)');

        function toggleSidebar() {
            if (sidebarMobileQuery.matches) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-minimized');
                localStorage.setItem(
                    'sidebarState',
                    document.body.classList.contains('sidebar-minimized') ? 'minimized' : 'expanded'
                );
            }
            updateSidebarControls();
        }

        function closeMobileSidebar() {
            document.body.classList.remove('sidebar-mobile-open');
            updateSidebarControls();
        }

        function updateSidebarControls() {
            const isMobile = sidebarMobileQuery.matches;
            const isExpanded = isMobile
                ? document.body.classList.contains('sidebar-mobile-open')
                : !document.body.classList.contains('sidebar-minimized');
            const headerToggle = document.querySelector('.header-toggler');
            const sidebarToggle = document.querySelector('.sidebar-toggler');

            if (headerToggle) {
                headerToggle.setAttribute('aria-expanded', String(isExpanded));
                headerToggle.setAttribute('aria-label', isMobile
                    ? (isExpanded ? 'Tutup navigasi' : 'Buka navigasi')
                    : (isExpanded ? 'Perkecil navigasi' : 'Perbesar navigasi'));
            }

            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-label', isExpanded ? 'Perkecil navigasi' : 'Perbesar navigasi');
                sidebarToggle.setAttribute('title', isExpanded ? 'Perkecil navigasi' : 'Perbesar navigasi');
                sidebarToggle.innerHTML = isExpanded
                    ? '<i class="fas fa-angle-left"></i>'
                    : '<i class="fas fa-angle-right"></i>';
            }
        }
        
        // Loading overlay management
        window.addEventListener('beforeunload', function() {
            showLoading();
        });
        
        window.addEventListener('load', function() {
            hideLoading();
        });
        
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new coreui.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Initialize tooltips
            const tooltips = document.querySelectorAll('[data-coreui-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new coreui.Tooltip(tooltip);
            });
            
            // Handle logout confirmation
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    if (!confirm('Apakah Anda yakin ingin logout?')) {
                        e.preventDefault();
                    }
                });
            }
            
            const sidebar = document.getElementById('sidebar');
            const savedState = localStorage.getItem('sidebarState');
            if (sidebar && savedState === 'minimized' && !sidebarMobileQuery.matches) {
                document.body.classList.add('sidebar-minimized');
            }
            updateSidebarControls();

            if (sidebar) {
                sidebar.querySelectorAll('.nav-link').forEach(link => {
                    const label = link.querySelector('.nav-label');
                    if (label) {
                        const text = label.textContent.trim();
                        link.setAttribute('aria-label', text);
                        link.setAttribute('title', text);
                    }
                });

                sidebar.querySelectorAll('.nav-link:not(.nav-group-toggle)').forEach(link => {
                    link.addEventListener('click', () => {
                        if (sidebarMobileQuery.matches) {
                            closeMobileSidebar();
                        }
                    });
                });
            }

            sidebarMobileQuery.addEventListener('change', () => {
                document.body.classList.remove('sidebar-mobile-open');
                updateSidebarControls();
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + S for save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    const saveButton = document.querySelector('button[type="submit"]');
                    if (saveButton) {
                        saveButton.click();
                    }
                }
                
                // Escape to close modals
                if (e.key === 'Escape') {
                    closeMobileSidebar();
                    const modals = document.querySelectorAll('.modal.show');
                    modals.forEach(modal => {
                        const modalInstance = coreui.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    });
                }
            });
        });
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const timing = performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
            
            // Log slow assets
            performance.getEntriesByType("resource").forEach(function(resource) {
                if (resource.duration > 1000) {
                    console.warn(`Slow resource: ${resource.name} took ${resource.duration}ms`);
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>