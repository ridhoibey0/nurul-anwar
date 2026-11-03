/**
 * Editor isi artikel (Trix).
 *
 * Dipisah dari app.js dan hanya dimuat di halaman tulis artikel. Trix sekitar
 * 200 KB; ikut dimuat di seluruh halaman publik, bobot itu memperlambat
 * halaman yang bahkan tidak punya editor -- dan kecepatan muat ikut dinilai
 * mesin pencari.
 */
import 'trix';
import 'trix/dist/trix.css';

// Harus setelah trix.css supaya penyesuaiannya tidak tertimpa.
import '../css/editor.css';

/*
| Lampiran berkas sengaja ditolak: tanpa endpoint unggahan, Trix menanam
| gambar sebagai data URI langsung di dalam HTML, dan satu artikel bisa jadi
| berukuran megabyte di dalam kolom database. Gambar artikel tetap lewat
| isian "Gambar sampul" yang terpisah.
*/
document.addEventListener('trix-file-accept', (event) => {
    event.preventDefault();
});
