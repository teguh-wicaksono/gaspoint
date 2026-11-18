@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('breadcrumb', 'Dashboard')

@push('styles')
<style>
    .admin-hero { background: linear-gradient(120deg, #173f35, #27865b); color: #fff; border-radius: 1rem; }
    .admin-stat { border: 0; border-radius: 1rem; box-shadow: 0 .35rem 1.2rem rgba(24, 43, 36, .06); height: 100%; }
    .admin-stat .stat-icon { align-items: center; background: #e7f4ec; border-radius: .85rem; color: #218653; display: flex; font-size: 1.25rem; height: 2.8rem; justify-content: center; width: 2.8rem; }
    .admin-stat .stat-value { color: #1c3129; font-size: clamp(1.2rem, 2vw, 1.65rem); font-weight: 750; letter-spacing: -.03em; }
    .admin-section { border: 0; border-radius: 1rem; }
    .admin-section .card-header { background: #fff; border-bottom-color: #edf0ee; padding: 1.1rem 1.25rem; }
</style>
@endpush

@section('content')
<div class="admin-hero p-4 p-lg-5 mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="small text-white-50 text-uppercase fw-semibold mb-2">Panel pengelolaan</div>
            <h1 class="h2 fw-bold mb-2">Ringkasan operasional</h1>
            <p class="mb-0 text-white-50">Pantau pendapatan, pergerakan stok, dan pesanan dalam satu tempat.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('stok.masuk.create') }}" class="btn btn-light text-success fw-semibold">
                <i class="fas fa-plus me-2"></i>Catat Stok Masuk
            </a>
            <a href="{{ route('barang.index') }}" class="btn btn-outline-light">
                <i class="fas fa-box me-2"></i>Kelola Barang
            </a>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="h5 fw-bold mb-1">Bulan {{ now()->translatedFormat('F Y') }}</h2>
        <p class="small text-body-secondary mb-0">Pendapatan dihitung dari pesanan berstatus selesai.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card admin-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Pendapatan bulan ini</div><div class="stat-value">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div></div><span class="stat-icon"><i class="fas fa-wallet"></i></span></div>
            <div class="small text-body-secondary mt-3">{{ $pendapatanHariIni > 0 ? 'Hari ini: Rp ' . number_format($pendapatanHariIni, 0, ',', '.') : 'Belum ada pendapatan hari ini' }}</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card admin-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Pesanan bulan ini</div><div class="stat-value">{{ number_format($pesananBulanIni) }}</div></div><span class="stat-icon"><i class="fas fa-receipt"></i></span></div>
            <div class="small text-body-secondary mt-3">{{ $pesananHariIni }} pesanan tercatat hari ini</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card admin-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Barang masuk</div><div class="stat-value">{{ number_format($barangMasukBulanIni) }} <span class="fs-6 fw-normal">unit</span></div></div><span class="stat-icon"><i class="fas fa-arrow-down"></i></span></div>
            <div class="small text-body-secondary mt-3">Bulan ini · termasuk pembelian stok</div>
        </div></div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card admin-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">Barang keluar manual</div><div class="stat-value">{{ number_format($barangKeluarBulanIni) }} <span class="fs-6 fw-normal">unit</span></div></div><span class="stat-icon"><i class="fas fa-arrow-up"></i></span></div>
            <div class="small text-body-secondary mt-3">{{ number_format($barangKeluarManualBulanIni) }} manual · {{ number_format($barangTerjualBulanIni) }} melalui pesanan</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card admin-section shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h2 class="h6 fw-bold mb-1">Pendapatan 7 hari terakhir</h2><div class="small text-body-secondary">Pesanan selesai berdasarkan tanggal pesanan</div></div>
            </div>
            <div class="card-body"><div style="height:280px"><canvas id="revenueChart" aria-label="Grafik pendapatan tujuh hari terakhir"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card admin-section shadow-sm h-100">
            <div class="card-header"><h2 class="h6 fw-bold mb-1">Perlu perhatian</h2><div class="small text-body-secondary">Tindakan admin yang masih diperlukan</div></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-3 border-bottom">
                    <span class="text-body"><i class="fas fa-clock text-warning me-2"></i>Pesanan menunggu</span>                    <strong class="text-body">{{ $pesananMenunggu }}</strong>
                </div>
                <div class="py-3">
                    <div class="small text-body-secondary mb-2">Persediaan tersedia</div>
                    <div class="d-flex justify-content-between"><span>Gas</span><strong>{{ number_format($stokGas) }} unit</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Galon</span><strong>{{ number_format($stokGalon) }} unit</strong></div>
                </div>
                <a href="{{ route('stok.masuk.create') }}" class="btn btn-success w-100 mt-2"><i class="fas fa-plus me-2"></i>Catat Stok Masuk</a>
            </div>
        </div>
    </div>
</div>

<div class="card admin-section shadow-sm mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="h6 fw-bold mb-1">Pesanan terbaru</h2><div class="small text-body-secondary">Lima pesanan terakhir yang masuk</div></div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Pesanan</th><th>Pembeli</th><th>Tanggal</th><th>Status</th><th class="text-end pe-4">Nilai</th></tr></thead>
            <tbody>
                @forelse($pesananTerbaru as $order)
                    <tr>
                        <td class="ps-4"><span class="fw-semibold">#{{ str_pad($order->id_pesanan, 4, '0', STR_PAD_LEFT) }}</span></td>
                        <td>{{ $order->nama_pembeli }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->tanggal)->format('d M Y') }}</td>
                        <td><span class="badge {{ $order->status === 'selesai' ? 'bg-success' : ($order->status === 'diproses' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ ucfirst($order->status) }}</span></td>
                        <td class="text-end pe-4">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('revenueChart');
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($chartData),
                borderColor: '#27865b',
                backgroundColor: 'rgba(39, 134, 91, .12)',
                fill: true,
                tension: .35
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: value => 'Rp ' + Number(value).toLocaleString('id-ID') } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
