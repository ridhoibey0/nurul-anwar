<?php

namespace App\Providers;

use App\Models\Pengaturan;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // View paginasi bawaan ada di vendor/ dan tidak dipindai Tailwind v4,
        // jadi dipakai view sendiri di resources/views/pagination.
        Paginator::defaultView('pagination.kustom');
        Paginator::defaultSimpleView('pagination.kustom');

        // Aktifkan dengan FORCE_HTTPS=true di .env bila hosting sudah memakai SSL.
        if (filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }

        // Tahun ajaran & status buka/tutup PPDB diatur dari panel admin.
        // Nilai di config/ppdb.php (dan .env) jadi nilai bawaan bila tabel
        // pengaturan masih kosong atau belum dimigrasikan.
        //
        // Dilewati saat config:cache supaya nilai dari database tidak ikut
        // terbekukan ke dalam berkas cache config.
        if (! $this->app->runningConsoleCommand('config:cache', 'optimize')) {
            Pengaturan::terapkanKeConfig();
        }
    }
}
