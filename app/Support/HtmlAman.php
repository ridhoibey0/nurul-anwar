<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Penyaring HTML dari editor WYSIWYG.
 *
 * Isi artikel ditulis panitia lewat editor dan disimpan sebagai HTML, lalu
 * dicetak ke halaman dengan {!! !!} -- tanpa penyaringan, siapa pun yang bisa
 * masuk panel admin dapat menanam <script> yang berjalan di setiap pengunjung
 * website sekolah.
 *
 * Yang tidak ada di daftar izin dibuang isinya saja, tidak seluruh teksnya:
 * tulisan panitia tidak boleh hilang hanya karena satu tag yang tidak dikenal.
 *
 * Penyaringan ini dijalankan saat menyimpan (lihat App\Models\Artikel), jadi
 * yang tersimpan di database memang sudah bersih -- bukan dibersihkan ulang
 * tiap kali halaman dibuka.
 */
final class HtmlAman
{
    /**
     * Tag yang boleh lewat, beserta atribut yang boleh ikut.
     *
     * Semua atribut lain dibuang, termasuk seluruh penangan kejadian
     * (onclick, onerror, dan kerabatnya) dan style.
     *
     * @var array<string, array<int, string>>
     */
    private const IZIN = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'em' => [],
        'del' => [],
        'h2' => [],
        'h3' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'pre' => [],
        'code' => [],
        'a' => ['href'],
    ];

    /**
     * Tag yang diganti tag lain sebelum disaring.
     *
     * Trix memakai <div> untuk paragraf dan <h1> untuk subjudul. Keduanya
     * tidak pas di halaman artikel: judul artikel sudah memakai <h1>, dan dua
     * <h1> dalam satu halaman membingungkan mesin pencari maupun pembaca layar.
     *
     * @var array<string, string>
     */
    private const GANTI = [
        'div' => 'p',
        'h1' => 'h2',
        'b' => 'strong',
        'i' => 'em',
        'strike' => 'del',
        's' => 'del',
    ];

    /**
     * Tag yang dibuang berikut seluruh isinya.
     *
     * Beda dari tag tak dikenal yang hanya dilepas pembungkusnya: isi
     * <script> bukan tulisan artikel, dan menyisakannya sebagai teks hanya
     * memunculkan kode mentah di tengah halaman.
     *
     * @var array<int, string>
     */
    private const BUANG_TOTAL = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math'];

    /** Skema tautan yang boleh. javascript: dan data: sengaja tidak ada. */
    private const SKEMA = ['http', 'https', 'mailto', 'tel'];

    public static function saring(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dokumen = new DOMDocument();

        // Dibungkus dokumen lengkap dengan meta charset: tanpa itu DOMDocument
        // menganggap masukannya ISO-8859-1 dan huruf beraksen berubah jadi
        // karakter aneh.
        $berhasil = @$dokumen->loadHTML(
            '<!DOCTYPE html><html><head>'
            .'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            .'</head><body>'.$html.'</body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        if (! $berhasil) {
            return '';
        }

        $akar = $dokumen->getElementsByTagName('body')->item(0);

        if (! $akar) {
            return '';
        }

        static::bersihkan($akar);

        $hasil = '';

        foreach (iterator_to_array($akar->childNodes) as $anak) {
            $hasil .= $dokumen->saveHTML($anak);
        }

        return trim($hasil);
    }

    /** Teks polos tanpa tag, untuk menghitung panjang dan waktu baca. */
    public static function teks(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** True bila isinya tidak ada apa-apanya selain tag kosong. */
    public static function kosong(?string $html): bool
    {
        return static::teks($html) === '';
    }

    private static function bersihkan(DOMNode $induk): void
    {
        // Disalin dulu: membuang simpul sambil menelusuri childNodes akan
        // melewatkan simpul berikutnya karena daftarnya ikut bergeser.
        foreach (iterator_to_array($induk->childNodes) as $anak) {
            if (! $anak instanceof DOMElement) {
                continue;
            }

            if (in_array(strtolower($anak->nodeName), self::BUANG_TOTAL, true)) {
                $anak->parentNode?->removeChild($anak);

                continue;
            }

            static::bersihkan($anak);

            $tag = strtolower($anak->nodeName);
            $tag = self::GANTI[$tag] ?? $tag;

            if (! array_key_exists($tag, self::IZIN)) {
                static::lepas($anak);

                continue;
            }

            if ($tag !== strtolower($anak->nodeName)) {
                $anak = static::gantiTag($anak, $tag);
            }

            static::saringAtribut($anak, self::IZIN[$tag]);
        }
    }

    /** Buang tag-nya, pertahankan isinya di posisi yang sama. */
    private static function lepas(DOMElement $elemen): void
    {
        $induk = $elemen->parentNode;

        if (! $induk) {
            return;
        }

        foreach (iterator_to_array($elemen->childNodes) as $anak) {
            $induk->insertBefore($anak, $elemen);
        }

        $induk->removeChild($elemen);
    }

    private static function gantiTag(DOMElement $elemen, string $tagBaru): DOMElement
    {
        $baru = $elemen->ownerDocument->createElement($tagBaru);

        foreach (iterator_to_array($elemen->attributes) as $atribut) {
            $baru->setAttribute($atribut->nodeName, $atribut->nodeValue);
        }

        foreach (iterator_to_array($elemen->childNodes) as $anak) {
            $baru->appendChild($anak);
        }

        $elemen->parentNode?->replaceChild($baru, $elemen);

        return $baru;
    }

    /**
     * @param  array<int, string>  $diizinkan
     */
    private static function saringAtribut(DOMElement $elemen, array $diizinkan): void
    {
        foreach (iterator_to_array($elemen->attributes) as $atribut) {
            $nama = strtolower($atribut->nodeName);

            if (! in_array($nama, $diizinkan, true)) {
                $elemen->removeAttribute($atribut->nodeName);

                continue;
            }

            if ($nama === 'href' && ! static::tautanAman($atribut->nodeValue)) {
                $elemen->removeAttribute($atribut->nodeName);
            }
        }

        // Tautan keluar dibuka di tab baru, dan rel-nya dipasang supaya
        // halaman tujuan tidak bisa menyetir tab asalnya.
        if (strtolower($elemen->nodeName) === 'a' && $elemen->hasAttribute('href')) {
            $elemen->setAttribute('target', '_blank');
            $elemen->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function tautanAman(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        // Tautan relatif dan jangkar tidak punya skema; itu aman.
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $skema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($skema, self::SKEMA, true);
    }
}
