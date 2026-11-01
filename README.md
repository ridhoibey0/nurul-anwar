# Website RA Nurul Anwar (Laravel)

Website profil sekolah sekaligus sistem **PPDB (Penerimaan Peserta Didik Baru)**
untuk RA Nurul Anwar. Ditulis ulang dari versi Next.js ke Laravel 12 + Blade +
Tailwind CSS v4.

## Isi

- **Landing page** — hero, statistik, program per jenjang, keunggulan,
  keseharian anak, tim pendidik, testimoni, blog, lokasi.
- **PPDB** — halaman informasi, formulir online 4 langkah dengan unggah berkas,
  halaman konfirmasi bernomor, dan cek status mandiri.
- **Blog** — halaman daftar artikel `/blog` dengan pencarian dan paginasi,
  halaman artikel `/blog/{slug}`, serta tiga artikel terbaru di beranda.
- **Galeri foto** — foto kegiatan di bagian Keseharian halaman depan, diunggah
  dan diurutkan dari panel panitia.
- **Panel panitia** — login, ringkasan kuota per jenjang, tabel pendaftar dengan
  filter & pencarian, detail pendaftar, ubah status, unduh berkas, ekspor CSV,
  kelola galeri foto, tulis/sunting/hapus artikel blog, serta pengaturan tahun
  ajaran dan saklar buka/tutup pendaftaran.

## Kebutuhan

| Kebutuhan | Versi |
| --- | --- |
| PHP | 8.2 atau lebih baru (ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `gd`) |
| Composer | 2.x |
| MySQL / MariaDB | 8.0 / 10.6 ke atas |
| Node.js | 20 ke atas |

