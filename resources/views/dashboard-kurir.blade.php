@extends('layouts.app')

@section('title', 'Dashboard Kurir')
@section('breadcrumb', 'Dashboard Kurir')

@push('styles')
<style>
    .kurir-hero { background: linear-gradient(120deg, #4a2c0a, #d4830a); color: #fff; border-radius: 1rem; }
    .kurir-stat { border: 0; border-radius: 1rem; box-shadow: 0 .35rem 1.2rem rgba(43, 36, 24, .06); height: 100%; }
    .kurir-stat .stat-icon { align-items: center; background: #fff3e0; border-radius: .85rem; color: #e67e22; display: flex; font-size: 1.25rem; height: 2.8rem; justify-content: center; width: 2.8rem; }
    .kurir-stat .stat-value { color: #3d2c1c; font-size: clamp(1.2rem, 2vw, 1.65rem); font-weight: 750; letter-spacing: -.03em; }
    .kurir-section { border: 0; border-radius: 1rem; }
    .kurir-section .card-header { background: #fff; border-bottom-color: #edf0ee; padding: 1.1rem 1.25rem; }
    .delivery-card { border-left: 4px solid #e67e22; border-radius: .5rem; padding: 1rem; margin-bottom: .75rem; background: #fffdf8; transition: background .2s; }
    .delivery-card:hover { background: #fff8ed; }
    .delivery-card.urgent { border-left-color: #dc3545; }
    .map-placeholder { background: linear-gradient(135deg, #f0f4f8, #dfe6ed); border-radius: 1rem; height: 250px; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #6c757d; }
</style>
@endpush

@section('content')
<div class="kurir-hero p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="small text-white-50 text-uppercase fw-semibold mb-2">Panel Kurir</div>
            <h1 class="h2 fw-bold mb-2">Halo, {{ Auth::user()->name }}!</h1>
            <p class="mb-0 text-white-50">Kelola pengantaran pesanan gas dan galon.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-light text-warning fw-semibold" disabled>
                <i class="fas fa-route me-2"></i>Mulai Antar
            </button>
            <button class="btn btn-outline-light" disabled>
                <i class="fas fa-list-check me-2"></i>Daftar Tugas
            </button>
        </div>
    </div>
</div>

{{-- Statistik Kurir --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card kurir-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Pesanan Hari Ini</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-box"></i></span></div>
            <div class="small text-body-secondary mt-3">Belum ada pesanan masuk</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card kurir-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Sedang Diantar</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-truck"></i></span></div>
            <div class="small text-body-secondary mt-3">Tidak ada pengantaran aktif</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card kurir-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Selesai Hari Ini</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-check-double"></i></span></div>
            <div class="small text-body-secondary mt-3">Belum ada pengantaran selesai</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card kurir-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Total Pengantaran Bulan Ini</div><div class="stat-value">0</div></div><span class="stat-icon"><i class="fas fa-chart-bar"></i></span></div>
            <div class="small text-body-secondary mt-3">Bulan {{ now()->translatedFormat('F Y') }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    {{-- Daftar Pengantaran --}}
    <div class="col-xl-8">
        <div class="card kurir-section shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h2 class="h6 fw-bold mb-1">Daftar Pengantaran</h2><div class="small text-body-secondary">Pesanan yang perlu diantar hari ini</div></div>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Menunggu: 0</span>
                    <span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Diantar: 0</span>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center text-body-secondary py-5">
                    <i class="fas fa-truck-loading fa-3x mb-3 text-muted"></i>
                    <p class="mb-1 fw-semibold">Belum ada pengantaran</p>
                    <p class="small mb-0">Pesanan yang perlu diantar akan muncul di sini.</p>
                </div>

                {{-- Contoh tampilan kartu pengantaran (saat ada data) --}}
                <div class="d-none">
                    <div class="delivery-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-warning text-dark mb-2"><i class="fas fa-clock me-1"></i>Menunggu</span>
                                <h6 class="fw-bold mb-1">#0001 - Budi Santoso</h6>
                                <p class="small text-body-secondary mb-1"><i class="fas fa-map-marker-alt me-1"></i>Jl. Merdeka No. 10, RT 05/03</p>
                                <p class="small text-body-secondary mb-0"><i class="fas fa-box me-1"></i>Gas LPG 3kg x2, Galon x1</p>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">Rp 48.000</div>
                                <button class="btn btn-sm btn-primary mt-2" disabled><i class="fas fa-route me-1"></i>Antar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Peta & Info --}}
    <div class="col-xl-4">
        <div class="card kurir-section shadow-sm mb-3">
            <div class="card-header"><h2 class="h6 fw-bold mb-1">Area Pengantaran</h2><div class="small text-body-secondary">Peta rute pengantaran</div></div>
            <div class="card-body">
                <div class="map-placeholder">
                    <i class="fas fa-map-marked-alt fa-3x mb-2"></i>
                    <p class="mb-0 fw-semibold">Peta akan ditampilkan di sini</p>
                    <p class="small mb-0">Saat ada pesanan aktif</p>
                </div>
            </div>
        </div>

        <div class="card kurir-section shadow-sm">
            <div class="card-header"><h2 class="h6 fw-bold mb-1">Ringkasan Harian</h2><div class="small text-body-secondary">Statistik pengantaran hari ini</div></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-body"><i class="fas fa-box text-warning me-2"></i>Menunggu diambil</span>
                    <strong class="text-body">0</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-body"><i class="fas fa-truck text-primary me-2"></i>Sedang diantar</span>
                    <strong class="text-body">0</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-body"><i class="fas fa-check-circle text-success me-2"></i>Terkirim</span>
                    <strong class="text-body">0</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-body"><i class="fas fa-times-circle text-danger me-2"></i>Gagal/Batal</span>
                    <strong class="text-body">0</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Pengantaran --}}
<div class="card kurir-section shadow-sm mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="h6 fw-bold mb-1">Riwayat Pengantaran</h2><div class="small text-body-secondary">Lima pengantaran terakhir</div></div>
        <button class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-history me-1"></i>Lihat Semua</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">No. Pesanan</th><th>Pembeli</th><th>Alamat</th><th>Status</th><th class="text-end pe-4">Waktu</th></tr></thead>
            <tbody>
                <tr><td colspan="5" class="text-center text-body-secondary py-4">Belum ada riwayat pengantaran.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
