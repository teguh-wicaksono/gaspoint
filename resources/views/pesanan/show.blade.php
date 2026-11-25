@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
@php
    $kode = str_pad($pesanan->id_pesanan, 4, '0', STR_PAD_LEFT);
    $badge = $pesanan->status === 'selesai' ? 'bg-success' : ($pesanan->status === 'diproses' ? 'bg-warning text-dark' : 'bg-secondary');
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Pesanan #{{ $kode }}</strong>
        <span class="badge {{ $badge }}">{{ ucfirst($pesanan->status) }}</span>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Pembeli</dt><dd class="col-sm-8">{{ $pesanan->nama_pembeli }}</dd>
                    <dt class="col-sm-4">No. HP</dt><dd class="col-sm-8">{{ $pesanan->no_hp }}</dd>
                    <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ $pesanan->tanggal->format('d/m/Y') }}</dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">{{ $pesanan->alamat }}</dd>
                </dl>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Jenis</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanan->detail as $i => $d)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $d->barang->nama_barang }}</td>
                            <td>{{ ucfirst($d->barang->jenis) }}</td>
                            <td class="text-end">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $d->jumlah }}</td>
                            <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total</th>
                        <th class="text-end">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Kembali</a>

            @if ($nextStatus)
                <form method="POST" action="{{ route('pesanan.status', $pesanan) }}">
                    @csrf
                    <button class="btn btn-success">
                        {{ $nextStatus === 'diproses' ? 'Proses pesanan' : 'Tandai selesai' }}
                    </button>
                </form>
            @endif

            @if ($pesanan->status !== 'selesai')
                <form method="POST" action="{{ route('pesanan.destroy', $pesanan) }}"
                      onsubmit="return confirm('Batalkan pesanan #{{ $kode }}? Stok barang akan dikembalikan.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">Batalkan pesanan</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
