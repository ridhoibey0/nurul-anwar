import Alpine from 'alpinejs';

/**
 * Formulir PPDB bertahap.
 *
 * Validasi bawaan peramban dijalankan per langkah supaya isian wajib di
 * langkah sebelumnya tidak terlewat — dan supaya peramban tidak mencoba
 * menyorot field yang sedang tersembunyi saat formulir dikirim.
 */
Alpine.data('formulirPendaftaran', (langkahAwal = 1) => ({
    langkah: langkahAwal,
    mengirim: false,
    judulLangkah: {
        1: 'Data Calon Siswa',
        2: 'Alamat & Riwayat',
        3: 'Orang Tua / Wali',
        4: 'Berkas & Konfirmasi',
    },

    maju() {
        if (!this.langkahValid()) {
            return;
        }

        if (this.langkah < 4) {
            this.langkah += 1;
            this.keAtas();
        }
    },

    mundur() {
        if (this.langkah > 1) {
            this.langkah -= 1;
            this.keAtas();
        }
    },

    langkahValid() {
        const seksi = this.$refs['langkah' + this.langkah];

        if (!seksi) {
            return true;
        }

        for (const kolom of seksi.querySelectorAll('input, select, textarea')) {
            if (!kolom.checkValidity()) {
                kolom.reportValidity();
                return false;
            }
        }

        return true;
    },

    keAtas() {
        const atas = this.$refs.wadah.getBoundingClientRect().top + window.scrollY - 110;
        window.scrollTo({ top: atas, behavior: 'smooth' });
    },
}));

window.Alpine = Alpine;
Alpine.start();

/**
 * Memunculkan elemen ber-class "muncul" saat masuk ke layar.
 * Tanpa JS aktif, CSS fallback di bawah tetap membuat konten terlihat.
 */
document.addEventListener('DOMContentLoaded', () => {
    const target = document.querySelectorAll('.muncul');

    if (!('IntersectionObserver' in window)) {
        target.forEach((el) => el.classList.add('tampil'));
        return;
    }

    const pengamat = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('tampil');
                    pengamat.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );

    target.forEach((el) => pengamat.observe(el));
});
