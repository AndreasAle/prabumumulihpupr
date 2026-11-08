# Portal Digital Dinas PUPR Kota Prabumulih

Portal pemerintah berbasis Laravel 12 yang memadukan website publik premium, CMS berbahasa Indonesia, layanan publik, proyek infrastruktur, transparansi dokumen, pengaduan bertiket, pencarian lintas konten, dan asisten berbasis knowledge base.

## Arsitektur

- Laravel 12, Blade, JavaScript ringan, Tailwind CSS 4/Vite.
- MySQL untuk produksi; SQLite digunakan untuk development cepat.
- Model terpisah untuk berita, layanan, proyek, dokumen, agenda, statistik, pengaduan, pengaturan, dan knowledge base.
- `AIProviderInterface` dengan `MockAIProvider`; provider produksi dapat ditambahkan tanpa mengubah UI.
- Session auth, role pengguna, validasi server, CSRF, rate limit, dan soft delete pada konten penting.

## Instalasi

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Untuk memigrasikan konten dari website WordPress lama, jalankan perintah idempotent berikut setelah migrasi database:

```bash
php artisan legacy:import
```

Perintah tersebut mengimpor artikel, halaman, metadata, dan aset media ke penyimpanan lokal. Perintah aman dijalankan kembali untuk menyinkronkan perubahan tanpa membuat duplikasi.

Pada Windows, jika port 8000 sedang digunakan atau dibatasi, jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8311 --no-reload
```

Untuk produksi, atur koneksi MySQL dan kredensial layanan pada `.env`.

## Akun development

- Email: `admin@pupr.test`
- Kata sandi: `PrabuDemo!2026`

Akun hanya dibuat seeder. Ganti dengan akun unik dan aman sebelum deployment.

## Modul

Website publik mencakup beranda, profil, visi-misi, tugas-fungsi, layanan, proyek, transparansi, berita, agenda, galeri, pengaduan, kontak, pencarian, dan Asisten PUPR.

CMS mencakup dashboard, CRUD berita, layanan, proyek, dokumen, dan pengelolaan status pengaduan. Seluruh angka dan proyek dari seeder dilabeli demonstrasi—bukan fakta resmi.

## Verifikasi

```bash
php artisan test
npm run build
```

## Pengembangan berikutnya

Provider AI produksi, object storage, GIS/peta proyek, audit log rinci, menu dinamis, rich-text editor, analytics, SSO pemerintah, serta integrasi WhatsApp dapat ditambahkan sesuai persetujuan instansi.
