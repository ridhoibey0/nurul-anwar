"""
Pembuat ERD basis data PPDB RA Nurul Anwar.

Menghasilkan dua berkas sekaligus dari satu sumber yang sama, supaya gambar
untuk laporan tidak pernah berbeda dari berkas yang bisa disunting:

    erd.drawio   dibuka / disunting di https://app.diagrams.net
    erd.png      untuk ditempel ke laporan (skala 2x, siap cetak)

Jalankan dari folder ini:

    pip install pillow
    python buat-erd.py

Skema di bawah mengikuti database/migrations/. Kalau ada migrasi baru,
perbarui daftar TABEL lalu jalankan ulang.

Tabel bawaan framework (cache, cache_locks, jobs, job_batches, failed_jobs,
sessions, password_reset_tokens) sengaja tidak digambar: itu infrastruktur
Laravel, bukan bagian rancangan sistem PPDB.
"""

from pathlib import Path
from xml.sax.saxutils import escape

from PIL import Image, ImageDraw, ImageFont

# ---------------------------------------------------------------------------
# Skema
# ---------------------------------------------------------------------------
# Tiap kolom: (penanda, nama, tipe)  -- penanda: 'PK', 'FKn', atau ''

TABEL = {
    "jenjang": {
        "x": 40, "y": 40, "lebar": 290,
        "kolom": [
            ("PK", "id",         "bigint(20) NOT NULL"),
            ("",   "kode",       "varchar(16) NOT NULL"),
            ("",   "nama",       "varchar(100) NOT NULL"),
            ("",   "usia",       "varchar(40) NULL"),
            ("",   "kuota",      "int(10) NOT NULL default 0"),
            ("",   "urutan",     "int(10) NOT NULL default 0"),
            ("",   "created_at", "timestamp NULL"),
            ("",   "updated_at", "timestamp NULL"),
        ],
    },
    "status_pendaftaran": {
        "x": 40, "y": 280, "lebar": 290,
        "kolom": [
            ("PK", "id",         "bigint(20) NOT NULL"),
            ("",   "kode",       "varchar(24) NOT NULL"),
            ("",   "label",      "varchar(60) NOT NULL"),
            ("",   "warna",      "varchar(20) NOT NULL"),
            ("",   "urutan",     "int(10) NOT NULL default 0"),
            ("",   "created_at", "timestamp NULL"),
            ("",   "updated_at", "timestamp NULL"),
        ],
    },
    "alamat": {
        "x": 40, "y": 500, "lebar": 290,
        "kolom": [
            ("PK", "id",         "bigint(20) NOT NULL"),
            ("",   "jalan",      "text NOT NULL"),
            ("",   "kelurahan",  "varchar(80) NULL"),
            ("",   "kecamatan",  "varchar(80) NULL"),
            ("",   "kota",       "varchar(80) NULL"),
            ("",   "kode_pos",   "varchar(10) NULL"),
            ("",   "created_at", "timestamp NULL"),
            ("",   "updated_at", "timestamp NULL"),
        ],
    },
    "users": {
        "x": 40, "y": 760, "lebar": 290,
        "kolom": [
            ("PK", "id",                "bigint(20) NOT NULL"),
            ("",   "name",              "varchar(255) NOT NULL"),
            ("",   "email",             "varchar(255) NOT NULL"),
            ("",   "email_verified_at", "timestamp NULL"),
            ("",   "password",          "varchar(255) NOT NULL"),
            ("",   "remember_token",    "varchar(100) NULL"),
            ("",   "created_at",        "timestamp NULL"),
            ("",   "updated_at",        "timestamp NULL"),
        ],
    },
    "pendaftaran_siswa": {
        "x": 430, "y": 40, "lebar": 330,
        "kolom": [
            ("PK",  "id",                "bigint(20) NOT NULL"),
            ("",    "nomor_pendaftaran", "varchar(32) NOT NULL"),
            ("",    "tahun_ajaran",      "varchar(16) NOT NULL"),
            ("FK1", "jenjang_id",        "bigint(20) NOT NULL"),
            ("",    "nama_lengkap",      "varchar(255) NOT NULL"),
            ("",    "nama_panggilan",    "varchar(60) NULL"),
            ("",    "jenis_kelamin",     "char(1) NOT NULL"),
            ("",    "tempat_lahir",      "varchar(80) NOT NULL"),
            ("",    "tanggal_lahir",     "date NOT NULL"),
            ("",    "nik",               "varchar(16) NULL"),
            ("",    "anak_ke",           "tinyint(3) NULL"),
            ("",    "jumlah_saudara",    "tinyint(3) NULL"),
            ("",    "agama",             "varchar(32) NOT NULL"),
            ("FK2", "alamat_id",         "bigint(20) NULL"),
            ("",    "asal_sekolah",      "varchar(255) NULL"),
            ("",    "riwayat_kesehatan", "text NULL"),
            ("",    "whatsapp",          "varchar(24) NOT NULL"),
            ("",    "email",             "varchar(255) NULL"),
            ("",    "catatan_orang_tua", "text NULL"),
            ("FK3", "status_id",         "bigint(20) NOT NULL"),
            ("",    "catatan_admin",     "text NULL"),
            ("",    "diverifikasi_pada", "timestamp NULL"),
            ("FK4", "diverifikasi_oleh", "bigint(20) NULL"),
            ("",    "ip_pendaftar",      "varchar(45) NULL"),
            ("",    "created_at",        "timestamp NULL"),
            ("",    "updated_at",        "timestamp NULL"),
        ],
    },
    "pendaftaran_orang_tua": {
        "x": 880, "y": 40, "lebar": 310,
        "kolom": [
            ("PK",  "id",             "bigint(20) NOT NULL"),
            ("FK1", "pendaftaran_id", "bigint(20) NOT NULL"),
            ("",    "peran",          "varchar(16) NOT NULL"),
            ("",    "nama",           "varchar(255) NOT NULL"),
            ("",    "nik",            "varchar(16) NULL"),
            ("",    "pendidikan",     "varchar(32) NULL"),
            ("",    "pekerjaan",      "varchar(80) NULL"),
            ("",    "penghasilan",    "varchar(40) NULL"),
            ("",    "telepon",        "varchar(24) NULL"),
            ("",    "hubungan",       "varchar(40) NULL"),
            ("",    "created_at",     "timestamp NULL"),
            ("",    "updated_at",     "timestamp NULL"),
        ],
    },
    "pendaftaran_berkas": {
        "x": 880, "y": 340, "lebar": 310,
        "kolom": [
            ("PK",  "id",             "bigint(20) NOT NULL"),
            ("FK1", "pendaftaran_id", "bigint(20) NOT NULL"),
            ("",    "jenis",          "varchar(16) NOT NULL"),
            ("",    "path",           "varchar(255) NOT NULL"),
            ("",    "created_at",     "timestamp NULL"),
            ("",    "updated_at",     "timestamp NULL"),
        ],
    },
    "galeri": {
        "x": 880, "y": 540, "lebar": 310,
        "kolom": [
            ("PK", "id",         "bigint(20) NOT NULL"),
            ("",   "judul",      "varchar(160) NULL"),
            ("",   "gambar",     "varchar(255) NOT NULL"),
            ("",   "urutan",     "int(10) NOT NULL default 0"),
            ("",   "tampil",     "tinyint(1) NOT NULL default 1"),
            ("",   "created_at", "timestamp NULL"),
            ("",   "updated_at", "timestamp NULL"),
        ],
    },
    "pengaturan": {
        "x": 880, "y": 760, "lebar": 310,
        "kolom": [
            ("PK", "id",         "bigint(20) NOT NULL"),
            ("",   "kunci",      "varchar(64) NOT NULL"),
            ("",   "nilai",      "text NULL"),
            ("",   "created_at", "timestamp NULL"),
            ("",   "updated_at", "timestamp NULL"),
        ],
    },
    "artikel": {
        "x": 430, "y": 760, "lebar": 330,
        "kolom": [
            ("PK",  "id",          "bigint(20) NOT NULL"),
            ("",    "judul",       "varchar(255) NOT NULL"),
            ("",    "slug",        "varchar(191) NOT NULL"),
            ("",    "kategori",    "varchar(80) NULL"),
            ("",    "ringkasan",   "text NOT NULL"),
            ("",    "isi",         "longtext NOT NULL"),
            ("",    "gambar",      "varchar(255) NULL"),
            ("",    "status",      "varchar(16) NOT NULL"),
            ("",    "terbit_pada", "timestamp NULL"),
            ("FK1", "penulis_id",  "bigint(20) NULL"),
            ("",    "created_at",  "timestamp NULL"),
            ("",    "updated_at",  "timestamp NULL"),
        ],
    },
}

