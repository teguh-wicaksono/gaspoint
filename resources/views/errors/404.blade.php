<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan - Rumah Gas &amp; Galon</title>
    <style>
        body { align-items: center; background: #f4f6f9; color: #343a40; display: flex; font-family: Arial, sans-serif; justify-content: center; margin: 0; min-height: 100vh; text-align: center; }
        main { padding: 2rem; }
        h1 { color: #2eb85c; font-size: 5rem; margin: 0; }
        p { margin: 1rem 0 1.5rem; }
        a, button { background: #2eb85c; border: 0; border-radius: .25rem; color: white; cursor: pointer; display: inline-block; font-size: 1rem; padding: .75rem 1rem; text-decoration: none; }
        .actions { align-items: center; display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
        form { margin: 0; }
        button.logout { background: #dc3545; }
    </style>
</head>
<body>
    <main>
        <h1>404</h1>
        <p>Maaf, halaman yang Anda cari tidak ditemukan.</p>
        <div class="actions">
            <a href="{{ route('welcome') }}">Kembali ke halaman utama</a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">Logout</button>
                </form>
            @endauth
        </div>
    </main>
</body>
</html>
