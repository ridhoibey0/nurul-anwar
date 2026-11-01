<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Login Panitia — {{ config('sekolah.nama_pendek') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full items-center justify-center bg-hijau-900 px-5 py-12 font-sans">
    <div class="w-full max-w-md">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2.5 text-white">
            <img src="{{ asset('images/kiddies/logo-mark.svg') }}" alt="" class="h-9 w-9">
            <span class="font-display text-xl">{{ config('sekolah.nama_pendek') }}</span>
        </a>

        <div class="mt-8 rounded-[2.5rem] bg-white p-8 bayang-kartu sm:p-10">
            <h1 class="font-display text-2xl text-tinta">Login Panitia PPDB</h1>
            <p class="mt-2 text-sm text-tinta-lembut">
                Halaman ini khusus untuk panitia penerimaan siswa baru.
            </p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 grid gap-5">
                @csrf

                <x-form.input nama="email" label="Alamat email" tipe="email" :wajib="true" autocomplete="username" autofocus />

                <div>
                    <label for="password" class="block text-sm font-semibold text-tinta">
                        Kata sandi <span class="text-koral-500" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-2 block w-full rounded-2xl border-2 bg-white px-4 py-3 text-sm text-tinta transition-colors focus:outline-none focus:ring-0 {{ $errors->has('password') ? 'border-koral-500' : 'border-hijau-100 focus:border-hijau-400' }}"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-koral-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2.5 text-sm text-tinta-lembut">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-2 border-hijau-300 text-hijau-600">
                    Ingat saya di perangkat ini
                </label>

                <x-tombol type="submit" warna="hijau" class="w-full">
                    Masuk
                </x-tombol>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-hijau-200">
            <a href="{{ route('home') }}" class="hover:text-white">&larr; Kembali ke website</a>
        </p>
    </div>
</body>
</html>