# (tabel_anak, kolom_fk, tabel_induk, kolom_pk, sisi, wajib, lajur)
#
# sisi  : 'kanan' = tabel anak ada di kiri induknya
#         'kiri'  = tabel anak ada di kanan induknya
# wajib : True  = foreign key NOT NULL, ujung induk digambar "tepat satu"
#         False = foreign key nullable, ujung induk digambar "nol atau satu"
# lajur : posisi x ruas tegak garisnya, supaya beberapa garis yang berangkat
#         ke arah sama tidak saling menimpa
RELASI = [
    ("pendaftaran_siswa",     "jenjang_id",        "jenjang",            "id", "kiri", True,  345),
    ("pendaftaran_siswa",     "alamat_id",         "alamat",             "id", "kiri", False, 370),
    ("pendaftaran_siswa",     "status_id",         "status_pendaftaran", "id", "kiri", True,  395),
    ("pendaftaran_siswa",     "diverifikasi_oleh", "users",              "id", "kiri", False, 420),
    ("artikel",               "penulis_id",        "users",              "id", "kiri", False, 395),
    ("pendaftaran_orang_tua", "pendaftaran_id",    "pendaftaran_siswa",  "id", "kiri", True,  800),
    ("pendaftaran_berkas",    "pendaftaran_id",    "pendaftaran_siswa",  "id", "kiri", True,  830),
]

