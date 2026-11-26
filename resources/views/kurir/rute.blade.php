@extends('layouts.app')

@section('title', 'Rute Pengantaran')
@section('breadcrumb-items')
    <li class="breadcrumb-item active">Rute Pengantaran</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm" style="border-radius: 1rem;">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Rute Pengantaran</h2>
            <div class="small text-body-secondary">Pesanan yang sedang diantar, urut sesuai nomor pesanan</div>
        </div>
        @if($mapsUrl)
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn btn-warning text-white fw-semibold">
                <i class="fas fa-route me-2"></i>Buka Rute di Google Maps
            </a>
        @endif
    </div>
    <ul class="list-group list-group-flush">
        @forelse($stops as $i => $p)
            <li class="list-group-item d-flex align-items-start gap-3 py-3">
                <span class="badge rounded-pill bg-warning text-dark mt-1">{{ $i + 1 }}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">#{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }} - {{ $p->nama_pembeli }}</div>
                    <div class="small text-body-secondary"><i class="fas fa-map-marker-alt me-1"></i>{{ $p->alamat }}</div>
                    <div class="small"><i class="fas fa-phone me-1"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $p->no_hp) }}">{{ $p->no_hp }}</a></div>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($p->alamat) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Peta</a>
            </li>
        @empty
            <li class="list-group-item text-center text-body-secondary py-5">
                <i class="fas fa-map-marked-alt fa-3x mb-3 text-muted"></i>
                <p class="mb-1 fw-semibold">Belum ada pesanan yang sedang diantar</p>
                <p class="small mb-0">Tekan "Antar" pada pesanan di <a href="{{ route('dashboard') }}">dashboard</a> untuk menyusun rute.</p>
            </li>
        @endforelse
    </ul>
</div>
@endsection
