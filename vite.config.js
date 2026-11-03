import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Hanya dimuat di halaman tulis artikel, lihat admin/artikel/form.
                'resources/js/editor.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