# ---------------------------------------------------------------------------
# Ukuran & warna
# ---------------------------------------------------------------------------

TINGGI_KEPALA = 26
TINGGI_BARIS = 20
LEBAR_PENANDA = 44

# Mengikuti gaya ERD cetak: kotak putih bergaris tipis, tanpa blok warna.
# Yang membedakan baris cuma tebal dan garis bawah teksnya.
WARNA_GARIS_TABEL = "#8C8C8C"
WARNA_ISI_KEPALA = "#FFFFFF"
WARNA_TEKS_KEPALA = "#1F1F1F"
WARNA_TEKS_PK = "#1F4E79"
WARNA_TEKS_KOLOM = "#2E74B5"
WARNA_TEKS_PENANDA = "#1F1F1F"
WARNA_RELASI = "#595959"

MARGIN = 40
SKALA_PNG = 2


def tinggi(nama: str) -> int:
    return TINGGI_KEPALA + len(TABEL[nama]["kolom"]) * TINGGI_BARIS


def baris_y(nama: str, indeks: int) -> int:
    """Titik tengah vertikal sebuah baris, dalam koordinat kanvas."""
    return TABEL[nama]["y"] + TINGGI_KEPALA + indeks * TINGGI_BARIS + TINGGI_BARIS // 2


def cari_kolom(nama_tabel: str, nama_kolom: str) -> int:
    for i, (_, kol, _) in enumerate(TABEL[nama_tabel]["kolom"]):
        if kol == nama_kolom:
            return i
    raise ValueError(f"{nama_tabel}.{nama_kolom} tidak ada di skema")


def kanvas() -> tuple[int, int]:
    kanan = max(t["x"] + t["lebar"] for t in TABEL.values())
    bawah = max(t["y"] + tinggi(n) for n, t in TABEL.items())
    return kanan + MARGIN, bawah + MARGIN


# ---------------------------------------------------------------------------
# Keluaran draw.io
# ---------------------------------------------------------------------------

