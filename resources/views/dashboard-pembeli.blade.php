@extends('layouts.app')

@section('title', 'Dashboard Pembeli')
@section('breadcrumb', 'Dashboard Pembeli')

@push('styles')
<style>
    .pembeli-hero { background: linear-gradient(120deg, #1a3a5c, #2980b9); color: #fff; border-radius: 1rem; }
    .pembeli-stat { border: 0; border-radius: 1rem; box-shadow: 0 .35rem 1.2rem rgba(24, 36, 43, .06); height: 100%; }
    .pembeli-stat .stat-icon { align-items: center; background: #e3f0ff; border-radius: .85rem; color: #2980b9; display: flex; font-size: 1.25rem; height: 2.8rem; justify-content: center; width: 2.8rem; }
    .pembeli-stat .stat-value { color: #1c2f3d; font-size: clamp(1.2rem, 2vw, 1.65rem); font-weight: 750; letter-spacing: -.03em; }
    .pembeli-section { border: 0; border-radius: 1rem; }
    .pembeli-section .card-header { background: #fff; border-bottom-color: #edf0ee; padding: 1.1rem 1.25rem; }
    .product-card { border: 0; border-radius: 1rem; box-shadow: 0 .2rem .8rem rgba(0,0,0,.06); transition: transform .2s, box-shadow .2s; }
    .product-card:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12); }
    .product-img-placeholder { background: linear-gradient(135deg, #e3f0ff, #cde4f7); height: 120px; border-radius: .85rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #2980b9; }
    .status-timeline { position: relative; padding-left: 2rem; }
    .status-timeline::before { content: ''; position: absolute; left: 0.6rem; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
    .status-dot { position: absolute; left: 0; width: 1.4rem; height: 1.4rem; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px #dee2e6; }
    .status-dot.active { background: #2980b9; box-shadow: 0 0 0 2px #2980b9; }
    .status-dot.done { background: #2eb85c; box-shadow: 0 0 0 2px #2eb85c; }
    .status-dot.pending { background: #dee2e6; }
</style>
@endpush

@section('content')
<div class="pembeli-hero p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="small text-white-50 text-uppercase fw-semibold mb-2">Panel Pembeli</div>
            <h1 class="h2 fw-bold mb-2">Selamat datang, {{ Auth::user()->name }}!</h1>
            <p class="mb-0 text-white-50">Pesan gas dan galon dengan mudah dan cepat.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-light text-primary fw-semibold" disabled>
                <i class="fas fa-shopping-cart me-2"></i>Buat Pesanan
            </button>
            <button class="btn btn-outline-light" disabled>
                <i class="fas fa-history me-2"></i>Riwayat Pesanan
            </button>
        </div>
    </div>
</div>

{{-- Statistik Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card pembeli-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Total Pesanan</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-shopping-bag"></i></span></div>
            <div class="small text-body-secondary mt-3">Belum ada pesanan</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card pembeli-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Pesanan Aktif</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-clock"></i></span></div>
            <div class="small text-body-secondary mt-3">Tidak ada pesanan aktif</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card pembeli-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Pesanan Selesai</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-check-circle"></i></span></div>
            <div class="small text-body-secondary mt-3">Belum ada pesanan selesai</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card pembeli-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Total Belanja</div><div class="stat-value">Rp 0</div></div><span class="stat-icon"><i class="fas fa-wallet"></i></span></div>
            <div class="small text-body-secondary mt-3">Belum ada transaksi</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    {{-- Katalog Produk --}}
    <div class="col-xl-8">
        <div class="card pembeli-section shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h2 class="h6 fw-bold mb-1">Katalog Produk</h2><div class="small text-body-secondary">Produk gas dan galon tersedia</div></div>
                <button class="btn btn-sm btn-outline-primary" disabled><i class="fas fa-search me-1"></i>Cari</button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Produk Gas 3kg --}}
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <div class="card-body text-center">
                                <div class="product-img-placeholder mb-3">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Gas LPG 3 Kg</h6>
                                <p class="small text-body-secondary mb-2">Gas elpiji tabung 3 kg</p>
                                <div class="fw-bold text-primary mb-3">Rp 20.000</div>
                                <button class="btn btn-sm btn-primary w-100" disabled>
                                    <i class="fas fa-cart-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- Produk Gas 12kg --}}
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <div class="card-body text-center">
                                <div class="product-img-placeholder mb-3">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Gas LPG 12 Kg</h6>
                                <p class="small text-body-secondary mb-2">Gas elpiji tabung 12 kg</p>
                                <div class="fw-bold text-primary mb-3">Rp 150.000</div>
                                <button class="btn btn-sm btn-primary w-100" disabled>
                                    <i class="fas fa-cart-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- Produk Galon --}}
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <div class="card-body text-center">
                                <div class="product-img-placeholder mb-3" style="background: linear-gradient(135deg, #e3fff0, #c0e8d5);">
                                    <i class="fas fa-tint" style="color: #198754;"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Galon Air Mineral</h6>
                                <p class="small text-body-secondary mb-2">Galon air mineral 19 liter</p>
                                <div class="fw-bold text-primary mb-3">Rp 8.000</div>
                                <button class="btn btn-sm btn-primary w-100" disabled>
                                    <i class="fas fa-cart-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Pesanan Terakhir --}}
    <div class="col-xl-4">
        <div class="card pembeli-section shadow-sm h-100">
            <div class="card-header"><h2 class="h6 fw-bold mb-1">Status Pesanan Terakhir</h2><div class="small text-body-secondary">Lacak pesanan Anda</div></div>
            <div class="card-body">
                <div class="text-center text-body-secondary py-4">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                    <p class="mb-1 fw-semibold">Belum ada pesanan</p>
                    <p class="small mb-3">Pesanan Anda akan muncul di sini setelah Anda membuat pesanan.</p>
                    <button class="btn btn-primary btn-sm" disabled>
                        <i class="fas fa-shopping-cart me-1"></i>Buat Pesanan Pertama
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Pesanan --}}
<div class="card pembeli-section shadow-sm mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="h6 fw-bold mb-1">Riwayat Pesanan</h2><div class="small text-body-secondary">Daftar semua pesanan Anda</div></div>
        <button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-filter me-1"></i>Filter</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">No. Pesanan</th><th>Tanggal</th><th>Produk</th><th>Status</th><th class="text-end pe-4">Total</th></tr></thead>
            <tbody>
                <tr><td colspan="5" class="text-center text-body-secondary py-4">Belum ada riwayat pesanan.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
