{{--
    Bukti pendaftaran dalam bentuk PDF, dirender oleh dompdf.

    Berkas ini TIDAK memakai Tailwind dan tidak boleh memakainya: dompdf cuma
    mengerti sebagian kecil CSS, tanpa flexbox maupun grid. Semua tata letak di
    sini pakai tabel dan blok sederhana, dan gayanya ditulis langsung di bawah.

    Font "DejaVu Sans" ikut dibundel dompdf dan mendukung Unicode, jadi nama
    dengan tanda baca atau huruf beraksen tidak berubah jadi kotak kosong.
--}}
@php
    /**
     * Baris "label: nilai" yang isinya kosong dibuang, bukan ditampilkan
     * sebagai tanda hubung. Bukti ini dipegang orang tua untuk memeriksa data
     * yang mereka kirim; baris kosong hanya jadi gangguan.
     *
     * @param  array<string, mixed>  $baris
     * @return array<string, string>
     */
    $isi = static function (array $baris): array {
        $hasil = [];

        foreach ($baris as $label => $nilai) {
            if (filled($nilai)) {
                $hasil[$label] = (string) $nilai;
            }
        }

        return $hasil;
    };

    $dataAnak = $isi([
        'Nama lengkap' => $pendaftaran->nama_lengkap,
        'Nama panggilan' => $pendaftaran->nama_panggilan,
        'Jenis kelamin' => $pendaftaran->jenis_kelamin_label,
        'Tempat, tanggal lahir' => $pendaftaran->tempat_lahir.', '.tanggal_id($pendaftaran->tanggal_lahir),
        'Usia saat mendaftar' => $pendaftaran->usiaPada($pendaftaran->created_at),
        'NIK' => $pendaftaran->nik,
        'Agama' => $pendaftaran->agama,
        'Anak ke' => $pendaftaran->anak_ke,
        'Jumlah saudara' => $pendaftaran->jumlah_saudara,
        'Asal sekolah / PAUD' => $pendaftaran->asal_sekolah,
        'Riwayat kesehatan' => $pendaftaran->riwayat_kesehatan,
    ]);

    $alamat = $isi([
        'Alamat' => $pendaftaran->alamat,
        'Kelurahan' => $pendaftaran->kelurahan,
        'Kecamatan' => $pendaftaran->kecamatan,
        'Kota / kabupaten' => $pendaftaran->kota,
        'Kode pos' => $pendaftaran->kode_pos,
    ]);

    $ayah = $isi([
        'Nama ayah' => $pendaftaran->nama_ayah,
        'NIK ayah' => $pendaftaran->nik_ayah,
        'Pendidikan' => $pendaftaran->pendidikan_ayah,
        'Pekerjaan' => $pendaftaran->pekerjaan_ayah,
        'Penghasilan' => $pendaftaran->penghasilan_ayah,
        'Telepon' => $pendaftaran->telepon_ayah,
    ]);

    $ibu = $isi([
        'Nama ibu' => $pendaftaran->nama_ibu,
        'NIK ibu' => $pendaftaran->nik_ibu,
        'Pendidikan' => $pendaftaran->pendidikan_ibu,
        'Pekerjaan' => $pendaftaran->pekerjaan_ibu,
        'Penghasilan' => $pendaftaran->penghasilan_ibu,
        'Telepon' => $pendaftaran->telepon_ibu,
    ]);

    $wali = $isi([
        'Nama wali' => $pendaftaran->nama_wali,
        'Hubungan dengan anak' => $pendaftaran->hubungan_wali,
        'Telepon wali' => $pendaftaran->telepon_wali,
    ]);

    $kontak = $isi([
        'Nomor WhatsApp' => $pendaftaran->whatsapp,
        'Email' => $pendaftaran->email,
    ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pendaftaran {{ $pendaftaran->nomor_pendaftaran }}</title>

    <style>
        @page { margin: 28px 34px; }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1b1b1b;
        }

        table { width: 100%; border-collapse: collapse; }

        /* Kop surat */
        .kop { border-bottom: 2px solid #1c6b49; padding-bottom: 10px; }
        .kop td { vertical-align: middle; }
        .kop-logo { width: 62px; }
        .kop-logo img { width: 54px; height: 54px; }
        .kop-nama { font-size: 15pt; font-weight: bold; color: #1c6b49; }
        .kop-rinci { font-size: 8pt; color: #555; margin-top: 2px; }

        /* Judul dokumen */
        .judul { text-align: center; margin: 18px 0 14px; }
        .judul-utama { font-size: 12pt; font-weight: bold; letter-spacing: 0.4px; }
        .judul-anak { font-size: 9pt; color: #555; margin-top: 3px; }

        /* Kotak nomor pendaftaran */
        .nomor { background-color: #f1f8f3; border: 1px solid #bfe3cd; padding: 10px 12px; }
        .nomor td { vertical-align: middle; }
        .nomor-label { font-size: 7.5pt; color: #555; text-transform: uppercase; letter-spacing: 0.6px; }
        .nomor-nilai { font-size: 15pt; font-weight: bold; color: #1c6b49; }
        .nomor-status { text-align: right; }

        /* Bagian data */
        .bagian { font-size: 9pt; font-weight: bold; text-transform: uppercase;
                  letter-spacing: 0.5px; background-color: #efefef;
                  padding: 4px 8px; margin: 14px 0 0; }

        .data td { padding: 3.5px 8px; border-bottom: 1px solid #eaeaea; vertical-align: top; }
        .data td.label { width: 34%; color: #555; }
        .data td.nilai { font-weight: bold; }

        /* Dua kolom berdampingan untuk data ayah & ibu. Labelnya dilebarkan
           karena ruangnya cuma separuh halaman. */
        .dua td { vertical-align: top; width: 50%; }
        .dua td.kiri { padding-right: 8px; }
        .dua td.kanan { padding-left: 8px; }
        .dua .data td.label { width: 45%; }

        .catatan { border: 1px solid #fad986; background-color: #fdf1d2;
                   padding: 8px 10px; margin-top: 14px; }
        .catatan.netral { border-color: #ddd; background-color: #fafafa; }
        .catatan-judul { font-size: 8pt; font-weight: bold; }

        .kaki { margin-top: 18px; border-top: 1px solid #ddd; padding-top: 8px;
                font-size: 7.5pt; color: #666; line-height: 1.5; }
        .kaki strong { color: #1b1b1b; }
    </style>
</head>
<body>

    {{-- Kop --}}
    <table class="kop">
        <tr>
            @if ($logo)
                <td class="kop-logo"><img src="{{ $logo }}" alt=""></td>
            @endif
            <td>
                <div class="kop-nama">{{ config('sekolah.nama') }}</div>
                <div class="kop-rinci">
                    {{ config('sekolah.alamat') }}<br>
                    @if (filled(config('sekolah.telepon')))
                        Telp. {{ config('sekolah.telepon') }}
                    @endif
                    @if (filled(config('sekolah.email')))
                        &middot; {{ config('sekolah.email') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Judul --}}
    <div class="judul">
        <div class="judul-utama">Bukti Pendaftaran Peserta Didik Baru</div>
        <div class="judul-anak">Tahun Ajaran {{ $pendaftaran->tahun_ajaran }}</div>
    </div>

    {{-- Nomor & status --}}
    <table class="nomor">
        <tr>
            <td>
                <div class="nomor-label">Nomor pendaftaran</div>
                <div class="nomor-nilai">{{ $pendaftaran->nomor_pendaftaran }}</div>
            </td>
            <td class="nomor-status">
                <div class="nomor-label">Status terakhir</div>
                <div style="font-weight: bold;">{{ $pendaftaran->status_label }}</div>
            </td>
        </tr>
    </table>

    {{-- A. Data anak --}}
    <p class="bagian">A. Data Calon Peserta Didik</p>

    <table class="data">
        <tr>
            <td class="label">Jenjang yang dipilih</td>
            <td class="nilai">{{ $pendaftaran->jenjang_label }}</td>
        </tr>
        @foreach ($dataAnak as $label => $nilai)
            <tr>
                <td class="label">{{ $label }}</td>
                <td class="nilai">{{ $nilai }}</td>
            </tr>
        @endforeach
    </table>

    {{-- B. Alamat --}}
    <p class="bagian">B. Alamat Tempat Tinggal</p>

    <table class="data">
        @foreach ($alamat as $label => $nilai)
            <tr>
                <td class="label">{{ $label }}</td>
                <td class="nilai">{{ $nilai }}</td>
            </tr>
        @endforeach
    </table>

    {{-- C. Orang tua / wali --}}
    <p class="bagian">C. Data Orang Tua / Wali</p>

    <table class="dua">
        <tr>
            <td class="kiri">
                <table class="data">
                    @foreach ($ayah as $label => $nilai)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="nilai">{{ $nilai }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td class="kanan">
                <table class="data">
                    @foreach ($ibu as $label => $nilai)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="nilai">{{ $nilai }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    @if ($wali)
        <table class="data" style="margin-top: 6px;">
            @foreach ($wali as $label => $nilai)
                <tr>
                    <td class="label">{{ $label }}</td>
                    <td class="nilai">{{ $nilai }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="data" style="margin-top: 6px;">
        @foreach ($kontak as $label => $nilai)
            <tr>
                <td class="label">{{ $label }}</td>
                <td class="nilai">{{ $nilai }}</td>
            </tr>
        @endforeach
    </table>

    {{-- D. Berkas --}}
    <p class="bagian">D. Berkas Yang Diterima Sistem</p>

    <table class="data">
        @foreach ($pendaftaran->daftarBerkas() as $berkas)
            <tr>
                <td class="label">{{ $berkas['label'] }}</td>
                <td class="nilai">{{ filled($berkas['path']) ? 'Sudah diunggah' : 'Belum ada' }}</td>
            </tr>
        @endforeach
    </table>

    @if (filled($pendaftaran->catatan_orang_tua))
        <div class="catatan netral">
            <div class="catatan-judul">Catatan dari orang tua</div>
            <div>{{ $pendaftaran->catatan_orang_tua }}</div>
        </div>
    @endif

    @if (filled($pendaftaran->catatan_admin))
        <div class="catatan">
            <div class="catatan-judul">Catatan dari panitia</div>
            <div>{{ $pendaftaran->catatan_admin }}</div>
        </div>
    @endif

    {{-- Kaki --}}
    <div class="kaki">
        <p style="margin: 0 0 5px;">
            Pendaftaran dikirim pada <strong>{{ tanggal_id($pendaftaran->created_at, true) }}</strong>.
            Berkas ini diunduh pada {{ tanggal_id($diunduhPada, true) }}.
        </p>

        <p style="margin: 0 0 5px;">
            Dokumen ini dihasilkan otomatis oleh sistem PPDB {{ config('sekolah.nama') }}
            dan sah tanpa tanda tangan. Keasliannya dapat diperiksa lewat menu
            Cek Status di website sekolah dengan nomor pendaftaran dan tanggal lahir anak.
        </p>

        {{-- Kalimat ini penting: bukti pendaftaran bukan surat penerimaan, dan
             jangan sampai dibaca sebagai pengumuman diterima. --}}
        <p style="margin: 0;">
            <strong>Bukti ini menyatakan bahwa data pendaftaran telah diterima sistem,
            bukan pernyataan bahwa calon peserta didik telah diterima.</strong>
            Hasil seleksi disampaikan panitia secara terpisah.
        </p>
    </div>

</body>
</html>
