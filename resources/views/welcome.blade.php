<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Rumah Gas dan Galon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #dc3545; /* Merah untuk gas */
            --secondary-color: #198754; /* Hijau untuk galon */
            --success-color: #1cc88a;
            --light-bg: #f8f9fc;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
            min-height: 100vh;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .logo-icon {
            font-size: 48px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .card-option {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 3px solid transparent;
        }
        
        .card-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
        }
        
        .icon-kasir {
            background: linear-gradient(135deg, var(--success-color) 0%, #17a673 100%);
        }
        
        .icon-pedagang {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .btn-kasir {
            background: var(--success-color);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-kasir:hover {
            background: #17a673;
            transform: scale(1.05);
            color: white;
        }
        
        .btn-pedagang {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-pedagang:hover {
            transform: scale(1.05);
            color: white;
        }
        
        .feature-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .feature-list li i {
            color: var(--success-color);
            margin-right: 10px;
        }
        
        .footer {
            background: #2e3a4e;
            color: white;
            padding: 30px 0;
            margin-top: 80px;
        }
        
        .feature-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .stats-box {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.5);
        }
        
        .stats-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .stats-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary-color);
            margin: 10px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .stats-label {
            font-size: 1rem;
            font-weight: 600;
            color: #2e3a4e;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-desc {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .stats-icon {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stats-icon.gas {
            color: var(--primary-color);
        }
        
        .stats-icon.galon {
            color: var(--secondary-color);
        }
        
        .stats-icon.time {
            color: #ffc107;
        }
        
        .stats-icon.delivery {
            color: #0dcaf0;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="logo-container">
                        <div class="logo">
                            <i class="bi bi-fire logo-icon"></i>
                        </div>
                        <h1 class="display-4 fw-bold mb-2">Rumah Gas dan Galon</h1>
                        <p class="lead mb-4">Portal pengelolaan operasional toko</p>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <span class="badge bg-light text-danger p-2">
                            <i class="bi bi-fire me-1"></i>Gas LPG
                        </span>
                        <span class="badge bg-light text-success p-2">
                            <i class="bi bi-droplet me-1"></i>Galon Air
                        </span>
                        <span class="badge bg-light text-primary p-2">
                            <i class="bi bi-clipboard-data me-1"></i>Manajemen Toko
                        </span>
                    </div>
                </div>
                <div class="col-lg-7 mt-4 mt-lg-0">
                    <div class="row g-4">
                        <!-- Tersedia Ukuran -->
                        <div class="col-6 col-md-3">
                            <div class="stats-box">
                                <i class="bi bi-fire stats-icon gas"></i>
                                <div class="stats-number"><i class="bi bi-box-seam"></i></div>
                                <div class="stats-label">INVENTARIS</div>
                                <p class="stats-desc">Kelola stok barang</p>
                            </div>
                        </div>
                        
                        <!-- Galon Standar -->
                        <div class="col-6 col-md-3">
                            <div class="stats-box">
                                <i class="bi bi-droplet stats-icon galon"></i>
                                <div class="stats-number"><i class="bi bi-receipt"></i></div>
                                <div class="stats-label">PESANAN</div>
                                <p class="stats-desc">Validasi pesanan mobile</p>
                            </div>
                        </div>
                        
                        <!-- Layanan -->
                        <div class="col-6 col-md-3">
                            <div class="stats-box">
                                <i class="bi bi-clock stats-icon time"></i>
                                <div class="stats-number"><i class="bi bi-arrow-left-right"></i></div>
                                <div class="stats-label">STOK</div>
                                <p class="stats-desc">Catat barang masuk &amp; keluar</p>
                            </div>
                        </div>
                        
                        <!-- Pengantaran -->
                        <div class="col-6 col-md-3">
                            <div class="stats-box">
                                <i class="bi bi-truck stats-icon delivery"></i>
                                <div class="stats-number"><i class="bi bi-graph-up"></i></div>
                                <div class="stats-label">DASHBOARD</div>
                                <p class="stats-desc">Ringkasan harian &amp; bulanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        @if(session('status'))
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @auth
            <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4" role="alert">
                <div>
                    <i class="bi bi-person-check-fill me-2"></i>
                    Anda masuk sebagai <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role_label }})
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i>Ke Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">Selamat Datang</h2>
            <p class="text-muted">Pilih akses masuk untuk mengelola operasional Rumah Gas dan Galon</p>
        </div>
        
        <div class="row justify-content-center g-4">
            <!-- Kasir -->
            <div class="col-md-6 col-lg-5">
                <div class="card card-option">
                    <div class="card-body text-center p-5">
                        <div class="card-icon icon-kasir">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h3 class="card-title fw-bold mb-3">Masuk sebagai Kasir</h3>
                        <p class="card-text text-muted mb-4">
                            Proses pesanan pelanggan dan cek ketersediaan stok langsung dari meja kasir.
                        </p>

                        <div class="mb-4 text-start">
                            <h6 class="fw-bold">Fitur Kasir:</h6>
                            <ul class="feature-list list-unstyled">
                                <li><i class="bi bi-check-circle"></i> Buat dan proses pesanan kasir</li>
                                <li><i class="bi bi-check-circle"></i> Validasi pesanan dari aplikasi mobile</li>
                                <li><i class="bi bi-check-circle"></i> Cek stok gas dan galon</li>
                                <li><i class="bi bi-check-circle"></i> Catat barang masuk dan keluar</li>
                            </ul>
                        </div>

                        <a href="{{ route('login', ['sebagai' => 'kasir']) }}" class="btn btn-kasir btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk sebagai Kasir
                        </a>

                        <p class="text-muted mt-3 small mb-0">
                            Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none">Daftar sebagai kasir</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pengelola -->
            <div class="col-md-6 col-lg-5">
                <div class="card card-option">
                    <div class="card-body text-center p-5">
                        <div class="card-icon icon-pedagang">
                            <i class="bi bi-shop"></i>
                        </div>
                        <h3 class="card-title fw-bold mb-3">Login Pengelola</h3>
                        <p class="card-text text-muted mb-4">
                            Kelola barang dan stok, serta pantau pendapatan dan pesanan toko dari satu dashboard.
                        </p>
                        
                        <div class="mb-4 text-start">
                            <h6 class="fw-bold">Fitur Pengelolaan:</h6>
                            <ul class="feature-list list-unstyled">
                                <li><i class="bi bi-check-circle"></i> Kelola barang masuk dan keluar</li>
                                <li><i class="bi bi-check-circle"></i> Kelola data gas dan galon</li>
                                <li><i class="bi bi-check-circle"></i> Pantau pesanan terbaru</li>
                                <li><i class="bi bi-check-circle"></i> Lihat grafik pendapatan 7 hari</li>
                            </ul>
                        </div>
                        
                        <a href="{{ route('login') }}" class="btn btn-pedagang btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
                        </a>
                        
                        <p class="text-muted mt-3 small mb-0">
                            <i class="bi bi-shield-lock me-1"></i>
                            Hanya pengelola toko yang terdaftar
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Management Features -->
        <div class="row mt-5 pt-5">
            <div class="col-12 text-center mb-4">
                <h3 class="fw-bold">Satu Portal untuk Operasional Toko</h3>
                <p class="text-muted">Semua alat pengelolaan tersedia setelah masuk</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center p-4">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="fw-bold">Kontrol Stok</h5>
                    <p>Catat barang masuk dan keluar serta pantau ketersediaan stok.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center p-4">
                    <div class="feature-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5 class="fw-bold">Pantau Pesanan</h5>
                    <p>Lihat pesanan terbaru dan nilainya langsung di dashboard.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center p-4">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold">Ringkasan Pendapatan</h5>
                    <p>Pantau pendapatan harian, bulanan, dan barang terlaris.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Rumah Gas dan Galon</h5>
                    <p>Sistem internal untuk mengelola barang, stok, dan pesanan toko.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white"><i class="bi bi-whatsapp fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-telephone me-2"></i> (021) 1234-5678</li>
                        <li><i class="bi bi-whatsapp me-2"></i> 0812-3456-7890</li>
                        <li><i class="bi bi-envelope me-2"></i> info@rumahgasgalon.com</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Jam Operasional</h5>
                    <ul class="list-unstyled">
                        <li>Senin - Minggu: 24 Jam</li>
                        <li>Hari Libur: Tetap Buka</li>
                        <li>Pengantaran: Setiap Hari</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; {{ date('Y') }} Rumah Gas dan Galon. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>