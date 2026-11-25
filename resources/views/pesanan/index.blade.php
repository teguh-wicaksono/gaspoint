@extends('layouts.app')

@section('title', 'Pesanan Kasir')

@section('content')
@php
    $badge = fn ($s) => $s === 'selesai' ? 'bg-success' : ($s === 'diproses' ? 'bg-warning text-dark' : 'bg-secondary');
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Pesanan Kasir</strong>
        <a href="{{ route('pesanan.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i> Pesanan Baru
        </a>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('pesanan.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Cari nama pembeli, no. HP, atau nomor pesanan">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    @foreach (['pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Cari</button>
                <a href="{{ route('pesanan.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pembeli</th>
                        <th>No. HP</th>
                        <th>Tanggal</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $row)
                        <tr>
                            <td class="fw-semibold">#{{ str_pad($row->id_pesanan, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $row->nama_pembeli }}</td>
                            <td>{{ $row->no_hp }}</td>
                            <td>{{ $row->tanggal->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $row->detail_count }}</td>
                            <td class="text-end">Rp {{ number_format($row->total_harga, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $badge($row->status) }}">{{ ucfirst($row->status) }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('pesanan.show', $row) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-body-secondary py-4">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $pesanan->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
