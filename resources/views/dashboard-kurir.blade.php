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
</style>
@endpush

@section('content')
<div class="kurir-hero p-4 p-lg-5 mb-4">
    <div class="small text-white-50 text-uppercase fw-semibold mb-2">Panel Kurir</div>
    <h1 class="h2 fw-bold mb-2">Halo, {{ Auth::user()->name }}!</h1>
    <p class="mb-0 text-white-50">Kelola pengantaran pesanan gas dan galon.</p>
</div>

{{-- Statistik Kurir --}}
@php
    $stats = [
        ['Menunggu Diantar', $menunggu, 'fa-box', 'Pesanan belum berangkat'],
        ['Sedang Diantar', $diantar, 'fa-truck', 'Pengantaran aktif'],
        ['Selesai Hari Ini', $selesaiHariIni, 'fa-check-double', 'Terkirim hari ini'],
        ['Selesai Bulan Ini', $selesaiBulanIni, 'fa-chart-bar', 'Bulan ' . now()->translatedFormat('F Y')],
    ];
@endphp
<div class="row g-3 mb-4">
    @foreach($stats as [$label, $nilai, $ikon, $ket])
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card kurir-stat"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><div class="small text-body-secondary mb-2">{{ $label }}</div><div class="stat-value">{{ $nilai }}</div></div><span class="stat-icon"><i class="fas {{ $ikon }}"></i></span></div>
            <div class="small text-body-secondary mt-3">{{ $ket }}</div>
        </div></div>
    </div>
    @endforeach
</div>

{{-- Daftar Pengantaran --}}
<div class="card kurir-section shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="h6 fw-bold mb-1">Daftar Pengantaran</h2><div class="small text-body-secondary">Pesanan yang belum selesai diantar</div></div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Menunggu: {{ $menunggu }}</span>
            <span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Diantar: {{ $diantar }}</span>
        </div>
    </div>
    <div class="card-body">
        @forelse($pesananAktif as $p)
            @php
                $diproses = $p->status === 'diproses';
                $items = $p->detail->map(fn ($d) => ($d->barang->nama_barang ?? 'Barang') . ' x' . $d->jumlah)->implode(', ');
            @endphp
            <div class="delivery-card">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <span class="badge {{ $diproses ? 'bg-primary' : 'bg-warning text-dark' }} mb-2">
                            <i class="fas {{ $diproses ? 'fa-truck' : 'fa-clock' }} me-1"></i>{{ $diproses ? 'Sedang diantar' : 'Menunggu' }}
                        </span>
                        <h6 class="fw-bold mb-1">
                            <a href="{{ route('pesanan.show', $p) }}" class="text-decoration-none text-body">#{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }} - {{ $p->nama_pembeli }}</a>
                        </h6>
                        <p class="small text-body-secondary mb-1">
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $p->alamat }}
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($p->alamat) }}" target="_blank" rel="noopener" class="ms-1">Buka peta</a>
                        </p>
                        <p class="small text-body-secondary mb-1">
                            <i class="fas fa-phone me-1"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $p->no_hp) }}">{{ $p->no_hp }}</a>
                        </p>
                        @if($items)
                            <p class="small text-body-secondary mb-0"><i class="fas fa-box me-1"></i>{{ $items }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</div>
                        <form method="POST" action="{{ route('pesanan.status', $p) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $diproses ? 'btn-success' : 'btn-primary' }}">
                                <i class="fas {{ $diproses ? 'fa-check' : 'fa-route' }} me-1"></i>{{ $diproses ? 'Selesai' : 'Antar' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-body-secondary py-5">
                <i class="fas fa-truck-loading fa-3x mb-3 text-muted"></i>
                <p class="mb-1 fw-semibold">Belum ada pengantaran</p>
                <p class="small mb-0">Pesanan yang perlu diantar akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Riwayat Pengantaran --}}
<div class="card kurir-section shadow-sm mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div><h2 class="h6 fw-bold mb-1">Riwayat Pengantaran</h2><div class="small text-body-secondary">Lima pengantaran terakhir</div></div>
        <a href="{{ route('pesanan.index', ['status' => 'selesai']) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-history me-1"></i>Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">No. Pesanan</th><th>Pembeli</th><th>Alamat</th><th>Status</th><th class="text-end pe-4">Waktu</th></tr></thead>
            <tbody>
                @forelse($riwayat as $r)
                    <tr>
                        <td class="ps-4">#{{ str_pad($r->id_pesanan, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $r->nama_pembeli }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($r->alamat, 40) }}</td>
                        <td><span class="badge bg-success">Terkirim</span></td>
                        <td class="text-end pe-4">{{ $r->updated_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-body-secondary py-4">Belum ada riwayat pengantaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
