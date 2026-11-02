<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Panel Admin') — {{ config('sekolah.nama_pendek') }}</title>

    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-krem font-sans" x-data="{ menu: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 -translate-x-full bg-hijau-900 p-6 transition-transform lg:static lg:translate-x-0"
            :class="menu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-white">
                @if (logo_url())
                    <img src="{{ logo_url() }}" alt="" class="h-8 w-8">
                @endif
                <span class="font-display text-lg">Panel Admin</span>
            </a>

            <nav class="mt-10 grid gap-1">
                @foreach ([
                    ['label' => 'Ringkasan', 'url' => route('admin.dashboard'), 'ikon' => 'dashboard', 'aktif' => request()->routeIs('admin.dashboard')],
                    ['label' => 'Data Pendaftar', 'url' => route('admin.pendaftaran.index'), 'ikon' => 'daftar', 'aktif' => request()->routeIs('admin.pendaftaran.*')],
                    ['label' => 'Galeri Foto', 'url' => route('admin.galeri.index'), 'ikon' => 'foto', 'aktif' => request()->routeIs('admin.galeri.*')],
                    ['label' => 'Blog', 'url' => route('admin.artikel.index'), 'ikon' => 'pensil', 'aktif' => request()->routeIs('admin.artikel.*')],
                    ['label' => 'Identitas Sekolah', 'url' => route('admin.pengaturan.sekolah'), 'ikon' => 'peta', 'aktif' => request()->routeIs('admin.pengaturan.sekolah*')],
                    ['label' => 'Pengaturan PPDB', 'url' => route('admin.pengaturan.edit'), 'ikon' => 'roda-gigi', 'aktif' => request()->routeIs('admin.pengaturan.edit')],
                ] as $tautan)
                    <a
                        href="{{ $tautan['url'] }}"
                        class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-colors {{ $tautan['aktif'] ? 'bg-white/15 text-white' : 'text-hijau-200 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-ikon :nama="$tautan['ikon']" class="h-5 w-5" />
                        {{ $tautan['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="absolute inset-x-6 bottom-6">
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-xs text-hijau-200">Masuk sebagai</p>
                    <p class="mt-0.5 truncate text-sm font-semibold text-white">{{ auth()->user()?->name }}</p>

                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold text-kuning-300 hover:text-kuning-400">
                            <x-ikon nama="keluar" class="h-4 w-4" />
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div
            x-show="menu"
            x-cloak
            x-on:click="menu = false"
            class="fixed inset-0 z-30 bg-tinta/40 lg:hidden"
        ></div>

        {{-- Konten --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-hijau-100 bg-krem/95 px-5 py-4 backdrop-blur sm:px-8">
                <button
                    type="button"
                    x-on:click="menu = ! menu"
                    aria-label="Buka menu"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-tinta hover:bg-hijau-100 lg:hidden"
                >
                    <x-ikon nama="menu" class="h-6 w-6" />
                </button>

                <h1 class="font-display text-xl text-tinta">@yield('title', 'Panel PPDB')</h1>

                <div class="ml-auto hidden items-center gap-2 sm:flex">
                    <span class="rounded-full bg-hijau-100 px-3 py-1 text-xs font-semibold text-hijau-700">
                        Tahun ajaran {{ config('ppdb.tahun_ajaran') }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ config('ppdb.buka') ? 'bg-hijau-700 text-white' : 'bg-koral-100 text-koral-600' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ config('ppdb.buka') ? 'bg-kuning-300' : 'bg-koral-500' }}"></span>
                        {{ config('ppdb.buka') ? 'Pendaftaran dibuka' : 'Pendaftaran ditutup' }}
                    </span>
                </div>
            </header>

            <main class="flex-1 px-5 py-8 sm:px-8">
                @if (session('sukses'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl bg-hijau-100 p-5 text-sm text-hijau-800">
                        <x-ikon nama="centang-lingkaran" class="mt-0.5 h-5 w-5 shrink-0" />
                        {{ session('sukses') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
