<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Response;

/**
 * Sitemap dan robots.txt dibuat lewat rute, bukan berkas statis di public/,
 * supaya alamatnya ikut APP_URL dan artikel baru langsung masuk tanpa ada
 * yang perlu menyunting berkas apa pun.
 */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $halaman = [
            ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('ppdb.info'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => route('ppdb.create'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => route('blog.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('ppdb.cek'), 'changefreq' => 'yearly', 'priority' => '0.4'],
        ];

        // Halaman konfirmasi pendaftaran sengaja tidak ada di sini: isinya
        // nomor pendaftaran anak dan sudah ditandai noindex.
        foreach (Artikel::query()->terbit()->terbaru()->get() as $artikel) {
            $halaman[] = [
                'loc' => route('blog.show', $artikel->slug),
                'lastmod' => $artikel->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $baris = [];

        foreach ($halaman as $item) {
            $isi = '<loc>'.e($item['loc']).'</loc>';

            if (! empty($item['lastmod'])) {
                $isi .= '<lastmod>'.$item['lastmod'].'</lastmod>';
            }

            $isi .= '<changefreq>'.$item['changefreq'].'</changefreq>';
            $isi .= '<priority>'.$item['priority'].'</priority>';

            $baris[] = '  <url>'.$isi.'</url>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .implode("\n", $baris)."\n"
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $baris = [
            'User-agent: *',
            'Disallow: /admin',
            // Halaman konfirmasi memuat nomor pendaftaran anak.
            'Disallow: /ppdb/selesai',
            // Hasil pencarian blog: isinya cuma susunan ulang artikel yang
            // sudah punya halamannya sendiri.
            'Disallow: /blog?q=',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $baris)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
