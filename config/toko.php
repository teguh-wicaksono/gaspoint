<?php

// Info pembayaran yang ditampilkan ke pembeli (isi lewat .env; kosong = tidak ditampilkan).
return [
    'rekening' => env('TOKO_REKENING'),   // contoh: "BCA 1234567890 a.n. Rumah Gas"
    'qris_url' => env('TOKO_QRIS_URL'),   // contoh: "/images/qris.png" (taruh file di public/images)
];
