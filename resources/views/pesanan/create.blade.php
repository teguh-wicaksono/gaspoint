@extends('layouts.app')

@section('title', 'Pesanan Baru')

@section('content')
<div class="card">
    <div class="card-header"><strong>Pesanan Baru (Kasir)</strong></div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pesanan.store') }}" id="form-pesanan">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama pembeli</label>
                    <input type="text" name="nama_pembeli" value="{{ old('nama_pembeli') }}" class="form-control" maxlength="100" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" class="form-control" maxlength="20" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" rows="2" class="form-control" maxlength="500" required>{{ old('alamat') }}</textarea>
            </div>

            <h6 class="mt-4">Barang</h6>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="tabel-item">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Barang</th>
                            <th class="text-end">Harga</th>
                            <th style="width:120px">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="item-rows"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end" id="total">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" class="btn btn-outline-success btn-sm mb-4" id="tambah-item">
                <i class="fas fa-plus me-1"></i> Tambah barang
            </button>

            <div class="d-flex gap-2">
                <button class="btn btn-success">Simpan pesanan</button>
                <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@php
    $barangJson = $barangs->map(function ($b) {
        return [
            'id'    => $b->id_barang,
            'nama'  => $b->nama_barang,
            'jenis' => $b->jenis,
            'harga' => $b->harga,
            'stok'  => $b->stok,
        ];
    })->values();
    $oldItems = old('items', []);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const barang = @json($barangJson);
    const oldItems = @json($oldItems);

    const rows = document.getElementById('item-rows');
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    let index = 0;

    function optionsHtml(selected) {
        return '<option value="">Pilih barang</option>' + barang.map(b =>
            `<option value="${b.id}" ${b.stok < 1 ? 'disabled' : ''} ${String(selected) === String(b.id) ? 'selected' : ''}>` +
            `${b.nama} (stok ${b.stok})</option>`).join('');
    }

    function addRow(item) {
        const i = index++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="items[${i}][id_barang]" class="form-select" required>${optionsHtml(item?.id_barang)}</select></td>
            <td class="text-end harga">-</td>
            <td><input type="number" name="items[${i}][jumlah]" class="form-control" min="1" value="${item?.jumlah ?? 1}" required></td>
            <td class="text-end subtotal">Rp 0</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger hapus" title="Hapus baris">&times;</button></td>`;
        rows.appendChild(tr);
        hitung();
    }

    function hitung() {
        let total = 0;
        rows.querySelectorAll('tr').forEach(tr => {
            const b = barang.find(x => String(x.id) === tr.querySelector('select').value);
            const input = tr.querySelector('input');
            const jumlah = parseInt(input.value) || 0;
            if (b) input.max = b.stok;
            const sub = b ? b.harga * jumlah : 0;
            tr.querySelector('.harga').textContent = b ? rupiah(b.harga) : '-';
            tr.querySelector('.subtotal').textContent = rupiah(sub);
            total += sub;
        });
        document.getElementById('total').textContent = rupiah(total);
    }

    rows.addEventListener('input', hitung);
    rows.addEventListener('change', hitung);
    rows.addEventListener('click', e => {
        if (e.target.closest('.hapus')) {
            e.target.closest('tr').remove();
            if (!rows.children.length) addRow();
            hitung();
        }
    });
    document.getElementById('tambah-item').addEventListener('click', () => addRow());

    const initial = Object.values(oldItems);
    initial.length ? initial.forEach(addRow) : addRow();
});
</script>
@endpush
