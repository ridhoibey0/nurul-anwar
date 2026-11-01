<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Mengganti email, nama, dan sandi akun panitia.
 *
 * Sengaja dibuat sebagai perintah tersendiri, bukan diserahkan ke tinker:
 * User::where(...)->update(['password' => '...']) menyimpan sandi MENTAH
 * karena Builder::update() memanggil query builder dasar dan melewati cast
 * 'hashed' di model. Di sini sandinya diisikan ke model lalu disimpan, jadi
 * cast-nya berjalan dan sandi selalu ter-hash.
 */
class AkunAdminCommand extends Command
{
    protected $signature = 'admin:akun
        {--email= : Email baru untuk akun ini}
        {--nama= : Nama yang tampil di panel}
        {--sandi= : Sandi baru (hindari di server bersama: tercatat di riwayat shell)}
        {--akun= : Email akun yang ingin diubah, bila ada lebih dari satu}
        {--baru : Buat akun panitia baru, bukan mengubah yang sudah ada}';

    protected $description = 'Ganti email, nama, atau sandi akun panitia PPDB';

    public function handle(): int
    {
        $akun = $this->option('baru') ? new User() : $this->pilihAkun();

        if ($akun === null) {
            return self::FAILURE;
        }

        $email = $this->option('email')
            ?: $this->ask('Email', $akun->email ?: null);

        $nama = $this->option('nama')
            ?: $this->ask('Nama yang tampil di panel', $akun->name ?: 'Panitia PPDB');

        $sandi = $this->option('sandi') ?: $this->tanyaSandi($akun->exists);

        // false berarti ulangan sandinya tidak cocok: batalkan seluruhnya,
        // jangan diam-diam menyimpan email baru dengan sandi lama.
        if ($sandi === false) {
            return self::FAILURE;
        }

        $galat = Validator::make(
            ['email' => $email, 'nama' => $nama, 'sandi' => $sandi],
            [
                'email' => [
                    'required', 'email:rfc', 'max:120',
                    // Email boleh tetap sama dengan milik akun ini sendiri.
                    'unique:users,email'.($akun->exists ? ','.$akun->id : ''),
                ],
                'nama' => ['required', 'string', 'max:120'],
                'sandi' => [$akun->exists ? 'nullable' : 'required', 'string', 'min:8'],
            ],
            [
                'email.unique' => 'Email itu sudah dipakai akun panitia lain.',
                'sandi.min' => 'Sandi minimal 8 karakter.',
                'sandi.required' => 'Akun baru harus diberi sandi.',
            ],
            [
                'email' => 'email',
                'nama' => 'nama',
                'sandi' => 'sandi',
            ],
        )->errors();

        if ($galat->isNotEmpty()) {
            foreach ($galat->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $akun->email = $email;
        $akun->name = $nama;

        if (filled($sandi)) {
            // Cast 'password' => 'hashed' di model User berjalan saat save().
            $akun->password = $sandi;
        }

        $akun->save();

        $this->newLine();
        $this->info($akun->wasRecentlyCreated ? 'Akun panitia dibuat.' : 'Akun panitia diperbarui.');
        $this->line('  Email : '.$akun->email);
        $this->line('  Nama  : '.$akun->name);
        $this->line('  Sandi : '.(filled($sandi) ? 'diganti' : 'tidak diubah'));

        return self::SUCCESS;
    }

    private function pilihAkun(): ?User
    {
        if ($email = $this->option('akun')) {
            $akun = User::query()->where('email', $email)->first();

            if (! $akun) {
                $this->error('Tidak ada akun panitia dengan email '.$email.'.');

                return null;
            }

            return $akun;
        }

        $daftar = User::query()->orderBy('id')->get();

        if ($daftar->isEmpty()) {
            $this->warn('Belum ada akun panitia. Membuat yang pertama.');

            return new User();
        }

        if ($daftar->count() === 1) {
            return $daftar->first();
        }

        $pilihan = $this->choice(
            'Akun mana yang ingin diubah?',
            $daftar->pluck('email')->all(),
        );

        return $daftar->firstWhere('email', $pilihan);
    }

    /**
     * @return string|null|false sandi baru, null bila dibiarkan, false bila
     *                           ulangannya tidak cocok dan harus dibatalkan
     */
    private function tanyaSandi(bool $akunSudahAda): string|null|false
    {
        $sandi = $this->secret($akunSudahAda
            ? 'Sandi baru (kosongkan bila tidak ingin diganti)'
            : 'Sandi');

        if (blank($sandi)) {
            return null;
        }

        if ($sandi !== $this->secret('Ulangi sandi')) {
            $this->error('Sandi tidak sama. Tidak ada yang diubah.');

            return false;
        }

        return $sandi;
    }
}