Di Windows, cara tercepat memenuhi semuanya adalah memasang
[Laravel Herd](https://herd.laravel.com/windows) (sudah termasuk PHP + Composer)
lalu [MySQL Community Server](https://dev.mysql.com/downloads/mysql/) atau XAMPP
untuk databasenya.

## Instalasi

```bash
cd laravel

# 1. Dependensi PHP
composer install

# 2. Konfigurasi
cp .env.example .env
php artisan key:generate

# 3. Buat database bernama "nurul_anwar" di MySQL, lalu sesuaikan
#    DB_USERNAME dan DB_PASSWORD di file .env.

# 4. Tabel + akun panitia + data contoh
php artisan migrate --seed

# 5. Dependensi & aset frontend
npm install
npm run build        # atau: npm run dev  (mode pengembangan)

# 6. Jalankan
php artisan serve
```

Buka <http://localhost:8000>.

### Akun panitia bawaan

| Email | Kata sandi |
| --- | --- |
| `admin@nurulanwar.website` | `password` |

**Ganti akun ini sebelum website dipakai sungguhan:**

```bash
php artisan admin:akun
```

Perintah itu menanyakan email, nama, dan sandi baru — ketikan sandinya
tersembunyi dan diminta dua kali. Untuk menambah akun panitia kedua:
`php artisan admin:akun --baru`.

> **Jangan** mengganti sandi lewat `User::where(...)->update(['password' => ...])`
> di tinker. `Builder::update()` memanggil query builder dasar dan melewati cast
> `'hashed'` di model, jadi sandinya tersimpan mentah: login gagal **dan** sandi
> polos tertinggal di database. Kalau tetap ingin lewat tinker, muat modelnya
> lalu simpan, supaya cast-nya berjalan:
>
> ```php
> $u = App\Models\User::where('email', 'admin@nurulanwar.website')->first();
> $u->password = 'sandi-baru-anda';
> $u->save();
> ```

## Pengaturan dari panel admin

Dua menu di panel panitia mengubah isi website tanpa menyentuh berkas apa pun
dan tanpa me-restart server.

### Identitas Sekolah

Nama, tagline, tahun berdiri, akreditasi, alamat, kota, jam operasional,
telepon, **nomor WhatsApp**, email, koordinat peta, dan tautan media sosial.

- Nomor WhatsApp boleh ditulis bebas (`0812-3456-7890`, `+62 812 3456 7890`,
  atau `6281234567890`) — semuanya dirapikan sendiri ke `62xxxxxxxxxx`.
- Isian yang **dikosongkan tetap kosong**, tidak kembali ke nilai `.env`.
  Mengosongkan akreditasi menghilangkan lencananya dari beranda; mengosongkan
  tautan media sosial menghilangkan ikonnya dari footer dan membuat akun itu
  tidak diklaim sebagai milik sekolah di data terstruktur Google.
- Alamat, kota, dan provinsi ikut masuk ke data terstruktur, jadi memperbarui
  alamat di sini sekaligus memperbaruinya untuk Google.

### Pengaturan PPDB

Menu **Pengaturan PPDB** mengatur:

- **Status pendaftaran** — saat ditutup, formulir online menolak pengiriman baru
  (termasuk POST langsung, bukan hanya menyembunyikan tombol). Halaman cek status
  tetap bisa dipakai orang tua. Tersedia juga saklar satu klik di halaman Ringkasan.
- **Tahun ajaran** — dipakai di seluruh website, menjadi bagian nomor pendaftaran
  (`PPDB-2728-0001`), dan menentukan data mana yang tampil di Ringkasan.
- **Tanggal dibuka & ditutup** — ditampilkan sebagai jadwal di halaman informasi PPDB.

### Bagaimana nilainya berlaku

Kedua menu menyimpan ke tabel `pengaturan` (kunci => nilai), lalu
`App\Models\Pengaturan::terapkanKeConfig()` menimpa `config/sekolah.php` dan
`config/ppdb.php` saat aplikasi boot. Seluruh kode lain tetap cukup memakai
`config('sekolah.*')` dan `config('ppdb.*')` seperti biasa.

Selama sebuah kunci belum pernah disimpan dari panel, nilai dari `.env` yang
dipakai. **Setelah sekali disimpan, `.env` tidak lagi berpengaruh untuk kunci
itu** — `.env` berfungsi sebagai nilai awal, bukan sumber kebenaran.

Penimpaan ini sengaja dilewati saat `php artisan config:cache` berjalan,
supaya nilai dari database tidak ikut terpanggang ke dalam berkas cache.

Mengganti tahun ajaran tidak menghapus data lama: pendaftar tahun sebelumnya
tetap tersimpan dan bisa dibuka lewat filter **Tahun ajaran** di menu Data
Pendaftar (filter ini juga ikut terpakai saat ekspor CSV).

## Galeri foto

Menu **Galeri Foto** di panel panitia mengisi bagian *Keseharian* di halaman
depan. Selama belum ada foto, bagian itu tidak ditampilkan.

- **Unggah beberapa sekaligus.** Pilih banyak berkas dalam satu kali unggah.
  Tiap foto diperkecil otomatis ke sisi terpanjang 1400 piksel dan disimpan
  ulang sebagai JPEG, jadi foto 5 MB dari ponsel tinggal ratusan kilobyte.
  Arah foto dari EXIF ikut diperbaiki supaya potret tidak tampil miring.
- **Judul foto** muncul sebagai keterangan di atas foto, dan dipakai sebagai
  `alt` untuk pembaca layar. Foto tanpa judul tetap tampil.
- **Urutan** diatur lewat tombol panah di pojok tiap foto. Urutan di panel
  sama persis dengan urutan di halaman depan.
- **Bentuk kolase** berulang tiap enam foto: slot ke-1 memanjang ke bawah
  (taruh foto potret di situ) dan slot ke-3 melebar dua kolom (taruh foto
  lanskap). Grid terisi rapat penuh bila jumlah fotonya kelipatan tiga —
  6, 9, 12, dan seterusnya. Jumlah lain menyisakan satu atau dua sel kosong
  di baris terakhir saja, bukan di tengah.
- **Sembunyikan** dengan melepas centang *Tampilkan di halaman depan*. Lebih
  baik disembunyikan daripada dihapus bila cuma ingin diistirahatkan
  sementara — menghapus ikut membuang berkasnya dari server.

Foto disimpan di `public/images/galeri/` (bukan `storage/`), jadi
`php artisan storage:link` tetap tidak diperlukan. Pastikan folder itu bisa
ditulis oleh web server, dan ekstensi **GD** aktif di PHP — tanpa GD, foto
tidak bisa diperkecil dan unggahan akan ditolak dengan pesan yang jelas.

## Menulis blog

Menu **Blog** di panel panitia untuk menulis, menyunting, dan menghapus artikel.

- **Status** — *Draf* tersimpan tanpa tampil di website. *Terbit* langsung tampil.
  Mengisi tanggal terbit di masa depan membuat artikel **terjadwal**: tersimpan,
  tapi baru muncul sendiri setelah tanggalnya tiba. Draf dan artikel terjadwal
  tidak bisa dibuka lewat URL, jadi tautannya aman dibagikan ke sesama panitia.
- **Isi artikel** ditulis dengan format sederhana: baris kosong memisahkan
  paragraf, `## ` untuk subjudul, `**tebal**`, `- ` untuk daftar, dan
  `[teks](url)` untuk tautan. HTML mentah dibuang otomatis, jadi tidak ada
  jalan masuk untuk skrip berbahaya.
- **Gambar sampul** maksimal 2 MB (JPG/PNG/WEBP), paling pas 1200 × 750 piksel.
  Tanpa gambar, kartu artikel memakai blok warna berlogo sekolah.
- **Alamat artikel** (slug) dibuat dari judul. Setelah artikel terbit, slug-nya
  dibekukan meski judulnya disunting — supaya tautan yang sudah dibagikan ke
  orang tua tidak mati.

Untuk menyembunyikan artikel sementara, ubah statusnya ke draf; jangan dihapus.

Gambar sampul disimpan di `public/images/artikel/` (bukan `storage/`), jadi
`php artisan storage:link` tetap tidak diperlukan. Pastikan folder itu bisa
ditulis oleh web server.

## Yang masih perlu dipastikan ke sekolah

Angka, kutipan, dan klaim yang belum dipastikan sengaja tidak ditulis di
website ini. Yang masih berisi contoh dan perlu diganti:

| Isi | Tempat |
| --- | --- |
| WhatsApp, email, tautan Instagram/Facebook/YouTube | **Panel admin > Identitas Sekolah** |
| Tahun berdiri dan akreditasi (tampil di hero & statistik) | **Panel admin > Identitas Sekolah** |
| Judul tiap foto galeri (sekarang masih keterangan seadanya) | **Panel admin > Galeri Foto** |
| Kutipan orang tua | `config/konten.php` (`testimoni`, kini kosong) |
| Peran tiap guru selain kepala RA | `config/konten.php` (`guru.*.peran`) |
| Foto guru (sekarang masih inisial nama) | `config/konten.php` (`guru.*.gambar`) |

Alamat dan nomor telepon **(0265) 338279** sudah mengikuti papan nama di
gedung sekolah. Ilustrasi di bagian hero dan Cara Kami Mengajar masih gambar
bawaan desain — ganti dengan foto asli lewat `public/images/` bila ada.

Bagian Testimoni menyembunyikan diri selama `testimoni` kosong, bagian
Keseharian selama galeri kosong, dan foto guru diganti inisial nama selama
`gambar` masih `null`. Jadi website tetap rapi tanpa data karangan.

## Mengganti konten

Hampir seluruh teks dan data sekolah tidak perlu menyentuh file Blade:

| Yang ingin diganti | File |
| --- | --- |
| Nama sekolah, alamat, telepon, WhatsApp, media sosial, peta | **Panel admin > Identitas Sekolah** (nilai awal di `.env` bagian `SCHOOL_*`) |
| Status buka/tutup PPDB, tahun ajaran, jadwal | **Panel admin > Pengaturan PPDB** (nilai awal di `.env` bagian `PPDB_*`) |
| Jenjang, kuota, syarat berkas, alur pendaftaran | `config/ppdb.php` |
| Jenjang, keunggulan, keseharian, daftar guru, testimoni | `config/konten.php` |
| Foto kegiatan di beranda | **Panel admin > Galeri Foto** |
| Artikel blog | **Panel admin > Blog** |
| Warna, radius, bayangan, font | `resources/css/app.css` (blok `@theme`) |

Setelah mengubah `.env` di server produksi, jalankan `php artisan config:clear`.
Mengubah lewat panel admin tidak perlu perintah apa pun — langsung berlaku.

## Seeder

Tiap seeder berdiri sendiri supaya bisa dipanggil terpisah dengan `--class`:

| Seeder | Isi | Produksi? |
| --- | --- | --- |
| `AkunPanitiaSeeder` | Akun bawaan untuk masuk panel admin. Dilewati bila sudah ada akun. | Boleh, tapi `php artisan admin:akun` lebih baik |
| `GaleriSeeder` | Enam foto asli sekolah yang sudah ada di `public/images/galeri/` | **Ya**, ini memang untuk dipakai |
| `PendaftaranSeeder` | 12 pendaftar fiktif supaya tabel tidak kosong saat mengembangkan | Tidak |

`php artisan migrate --seed` menjalankan yang terdaftar di
`database/seeders/DatabaseSeeder.php`. `PendaftaranSeeder` sengaja
dinonaktifkan di sana — aktifkan sendiri bila sedang mengembangkan.

Di server produksi, panggil yang diperlukan saja:

```bash
php artisan db:seed --class=GaleriSeeder --force
php artisan admin:akun          # buat akun panitia dengan sandi asli
```

Untuk membuang pendaftar fiktif yang sempat masuk:

```bash
php artisan tinker
>>> App\Models\Pendaftaran::truncate();
```

Blog tidak diisi data contoh: artikel ditulis sendiri dari panel admin.

## Berkas pendaftar

Kartu Keluarga, akta, dan pas foto berisi data pribadi, jadi **tidak** disimpan
di folder `public/`. Berkas masuk ke `storage/app/private/ppdb/<tahun-ajaran>/`
dan hanya bisa diunduh lewat panel admin yang sudah login. Karena itu
`php artisan storage:link` tidak diperlukan.

Pastikan batas unggah PHP cukup (`php.ini`). Nilai ini juga menentukan berapa
foto galeri yang bisa diunggah sekaligus:

```ini
upload_max_filesize = 8M
post_max_size = 64M
```

## SEO

Yang sudah terpasang dan tidak perlu disentuh lagi:

- **Judul & deskripsi** berbeda di tiap halaman, `lang="id"`, satu `<h1>` per
  halaman, dan HTML semantik (`main`, `article`, `figure`, `nav`).
- **`sitemap.xml` dan `robots.txt`** dibuat lewat rute, bukan berkas statis,
  jadi artikel blog baru langsung masuk tanpa ada yang perlu disunting.
- **Alamat kanonik** di tiap halaman. `/blog?q=...` menunjuk balik ke `/blog`,
  sedangkan `/blog?page=2` berdiri sendiri karena isinya memang lain.
- **Pratinjau tautan** (Open Graph): artikel memakai gambar sampulnya sendiri,
  halaman lain memakai `public/images/og.jpg`. Ini yang tampil saat orang tua
  membagikan tautan lewat WhatsApp.
- **Data terstruktur** `EducationalOrganization` di beranda (alamat, telepon,
  koordinat) dan `BlogPosting` di tiap artikel.
- **Halaman yang tidak boleh terindeks**: panel admin dan halaman konfirmasi
  pendaftaran, yang memuat nomor pendaftaran anak.

Yang masih bergantung pada Anda:

| Hal | Keterangan |
| --- | --- |
| `APP_URL` | **Wajib** diisi alamat asli di produksi. Semua URL kanonik, sitemap, dan gambar pratinjau dibangun dari sini. Selama masih `localhost`, tidak ada yang benar. |
| Google Search Console | Daftarkan domainnya, lalu kirim `https://domainanda/sitemap.xml`. |
| Google Bisnisku | Untuk sekolah, ini lebih berpengaruh daripada apa pun di halaman. Pastikan nama, alamat, dan telepon sama persis dengan yang di website. |
| Tautan media sosial | Selama masih domain polos di `.env`, akun sekolah sengaja tidak diklaim di data terstruktur. |
| `public/images/og.jpg` | Ganti bila ada foto yang lebih mewakili. Ukuran 1200 × 630. |

## Pengujian

```bash
php artisan test
```

Pengujian memakai SQLite in-memory, jadi tidak menyentuh database MySQL Anda.

## Struktur singkat

```
app/Http/Controllers/     HomeController, PendaftaranController, Admin/*
app/Models/Pendaftaran.php    model + penomoran pendaftaran
app/Models/Pengaturan.php     identitas sekolah & PPDB, diubah dari panel admin
app/Models/Artikel.php        artikel blog + slug & status terbit
app/Models/Galeri.php         foto kegiatan di halaman depan
app/Support/Gambar.php        pengecil foto unggahan (pakai GD)
config/sekolah.php        identitas sekolah
config/ppdb.php           nilai bawaan PPDB
config/konten.php         isi landing page
resources/views/sections/ potongan landing page
resources/views/pendaftaran/  info, formulir, konfirmasi, cek status
resources/views/artikel/      daftar & detail blog publik
resources/views/admin/    panel panitia
```

## Deploy ke hosting cPanel

1. Unggah seluruh isi folder `laravel/` ke luar `public_html`, misal ke `~/app`.
2. Pindahkan isi `~/app/public` ke `public_html`.
3. Di `public_html/index.php`, ubah dua `require` agar menunjuk ke `~/app`.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, dan isi `APP_URL` di `.env`.
5. Jalankan `php artisan config:cache route:cache view:cache`.