def tulis_drawio(tujuan: Path) -> None:
    sel: list[str] = []

    for nama, t in TABEL.items():
        tid = nama
        gaya_tabel = (
            "shape=table;startSize={};container=1;collapsible=0;childLayout=tableLayout;"
            "fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;html=1;"
            "fillColor={};strokeColor={};fontColor={};fontSize=12;"
        ).format(TINGGI_KEPALA, WARNA_ISI_KEPALA, WARNA_GARIS_TABEL, WARNA_TEKS_KEPALA)

        sel.append(
            f'<mxCell id="{tid}" value="{escape(nama)}" style="{gaya_tabel}" vertex="1" parent="1">'
            f'<mxGeometry x="{t["x"]}" y="{t["y"]}" width="{t["lebar"]}" height="{tinggi(nama)}" as="geometry"/>'
            f'</mxCell>'
        )

        for i, (penanda, kolom, tipe) in enumerate(t["kolom"]):
            rid = f"{tid}-r{i}"
            # Garis pemisah di bawah blok primary key saja, seperti ERD cetak.
            bawah = 1 if penanda == "PK" else 0

            gaya_baris = (
                "shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;"
                "fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];"
                "portConstraint=eastwest;top=0;left=0;right=0;bottom=0;"
            )
            sel.append(
                f'<mxCell id="{rid}" value="" style="{gaya_baris}" vertex="1" parent="{tid}">'
                f'<mxGeometry y="{TINGGI_KEPALA + i * TINGGI_BARIS}" width="{t["lebar"]}" '
                f'height="{TINGGI_BARIS}" as="geometry"/></mxCell>'
            )

            gaya_penanda = (
                "shape=partialRectangle;connectable=0;fillColor=none;align=center;"
                "verticalAlign=middle;overflow=hidden;html=1;fontSize=10;fontStyle=1;"
                f"fontColor={WARNA_TEKS_PENANDA};strokeColor={WARNA_GARIS_TABEL};"
                f"top=0;left=0;right=1;bottom={bawah};"
            )
            sel.append(
                f'<mxCell id="{rid}-c0" value="{escape(penanda)}" style="{gaya_penanda}" '
                f'vertex="1" parent="{rid}">'
                f'<mxGeometry width="{LEBAR_PENANDA}" height="{TINGGI_BARIS}" as="geometry"/></mxCell>'
            )

            # fontStyle 5 = tebal + garis bawah, penanda baku untuk primary key.
            gaya_isi = (
                "shape=partialRectangle;connectable=0;fillColor=none;align=left;"
                "verticalAlign=middle;spacingLeft=6;overflow=hidden;html=1;fontSize=10;"
                f"fontStyle={5 if penanda == 'PK' else 0};"
                f"fontColor={WARNA_TEKS_PK if penanda == 'PK' else WARNA_TEKS_KOLOM};"
                f"strokeColor={WARNA_GARIS_TABEL};top=0;left=0;right=0;bottom={bawah};"
            )
            sel.append(
                f'<mxCell id="{rid}-c1" value="{escape(kolom + " " + tipe)}" style="{gaya_isi}" '
                f'vertex="1" parent="{rid}">'
                f'<mxGeometry x="{LEBAR_PENANDA}" width="{t["lebar"] - LEBAR_PENANDA}" '
                f'height="{TINGGI_BARIS}" as="geometry"/></mxCell>'
            )

    for n, (anak, fk, induk, pk, sisi, wajib, lajur) in enumerate(RELASI):
        sumber = f"{anak}-r{cari_kolom(anak, fk)}"
        tujuan_id = f"{induk}-r{cari_kolom(induk, pk)}"
        keluar, masuk = (1, 0) if sisi == "kanan" else (0, 1)

        # startArrow di ujung anak, endArrow di ujung induk.
        # ERzeroToMany = lingkaran + kaki gagak (nol atau banyak)
        # ERmandOne    = dua garis (tepat satu), untuk foreign key NOT NULL
        # ERzeroToOne  = lingkaran + satu garis, untuk foreign key nullable
        ujung_induk = "ERmandOne" if wajib else "ERzeroToOne"

        gaya = (
            "edgeStyle=entityRelationEdgeStyle;rounded=0;html=1;exitDx=0;exitDy=0;"
            f"exitX={keluar};exitY=0.5;entryX={masuk};entryY=0.5;entryDx=0;entryDy=0;"
            f"startArrow=ERzeroToMany;startFill=0;endArrow={ujung_induk};endFill=0;"
            f"strokeColor={WARNA_RELASI};strokeWidth=1;"
        )

        # Titik belok ditulis eksplisit supaya ruas tegaknya berada di lajur
        # yang sudah ditentukan, tidak saling menimpa dengan relasi lain.
        y1 = baris_y(anak, cari_kolom(anak, fk))
        y2 = baris_y(induk, cari_kolom(induk, pk))

        sel.append(
            f'<mxCell id="rel{n}" style="{gaya}" edge="1" parent="1" '
            f'source="{sumber}" target="{tujuan_id}">'
            f'<mxGeometry relative="1" as="geometry">'
            f'<Array as="points">'
            f'<mxPoint x="{lajur}" y="{y1}"/><mxPoint x="{lajur}" y="{y2}"/>'
            f'</Array></mxGeometry></mxCell>'
        )

    isi_sel = "\n        ".join(sel)
    xml = f'''<mxfile host="app.diagrams.net" type="device">
  <diagram name="ERD PPDB RA Nurul Anwar" id="erd-ppdb">
    <mxGraphModel dx="1400" dy="900" grid="1" gridSize="10" guides="1" tooltips="1"
                  connect="1" arrows="1" fold="1" page="1" pageScale="1"
                  pageWidth="1654" pageHeight="1169" math="0" shadow="0">
      <root>
        <mxCell id="0"/>
        <mxCell id="1" parent="0"/>
        {isi_sel}
      </root>
    </mxGraphModel>
  </diagram>
</mxfile>
'''
    tujuan.write_text(xml, encoding="utf-8")


