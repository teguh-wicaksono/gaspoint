# gaspoint
Ayo digarap, ojok mangkrak

# Rumah Gas dan Galon

Aplikasi pengelolaan penjualan gas dan galon (Laravel 13, PHP 8.3+, SQLite).

## Kebutuhan

- PHP 8.3 atau lebih baru, Composer, Git, VS Code
- Extension PHP aktif: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo` (cek dengan `php -m`)
- Windows: Laragon atau Laravel Herd sudah berisi PHP dan Composer

## Menjalankan proyek

PowerShell (Windows):

```powershell
git clone https://github.com/teguh-wicaksono/gaspoint.git
cd gaspoint
git checkout develop
composer install
copy .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
php artisan serve
```

macOS / Linux / Git Bash:

```bash
git clone https://github.com/teguh-wicaksono/gaspoint.git
cd gaspoint
git checkout develop
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Buka http://127.0.0.1:8000. File `.env` dan `database.sqlite` tidak ada di GitHub,
jadi setiap orang membuatnya sendiri.

Akun dari seeder: `admin@rumahgas.test` / `password123`

## Alur Git

Tidak ada yang menulis langsung ke `main` atau `develop`. Satu fitur satu branch:

```bash
git checkout develop
git pull origin develop
git checkout -b feature/nama-modul
# kerjakan, lalu:
git add .
git commit -m "feat(modul): deskripsi singkat"
git push -u origin feature/nama-modul
```

Buka pull request ke `develop` dan minta review. Awalan commit: `feat`, `fix`, `test`, `docs`, `chore`.
Periksa `git status` sebelum commit agar `.env`, `vendor`, dan `database.sqlite` tidak ikut.

## Menyamakan dengan pekerjaan teman

```bash
git checkout develop
git pull origin develop
git checkout feature/nama-modul
git merge develop
composer install
php artisan migrate
```

## Pengujian

```bash
php artisan test
```