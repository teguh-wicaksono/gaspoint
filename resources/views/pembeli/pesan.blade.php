<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Gas &amp; Galon - Rumah Gas dan Galon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%); min-height: 100vh; }
        .hero { background: linear-gradient(120deg, #1a3a5c, #2980b9); color: #fff; border-radius: 1rem; }
        .kartu { border: 0; border-radius: 1rem; box-shadow: 0 .35rem 1.2rem rgba(24, 36, 43, .08); }
        .qty { width: 5.5rem; text-align: center; }
        .btn-kirim { background: linear-gradient(135deg, #1a3a5c 0%, #2980b9 100%); border: 0; color: #fff; font-weight: 600; }
        .btn-kirim:hover { color: #fff; filter: brightness(1.1); }
    </style>
</head>
<body>
<div class="container py-4" style="max-width: 760px;">

    <a href="{{ route('welcome') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>

    <div class="hero p-4 mb-4">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-fire me-2"></i>Pesan Gas &amp; Galon</h1>
        <p class="mb-0 text-white-50">Isi formulir di bawah. Pesanan diperiksa kasir, lalu diantar kurir ke alamat Anda.</p>
    </div>

    @if(session('pesanan_baru'))
        @php
            $baru = session('pesanan_baru');
            $total = number_format($baru['total'], 0, ',', '.');
        @endphp
        <div class="card kartu border-success mb-4" role="status">
            <div class="card-body">
                <h2 class="h5 fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Pesanan #{{ $baru['kode'] }} diterima</h2>
                <p class="mb-2">Total <strong>Rp {{ $total }}</strong>. Kasir akan memeriksa pesanan Anda, lalu kurir mengantarnya. Simpan nomor pesanan ini.</p>
                @if($baru['pembayaran'] === 'cod')
                    <p class="mb-0 small">Pembayaran <strong>COD</strong>: siapkan uang tunai Rp {{ $total }} untuk dibayar ke kurir saat pesanan sampai.</p>
                @elseif($baru['pembayaran'] === 'transfer')
                    <p class="mb-0 small">Pembayaran <strong>Transfer</strong>:
                        @if(config('toko.rekening'))
                            transfer Rp {{ $total }} ke <strong>{{ config('toko.rekening') }}</strong>. Pesanan disetujui setelah pembayaran diterima kasir.
                        @else
                            kasir akan menghubungi Anda lewat nomor HP untuk info rekening.
                        @endif
                    </p>
                @else
                    <p class="mb-2 small">Pembayaran <strong>QRIS</strong>: bayar Rp {{ $total }}. Pesanan disetujui setelah pembayaran diterima kasir.
                        @unless(config('toko.qris_url')) Kasir akan menghubungi Anda lewat nomor HP. @endunless
                    </p>
                    @if(config('toko.qris_url'))
                        <img src="{{ config('toko.qris_url') }}" alt="Kode QRIS" class="img-fluid rounded border" style="max-width: 220px;">
                    @endif
                @endif
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pembeli.store') }}" id="form-pesan">
        @csrf

        {{-- 1. Barang --}}
        <div class="card kartu mb-3">
            <div class="card-header bg-white fw-bold border-0 pt-3">1. Pilih barang</div>
            <ul class="list-group list-group-flush">
                @forelse($barangs as $b)
                    <li class="list-group-item d-flex align-items-center gap-3 py-3">
                        <i class="bi {{ $b->jenis === 'gas' ? 'bi-fire text-danger' : 'bi-droplet-fill text-primary' }} fs-3"></i>
                        <div class="flex-grow-1">
                            <label class="fw-semibold" for="jumlah-{{ $b->id_barang }}">{{ $b->nama_barang }}</label>
                            <div class="small text-body-secondary">
                                Rp {{ number_format($b->harga, 0, ',', '.') }}
                                &middot; {{ $b->stok < 1 ? 'stok habis' : 'stok ' . $b->stok }}
                            </div>
                        </div>
                        <input type="number" class="form-control qty" id="jumlah-{{ $b->id_barang }}"
                               name="jumlah[{{ $b->id_barang }}]" data-harga="{{ $b->harga }}"
                               min="0" max="{{ $b->stok }}" step="1" inputmode="numeric"
                               value="{{ old('jumlah.' . $b->id_barang, 0) }}" @disabled($b->stok < 1)>
                    </li>
                @empty
                    <li class="list-group-item text-center text-body-secondary py-4">Belum ada barang yang dijual.</li>
                @endforelse
            </ul>
        </div>

        {{-- 2. Data pemesan --}}
        <div class="card kartu mb-3">
            <div class="card-header bg-white fw-bold border-0 pt-3">2. Data pengantaran</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama_pembeli">Nama</label>
                        <input type="text" class="form-control" id="nama_pembeli" name="nama_pembeli"
                               value="{{ old('nama_pembeli') }}" maxlength="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_hp">No. HP</label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp"
                               value="{{ old('no_hp') }}" maxlength="20" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alamat">Alamat lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" maxlength="500" required>{{ old('alamat') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Pembayaran --}}
        @php $bayar = old('pembayaran', 'cod'); @endphp
        <div class="card kartu mb-3">
            <div class="card-header bg-white fw-bold border-0 pt-3">3. Pembayaran</div>
            <div class="card-body">
                <div class="btn-group w-100 mb-3" role="group" aria-label="Metode pembayaran">
                    <input type="radio" class="btn-check" name="pembayaran" id="bayar-cod" value="cod" @checked($bayar === 'cod')>
                    <label class="btn btn-outline-primary" for="bayar-cod"><i class="bi bi-cash-coin me-1"></i>COD</label>
                    <input type="radio" class="btn-check" name="pembayaran" id="bayar-transfer" value="transfer" @checked($bayar === 'transfer')>
                    <label class="btn btn-outline-primary" for="bayar-transfer"><i class="bi bi-bank me-1"></i>Transfer</label>
                    <input type="radio" class="btn-check" name="pembayaran" id="bayar-qris" value="qris" @checked($bayar === 'qris')>
                    <label class="btn btn-outline-primary" for="bayar-qris"><i class="bi bi-qr-code me-1"></i>QRIS</label>
                </div>

                <div class="small text-body-secondary" id="hint-cod" @if($bayar !== 'cod') hidden @endif>
                    Bayar tunai ke kurir saat pesanan sampai.
                </div>
                <div class="small text-body-secondary" id="hint-transfer" @if($bayar !== 'transfer') hidden @endif>
                    @if(config('toko.rekening'))
                        Transfer ke <strong>{{ config('toko.rekening') }}</strong>. Pesanan disetujui setelah pembayaran diterima kasir.
                    @else
                        Setelah pesanan dikirim, kasir akan menghubungi Anda lewat HP untuk info rekening.
                    @endif
                </div>
                <div class="small text-body-secondary" id="hint-qris" @if($bayar !== 'qris') hidden @endif>
                    Scan kode QRIS setelah pesanan dikirim. Pesanan disetujui setelah pembayaran diterima kasir.
                </div>
            </div>
        </div>

        <div class="card kartu">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="small text-body-secondary">Total</div>
                    <div class="fs-3 fw-bold" id="total">Rp 0</div>
                </div>
                <button type="submit" class="btn btn-kirim btn-lg px-4" id="btn-kirim" disabled>
                    <i class="bi bi-send me-2"></i>Kirim Pesanan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtys = document.querySelectorAll('.qty');

    function hitung() {
        let total = 0, n = 0;
        qtys.forEach(el => {
            const q = Math.max(0, Math.min(parseInt(el.value, 10) || 0, parseInt(el.max, 10) || 0));
            total += q * Number(el.dataset.harga);
            n += q;
        });
        document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('btn-kirim').disabled = n === 0;
    }
    qtys.forEach(el => el.addEventListener('input', hitung));

    document.querySelectorAll('input[name=pembayaran]').forEach(r => r.addEventListener('change', () => {
        ['cod', 'transfer', 'qris'].forEach(k => {
            document.getElementById('hint-' + k).hidden = document.querySelector('input[name=pembayaran]:checked').value !== k;
        });
    }));

    hitung();
});
</script>
</body>
</html>