# ---------------------------------------------------------------------------
# Keluaran PNG
# ---------------------------------------------------------------------------

def font(ukuran: int, tebal: bool = False):
    nama = "C:/Windows/Fonts/segoeui{}.ttf".format("b" if tebal else "")
    try:
        return ImageFont.truetype(nama, ukuran)
    except OSError:
        return ImageFont.truetype("C:/Windows/Fonts/arial.ttf", ukuran)


def tulis_png(tujuan: Path) -> None:
    lebar, tinggi_kanvas = kanvas()
    s = SKALA_PNG
    img = Image.new("RGB", (lebar * s, tinggi_kanvas * s), "white")
    d = ImageDraw.Draw(img)

    f_kepala = font(12 * s, tebal=True)
    f_isi = font(10 * s)
    f_isi_tebal = font(10 * s, tebal=True)
    f_penanda = font(9 * s, tebal=True)

    def kotak(x1, y1, x2, y2, isi=None, garis=WARNA_GARIS_TABEL, tebal=1):
        d.rectangle([x1 * s, y1 * s, x2 * s, y2 * s], fill=isi, outline=garis, width=tebal * s)

    def garis(titik, warna=WARNA_GARIS_TABEL, tebal=1):
        d.line([(x * s, y * s) for x, y in titik], fill=warna, width=tebal * s)

    def teks(x, y, isi, fnt, warna, tengah=False, garis_bawah=False):
        kiri, atas, kanan, bawah = d.textbbox((0, 0), isi, font=fnt)
        px = x * s - (kanan - kiri) // 2 if tengah else x * s
        py = y * s - (bawah - atas) // 2 - atas
        d.text((px, py), isi, font=fnt, fill=warna)
        if garis_bawah:
            yb = py + (bawah - atas) + 2 * s
            d.line([(px, yb), (px + (kanan - kiri), yb)], fill=warna, width=max(1, s // 2))

    # Tabel
    for nama, t in TABEL.items():
        x, y, w = t["x"], t["y"], t["lebar"]
        h = tinggi(nama)

        kotak(x, y, x + w, y + h, isi="white")
        kotak(x, y, x + w, y + TINGGI_KEPALA, isi=WARNA_ISI_KEPALA)
        teks(x + w / 2, y + TINGGI_KEPALA / 2, nama, f_kepala, WARNA_TEKS_KEPALA, tengah=True)

        for i, (penanda, kolom, tipe) in enumerate(t["kolom"]):
            atas = y + TINGGI_KEPALA + i * TINGGI_BARIS
            tengah_y = atas + TINGGI_BARIS / 2

            # Garis pemisah antara kolom penanda (PK/FK) dan nama kolom.
            garis([(x + LEBAR_PENANDA, atas), (x + LEBAR_PENANDA, atas + TINGGI_BARIS)])

            if penanda:
                teks(x + LEBAR_PENANDA / 2, tengah_y, penanda, f_penanda,
                     WARNA_TEKS_PENANDA, tengah=True)

            adalah_pk = penanda == "PK"
            teks(x + LEBAR_PENANDA + 6, tengah_y, f"{kolom} {tipe}",
                 f_isi_tebal if adalah_pk else f_isi,
                 WARNA_TEKS_PK if adalah_pk else WARNA_TEKS_KOLOM,
                 garis_bawah=adalah_pk)

            if adalah_pk:
                bawah_pk = atas + TINGGI_BARIS
                garis([(x, bawah_pk), (x + w, bawah_pk)])

    # Relasi
    for anak, fk, induk, pk, sisi, wajib, lajur in RELASI:
        ta, ti = TABEL[anak], TABEL[induk]
        y1 = baris_y(anak, cari_kolom(anak, fk))
        y2 = baris_y(induk, cari_kolom(induk, pk))

        if sisi == "kanan":          # anak di kiri induk
            x1, x2 = ta["x"] + ta["lebar"], ti["x"]
        else:                        # anak di kanan induk
            x1, x2 = ta["x"], ti["x"] + ti["lebar"]

        garis([(x1, y1), (lajur, y1), (lajur, y2), (x2, y2)], warna=WARNA_RELASI)

        kaki_gagak(d, s, x1, y1, 1 if x1 < lajur else -1)

        if wajib:
            tepat_satu(d, s, x2, y2, 1 if x2 < lajur else -1)
        else:
            nol_atau_satu(d, s, x2, y2, 1 if x2 < lajur else -1)

    img.save(tujuan, "PNG", dpi=(96 * SKALA_PNG, 96 * SKALA_PNG))


def kaki_gagak(d, s, x, y, arah):
    """Lingkaran + kaki gagak: nol atau banyak."""
    r = 3
    pusat = x + arah * (r + 9)
    d.ellipse([(pusat - r) * s, (y - r) * s, (pusat + r) * s, (y + r) * s],
              outline=WARNA_RELASI, fill="white", width=max(1, s))

    pangkal = x + arah * 9
    for dy in (-6, 0, 6):
        d.line([(x * s, y * s), (pangkal * s, (y + dy) * s)],
               fill=WARNA_RELASI, width=max(1, s))


def nol_atau_satu(d, s, x, y, arah):
    """Lingkaran + satu garis: foreign key-nya nullable."""
    r = 3
    pusat = x + arah * (r + 9)
    d.ellipse([(pusat - r) * s, (y - r) * s, (pusat + r) * s, (y + r) * s],
              outline=WARNA_RELASI, fill="white", width=max(1, s))

    palang = x + arah * 7
    d.line([(palang * s, (y - 6) * s), (palang * s, (y + 6) * s)],
           fill=WARNA_RELASI, width=max(1, s))


def tepat_satu(d, s, x, y, arah):
    """Dua garis: foreign key-nya NOT NULL, jadi induknya selalu ada."""
    for jarak in (6, 11):
        px = x + arah * jarak
        d.line([(px * s, (y - 6) * s), (px * s, (y + 6) * s)],
               fill=WARNA_RELASI, width=max(1, s))


if __name__ == "__main__":
    folder = Path(__file__).parent
    tulis_drawio(folder / "erd.drawio")
    tulis_png(folder / "erd.png")

    lebar, tinggi_kanvas = kanvas()
    print(f"erd.drawio dan erd.png dibuat ({lebar * SKALA_PNG} x {tinggi_kanvas * SKALA_PNG} piksel)")
    print(f"{len(TABEL)} tabel, {len(RELASI)} relasi")
