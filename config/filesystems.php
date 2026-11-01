<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        | Gambar sampul artikel memang untuk dilihat publik, jadi ditulis
        | langsung ke dalam public/ agar tidak butuh "php artisan storage:link"
        | (symlink sering dimatikan di hosting cPanel).
        */
        'artikel' => [
            'driver' => 'local',
            'root' => public_path('images/artikel'),
            'url' => '/images/artikel',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'galeri' => [
            'driver' => 'local',
            'root' => public_path('images/galeri'),
            'url' => '/images/galeri',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
