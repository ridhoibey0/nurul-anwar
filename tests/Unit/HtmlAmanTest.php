<?php

namespace Tests\Unit;

use App\Support\HtmlAman;
use PHPUnit\Framework\TestCase;

/**
 * Penyaring HTML dari editor artikel.
 *
 * Isi artikel dicetak ke halaman dengan {!! !!}, jadi inilah satu-satunya
 * yang berdiri antara panel admin dan skrip yang berjalan di peramban setiap
 * pengunjung website sekolah.
 */
class HtmlAmanTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Yang harus dibuang
    |--------------------------------------------------------------------------
    */

    public function test_script_dibuang_berikut_isinya(): void
    {
        $hasil = HtmlAman::saring('<p>Halo</p><script>alert("xss")</script>');

        $this->assertStringNotContainsString('script', $hasil);
        $this->assertStringNotContainsString('alert', $hasil);
        $this->assertStringContainsString('Halo', $hasil);
    }

    public function test_penangan_kejadian_dibuang(): void
    {
        $hasil = HtmlAman::saring('<p onclick="curi()">Teks</p>');

        $this->assertStringNotContainsString('onclick', $hasil);
        $this->assertStringNotContainsString('curi', $hasil);
        $this->assertStringContainsString('Teks', $hasil);
    }

    public function test_onerror_pada_tag_tak_dikenal_ikut_hilang(): void
    {
        // <img> tidak ada di daftar izin, jadi tag-nya lepas seluruhnya
        // beserta atribut jahatnya.
        $hasil = HtmlAman::saring('<p>a<img src=x onerror="alert(1)">b</p>');

        $this->assertStringNotContainsString('onerror', $hasil);
        $this->assertStringNotContainsString('<img', $hasil);
    }

    public function test_tautan_javascript_ditolak(): void
    {
        $hasil = HtmlAman::saring('<p><a href="javascript:alert(1)">klik</a></p>');

        $this->assertStringNotContainsString('javascript', $hasil);
        // Tautannya hilang, tapi tulisannya tetap ada.
        $this->assertStringContainsString('klik', $hasil);
    }

    public function test_tautan_data_uri_ditolak(): void
    {
        $hasil = HtmlAman::saring('<p><a href="data:text/html,<script>alert(1)</script>">klik</a></p>');

        $this->assertStringNotContainsString('data:', $hasil);
    }

    public function test_style_dan_iframe_dibuang(): void
    {
        $hasil = HtmlAman::saring(
            '<style>body{display:none}</style><iframe src="https://jahat.test"></iframe><p>Isi</p>'
        );

        $this->assertStringNotContainsString('iframe', $hasil);
        $this->assertStringNotContainsString('display:none', $hasil);
        $this->assertStringContainsString('Isi', $hasil);
    }

    public function test_atribut_style_dibuang(): void
    {
        $hasil = HtmlAman::saring('<p style="position:fixed;top:0">Teks</p>');

        $this->assertStringNotContainsString('style', $hasil);
        $this->assertStringContainsString('Teks', $hasil);
    }

    /*
    |--------------------------------------------------------------------------
    | Yang harus bertahan
    |--------------------------------------------------------------------------
    */

    public function test_format_dasar_dipertahankan(): void
    {
        $hasil = HtmlAman::saring(
            '<p>Ada <strong>tebal</strong> dan <em>miring</em>.</p><ul><li>Satu</li><li>Dua</li></ul>'
        );

        $this->assertStringContainsString('<strong>tebal</strong>', $hasil);
        $this->assertStringContainsString('<em>miring</em>', $hasil);
        $this->assertStringContainsString('<li>Satu</li>', $hasil);
    }

    public function test_tautan_biasa_dipertahankan_dan_dibuka_di_tab_baru(): void
    {
        $hasil = HtmlAman::saring('<p><a href="https://kemenag.go.id">Kemenag</a></p>');

        $this->assertStringContainsString('href="https://kemenag.go.id"', $hasil);
        $this->assertStringContainsString('target="_blank"', $hasil);
        $this->assertStringContainsString('rel="noopener noreferrer"', $hasil);
    }

    public function test_huruf_beraksen_tidak_berubah(): void
    {
        // DOMDocument menganggap masukannya ISO-8859-1 bila charset-nya tidak
        // disebut, dan hasilnya huruf beraksen jadi karakter aneh.
        $hasil = HtmlAman::saring('<p>Doa sebelum belajar — “bismillah”, dibaca bersama</p>');

        $this->assertStringContainsString('—', $hasil);
        $this->assertStringContainsString('“bismillah”', $hasil);
    }

    public function test_tag_tak_dikenal_dilepas_tanpa_menghapus_tulisannya(): void
    {
        $hasil = HtmlAman::saring('<p>Halo <span class="x">dunia</span> ini</p>');

        $this->assertStringNotContainsString('<span', $hasil);
        $this->assertStringContainsString('dunia', $hasil);
        $this->assertStringContainsString('Halo', $hasil);
        $this->assertStringContainsString('ini', $hasil);
    }

    /*
    |--------------------------------------------------------------------------
    | Penyesuaian keluaran Trix
    |--------------------------------------------------------------------------
    */

    public function test_div_diubah_jadi_paragraf(): void
    {
        // Trix memakai <div> untuk paragraf.
        $hasil = HtmlAman::saring('<div>Paragraf pertama</div><div>Kedua</div>');

        $this->assertStringContainsString('<p>Paragraf pertama</p>', $hasil);
        $this->assertStringNotContainsString('<div', $hasil);
    }

    public function test_h1_diturunkan_jadi_h2(): void
    {
        // Judul artikel sudah memakai <h1>; dua <h1> dalam satu halaman
        // membingungkan mesin pencari dan pembaca layar.
        $hasil = HtmlAman::saring('<h1>Subjudul</h1>');

        $this->assertStringContainsString('<h2>Subjudul</h2>', $hasil);
        $this->assertStringNotContainsString('<h1', $hasil);
    }

    public function test_b_dan_i_disamakan_dengan_strong_dan_em(): void
    {
        $hasil = HtmlAman::saring('<p><b>tebal</b> <i>miring</i></p>');

        $this->assertStringContainsString('<strong>tebal</strong>', $hasil);
        $this->assertStringContainsString('<em>miring</em>', $hasil);
    }

    /*
    |--------------------------------------------------------------------------
    | Bantuan
    |--------------------------------------------------------------------------
    */

    public function test_kosong_mengenali_editor_yang_tidak_diisi(): void
    {
        // Yang dikirim Trix saat kotaknya dikosongkan.
        $this->assertTrue(HtmlAman::kosong('<div><br></div>'));
        $this->assertTrue(HtmlAman::kosong(''));
        $this->assertTrue(HtmlAman::kosong(null));
        $this->assertTrue(HtmlAman::kosong('<p>   </p>'));

        $this->assertFalse(HtmlAman::kosong('<p>Ada isinya</p>'));
    }

    public function test_teks_mengembalikan_tulisan_tanpa_tag(): void
    {
        $this->assertSame(
            'Halo dunia',
            HtmlAman::teks('<p>Halo <strong>dunia</strong></p>'),
        );
    }

    public function test_masukan_kosong_tidak_menghasilkan_apa_apa(): void
    {
        $this->assertSame('', HtmlAman::saring(''));
        $this->assertSame('', HtmlAman::saring(null));
        $this->assertSame('', HtmlAman::saring('   '));
    }

    public function test_menyaring_dua_kali_hasilnya_sama(): void
    {
        // Isi lama di database ikut disaring ulang saat ditampilkan, jadi
        // penyaringan berulang tidak boleh merusak apa pun.
        $awal = HtmlAman::saring('<div>Halo <a href="https://contoh.test">tautan</a></div>');

        $this->assertSame($awal, HtmlAman::saring($awal));
    }
}
