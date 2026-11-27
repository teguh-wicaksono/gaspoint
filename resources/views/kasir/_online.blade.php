@if($online->isNotEmpty())
<div class="card border-warning mb-3" data-ids="{{ $online->pluck('id_pesanan')->implode(',') }}">
    <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-bell me-1"></i> Pesanan online menunggu persetujuan</strong>
        <span class="badge bg-danger">{{ $online->count() }}</span>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($online as $p)
            <li class="list-group-item">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <a href="{{ route('pesanan.show', $p) }}" class="fw-semibold text-decoration-none">#{{ $p->kode }} · {{ $p->nama_pembeli }}</a>
                        <span class="badge bg-info text-dark ms-1">{{ $p->pembayaran_label }}</span>
                        <div class="small text-muted">
                            {{ $p->detail->map(fn ($d) => ($d->barang->nama_barang ?? 'Barang') . ' ×' . $d->jumlah)->implode(', ') }}
                        </div>
                        <div class="small"><i class="fas fa-phone me-1"></i>{{ $p->no_hp }} · <i class="fas fa-map-marker-alt me-1"></i>{{ $p->alamat }}</div>
                        <div class="small text-muted">
                            {{ $p->pembayaran === 'cod' ? 'COD: dibayar ke kurir saat diantar.' : 'Pastikan pembayaran sudah masuk sebelum menyetujui.' }}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold mb-2">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</div>
                        <div class="d-flex gap-2 justify-content-end">
                            <form method="POST" action="{{ route('pesanan.setujui', $p) }}">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('pesanan.destroy', $p) }}"
                                  onsubmit="return confirm('Tolak pesanan #{{ $p->kode }}? Stok akan dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Tolak</button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endif
