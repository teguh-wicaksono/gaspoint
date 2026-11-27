@extends('layouts.app')

@section('title', 'Kasir')

@section('breadcrumb-items')
    <li class="breadcrumb-item active">Kasir</li>
@endsection

@push('styles')
<style>
    .produk { cursor: pointer; transition: transform .15s, box-shadow .15s; }
    .produk:hover:not(.habis) { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }
    .produk.habis { opacity: .5; cursor: not-allowed; }
    .keranjang-sticky { position: sticky; top: 5.5rem; }
    .qty-btn { width: 2rem; padding: 0; }
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card"><div class="card-body py-2">
            <div class="text-muted small">Pesanan hari ini</div>
            <div class="fs-4 fw-bold">{{ $pesananHariIni }}</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card"><div class="card-body py-2">
            <div class="text-muted small">Omzet hari ini</div>
            <div class="fs-4 fw-bold">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-12 col-md-6 d-flex align-items-center justify-content-md-end gap-2">
        <a href="{{ route('pesanan.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> Daftar Pesanan
        </a>
    </div>
</div>

<div id="antrean-online" data-url="{{ route('kasir.online') }}">@include('kasir._online')</div>

<div class="row g-3">
    {{-- Kiri: pilih barang --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                <strong class="me-auto"><i class="fas fa-box-open me-1"></i> Pilih Barang</strong>
                <div class="btn-group btn-group-sm" role="group" id="filter-jenis">
                    <button type="button" class="btn btn-outline-success active" data-jenis="">Semua</button>
                    <button type="button" class="btn btn-outline-success" data-jenis="gas">Gas</button>
                    <button type="button" class="btn btn-outline-success" data-jenis="galon">Galon</button>
                </div>
                <input type="search" id="cari" class="form-control form-control-sm" style="max-width:180px"
                       placeholder="Cari barang…" aria-label="Cari barang">
            </div>
            <div class="card-body">
                <div class="row g-3" id="grid-barang">
                    @forelse($barangs as $b)
                        <div class="col-6 col-md-4 item-barang" data-jenis="{{ $b->jenis }}"
                             data-nama="{{ strtolower($b->nama_barang) }}">
                            <div class="card produk h-100 {{ $b->stok < 1 ? 'habis' : '' }}"
                                 data-id="{{ $b->id_barang }}" role="button" tabindex="0"
                                 @if($b->stok < 1) aria-disabled="true" @endif>
                                <div class="card-body text-center p-3">
                                    <i class="fas {{ $b->jenis === 'gas' ? 'fa-fire text-danger' : 'fa-water text-primary' }} fa-2x mb-2"></i>
                                    <div class="fw-semibold">{{ $b->nama_barang }}</div>
                                    <div class="text-success fw-bold">Rp {{ number_format($b->harga, 0, ',', '.') }}</div>
                                    <span class="badge {{ $b->stok < 1 ? 'bg-danger' : ($b->stok < 10 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ $b->stok < 1 ? 'Stok habis' : 'Stok ' . $b->stok }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-4">Belum ada barang. Tambahkan dulu di menu Barang.</div>
                    @endforelse
                </div>
                <div id="kosong-cari" class="text-center text-muted py-4 d-none">Barang tidak ditemukan.</div>
            </div>
        </div>
    </div>

    {{-- Kanan: keranjang + data pembeli --}}
    <div class="col-lg-5">
        <form method="POST" action="{{ route('pesanan.store') }}" id="form-kasir" class="keranjang-sticky">
            @csrf
            <input type="hidden" name="dari" value="kasir">

            <div class="card">
                <div class="card-header"><strong><i class="fas fa-shopping-basket me-1"></i> Keranjang</strong></div>
                <div class="card-body">
                    <div id="keranjang" class="mb-2"></div>
                    <div id="keranjang-kosong" class="text-center text-muted py-3">Klik barang di kiri untuk menambahkan.</div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-3">
                        <span class="fw-semibold">Total</span>
                        <span class="fs-4 fw-bold text-success" id="total">Rp 0</span>
                    </div>

                    {{-- Kasir = beli di tempat, jadi selalu "langsung"; yang dipilih hanya cara bayar. --}}
                    <input type="hidden" name="metode" value="langsung">
                    <div class="btn-group w-100 mb-3" role="group" aria-label="Metode pembayaran">
                        <input type="radio" class="btn-check" name="pembayaran" id="bayar-cash" value="cash" @checked(old('pembayaran', 'cash') === 'cash')>
                        <label class="btn btn-outline-success" for="bayar-cash"><i class="fas fa-money-bill-wave me-1"></i> Cash</label>
                        <input type="radio" class="btn-check" name="pembayaran" id="bayar-qris" value="qris" @checked(old('pembayaran') === 'qris')>
                        <label class="btn btn-outline-primary" for="bayar-qris"><i class="fas fa-qrcode me-1"></i> QRIS</label>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1" for="nama_pembeli">Nama pembeli (opsional)</label>
                        <input type="text" id="nama_pembeli" name="nama_pembeli" value="{{ old('nama_pembeli') }}"
                               class="form-control" maxlength="100">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-1" for="no_hp">No. HP (opsional)</label>
                            <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                                   class="form-control" maxlength="20">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1" for="tanggal">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}"
                                   class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1" id="btn-simpan" disabled>
                            <i class="fas fa-check me-1"></i> Selesaikan Pembelian
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-kosongkan" title="Kosongkan keranjang">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mt-3">
            <div class="card-header"><strong>Pesanan Terbaru</strong></div>
            <ul class="list-group list-group-flush">
                @forelse($terbaru as $p)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('pesanan.show', $p) }}" class="text-decoration-none">
                            #{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }} · {{ $p->nama_pembeli }}
                        </a>
                        <span class="small">
                            Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                            <span class="badge {{ $p->metode === 'antar' ? 'bg-warning text-dark' : 'bg-success' }} ms-1">{{ $p->metode === 'antar' ? 'Antar' : 'Langsung' }}</span>
                            <span class="badge {{ $p->menunggu_persetujuan ? 'bg-danger' : 'bg-secondary' }} ms-1">{{ $p->menunggu_persetujuan ? 'menunggu persetujuan' : $p->status }}</span>
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center">Belum ada pesanan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $barangJson = $barangs->mapWithKeys(fn ($b) => [$b->id_barang => [
        'id' => $b->id_barang, 'nama' => $b->nama_barang, 'harga' => $b->harga, 'stok' => $b->stok,
    ]]);
    // Isi ulang keranjang kalau validasi server gagal.
    $oldCart = collect(old('items', []))->mapWithKeys(fn ($i) => [(int) ($i['id_barang'] ?? 0) => (int) ($i['jumlah'] ?? 0)]);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const barang = @json($barangJson);
    const cart = {};
    Object.entries(@json($oldCart)).forEach(([id, q]) => { if (barang[id] && q > 0) cart[id] = Math.min(q, barang[id].stok); });

    const $ = id => document.getElementById(id);
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function render() {
        const ids = Object.keys(cart);
        let total = 0, n = 0;
        $('keranjang').innerHTML = ids.map((id, i) => {
            const b = barang[id], q = cart[id], sub = b.harga * q;
            total += sub; n += q;
            return `
            <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                <div class="flex-grow-1">
                    <div class="fw-semibold">${esc(b.nama)}</div>
                    <div class="small text-muted">${rupiah(b.harga)} × ${q} = <strong>${rupiah(sub)}</strong></div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary qty-btn" data-aksi="kurang" data-id="${id}" aria-label="Kurangi">−</button>
                    <span class="btn btn-light disabled px-2">${q}</span>
                    <button type="button" class="btn btn-outline-secondary qty-btn" data-aksi="tambah" data-id="${id}" aria-label="Tambah" ${q >= b.stok ? 'disabled' : ''}>+</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-aksi="hapus" data-id="${id}" aria-label="Hapus">&times;</button>
                <input type="hidden" name="items[${i}][id_barang]" value="${id}">
                <input type="hidden" name="items[${i}][jumlah]" value="${q}">
            </div>`;
        }).join('');
        $('keranjang-kosong').classList.toggle('d-none', n > 0);
        $('total').textContent = rupiah(total);
        $('btn-simpan').disabled = n === 0;
    }

    function tambah(id) {
        const b = barang[id];
        if (!b || b.stok < 1) return;
        if ((cart[id] || 0) >= b.stok) { alert('Stok ' + b.nama + ' hanya ' + b.stok + ' unit.'); return; }
        cart[id] = (cart[id] || 0) + 1;
        render();
    }

    $('grid-barang').addEventListener('click', e => {
        const el = e.target.closest('.produk');
        if (el) tambah(el.dataset.id);
    });
    $('grid-barang').addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.closest('.produk')) {
            e.preventDefault();
            tambah(e.target.closest('.produk').dataset.id);
        }
    });

    $('keranjang').addEventListener('click', e => {
        const btn = e.target.closest('[data-aksi]');
        if (!btn) return;
        const id = btn.dataset.id;
        if (btn.dataset.aksi === 'tambah') tambah(id);
        if (btn.dataset.aksi === 'kurang') { if (--cart[id] < 1) delete cart[id]; render(); }
        if (btn.dataset.aksi === 'hapus') { delete cart[id]; render(); }
    });

    $('btn-kosongkan').addEventListener('click', () => {
        if (Object.keys(cart).length && confirm('Kosongkan keranjang?')) {
            Object.keys(cart).forEach(k => delete cart[k]);
            render();
        }
    });

    // Filter jenis + pencarian
    let jenis = '';
    function saring() {
        const q = $('cari').value.trim().toLowerCase();
        let tampil = 0;
        document.querySelectorAll('.item-barang').forEach(el => {
            const ok = (!jenis || el.dataset.jenis === jenis) && el.dataset.nama.includes(q);
            el.classList.toggle('d-none', !ok);
            if (ok) tampil++;
        });
        $('kosong-cari').classList.toggle('d-none', tampil > 0 || !document.querySelector('.item-barang'));
    }
    $('cari').addEventListener('input', saring);
    $('filter-jenis').addEventListener('click', e => {
        const b = e.target.closest('[data-jenis]');
        if (!b) return;
        jenis = b.dataset.jenis;
        document.querySelectorAll('#filter-jenis .btn').forEach(x => x.classList.toggle('active', x === b));
        saring();
    });

    // Keranjang kosong tidak boleh terkirim (mis. lewat Enter).
    $('form-kasir').addEventListener('submit', e => {
        if (!Object.keys(cart).length) e.preventDefault();
    });

    // Segarkan daftar pesanan online tiap 20 detik; keranjang di sebelah kanan tidak ikut berubah.
    const antrean = $('antrean-online');
    const idsDari = el => (el && el.firstElementChild && el.firstElementChild.dataset.ids) || '';
    setInterval(() => {
        if (document.hidden) return;
        fetch(antrean.dataset.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(r => (r.ok && !r.redirected ? r.text() : null))
            .then(html => {
                if (html === null) return;
                const t = document.createElement('template');
                t.innerHTML = html.trim();
                if (idsDari(t.content) !== idsDari(antrean)) antrean.innerHTML = html;
            })
            .catch(() => {});
    }, 20000);

    render();
});
</script>
@endpush
