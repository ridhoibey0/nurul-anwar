# ERD basis data PPDB RA Nurul Anwar

| Berkas | Untuk apa |
| --- | --- |
| `erd.drawio` | Dibuka dan disunting di [app.diagrams.net](https://app.diagrams.net) |
| `erd.png` | Ditempel ke laporan. 2360 × 2132 piksel, skala 2× agar tajam saat dicetak |
| `buat-erd.py` | Pembuat kedua berkas di atas |

Keduanya dihasilkan dari satu sumber yang sama, jadi gambar di laporan tidak
akan pernah berbeda dari berkas yang bisa disunting.

## Isi diagram

Lima tabel dengan dua relasi, keduanya menuju `users`:

| Relasi | Arti |
| --- | --- |
| `artikel.penulis_id` → `users.id` | Satu akun panitia menulis banyak artikel |
| `pendaftaran_siswa.diverifikasi_oleh` → `users.id` | Satu akun panitia memverifikasi banyak pendaftaran |

`galeri` dan `pengaturan` berdiri sendiri tanpa relasi. Keduanya memang tabel
mandiri: galeri menyimpan foto kegiatan, dan pengaturan menyimpan pasangan
kunci–nilai untuk identitas sekolah serta pengaturan PPDB.

### Notasi kaki gagak

Kedua foreign key bersifat `nullable`, dan notasinya mengikuti itu:

- Ujung di `users` &mdash; **lingkaran + satu garis** (nol atau satu). Artikel
  boleh tidak punya penulis tercatat, dan pendaftaran yang belum diverifikasi
  belum punya verifikator.
- Ujung di tabel anak &mdash; **lingkaran + kaki gagak** (nol atau banyak).

Garis relasi sengaja ditarik ke **baris foreign key-nya**, bukan ke tepi kotak
tabel, supaya terlihat kolom mana persisnya yang menghubungkan kedua tabel.

## Tabel yang tidak digambar

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, dan
`password_reset_tokens` tidak dimasukkan. Ketujuhnya dibuat otomatis oleh
Laravel untuk keperluan internal framework (antrean, cache, sesi login) dan
bukan bagian dari rancangan basis data sistem PPDB.

Bila pembimbing meminta seluruh tabel ikut digambar, tambahkan saja ke daftar
`TABEL` di `buat-erd.py` lalu jalankan ulang.

## Membuat ulang

Perlu dilakukan setiap kali ada migrasi baru:

```bash
pip install pillow
cd docs/erd
python buat-erd.py
```

Skemanya ditulis ulang secara manual di `buat-erd.py`, tidak dibaca langsung
dari database &mdash; jadi setelah menambah migrasi, perbarui daftar `TABEL`
di berkas itu agar diagramnya tetap sesuai.
