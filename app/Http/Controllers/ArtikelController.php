<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index(Request $request): View
    {
        return view('artikel.index', [
            'daftar' => Artikel::query()
                ->terbit()
                ->cari($request->string('q')->toString())
                ->terbaru()
                ->paginate(9)
                ->withQueryString(),
            'kata' => $request->string('q')->toString(),
        ]);
    }

    public function show(string $slug): View
    {
        // Dicari lewat scope terbit, bukan route binding, supaya draf dan
        // artikel terjadwal tidak bisa dibuka lewat URL.
        $artikel = Artikel::query()
            ->terbit()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('artikel.show', [
            'artikel' => $artikel,
            'lainnya' => Artikel::query()
                ->terbit()
                ->whereKeyNot($artikel->id)
                ->terbaru()
                ->limit(3)
                ->get(),
        ]);
    }
}
