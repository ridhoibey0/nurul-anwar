# ERD basis data PPDB RA Nurul Anwar

| Berkas | Untuk apa |
| --- | --- |
| `erd.drawio` | Dibuka dan disunting di [app.diagrams.net](https://app.diagrams.net) |
| `erd.png` | Ditempel ke laporan. Skala 2× agar tajam saat dicetak |
| `buat-erd.py` | Pembuat kedua berkas di atas |

Keduanya dihasilkan dari satu sumber yang sama, jadi gambar di laporan tidak
akan pernah berbeda dari berkas yang bisa disunting.

## Isi diagram

Sepuluh tabel dengan tujuh relasi.

| Relasi | Arti |
| --- | --- |
| `pendaftaran_siswa.jenjang_id` → `jenjang` | Satu jenjang menampung banyak pendaftar |
| `pendaftaran_siswa.status_id` → `status_pendaftaran` | Satu status dipakai banyak pendaftaran |
| `pendaftaran_siswa.alamat_id` → `alamat` | Satu pendaftaran punya satu alamat |
| `pendaftaran_siswa.diverifikasi_oleh` → `users` | Satu panitia memverifikasi banyak pendaftaran |
| `pendaftaran_orang_tua.pendaftaran_id` → `pendaftaran_siswa` | Satu pendaftaran punya ayah, ibu, dan wali |
| `pendaftaran_berkas.pendaftaran_id` → `pendaftaran_siswa` | Satu pendaftaran punya beberapa berkas |
| `artikel.penulis_id` → `users` | Satu panitia menulis banyak artikel |

`galeri` dan `pengaturan` berdiri sendiri. Keduanya memang tabel mandiri:
galeri menyimpan foto kegiatan, dan pengaturan menyimpan pasangan kunci–nilai
untuk identitas sekolah serta pengaturan PPDB.

### Notasi kaki gagak

Garis relasi ditarik ke **baris foreign key-nya**, bukan ke tepi kotak tabel,
supaya terlihat kolom mana persisnya yang menghubungkan kedua tabel.

| Lambang | Di ujung | Arti |
| --- | --- | --- |
| lingkaran + kaki gagak | tabel anak | Nol atau banyak |
| dua garis | tabel induk | Tepat satu — foreign key-nya `NOT NULL` |
| lingkaran + satu garis | tabel induk | Nol atau satu — foreign key-nya `nullable` |

Dua yang terakhir dibedakan karena memang berbeda: pendaftaran **wajib** punya
jenjang dan status, tapi **boleh** belum punya verifikator (selama belum
diperiksa panitia) dan boleh tanpa alamat.

## Kenapa dipecah seperti ini

Sebelumnya `pendaftaran_siswa` punya 48 kolom dalam satu tabel. Dua bagiannya
berbentuk kelompok berulang — pola yang dipakai buku basis data sebagai contoh
pelanggaran bentuk normal pertama:

```
nama_ayah  nik_ayah  pendidikan_ayah  pekerjaan_ayah  penghasilan_ayah  telepon_ayah
nama_ibu   nik_ibu   pendidikan_ibu   pekerjaan_ibu   penghasilan_ibu   telepon_ibu
nama_wali            hubungan_wali                                      telepon_wali

berkas_kk  berkas_akta  berkas_foto
```

Tiga baris berbentuk sama dipaksa jadi 18 kolom, dan satu hal yang sama
dinomori jadi tiga kolom. Keduanya kini jadi tabel tersendiri dengan kolom
pembeda `peran` dan `jenis`, sehingga menambah peran atau syarat berkas baru
tidak lagi mengubah struktur tabel.

`jenjang` dan `status_pendaftaran` dipisah supaya pendaftaran menyimpan
rujukan, bukan menyalin ulang teks jenjang dan status di setiap barisnya.

Satu catatan jujur: `alamat` berhubungan satu-ke-satu dengan pendaftaran dan
tidak dipakai bersama antar pendaftar, jadi pemisahannya lebih ke kerapian
struktur daripada menghilangkan perulangan data.

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
dari database — jadi setelah menambah migrasi, perbarui daftar `TABEL` di
berkas itu agar diagramnya tetap sesuai.

Posisi tiap kotak diatur lewat `x` dan `y`, dan posisi ruas tegak tiap garis
relasi lewat kolom `lajur` di daftar `RELASI`. Keduanya ditulis tangan supaya
garis-garisnya tidak saling menimpa; kalau ada tabel baru, sesuaikan juga
angkanya.
